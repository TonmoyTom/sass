<?php

declare(strict_types=1);

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * PdfService
 *
 * Reusable service for generating PDFs.
 * Used by: Invoices, Certificates, ID Cards, Reports, Marksheets, etc.
 *
 * Usage:
 * $pdf = app(PdfService::class);
 *
 * // Generate from blade view
 * $pdf->generateFromView('pdfs.invoice', ['invoice' => $invoice]);
 *
 * // Save and get path
 * $path = $pdf->generateAndSave('pdfs.invoice', $data, 'invoices', 'invoice-123.pdf');
 *
 * // Download in browser
 * return $pdf->download('pdfs.invoice', $data, 'invoice-123.pdf');
 *
 * // Stream in browser (preview)
 * return $pdf->stream('pdfs.invoice', $data, 'invoice-123.pdf');
 */
class PdfService
{
    /**
     * Default paper size.
     */
    protected string $paperSize = 'a4';

    /**
     * Default orientation: portrait or landscape.
     */
    protected string $orientation = 'portrait';

    /**
     * FileStorageService instance.
     */
    protected FileStorageService $storageService;

    public function __construct(FileStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Generate PDF from a Blade view.
     *
     * @param  string  $view  Blade view name (e.g., 'pdfs.invoice')
     * @param  array  $data  Data to pass to view
     * @param  array  $options  ['paper' => 'a4', 'orientation' => 'portrait']
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateFromView(string $view, array $data = [], array $options = [])
    {
        $paper = $options['paper'] ?? $this->paperSize;
        $orientation = $options['orientation'] ?? $this->orientation;

        return Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation);
    }

    /**
     * Generate PDF from HTML string.
     *
     * @param  string  $html
     * @param  array  $options
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateFromHtml(string $html, array $options = [])
    {
        $paper = $options['paper'] ?? $this->paperSize;
        $orientation = $options['orientation'] ?? $this->orientation;

        return Pdf::loadHTML($html)
            ->setPaper($paper, $orientation);
    }

    /**
     * Generate PDF and save to storage.
     *
     * @param  string  $view  Blade view name
     * @param  array  $data  View data
     * @param  string  $directory  Storage directory (e.g., 'invoices')
     * @param  string|null  $filename  Filename, or null for auto-generated UUID
     * @param  array  $options
     * @return string Stored file path
     */
    public function generateAndSave(
        string $view,
        array $data,
        string $directory,
        ?string $filename = null,
        array $options = []
    ): string {
        $pdf = $this->generateFromView($view, $data, $options);
        $filename = $filename ?? Str::uuid()->toString() . '.pdf';

        // Ensure .pdf extension
        if (!str_ends_with(strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }

        $content = $pdf->output();

        return $this->storageService->saveContent(
            $content,
            $directory,
            $filename
        );
    }

    /**
     * Generate PDF and save to tenant-specific storage.
     */
    public function generateAndSaveForTenant(
        string $view,
        array $data,
        string $directory,
        ?string $filename = null,
        array $options = []
    ): string {
        $tenantPath = $this->storageService->tenantPath($directory);
        return $this->generateAndSave($view, $data, $tenantPath, $filename, $options);
    }

    /**
     * Generate PDF and download in browser.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(
        string $view,
        array $data,
        string $filename,
        array $options = []
    ) {
        $pdf = $this->generateFromView($view, $data, $options);

        if (!str_ends_with(strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }

        return $pdf->download($filename);
    }

    /**
     * Generate PDF and stream in browser (for preview).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stream(
        string $view,
        array $data,
        string $filename = 'document.pdf',
        array $options = []
    ) {
        $pdf = $this->generateFromView($view, $data, $options);

        if (!str_ends_with(strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }

        return $pdf->stream($filename);
    }

    /**
     * Generate multiple PDFs and combine into one (basic merge).
     * Note: For complex merging, use a dedicated PDF merge library.
     *
     * @param  array  $views  Array of ['view' => 'x', 'data' => []]
     * @param  string  $directory
     * @param  string  $filename
     * @return string Stored path
     */
    public function generateBulk(array $views, string $directory, string $filename): string
    {
        $combinedHtml = '';
        foreach ($views as $index => $item) {
            $html = view($item['view'], $item['data'])->render();
            $combinedHtml .= $html;

            // Page break between documents
            if ($index < count($views) - 1) {
                $combinedHtml .= '<div style="page-break-after: always;"></div>';
            }
        }

        $pdf = $this->generateFromHtml($combinedHtml);
        $content = $pdf->output();

        return $this->storageService->saveContent($content, $directory, $filename);
    }

    /**
     * Set custom paper size.
     */
    public function setPaperSize(string $size): self
    {
        $this->paperSize = $size;
        return $this;
    }

    /**
     * Set custom orientation.
     */
    public function setOrientation(string $orientation): self
    {
        $this->orientation = $orientation;
        return $this;
    }
}
