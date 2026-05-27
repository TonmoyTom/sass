<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

/**
 * ExportReadyNotification
 *
 * Sent to user when their bulk export is ready (or failed).
 * Stored in database (for in-app notifications) and sent via email.
 *
 * Usage:
 * $user->notify(new ExportReadyNotification(
 *     filePath: 'exports/students.csv',
 *     filename: 'students.csv',
 *     success: true
 * ));
 */
class ExportReadyNotification extends Notification
{
    use Queueable;

    public ?string $filePath;
    public string $filename;
    public bool $success;
    public ?string $errorMessage;

    public function __construct(
        ?string $filePath,
        string $filename,
        bool $success = true,
        ?string $errorMessage = null
    ) {
        $this->filePath = $filePath;
        $this->filename = $filename;
        $this->success = $success;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Send via database (in-app) and mail
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if (!$this->success) {
            return (new MailMessage)
                ->error()
                ->subject('Export Failed: ' . $this->filename)
                ->greeting('Hi ' . ($notifiable->name ?? 'there') . ',')
                ->line('Unfortunately, your export failed.')
                ->line('Filename: ' . $this->filename)
                ->line('Error: ' . ($this->errorMessage ?? 'Unknown error'))
                ->line('Please try again or contact support if the issue persists.');
        }

        $downloadUrl = $this->filePath
            ? Storage::disk('public')->url($this->filePath)
            : null;

        $message = (new MailMessage)
            ->success()
            ->subject('Your Export is Ready')
            ->greeting('Hi ' . ($notifiable->name ?? 'there') . ',')
            ->line('Your export "' . $this->filename . '" is ready to download.');

        if ($downloadUrl) {
            $message->action('Download Now', $downloadUrl);
        }

        return $message
            ->line('This link will be valid for 7 days.')
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation for database storage (in-app notification).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'export_ready',
            'success' => $this->success,
            'filename' => $this->filename,
            'file_path' => $this->filePath,
            'download_url' => $this->filePath
                ? Storage::disk('public')->url($this->filePath)
                : null,
            'error_message' => $this->errorMessage,
            'message' => $this->success
                ? "Your export '{$this->filename}' is ready"
                : "Export '{$this->filename}' failed",
            'icon' => $this->success ? 'check-circle' : 'alert-circle',
            'color' => $this->success ? 'green' : 'red',
        ];
    }
}
