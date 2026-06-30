<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        $seller = $this->route('seller');
        $userId = is_object($seller) ? $seller->user_id : null;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'regex:/^\+[1-9]\d{6,14}$/'],
            'status' => ['required', Rule::in(['active', 'suspended', 'pending'])],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
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
