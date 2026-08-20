<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\LMS\Models\Lesson;
use Modules\LMS\Models\LessonNote;
use Modules\LMS\Traits\InteractsWithStudent;

class StudentNoteController extends Controller
{
    use InteractsWithStudent;

    public function save(Request $request, string $tenant, Lesson $lesson): JsonResponse
    {
        $data = $request->validate([
            'content' => ['nullable', 'string', 'max:20000'],
        ]);

        $note = LessonNote::updateOrCreate(
            ['lesson_id' => $lesson->id, 'student_id' => $this->studentId()],
            ['content' => $data['content'] ?? '']
        );

        return response()->json([
            'saved_at' => $note->updated_at->toIso8601String(),
        ]);
    }
}