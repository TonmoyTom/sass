<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreSellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'first_name'      => ['required', 'string', 'max:255'],
            'last_name'       => ['nullable', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', Rule::unique(User::class)],
            'phone'           => ['nullable', 'string', 'regex:/^\+[1-9]\d{6,14}$/'],
            'password'        => ['required', 'confirmed', Password::defaults()],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status'          => ['required', Rule::in(['active', 'suspended', 'pending'])],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account' => ['nullable', 'string', 'max:50'],
            'bkash_number' => ['nullable', 'string', 'max:20'],
            'nid_number' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'nid_verified' => ['boolean'],
        ];
    }
}
