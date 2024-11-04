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
        Schema::create('address_laundries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laundry_id')->constrained()->onDelete('cascade');
            $table->string('city');
            $table->string('address_line_1')->default('0');
            $table->decimal('lat', 8, 5); 
            $table->decimal('lng', 8, 5);
            $table->boolean('is_primary')->default(0); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_laundries');
    }
};
