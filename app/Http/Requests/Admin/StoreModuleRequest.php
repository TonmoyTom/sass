<?php

namespace App\Http\Requests\Admin;

use App\Models\ModulePackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:100'],
            'alias'           => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique(ModulePackage::class, 'alias')],
            'description'     => ['nullable', 'string'],
            'icon'            => ['nullable', 'string', 'max:100'],
            'version'         => ['nullable', 'string', 'max:20'],
            'pricing_type'    => ['required', Rule::in(['subscription', 'one_time'])],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'module_category' => ['required', Rule::in(['core', 'addon', 'utility'])],
            'is_active'       => ['boolean'],
            'is_core'         => ['boolean'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],
            'features'        => ['nullable', 'string'],

            // tiers (nested)
            'tiers'                  => ['required', 'array', 'min:1'],
            'tiers.*.name'           => ['required', 'string', 'max:100'],
            'tiers.*.slug'           => ['nullable', 'string', 'max:50'],
            'tiers.*.limits'         => ['nullable', 'array'],
            'tiers.*.monthly_price'  => ['nullable', 'numeric', 'min:0'],
            'tiers.*.yearly_price'   => ['nullable', 'numeric', 'min:0'],
            'tiers.*.one_time_price' => ['nullable', 'numeric', 'min:0'],
            'tiers.*.is_active'      => ['boolean'],
            'tiers.*.is_popular'     => ['boolean'],
        ];
    }
}
