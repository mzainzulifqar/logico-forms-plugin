<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_logic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('form_questions')->cascadeOnDelete();
            $table->string('operator'); // equals, not_equals, always, greater_than, etc.
            $table->string('value')->default('');
            $table->unsignedBigInteger('next_question_id')->nullable();
            $table->timestamps();

            $table->foreign('next_question_id')
                ->references('id')
                ->on('form_questions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_logic');
    }
};
