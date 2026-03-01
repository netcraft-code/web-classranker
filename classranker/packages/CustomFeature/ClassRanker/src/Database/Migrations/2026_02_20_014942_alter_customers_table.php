<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('board_id')->nullable()->after('id');
            $table->unsignedBigInteger('grade_id')->nullable()->after('board_id');

            $table->foreign('board_id')->references('id')->on('boards')->onDelete('set null');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['board_id']);
            $table->dropForeign(['grade_id']);

            $table->dropColumn(['board_id', 'grade_id']);
        });
    }
};
