<?php

use App\Models\Goal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goal_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['goal_id', 'department_id']);
        });

        Schema::create('goal_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['goal_id', 'unit_id']);
        });

        Goal::query()->with(['department', 'unit'])->each(function (Goal $goal) {
            if ($goal->department_id) {
                $goal->assignedDepartments()->syncWithoutDetaching([$goal->department_id]);
            }

            if ($goal->unit_id) {
                $goal->assignedUnits()->syncWithoutDetaching([$goal->unit_id]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_unit');
        Schema::dropIfExists('goal_department');
    }
};
