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
        Schema::table('question_items', function (Blueprint $table) {
            $table->string('video_solution')
                ->nullable()
                ->after('page_number');
        });
    }

    public function down(): void
    {
        Schema::table('question_items', function (Blueprint $table) {
            $table->dropColumn('video_solution');
        });
    }
};
