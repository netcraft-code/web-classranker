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
        Schema::create('video_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('video_id')->constrained('videos')->onDelete('cascade');

            $table->string('title');
            $table->string('video_link');
            $table->string('thumbnail')->nullable();
            $table->string('duration')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('status')->default(1);

            $table->timestamps();

            $table->index(['video_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_items');
    }
};
