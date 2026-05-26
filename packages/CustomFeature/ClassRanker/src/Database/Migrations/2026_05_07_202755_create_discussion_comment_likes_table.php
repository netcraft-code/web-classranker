<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discussion_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('discussion_comment_id');
            $table->unsignedInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['discussion_comment_id', 'customer_id'], 'dcl_comment_customer_unique');
            $table->foreign('discussion_comment_id')->references('id')->on('discussion_comments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_comment_likes');
    }
};