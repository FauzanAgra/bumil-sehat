<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nik_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mother_profile_id')->index();
            $table->enum('action', ['READ', 'DECRYPT', 'WRITE', 'UPDATE', 'DELETE', 'FAILED']);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_role', 20);  //admin|midwife|doctor|mother|system
            $table->string('ip_address', 45); //IPv4 & IPv6
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_anomaly')->default(false)->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['mother_profile_id', 'created_at']);
            $table->index(['actor_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot drop in Production mode
        if (app()->isProduction()) {
            throw new \Exception(
                'Tabel nik_audit_logs tidak dapat dihapus di production (UU PDP).'
            );
        }
        Schema::dropIfExists('nik_audit_logs');
    }
};
