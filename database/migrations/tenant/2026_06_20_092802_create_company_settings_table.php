<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            // company / business identity
           $table->boolean('setup_completed')->default(false);
            $table->string('company_name');
            $table->string('legal_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('tagline')->nullable();

            // contact
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('alt_phone')->nullable();
            $table->string('website')->nullable();

            // address
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Bangladesh');

            // business / legal
            $table->string('registration_no')->nullable();   // trade license
            $table->string('tax_id')->nullable();             // TIN/BIN
            $table->string('vat_no')->nullable();

            // localization
            $table->string('currency', 10)->default('BDT');
            $table->string('currency_symbol', 10)->default('৳');
            $table->string('timezone')->default('Asia/Dhaka');
            $table->string('locale', 10)->default('en');
            $table->string('date_format')->default('d M Y');

            // social
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('whatsapp')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
