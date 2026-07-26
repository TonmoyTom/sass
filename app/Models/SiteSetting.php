<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasSeo ,Filterable;

    protected $fillable = [
        'page_key', 'page_name', 'page_url',
    ];
}
