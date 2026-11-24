<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregnancy_id')->constrained('pregnancies')->cascadeOnDelete();
            $table->foreignId('anc_record_id')->nullable()->constrained('anc_records')->nullOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->restrictOnDelete();
            $table->enum('follow_up_level', ['routine', 'monitor', 'refer'])->default('routine');
            $table->text('recommendation');
            $table->timestamps();

            $table->index(['pregnancy_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_recommendations');
    }
};
