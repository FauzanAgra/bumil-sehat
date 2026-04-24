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
        Schema::create('anc_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')
                ->unique()
                ->constrained('anc_schedules')
                ->restrictOnDelete();
            $table->foreignId('mother_id')
                ->constrained('mother_profiles')
                ->restrictOnDelete();

            // Data Fisik
            $table->decimal('weight', 5, 2)->nullable(); //kg
            $table->decimal('height', 5, 2)->nullable(); //cm
            $table->decimal('lila', 5, 2)->nullable(); //cm
            $table->unsignedSmallInteger('systolic')->nullable(); //mmHg
            $table->unsignedSmallInteger('diastolic')->nullable(); //mmHg

            // Status Pemeriksaan
            $table->enum('tt_status', ['done', 'not_done'])->nullable();
            $table->enum('fetal_position', ['head_down', 'transverse', 'breech'])->nullable();
            $table->unsignedSmallInteger('fetal_heart_rate')->nullable(); //bpm

            // Audit Data
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('submitted_at')->useCurrent();
            $table->boolean('is_locked')->default(false);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anc_records');
    }
};
