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
        Schema::create('ttd_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ttd_status_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_taken')->default(false);
            $table->timestamps();

            $table->unique(['ttd_status_id', 'date']);
            $table->index('ttd_status_id', 'date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ttd_logs');
    }
};
