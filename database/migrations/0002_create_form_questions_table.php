<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('type'); // text, email, number, radio, select, checkbox, rating, picture_choice, opinion_scale
            $table->text('question_text');
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('order_index')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_questions');
    }
};
