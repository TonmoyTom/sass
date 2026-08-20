<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\CourseOrder;
use Modules\LMS\Traits\InteractsWithStudent;

class StudentOrderController extends Controller
{
    use InteractsWithStudent;


    public function __construct()
    {
        $this->middleware('can:lms.my-orders.view')->only(['index', 'invoice']);
    }

    public function index(Request $request): Response
    {
        $orders = CourseOrder::where('student_id', $this->studentId())
            ->with('course:id,title,thumbnail')
            ->latest('purchased_at')
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'invoice_number' => $o->invoice_number,
                'course_title' => $o->course?->title,
                'course_thumbnail' => $o->course?->thumbnail_url,
                'amount' => $o->amount,
                'status' => $o->status,
                'payment_method' => $o->payment_method,
                'purchased_at' => $o->purchased_at?->format('d M Y'),
            ]);

        return Inertia::render('LMS::Tenant/Learn/MyOrders', [
            'orders' => $orders,
        ]);
    }

    public function invoice(string $tenant, CourseOrder $order): Response
    {
        abort_unless($order->student_id === $this->studentId(), 403);

        $order->load('course:id,title');
        $student = $this->currentStudent();
        $company = CompanySetting::first();

        return Inertia::render('LMS::Tenant/Learn/OrderInvoice', [
            'order' => [
                'id' => $order->id,
                'invoice_number' => $order->invoice_number,
                'course_title' => $order->course?->title,
                'amount' => $order->amount,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'transaction_id' => $order->transaction_id,
                'purchased_at' => $order->purchased_at?->format('d M Y, h:i A'),
            ],
            'student' => [
                'name' => $student?->name,
                'email' => $student?->email,
            ],
            'company' => [
                'name' => $company?->company_name ?? config('app.name'),
                'logo_url' => $company?->logo_url,
            ],
        ]);
    }
}