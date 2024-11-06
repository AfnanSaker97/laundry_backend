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
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->string('price');
            $table->foreignId('laundry_item_id')->constrained('laundry_items');  
            $table->foreignId('laundry_id')->constrained('laundries');  
            $table->foreignId('service_id')->constrained('services'); 
            $table->foreignId('order_type_id')->constrained('order_types'); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
