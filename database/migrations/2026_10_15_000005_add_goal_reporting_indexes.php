<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->index(['status', 'created_by'], 'goals_status_creator_index');
            $table->index(['quarter_id', 'goal_pillar_id', 'status'], 'goals_quarter_pillar_status_index');
        });

        Schema::table('goal_assignments', function (Blueprint $table) {
            $table->index(['goal_id', 'department_id', 'section_id', 'user_id'], 'goal_assignments_visibility_index');
        });

        Schema::table('goal_objectives', function (Blueprint $table) {
            $table->index(['goal_id', 'status'], 'goal_objectives_goal_status_index');
        });

        Schema::table('weekly_updates', function (Blueprint $table) {
            $table->index(['goal_objective_id', 'status', 'report_period_start'], 'weekly_updates_progress_index');
        });

        Schema::table('supervisor_reviews', function (Blueprint $table) {
            $table->index(['weekly_update_id', 'decision', 'created_at'], 'supervisor_reviews_latest_index');
        });
    }

    public function down(): void
    {
        Schema::table('supervisor_reviews', function (Blueprint $table) {
            $table->dropIndex('supervisor_reviews_latest_index');
        });

        Schema::table('weekly_updates', function (Blueprint $table) {
            $table->dropIndex('weekly_updates_progress_index');
        });

        Schema::table('goal_objectives', function (Blueprint $table) {
            $table->dropIndex('goal_objectives_goal_status_index');
        });

        Schema::table('goal_assignments', function (Blueprint $table) {
            $table->dropIndex('goal_assignments_visibility_index');
        });

        Schema::table('goals', function (Blueprint $table) {
            $table->dropIndex('goals_quarter_pillar_status_index');
            $table->dropIndex('goals_status_creator_index');
        });
    }
};
