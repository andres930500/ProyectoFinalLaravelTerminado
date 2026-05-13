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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('status', [
                'pending',
                'confirmed',
                'rejected',
                'cancelled',
                'finished',
            ])->default('pending');
            $table->string('user_name', 255);
            $table->string('user_email', 255);
            $table->string('user_phone')->nullable();
            $table->text('notes')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();

            $table->index('space_id');
            $table->index('status');
            $table->index('start_time');
            $table->index('end_time');
            $table->index(['space_id', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
