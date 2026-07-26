<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function clearSession(Request $request): JsonResponse
    {
        $session = $request->session();
        $keysToKeep = ['_token'];
        foreach (array_keys($session->all()) as $key) {
            if (str_starts_with($key, 'login_') || str_starts_with($key, 'password_hash_')) {
                $keysToKeep[] = $key;
            }
        }

        $preserved = $session->only($keysToKeep);

        $session->flush();

        foreach ($preserved as $key => $value) {
            $session->put($key, $value);
        }

        $session->regenerate(false);

        return response()->json([
            'status' => 'success',
            'message' => 'Session data cleared (login preserved).',
        ]);
    }

    public function clearCookies(Request $request): JsonResponse
    {
        $sessionCookieName = config('session.cookie');
        $cookies = $request->cookies->keys();

        $response = response()->json([
            'status' => 'success',
            'message' => 'Cookies cleared successfully.',
            'cleared' => array_diff($cookies, [$sessionCookieName]),
        ]);

        foreach ($cookies as $name) {
            if ($name !== $sessionCookieName) {
                $response->headers->clearCookie($name);
            }
        }

        return $response;
    }
}
