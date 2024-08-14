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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('driver_id');
            $table->string('driver_phone')->default('0');
            $table->boolean('status')->default(0);
            $table->decimal('lat')->default('0');
            $table->decimal('lng')->default('0');
            $table->foreignId('laundry_id')->constrained('laundries');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};