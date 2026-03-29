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
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
            $table->morphs('bookmarkable'); // bookmarkable_id + bookmarkable_type
            $table->timestamps();
            
            $table->unique(['customer_id', 'bookmarkable_id', 'bookmarkable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmark');
    }
};
