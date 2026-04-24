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
        Schema::create('ttd_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregnacy_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is-active')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->enum('activated_by', ['system', 'midwife'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ttd_statuses');
    }
};
