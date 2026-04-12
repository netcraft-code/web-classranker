<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recently_viewed', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
            $table->morphs('viewable');
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['customer_id', 'viewable_id', 'viewable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recently_viewed');
    }
};