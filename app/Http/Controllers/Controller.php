<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Controller — Hybrid Base Controller
 *
 * Base controller for the entire app. Supports BOTH:
 * - Inertia responses (web browser)
 * - JSON API responses (mobile app, third-party)
 *
 * All controllers should extend this. Use respond() helper to auto-pick the right format.
 *
 * Usage:
 * class StudentController extends Controller
 * {
 *     public function index(Request $request)
 *     {
 *         $students = Student::paginate(20);
 *
 *         // Auto-detects: returns Inertia for web, JSON for API
 *         return $this->respond(
 *             page: 'School::Students/Index',
 *             data: ['students' => $students],
 *         );
 *     }
 * }
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, ApiResponse;

    /**
     * Smart response: returns Inertia OR JSON based on request.
     *
     * @param  string  $page  Inertia page name (for web)
     * @param  array  $data  Data to pass (works for both)
     * @param  string|null  $apiMessage  Custom message for API response
     */
    protected function respond(
        string $page,
        array $data = [],
        ?string $apiMessage = null
    ): InertiaResponse|\Illuminate\Http\JsonResponse {
        if (request()->wantsJson() || request()->is('api/*')) {
            return $this->successResponse(
                $this->extractData($data),
                $apiMessage ?? 'Success'
            );
        }

        return Inertia::render($page, $data);
    }

    /**
     * Smart response for create/update actions.
     *
     * @param  string  $redirectRoute  Route to redirect for web
     * @param  array  $routeParams  Route parameters
     * @param  mixed  $data  Data for API response
     * @param  string  $message  Success message
     * @param  int  $statusCode  HTTP status (200, 201)
     */
    protected function respondAction(
        string $redirectRoute,
        array $routeParams = [],
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse {
        if (request()->wantsJson() || request()->is('api/*')) {
            return $this->successResponse($data, $message, $statusCode);
        }

        return redirect()
            ->route($redirectRoute, $routeParams)
            ->with('success', $message);
    }

    /**
     * Smart error response.
     */
    protected function respondError(
        string $message = 'Error',
        int $statusCode = 400,
        ?string $redirectRoute = null
    ): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse {
        if (request()->wantsJson() || request()->is('api/*')) {
            return $this->errorResponse($message, $statusCode);
        }

        if ($redirectRoute) {
            return redirect()->route($redirectRoute)->with('error', $message);
        }

        return back()->with('error', $message);
    }

    /**
     * Extract main data from Inertia data array.
     * For API, we usually want the primary resource, not all sidebar data.
     */
    protected function extractData(array $data): mixed
    {
        // If only one key, return that
        if (count($data) === 1) {
            return reset($data);
        }

        // Otherwise return as-is
        return $data;
    }

    /**
     * Check if request is from API/app.
     */
    protected function isApiRequest(Request $request = null): bool
    {
        $request = $request ?? request();
        return $request->wantsJson() || $request->is('api/*');
    }
}
