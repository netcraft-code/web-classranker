<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discussion_hashtag', function (Blueprint $table) {
            $table->unsignedBigInteger('discussion_id');
            $table->unsignedBigInteger('hashtag_id');
            $table->primary(['discussion_id', 'hashtag_id']);

            $table->foreign('discussion_id')->references('id')->on('discussions')->onDelete('cascade');
            $table->foreign('hashtag_id')->references('id')->on('hashtags')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_hashtag');
    }
};