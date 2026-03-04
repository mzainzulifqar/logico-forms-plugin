<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('form_sessions')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('form_questions')->cascadeOnDelete();
            $table->json('answer_value')->nullable();
            $table->timestamp('answered_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_answers');
    }
};
