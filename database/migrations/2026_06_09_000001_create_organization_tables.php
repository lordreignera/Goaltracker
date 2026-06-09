<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['department_id', 'name']);
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('password');
            }
            if (! Schema::hasColumn('users', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('department_id');
            }
            if (! Schema::hasColumn('users', 'supervisor_id')) {
                $table->unsignedBigInteger('supervisor_id')->nullable()->after('unit_id');
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('staff')->after('supervisor_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->foreign('supervisor_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['supervisor_id']);
        });

        Schema::dropIfExists('units');
        Schema::dropIfExists('departments');
    }
};
