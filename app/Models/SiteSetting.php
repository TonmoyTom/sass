<?php

namespace App\Models;

use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasSeo;

    protected $fillable = [
        'page_key', 'page_name', 'page_url',
    ];
}
