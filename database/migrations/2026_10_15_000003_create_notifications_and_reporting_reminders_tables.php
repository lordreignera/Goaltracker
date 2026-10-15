<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reporting_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_objective_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reporting_frequency', 16);
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamp('notified_at');
            $table->timestamps();
            $table->unique(
                ['goal_objective_id', 'user_id', 'reporting_frequency', 'period_start'],
                'reporting_reminders_period_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_reminders');
        Schema::dropIfExists('notifications');
    }
};
