<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $guarded = [];

    protected $appends = ['logo_url', 'favicon_url'];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? $this->assetUrl($this->logo) : null;
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->favicon ? $this->assetUrl($this->favicon) : null;
    }

    /**
     * Tenant context hole tenancy assets path, central hole storage path.
     */
    protected function assetUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $path = preg_replace('#^storage/#', '', $path);

        if (function_exists('tenant') && tenant()) {
            return url('/tenancy/assets/'.$path);
        }
        return asset('storage/'.$path);
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'company_name' => config('app.name', 'My Company'),
        ]);
    }
}
