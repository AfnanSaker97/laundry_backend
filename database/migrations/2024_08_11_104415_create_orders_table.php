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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
                    // Custom columns for date and time
            $table->timestamp('pickup_time')->nullable(); 
            $table->timestamp('delivery_time')->nullable();
            $table->timestamp('order_date')->nullable(); 
            $table->string('status')->default('pending');
            $table->string('total_price')->default('0');
            $table->string('note')->default('0');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->constrained('addresses');
            $table->foreignId('laundry_id')->constrained('laundries');
            $table->foreignId('car_id')->nullable()->constrained('cars');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
