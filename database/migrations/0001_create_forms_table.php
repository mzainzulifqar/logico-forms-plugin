<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->string('status')->default('draft'); // draft, published, closed
            $table->json('theme')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('end_screen_title')->nullable();
            $table->text('end_screen_message')->nullable();
            $table->string('end_screen_image_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
