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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();

            // Explicit unsignedBigInteger for foreign key
            $table->unsignedBigInteger('board_id');

            $table->string('name');
            $table->string('code');
            $table->string('title');
            $table->boolean('is_premium')->default(false);
            $table->string('avatar')->nullable();
            $table->boolean('status')->default(false);

            $table->timestamps();

            // Official, Eloquent-friendly foreign key
            $table->foreign('board_id')
                ->references('id')
                ->on('boards')
                ->onDelete('cascade'); // automatic cleanup
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropForeign(['board_id']); // drop foreign key first
        });

        Schema::dropIfExists('grades');
    }
};
