<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Notifications\ExportReadyNotification;
use App\Services\CsvExportService;
use App\Services\FileStorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * ProcessBulkExportJob
 *
 * Reusable queue job for processing large data exports in the background.
 * Sends notification to user when export is ready.
 *
 * Usage:
 * ProcessBulkExportJob::dispatch(
 *     userId: auth()->id(),
 *     exportClass: \App\Exports\StudentsExport::class,
 *     exportParams: ['class_id' => 5],
 *     filename: 'students-class-5.csv',
 *     tenantId: tenant()->id ?? null
 * );
 */
class ProcessBulkExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600; // 10 minutes

    /**
     * The user who initiated the export (will be notified).
     */
    public int $userId;

    /**
     * The export class that handles data fetching.
     */
    public string $exportClass;

    /**
     * Parameters to pass to the export class.
     */
    public array $exportParams;

    /**
     * Output filename.
     */
    public string $filename;

    /**
     * Tenant ID (for multi-tenant context restoration).
     */
    public ?string $tenantId;

    /**
     * Storage directory.
     */
    public string $directory;

    public function __construct(
        int $userId,
        string $exportClass,
        array $exportParams = [],
        string $filename = 'export.csv',
        ?string $tenantId = null,
        string $directory = 'exports'
    ) {
        $this->userId = $userId;
        $this->exportClass = $exportClass;
        $this->exportParams = $exportParams;
        $this->filename = $filename;
        $this->tenantId = $tenantId;
        $this->directory = $directory;
    }

    /**
     * Execute the job.
     */
    public function handle(
        CsvExportService $csvService,
        FileStorageService $storageService
    ): void {
        try {
            // Initialize tenancy if this is a tenant export
            if ($this->tenantId && function_exists('tenancy')) {
                tenancy()->initialize($this->tenantId);
            }

            // Verify export class exists
            if (!class_exists($this->exportClass)) {
                throw new \Exception("Export class {$this->exportClass} does not exist");
            }

            // Instantiate export class
            $export = app($this->exportClass, $this->exportParams);

            // Verify export class has required methods
            if (!method_exists($export, 'headers') || !method_exists($export, 'query')) {
                throw new \Exception("Export class must implement headers() and query() methods");
            }

            $headers = $export->headers();
            $query = $export->query();

            // Use rowMapper if defined
            $rowMapper = method_exists($export, 'mapRow')
                ? [$export, 'mapRow']
                : null;

            // Generate CSV in chunks
            $directory = $this->tenantId
                ? $storageService->tenantPath($this->directory)
                : $this->directory;

            // For very large exports, use streaming chunks
            $tempContent = '';
            $output = fopen('php://temp', 'r+');
            fwrite($output, "\xEF\xBB\xBF"); // BOM
            fputcsv($output, $headers);

            $query->chunk(1000, function ($rows) use ($output, $rowMapper) {
                foreach ($rows as $row) {
                    $mapped = $rowMapper ? $rowMapper($row) : (array) $row;
                    fputcsv($output, $mapped);
                }
            });

            rewind($output);
            $content = stream_get_contents($output);
            fclose($output);

            $path = $storageService->saveContent($content, $directory, $this->filename);

            // End tenancy if needed
            if ($this->tenantId && function_exists('tenancy')) {
                tenancy()->end();
            }

            // Notify user
            $this->notifyUser($path);

            Log::info('Bulk export completed', [
                'user_id' => $this->userId,
                'file' => $path,
                'tenant_id' => $this->tenantId,
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk export failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->notifyUserOfFailure($e->getMessage());

            throw $e;
        }
    }

    /**
     * Notify user that export is ready.
     */
    protected function notifyUser(string $filePath): void
    {
        // Find user from central DB (notifications go to central user)
        $user = $this->findUser();

        if (!$user) {
            Log::warning('User not found for notification', ['user_id' => $this->userId]);
            return;
        }

        $user->notify(new ExportReadyNotification(
            filePath: $filePath,
            filename: $this->filename,
            success: true
        ));
    }

    /**
     * Notify user that export failed.
     */
    protected function notifyUserOfFailure(string $error): void
    {
        $user = $this->findUser();

        if (!$user) {
            return;
        }

        $user->notify(new ExportReadyNotification(
            filePath: null,
            filename: $this->filename,
            success: false,
            errorMessage: $error
        ));
    }

    /**
     * Find user (central or tenant scope).
     */
    protected function findUser()
    {
        // Try to find in central DB first
        $userClass = config('auth.providers.users.model', \App\Models\User::class);
        return $userClass::find($this->userId);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessBulkExportJob permanently failed', [
            'user_id' => $this->userId,
            'export_class' => $this->exportClass,
            'error' => $exception->getMessage(),
        ]);

        $this->notifyUserOfFailure($exception->getMessage());
    }
}
