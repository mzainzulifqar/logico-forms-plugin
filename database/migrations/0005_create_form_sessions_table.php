<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->uuid('session_uuid')->unique();
            $table->unsignedBigInteger('current_question_id')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->foreign('current_question_id')
                ->references('id')
                ->on('form_questions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_sessions');
    }
};
