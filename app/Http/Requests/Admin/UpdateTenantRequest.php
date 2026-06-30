<?php

namespace App\Http\Requests\Admin;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        $tenant = $this->route('tenant');
        $tenantId = is_object($tenant) ? $tenant->id : $tenant;
        $ownerId = is_object($tenant) ? $tenant->owner_id : null;

        return [
            'name'   => ['required', 'string', 'max:255', Rule::unique(Tenant::class, 'name')->ignore($tenantId)],
            'status' => ['required', Rule::in(['active', 'suspended', 'trial', 'expired'])],

            'owner_first_name' => ['required', 'string', 'max:255'],
            'owner_last_name'  => ['nullable', 'string', 'max:255'],
            'owner_email'      => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($ownerId)],
            'phone'            => ['nullable', 'string', 'regex:/^\+[1-9]\d{6,14}$/'],

            'country'     => ['nullable', 'string', 'max:100'],
            'city'        => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'facebook'    => ['nullable', 'url', 'max:255'],
            'twitter'     => ['nullable', 'url', 'max:255'],
            'linkedin'    => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string'],
        ];
    }
}
