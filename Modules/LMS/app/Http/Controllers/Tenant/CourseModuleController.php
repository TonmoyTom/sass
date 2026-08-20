<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseModule;

class CourseModuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:lessons.manage');
    }
    
    public function store(Request $request,string $tenant,  Course $course): RedirectResponse
    {
     
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $sortOrder = $course->modules()->max('sort_order') + 1;

        $course->modules()->create([
            'title' => $data['title'],
            'sort_order' => $sortOrder,
        ]);

        return back()->with('status', 'Module added.');
    }

    public function update(Request $request, CourseModule $module): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $module->update($data);

        return back()->with('status', 'Module updated.');
    }

    public function reorder(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:course_modules,id'],
        ]);

        foreach ($data['order'] as $index => $moduleId) {
            CourseModule::where('id', $moduleId)
                ->where('course_id', $course->id)
                ->update(['sort_order' => $index]);
        }

        return back()->with('status', 'Order updated.');
    }

    public function destroy(CourseModule $module): RedirectResponse
    {
        $module->delete(); // cascade — lessons o delete hobe

        return back()->with('status', 'Module deleted.');
    }
}
