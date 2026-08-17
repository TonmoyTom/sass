<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\LMS\Models\CourseModule;
use Modules\LMS\Models\Lesson;

class LessonController extends Controller
{
    public function store(Request $request, string $tenant, CourseModule $module, FileStorageService $storage): RedirectResponse
    {
        $data = $this->validateLesson($request);

        $data['course_module_id'] = $module->id;
        $data['sort_order'] = $module->lessons()->max('sort_order') + 1;

        if (! empty($data['video_path'])) {
            $data['video_source'] = 'upload';
            $data['video_url'] = null;
        } elseif (! empty($data['video_url'])) {
            $data['video_source'] = 'youtube';
            $data['video_path'] = null;
        }

        if ($request->hasFile('ebook_file')) {
            $data['ebook_path'] = $storage->uploadFile(
                $request->file('ebook_file'),
                'lms/ebooks'
            );
        }

        unset($data['ebook_file']);

        Lesson::create($data);

        return back()->with('status', 'Lesson added.');
    }

    public function update(Request $request, string $tenant, Lesson $lesson, FileStorageService $storage): RedirectResponse
    {
        $data = $this->validateLesson($request);

        if (! empty($data['video_path'])) {
            $data['video_source'] = 'upload';
            $data['video_url'] = null;
        } elseif (! empty($data['video_url'])) {
            $data['video_source'] = 'youtube';
            $data['video_path'] = null;
        } else {

            $data['video_source'] = null;
            $data['video_path'] = null;
            $data['video_url'] = null;
        }

        if ($request->hasFile('ebook_file')) {
            $storage->deleteFile($lesson->ebook_path);

            $data['ebook_path'] = $storage->uploadFile(
                $request->file('ebook_file'),
                'lms/ebooks'
            );
        }

        unset($data['ebook_file']);

        $lesson->update($data);

        return back()->with('status', 'Lesson updated.');
    }

    public function attachQuiz(Request $request, string $tenant, Lesson $lesson): JsonResponse
    {
        $data = $request->validate([
            'quiz_id' => ['required', 'exists:quizzes,id'],
        ]);

        $lesson->quizzes()->syncWithoutDetaching([$data['quiz_id']]);

        return response()->json(['status' => 'ok']);
    }

    public function detachQuiz(string $tenant, Lesson $lesson, int $quizId): JsonResponse
    {
        $lesson->quizzes()->detach($quizId);

        return response()->json(['status' => 'ok']);
    }

    public function reorder(Request $request, CourseModule $module): RedirectResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:lessons,id'],
        ]);

        foreach ($data['order'] as $index => $lessonId) {
            Lesson::where('id', $lessonId)
                ->where('course_module_id', $module->id)
                ->update(['sort_order' => $index]);
        }

        return back()->with('status', 'Order updated.');
    }

    public function destroy(string $tenant, Lesson $lesson): RedirectResponse
    {
        $lesson->delete();

        return back()->with('status', 'Lesson deleted.');
    }

    protected function validateLesson(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'is_free_preview' => ['boolean'],
            'requires_completion' => ['boolean'],
            'video_url' => ['nullable', 'url'],
            'video_path' => ['nullable', 'string'],
            'video_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'ebook_title' => ['nullable', 'string', 'max:255'],
            'ebook_file' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);
    }

    public function uploadVideo(Request $request, FileStorageService $storage): JsonResponse
    {
        $request->validate([
            'video' => ['required', 'file', 'mimes:mp4,mov', 'max:512000'], // 500MB
        ]);

        $originalName = $request->file('video')->getClientOriginalName();

        $path = $storage->uploadFile(
            $request->file('video'),
            'lms/videos',
        );

        return response()->json([
            'path' => $path,
            'url' => $storage->getUrl($path),
            'name' => $originalName,
        ]);
    }
}
