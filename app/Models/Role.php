<?php

namespace App\Models;

use App\Traits\Filterable;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use Filterable;
}
