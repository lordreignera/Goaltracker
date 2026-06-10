<?php

use App\Models\GoalObjective;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goal_objectives', function (Blueprint $table) {
            $table->date('starts_at')->nullable()->after('status');
        });

        GoalObjective::query()
            ->with('goal.quarter')
            ->each(function (GoalObjective $objective) {
                $objective->forceFill([
                    'starts_at' => $objective->goal?->quarter?->starts_at,
                ])->save();
            });
    }

    public function down(): void
    {
        Schema::table('goal_objectives', function (Blueprint $table) {
            $table->dropColumn('starts_at');
        });
    }
};
