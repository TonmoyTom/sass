<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\Assignment;
use Modules\LMS\Models\Course;

class AssignmentController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:assignments.view')->only(['index', 'search']);
        $this->middleware('can:assignments.create')->only(['store']);
        $this->middleware('can:assignments.edit')->only(['update']);
        $this->middleware('can:assignments.delete')->only(['destroy']);
    }
        
    /**
     * Overview across every course — entry point for grading.
     */
    public function index(Request $request): Response
    {
        $assignments = Assignment::query()
            ->with('course:id,title')
            ->withCount('submissions')
            ->withCount(['submissions as graded_count' => fn ($q) => $q->whereNotNull('grade')])
            ->filterAndCache(
                $request,
                searchable: ['title'],
                filterable: [],
                sortable: ['title', 'due_date', 'created_at'],
                ttlSeconds: 60,
                perPage: 20,
                transform: fn ($a) => [
                    'id' => $a->id,
                    'title' => $a->title,
                    'course_id' => $a->course_id,
                    'course_title' => $a->course?->title,
                    'due_date' => $a->due_date?->format('d M Y, h:i A'),
                    'max_score' => $a->max_score,
                    'submissions_count' => $a->submissions_count,
                    'graded_count' => $a->graded_count,
                ]
            );

        return Inertia::render('LMS::Tenant/Assignments/Index', [
            'assignments' => $assignments,
            'filters' => [
                'search' => $request->input('search', ''),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_dir' => $request->input('sort_dir', 'desc'),
            ],
        ]);
    }

    public function store(Request $request, string $tenant, Course $course, FileStorageService $storage): RedirectResponse
    {
        $data = $this->validateAssignment($request);

        if ($request->hasFile('file')) {
            $data['file_name'] = $request->file('file')->getClientOriginalName();
            $data['file_path'] = $storage->uploadFile($request->file('file'), 'lms/assignment-files');
        }

        $course->assignments()->create($data);

        return back()->with('status', 'Assignment added.');
    }

    public function update(Request $request, string $tenant, Assignment $assignment, FileStorageService $storage): RedirectResponse
    {
        $data = $this->validateAssignment($request);

        if ($request->hasFile('file')) {
            if ($assignment->file_path) {
                $storage->deleteFile($assignment->file_path);
            }
            $data['file_name'] = $request->file('file')->getClientOriginalName();
            $data['file_path'] = $storage->uploadFile($request->file('file'), 'lms/assignment-files');
        } elseif ($request->boolean('remove_file')) {
            if ($assignment->file_path) {
                $storage->deleteFile($assignment->file_path);
            }
            $data['file_name'] = null;
            $data['file_path'] = null;
        }

        $assignment->update($data);

        return back()->with('status', 'Assignment updated.');
    }

    public function destroy(string $tenant, Assignment $assignment, FileStorageService $storage): RedirectResponse
    {
        if ($assignment->file_path) {
            $storage->deleteFile($assignment->file_path);
        }

        $assignment->delete();

        return back()->with('status', 'Assignment deleted.');
    }

    public function search(Request $request): JsonResponse
    {
        $assignments = Assignment::query()
            ->when($request->q, fn ($q) => $q->where('title', 'like', "%{$request->q}%"))
            ->when($request->course_id, fn ($q) => $q->where('course_id', $request->course_id))
            ->orderBy('title')
            ->limit(20)
            ->get(['id', 'title']);

        return response()->json($assignments);
    }

    protected function validateAssignment(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'max_score' => ['required', 'integer', 'min:1'],
            'allow_late_submission' => ['boolean'],
            'file' => ['nullable', 'file', 'max:20480'], // 20MB
        ]);
    }
}