<?php

namespace App\Models;

use App\Traits\Filterable;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use Filterable;

    public function moduleGrants()
        {
            return $this->hasMany(RoleModule::class);
        }

        public function grantedModuleAliases(): array
        {
            return $this->moduleGrants->pluck('module')->all();
        }

        public function syncModules(array $moduleAliases): void
        {
            $this->moduleGrants()->whereNotIn('module', $moduleAliases)->delete();

            foreach ($moduleAliases as $alias) {
                $this->moduleGrants()->firstOrCreate(['module' => $alias]);
            }
        }
}
