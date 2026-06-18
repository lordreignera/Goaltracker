<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quarters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quarter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('specific')->nullable();
            $table->text('measurable')->nullable();
            $table->text('achievable')->nullable();
            $table->text('relevant')->nullable();
            $table->text('time_bound')->nullable();
            $table->text('key_action_steps')->nullable();
            $table->string('primary_metric')->nullable();
            $table->date('deadline')->nullable();
            $table->enum('level', ['department', 'section', 'unit', 'individual'])->default('department');
            $table->enum('status', ['draft', 'submitted', 'approved', 'in_progress', 'completed', 'archived'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('goal_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['department_id', 'section_id', 'unit_id']);
            $table->index(['user_id']);
        });

        Schema::create('goal_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('specific_output');
            $table->text('success_measure');
            $table->unsignedTinyInteger('weight');
            $table->unsignedTinyInteger('planned_weeks');
            $table->enum('status', ['pending', 'approved', 'rejected', 'revision_requested', 'completed'])->default('pending');
            $table->date('starts_at');
            $table->date('due_at');
            $table->timestamps();
        });

        Schema::create('weekly_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_objective_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('week_number');
            $table->date('week_starting')->nullable();
            $table->text('progress_summary');
            $table->text('achievements')->nullable();
            $table->text('challenges')->nullable();
            $table->text('next_actions')->nullable();
            $table->enum('status', ['submitted', 'approved', 'rejected', 'revision_requested'])->default('submitted');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['goal_objective_id', 'user_id', 'week_starting']);
        });

        Schema::create('supervisor_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_update_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('decision', ['approved', 'rejected', 'revision_requested']);
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        Schema::create('quarterly_reflections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quarter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('goals_completed')->nullable();
            $table->text('goals_partially_completed')->nullable();
            $table->text('key_wins')->nullable();
            $table->text('challenges')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->text('next_quarter_focus')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quarterly_reflections');
        Schema::dropIfExists('supervisor_reviews');
        Schema::dropIfExists('weekly_updates');
        Schema::dropIfExists('goal_objectives');
        Schema::dropIfExists('goal_assignments');
        Schema::dropIfExists('goals');
        Schema::dropIfExists('quarters');
    }
};
