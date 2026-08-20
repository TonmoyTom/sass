<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Services\FileStorageService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\LMS\Models\Certificate;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\Enrollment;
use Modules\LMS\Traits\InteractsWithStudent;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentCertificateController extends Controller
{
    use InteractsWithStudent;

    public function download(Request $request, string $tenant, Course $course, PdfService $pdfService, FileStorageService $storage): StreamedResponse
    {
        $enrollment = Enrollment::where('course_id', $course->id)
            ->where('student_id', $this->studentId())
            ->where('status', 'completed')
            ->firstOrFail();

        $certificate = Certificate::firstOrCreate(
            ['course_id' => $course->id, 'student_id' => $this->studentId()],
            [
                'certificate_number' => Certificate::generateNumber(),
                'enrollment_id' => $enrollment->id,
                'issued_at' => $enrollment->completed_at ?? now(),
            ]
        );

        if (! $certificate->file_path) {
            $this->generateAndAttach($certificate, $course, $pdfService, $storage);
        }

        return Storage::disk('public')->download(
            $certificate->file_path,
            "certificate-{$certificate->certificate_number}.pdf"
        );
    }

    protected function generateAndAttach(Certificate $certificate, Course $course, PdfService $pdfService, FileStorageService $storage): void
    {
        $student = $this->currentStudent();
        $company = CompanySetting::first();

        $path = $pdfService->setPaperSize('a4')->setOrientation('landscape')->generateAndSave(
            'lms::pdfs.certificate',
            [
                'studentName' => $student?->name ?? 'Student',
                'courseTitle' => $course->title,
                'issuedDate' => $certificate->issued_at->format('d F, Y'),
                'certificateNumber' => $certificate->certificate_number,
                'companyName' => $company?->company_name ?? config('app.name'),
                'companyLogo' => $company?->logo_url,
                'verifyUrl' => url("/verify-certificate/lms/{$certificate->certificate_number}"),
            ],
            'lms/certificates',
            "{$certificate->certificate_number}.pdf",
        );

        $certificate->update(['file_path' => $path]);
    }
}
