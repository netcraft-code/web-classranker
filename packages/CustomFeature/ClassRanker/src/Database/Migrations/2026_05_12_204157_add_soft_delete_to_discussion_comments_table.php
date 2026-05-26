<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discussion_comments', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('deleted_by_type')->nullable()->after('deleted_at'); // 'customer' | 'admin'
            $table->unsignedBigInteger('deleted_by_id')->nullable()->after('deleted_by_type');
            $table->timestamp('edited_at')->nullable()->after('deleted_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('discussion_comments', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['deleted_by_type', 'deleted_by_id', 'edited_at']);
        });
    }
};