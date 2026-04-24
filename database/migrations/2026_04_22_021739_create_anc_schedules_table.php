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
        Schema::create('anc_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregnancy_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('anc_number');
            $table->date('schedule_date');
            $table->time('schedule_time')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['scheduled', 'done', 'cancelled', 'missed'])->default('scheduled');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['pregnancy_id', 'anc_number']);
            $table->index(['pregnancy_id', 'status']);
            $table->index('schedule_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anc_schedules');
    }
};
