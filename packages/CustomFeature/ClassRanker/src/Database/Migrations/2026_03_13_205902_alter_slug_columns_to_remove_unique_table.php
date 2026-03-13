<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        Schema::table('pdfs', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('pdfs', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->unique('slug');
        });
    }
};