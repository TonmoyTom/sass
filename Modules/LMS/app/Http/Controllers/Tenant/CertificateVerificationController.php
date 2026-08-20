<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\Certificate;

class CertificateVerificationController extends Controller
{
    public function show(?string $certificateNumber = null): Response
    {
        $certificate = null;
        $notFound = false;

        if ($certificateNumber) {
            $certificate = Certificate::where('certificate_number', $certificateNumber)
                ->with('course:id,title')
                ->first();

            $notFound = ! $certificate;
        }

        return Inertia::render('LMS::Tenant/Learn/VerifyCertificate', [
            'certificate' => $certificate ? [
                'certificate_number' => $certificate->certificate_number,
                'student_name' => $certificate->student()?->name ?? 'Student',
                'course_title' => $certificate->course?->title,
                'issued_at' => $certificate->issued_at->format('d F, Y'),
            ] : null,
            'searched_number' => $certificateNumber,
            'not_found' => $notFound,
        ]);
    }
}
