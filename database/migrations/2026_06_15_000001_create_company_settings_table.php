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
            $table->string('company_name')->default('Africa Renewal Ministries');
            $table->string('company_short_name')->default('Africa Renewal');
            $table->string('brand_mark')->default('90');
            $table->string('product_name')->default('SMART Goals Tracker');
            $table->string('tagline')->default('Plan, review, approve, and report on measurable goals.');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
