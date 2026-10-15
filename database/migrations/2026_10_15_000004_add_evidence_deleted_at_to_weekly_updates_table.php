<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weekly_updates', function (Blueprint $table) {
            $table->timestamp('evidence_deleted_at')->nullable()->after('evidence_original_name');
            $table->index('evidence_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_updates', function (Blueprint $table) {
            $table->dropIndex(['evidence_deleted_at']);
            $table->dropColumn('evidence_deleted_at');
        });
    }
};
