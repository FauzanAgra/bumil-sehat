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
        Schema::create('detected_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anc_record_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('condition_type', ['kek', 'preeclampsia', 'anemia']);
            $table->enum('status', ['detected', 'confirmed', 'rejected'])
                ->default('detected');
            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('detected_at')->useCurrent();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->unique(['anc_record_id', 'condition_type']);
            $table->index(['anc_record_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detected_conditions');
    }
};
