<?php

namespace App\Http\Requests\Admin;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique(Tenant::class, 'name'),
            ],
            'subdomain' => [
                'required', 'string', 'max:100',
                'alpha_dash', 'lowercase',
                Rule::unique(Tenant::class, 'subdomain'),
                Rule::notIn(['www', 'admin', 'api', 'mail', 'blog']),
            ],
            'owner_first_name' => ['required', 'string', 'max:255'],
            'owner_last_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'phone' => ['nullable', 'string', 'regex:/^\+[1-9]\d{6,14}$/'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'twitter' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
        ];
    }
}
