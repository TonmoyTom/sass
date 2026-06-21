<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $guarded = [];

    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? '/tenancy/assets/'.$this->logo : null;
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'company_name' => config('app.name', 'My Company'),
        ]);
    }
}
