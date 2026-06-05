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
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('text')->nullable();

            $table->string('image')->nullable();

            $table->enum('redirect_type', ['internal', 'external'])
                ->default('internal');

            $table->string('link')->nullable();

            $table->string('path')->nullable();

            $table->json('params')->nullable();
            
            $table->unsignedTinyInteger('count')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_notifications');
    }
};