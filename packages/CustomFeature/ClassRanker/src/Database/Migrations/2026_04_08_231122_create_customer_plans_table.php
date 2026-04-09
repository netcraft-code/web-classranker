<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id');
            $table->unsignedBigInteger('plan_id');

            // PayU fields
            $table->string('payu_txnid')->nullable();
            $table->string('payu_mihpayid')->nullable();
            $table->string('payu_hash')->nullable();

            // Pricing snapshot
            $table->decimal('amount_paid', 10, 2);
            $table->string('currency', 10)->default('INR');

            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_plans');
    }
};