<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('goal_pillars')) {
            return;
        }

        Schema::create('goal_pillars', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('annual_goal')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        if (! Schema::hasTable('goals')) {
            Schema::dropIfExists('goal_pillars');
        }
    }
};
