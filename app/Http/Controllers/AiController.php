<?php

namespace App\Http\Controllers;

use App\Services\OllamaService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function chat(Request $request, OllamaService $ollama)
    {
        $request->validate(['message' => ['required', 'string']]);

        $user = $request->user();
        $tenantId = function_exists('tenant') ? tenant('id') : null;

        return response()->json(
            $ollama->chat($request->message, $user, $tenantId)
        );
    }
}
