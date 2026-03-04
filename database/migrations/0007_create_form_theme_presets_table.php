<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_theme_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_system')->default(false);
            $table->string('background_color', 7)->default('#FFFFFF');
            $table->string('question_color', 7)->default('#000000');
            $table->string('answer_color', 7)->default('#4F46E5');
            $table->string('button_color', 7)->default('#4F46E5');
            $table->string('button_text_color', 7)->default('#FFFFFF');
            $table->string('font')->default('Inter');
            $table->string('border_radius')->default('medium');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_theme_presets');
    }
};
