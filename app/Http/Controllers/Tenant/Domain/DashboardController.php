<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Inertia\Inertia;
use Inertia\Response;


class DashboardController extends Controller
{
    public function index(): Response
    {
        $settings = CompanySetting::current();
        $user = auth()->user();

        return Inertia::render('Tenant/Domain/Dashboard', [
            'company' => [
                'name' => $settings->company_name ?? 'Workspace',
            ],
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
