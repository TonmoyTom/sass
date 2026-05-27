<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;

/**
 * ApiResponse Trait
 *
 * Provides consistent JSON response formats for controllers.
 * Use in any controller that returns JSON (API endpoints, AJAX responses).
 *
 * Usage:
 * class StudentController extends Controller
 * {
 *     use ApiResponse;
 *
 *     public function index()
 *     {
 *         $students = Student::all();
 *         return $this->successResponse($students);
 *     }
 *
 *     public function store(Request $request)
 *     {
 *         try {
 *             $student = Student::create($request->validated());
 *             return $this->successResponse($student, 'Student created', 201);
 *         } catch (\Exception $e) {
 *             return $this->errorResponse($e->getMessage(), 500);
 *         }
 *     }
 * }
 */
trait ApiResponse
{
    /**
     * Success response.
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Error response.
     */
    protected function errorResponse(
        string $message = 'Error',
        int $statusCode = 400,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response (422).
     */
    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->errorResponse('Validation failed', 422, $errors);
    }

    /**
     * Not found response (404).
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Unauthorized response (401).
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Forbidden response (403).
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Paginated response.
     */
    protected function paginatedResponse(
        \Illuminate\Pagination\LengthAwarePaginator $paginator,
        string $message = 'Success'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }
}
