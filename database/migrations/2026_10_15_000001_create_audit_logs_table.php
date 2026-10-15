<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 50)->index();
            $table->string('action', 120)->index();
            $table->nullableMorphs('subject');
            $table->string('description', 500);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('user_agent', 1024)->nullable();
            $table->uuid('request_id')->nullable()->index();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['subject_type', 'subject_id', 'created_at'], 'audit_subject_timeline');
            $table->index(['actor_id', 'created_at'], 'audit_actor_timeline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
