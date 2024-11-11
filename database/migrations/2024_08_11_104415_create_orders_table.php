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
            $table->string('order_number')->default('0');
            $table->string('type_order')->default('app');
            $table->timestamp('pickup_time')->nullable(); 
            $table->timestamp('delivery_time')->nullable();
            $table->timestamp('order_date')->nullable(); 
            $table->string('status')->default('pending');
            $table->decimal('base_cost')->default('0');
           // $table->decimal('total_price')->default('0');
            $table->boolean('paid')->default('0');
            $table->string('note')->default('0');
            $table->decimal('point', 8, 1)->default(0);
            $table->foreignId('order_type_id')->constrained('order_types');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->nullable()->constrained('addresses');
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
