<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseFaq;

class CourseFaqController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:lessons.manage');
    }
        
    public function store(Request $request, string $tenant, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
        ]);

        $sortOrder = $course->faqs()->max('sort_order') + 1;

        $course->faqs()->create([
            'question' => $data['question'],
            'answer' => $data['answer'],
            'sort_order' => $sortOrder,
        ]);

        return back()->with('status', 'FAQ added.');
    }

    public function update(Request $request, CourseFaq $faq): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
        ]);

        $faq->update($data);

        return back()->with('status', 'FAQ updated.');
    }

    public function reorder(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:course_faqs,id'],
        ]);

        foreach ($data['order'] as $index => $faqId) {
            CourseFaq::where('id', $faqId)
                ->where('course_id', $course->id)
                ->update(['sort_order' => $index]);
        }

        return back()->with('status', 'Order updated.');
    }

    public function destroy(CourseFaq $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('status', 'FAQ deleted.');
    }
}