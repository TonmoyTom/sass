<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseCategory;
use Modules\LMS\Models\Enrollment;
use Modules\LMS\Classes\Services\CourseEnrollmentService;
use Modules\LMS\Classes\Services\LessonAccessService;
use Modules\LMS\Traits\InteractsWithStudent;

class StudentCourseController extends Controller
{
    use InteractsWithStudent;


    public function __construct()
    {
        $this->middleware('can:lms.my-courses.view')->only(['myCourses']);
    }

    /**
     * LMS landing page — hero, stats, categories, featured courses.
     */
    public function landing(): Response
    {
        $enrolledCourseIds = $this->enrolledCourseIds();

        $featuredCourses = Course::query()
            ->where('status', 'published')
            ->with(['category'])
            ->withCount('enrollments')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'slug' => $c->slug,
                'thumbnail' => $c->thumbnail_url,
                'category_name' => $c->category?->name,
                'is_free' => $c->is_free,
                'price' => $c->price,
                'enrollments_count' => $c->enrollments_count,
                'instructor_name' => $c->instructor?->name,
                'is_enrolled' => in_array($c->id, $enrolledCourseIds),
            ]);

        $categories = CourseCategory::where('is_active', true)
            ->withCount(['courses' => fn ($q) => $q->where('status', 'published')])
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug'])
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'courses_count' => $cat->courses_count,
            ])
            ->filter(fn ($cat) => $cat['courses_count'] > 0)
            ->values();

        $stats = [
            'total_courses' => Course::where('status', 'published')->count(),
            'total_students' => Enrollment::distinct()->count('student_id'),
            'total_categories' => $categories->count(),
        ];

        $siteSetting = SiteSetting::where('page_url', request()->url())->with('seo')->first();

        $seo = $siteSetting?->frontSeoArray() ?? [
            'title' => 'Learn',
            'description' => 'Browse and enroll in courses — self-paced video lessons, downloadable material, and quizzes to track your progress.',
            'canonical' => url()->current(),
            'robots' => 'index,follow',
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
        ];

        return Inertia::render('LMS::Tenant/Learn/Index', [
            'featuredCourses' => $featuredCourses,
            'categories' => $categories,
            'stats' => $stats,
            'seo' => $seo,
        ]);
    }

    public function browse(Request $request): Response
        {
            $enrolledCourseIds = $this->enrolledCourseIds();

            $courses = Course::query()
                ->where('status', 'published')
                ->with(['category'])
                ->withCount('enrollments')
                ->filterAndCache(
                    $request,
                    searchable: ['title'],
                    filterable: ['category_id', 'is_free'],
                    sortable: ['title', 'created_at', 'price'],
                    ttlSeconds: 180,
                    perPage: 12,
                    transform: fn ($c) => [
                        'id' => $c->id,
                        'title' => $c->title,
                        'slug' => $c->slug,
                        'thumbnail' => $c->thumbnail_url,
                        'description' => $c->description,
                        'category_name' => $c->category?->name,
                        'is_free' => $c->is_free,
                        'price' => $c->price,
                        'enrollments_count' => $c->enrollments_count,
                        'instructor_name' => $c->instructor?->name,
                        'is_enrolled' => in_array($c->id, $enrolledCourseIds),
                    ]
                );

            $siteSetting = SiteSetting::where('page_key', 'lms-browse')->with('seo')->first();

            $seo = $siteSetting?->frontSeoArray() ?? [
                'title' => 'Browse courses',
                'description' => 'Explore every published course, filter by category, and find what you want to learn next.',
                'canonical' => url()->current(),
                'robots' => 'index,follow',
                'og_type' => 'website',
                'twitter_card' => 'summary_large_image',
            ];

            return Inertia::render('LMS::Tenant/Learn/Browse', [
                'courses' => $courses,
                'categories' => CourseCategory::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
                'filters' => [
                    'search' => $request->input('search', ''),
                    'category_id' => $request->input('category_id', ''),
                    'is_free' => $request->input('is_free', ''),
                    'sort_by' => $request->input('sort_by', 'created_at'),
                    'sort_dir' => $request->input('sort_dir', 'desc'),
                ],
                'seo' => $seo,
            ]);
        }

        /**
         * Single course detail page — enroll/purchase button soho.
         */
         public function show(string $tenant, Course $course, LessonAccessService $accessService): Response
        {
            abort_unless($course->status === 'published', 404);
    
            $enrollment = Enrollment::where('course_id', $course->id)
                ->where('student_id', $this->studentId())
                ->first();
    
            $course->load(['category', 'modules.lessons', 'faqs', 'instructors.info:user_id,bio']);
    
            $completedIds = $enrollment
                ? $enrollment->progress()->get()->filter(fn ($p) => $p->isFullyComplete())->pluck('lesson_id')
                : collect();
    
            $seo = [
                'title' => $course->title,
                'description' => $course->short_description
                    ?: str($course->description ?? '')->limit(160)->toString(),
                'canonical' => url()->current(),
                'robots' => 'index,follow',
                'og_image' => $course->thumbnail_url,
                'og_type' => 'website',
                'twitter_card' => 'summary_large_image',
            ];
    
            return Inertia::render('LMS::Tenant/Learn/CoursePreview', [
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'short_description' => $course->short_description,
                    'description' => $course->description,
                    'thumbnail' => $course->thumbnail_url,
                    'preview_image' => $course->preview_image_url,
                    'has_preview_video' => $course->hasPreviewVideo(),
                    'preview_video_url' => $course->resolved_preview_video_url,
                    'category_name' => $course->category?->name,
                    'instructor_name' => $course->instructor?->name,
                    'instructors' => $course->instructors->map(fn ($i) => [
                        'id' => $i->id,
                        'name' => $i->name,
                        'avatar' => $i->avatar_url,
                        'bio' => $i->info?->bio,
                    ]),
                    'is_free' => $course->is_free,
                    'price' => $course->price,
                    'discount_price' => $course->discount_price,
                    'has_discount' => $course->hasDiscount(),
                    'live_class_starts_at' => $course->live_class_starts_at?->toIso8601String(),
                    'modules' => $course->modules->map(fn ($m) => [
                        'id' => $m->id,
                        'title' => $m->title,
                        'lessons' => $m->lessons->map(fn ($l) => [
                            'id' => $l->id,
                            'title' => $l->title,
                            'is_free_preview' => $l->is_free_preview,
                            'has_video' => (bool) ($l->video_url || $l->video_path),
                            'has_ebook' => (bool) $l->ebook_path,
                            'video_duration_minutes' => $l->video_duration_minutes,
                            'preview_video_url' => $l->is_free_preview ? $l->resolved_video_url : null,
                            'preview_ebook_url' => $l->is_free_preview ? $l->resolved_ebook_url : null,
                            'is_unlocked' => $accessService->canAccess($l, $enrollment),
                            'is_complete' => $completedIds->contains($l->id),
                        ]),
                    ]),
                    'total_lessons' => $course->modules->sum(fn ($m) => $m->lessons->count()),
                    'faqs' => $course->faqs->map(fn ($f) => [
                        'id' => $f->id,
                        'question' => $f->question,
                        'answer' => $f->answer,
                    ]),
                    'average_rating' => $course->averageRating(),
                    'reviews_count' => $course->reviewsCount(),
                    'reviews' => $course->reviews()->limit(10)->get()->map(fn ($r) => [
                        'id' => $r->id,
                        'rating' => $r->rating,
                        'comment' => $r->comment,
                        'student_name' => $r->student()?->name ?? 'Student',
                        'created_at' => $r->created_at->format('d M Y'),
                    ]),
                ],
                'enrollment' => $enrollment ? [
                    'status' => $enrollment->status,
                    'enrolled_at' => $enrollment->enrolled_at?->format('d M Y'),
                ] : null,
                'payment_methods' => $course->is_free ? [] : $this->activePaymentMethods(),
                'seo' => $seo,
            ]);
        }

        /**
         * Purchase/Enroll.
         */
        public function enroll(Request $request, string $tenant, Course $course, CourseEnrollmentService $service): RedirectResponse
        {
            $data = $request->validate([
                'payment_method' => [$course->is_free ? 'nullable' : 'required', 'string'],
                'transaction_id' => [$course->is_free ? 'nullable' : 'required', 'string', 'max:100'],
            ]);

            $student = $this->currentStudent();

            try {
                $service->purchase(
                    $course,
                    $student,
                    $data['payment_method'] ?? null,
                    $data['transaction_id'] ?? null,
                );
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }

            return redirect('/my-courses')
                ->with('status', 'Enrolled successfully!');
        }

        /**
         * "My Courses" — enrolled course list, progress soho.
         */
         public function myCourseShow(string $tenant, Course $course, LessonAccessService $accessService): Response|RedirectResponse
         {
            abort_unless($course->status === 'published', 404);

            $enrollment = Enrollment::where('course_id', $course->id)
                ->where('student_id', $this->studentId())
                ->first();

            if (! $enrollment) {
                return redirect()
                    ->route('tenant.lms.learn.browse.show', $course->id)
                    ->with('error', "You're not enrolled in this course yet.");
            }

            $course->load(['category', 'modules.lessons', 'faqs', 'instructors.info:user_id,bio']);

            $completedIds = $enrollment->progress()->get()->filter(fn ($p) => $p->isFullyComplete())->pluck('lesson_id');
            $totalLessons = $course->modules->sum(fn ($m) => $m->lessons->count());

            // dd(round(($completedIds->count() / $totalLessons) * 100) , $completedIds->count() ,$totalLessons, $enrollment->progress()->get()->pluck('lesson_id'));
            return Inertia::render('LMS::Tenant/Learn/Details', [
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'short_description' => $course->short_description,
                    'category_name' => $course->category?->name,
                    'instructors' => $course->instructors->map(fn ($i) => [
                        'id' => $i->id,
                        'name' => $i->name,
                        'avatar' => $i->avatar_url,
                        'bio' => $i->info?->bio,
                    ]),
                    'modules' => $course->modules->map(fn ($m) => [
                        'id' => $m->id,
                        'title' => $m->title,
                        'lessons' => $m->lessons->map(fn ($l) => [
                            'id' => $l->id,
                            'title' => $l->title,
                            'has_video' => (bool) ($l->video_url || $l->video_path),
                            'has_ebook' => (bool) $l->ebook_path,
                            'video_duration_minutes' => $l->video_duration_minutes,
                            'is_unlocked' => $accessService->canAccess($l, $enrollment),
                            'is_complete' => $completedIds->contains($l->id),
                        ]),
                    ]),
                    'total_lessons' => $totalLessons,
                    'faqs' => $course->faqs->map(fn ($f) => [
                        'id' => $f->id,
                        'question' => $f->question,
                        'answer' => $f->answer,
                    ]),
                ],
                'enrollment' => [
                    'status' => $enrollment->status,
                    'enrolled_at' => $enrollment->enrolled_at?->format('d M Y'),
                    'progress' => $totalLessons ? round(($completedIds->count() / $totalLessons) * 100) : 0,
                ],
                'seo' => [
                    'title' => $course->title,
                    'robots' => 'noindex,nofollow',
                ],
            ]);
    }

    protected function activePaymentMethods(): array
        {
            return PaymentSetting::where('is_active', true)
                ->get()
                ->map(fn ($p) => [
                    'method' => $p->method,
                    'merchant_number' => $p->merchant_number,
                    'bank_name' => $p->bank_name,
                    'account_name' => $p->account_name,
                    'account_number' => $p->account_number,
                    'routing_number' => $p->routing_number,
                    'branch' => $p->branch,
                    'instructions' => $p->instructions,
                ])
                ->values()
                ->all();
        }

}
