<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('id');
            }
            if (! Schema::hasColumn('users', 'second_name')) {
                $table->string('second_name')->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'approval_status')) {
                $table->string('approval_status')->default('approved')->after('role');
            }
            if (! Schema::hasColumn('users', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approval_status');
            }
            if (! Schema::hasColumn('users', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }

            $table->dropColumn([
                'first_name',
                'second_name',
                'phone_number',
                'approval_status',
                'approved_at',
            ]);
        });
    }
};
