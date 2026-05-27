<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CsvExportService
 *
 * Reusable service for exporting data to CSV files.
 * Used by: Students export, Invoices export, Sales reports, etc.
 *
 * Usage:
 * $csv = app(CsvExportService::class);
 *
 * // Quick download
 * return $csv->download($students, ['ID', 'Name', 'Class'], 'students.csv');
 *
 * // Save to storage (for queued exports)
 * $path = $csv->saveToStorage($data, $headers, 'exports', 'students-2026.csv');
 *
 * // Stream large data (memory-efficient)
 * return $csv->streamDownload($query, $headers, 'large-export.csv');
 */
class CsvExportService
{
    protected FileStorageService $storageService;

    public function __construct(FileStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Export data to CSV string.
     *
     * @param  iterable  $data  Array or Collection of rows
     * @param  array  $headers  Column headers
     * @param  callable|null  $rowMapper  Function to transform each row
     * @return string CSV content
     */
    public function toString(
        iterable $data,
        array $headers,
        ?callable $rowMapper = null
    ): string {
        $output = fopen('php://temp', 'r+');

        // Write BOM for UTF-8 (helps Excel display Bangla correctly)
        fwrite($output, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($output, $headers);

        // Write rows
        foreach ($data as $row) {
            $mappedRow = $rowMapper ? $rowMapper($row) : (array) $row;
            fputcsv($output, $mappedRow);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    /**
     * Save CSV to storage and return path.
     *
     * @param  iterable  $data
     * @param  array  $headers
     * @param  string  $directory
     * @param  string|null  $filename
     * @param  callable|null  $rowMapper
     * @return string Stored path
     */
    public function saveToStorage(
        iterable $data,
        array $headers,
        string $directory,
        ?string $filename = null,
        ?callable $rowMapper = null
    ): string {
        $content = $this->toString($data, $headers, $rowMapper);
        $filename = $filename ?? 'export-' . Str::uuid()->toString() . '.csv';

        if (!str_ends_with(strtolower($filename), '.csv')) {
            $filename .= '.csv';
        }

        return $this->storageService->saveContent($content, $directory, $filename);
    }

    /**
     * Save to tenant-specific storage.
     */
    public function saveToTenantStorage(
        iterable $data,
        array $headers,
        string $directory,
        ?string $filename = null,
        ?callable $rowMapper = null
    ): string {
        $tenantPath = $this->storageService->tenantPath($directory);
        return $this->saveToStorage($data, $headers, $tenantPath, $filename, $rowMapper);
    }

    /**
     * Direct download CSV (small datasets only).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(
        iterable $data,
        array $headers,
        string $filename,
        ?callable $rowMapper = null
    ) {
        $content = $this->toString($data, $headers, $rowMapper);

        if (!str_ends_with(strtolower($filename), '.csv')) {
            $filename .= '.csv';
        }

        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Stream download for large datasets (memory-efficient).
     * Uses chunked queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query  Eloquent query
     * @param  array  $headers
     * @param  string  $filename
     * @param  callable  $rowMapper  Required for streaming
     * @param  int  $chunkSize
     * @return StreamedResponse
     */
    public function streamDownload(
        $query,
        array $headers,
        string $filename,
        callable $rowMapper,
        int $chunkSize = 1000
    ): StreamedResponse {
        if (!str_ends_with(strtolower($filename), '.csv')) {
            $filename .= '.csv';
        }

        return response()->streamDownload(
            function () use ($query, $headers, $rowMapper, $chunkSize) {
                $output = fopen('php://output', 'w');

                // BOM for UTF-8
                fwrite($output, "\xEF\xBB\xBF");

                // Write headers
                fputcsv($output, $headers);

                // Stream chunks
                $query->chunk($chunkSize, function ($rows) use ($output, $rowMapper) {
                    foreach ($rows as $row) {
                        fputcsv($output, $rowMapper($row));
                    }
                });

                fclose($output);
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]
        );
    }

    /**
     * Convert array of arrays to CSV string with custom delimiter.
     */
    public function withCustomDelimiter(
        iterable $data,
        array $headers,
        string $delimiter = ','
    ): string {
        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, $headers, $delimiter);

        foreach ($data as $row) {
            fputcsv($output, (array) $row, $delimiter);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }
}
