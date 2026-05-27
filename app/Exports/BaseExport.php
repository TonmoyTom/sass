<?php

declare(strict_types=1);

namespace App\Exports;

/**
 * BaseExport
 *
 * Abstract base class for all CSV/Excel exports.
 * Each export class must extend this and implement headers() and query().
 * Optionally override mapRow() to transform each record.
 *
 * Example: app/Exports/StudentsExport.php
 *
 * class StudentsExport extends BaseExport
 * {
 *     protected ?int $classId;
 *
 *     public function __construct(?int $classId = null)
 *     {
 *         $this->classId = $classId;
 *     }
 *
 *     public function headers(): array
 *     {
 *         return ['ID', 'Name', 'Class', 'Section', 'Roll', 'Phone'];
 *     }
 *
 *     public function query()
 *     {
 *         return Student::query()
 *             ->when($this->classId, fn($q) => $q->where('class_id', $this->classId))
 *             ->with(['class', 'section']);
 *     }
 *
 *     public function mapRow($student): array
 *     {
 *         return [
 *             $student->id,
 *             $student->first_name . ' ' . $student->last_name,
 *             $student->class->name ?? '',
 *             $student->section->name ?? '',
 *             $student->roll_number,
 *             $student->phone,
 *         ];
 *     }
 * }
 */
abstract class BaseExport
{
    /**
     * Define column headers for the export.
     */
    abstract public function headers(): array;

    /**
     * Define the query to fetch data.
     * Should return an Eloquent query builder for chunking.
     */
    abstract public function query();

    /**
     * Transform a row before writing to CSV.
     * Override this if you need custom transformations.
     */
    public function mapRow($row): array
    {
        return (array) $row;
    }

    /**
     * Optional: define the filename.
     */
    public function filename(): string
    {
        return 'export-' . now()->format('Y-m-d-His') . '.csv';
    }
}
