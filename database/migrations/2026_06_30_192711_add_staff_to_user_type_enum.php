<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: modify enum to add 'staff'
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('super_admin', 'staff', 'seller', 'tenant_owner') DEFAULT 'tenant_owner'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('super_admin', 'seller', 'tenant_owner') DEFAULT 'tenant_owner'");
    }
};
