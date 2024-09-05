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
        Schema::create('laundries', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('description_ar');
            $table->string('description_en');
            $table->string('phone_number');
            $table->string('city');
            $table->string('address_line_1')->default('0');
            $table->decimal('lat', 6, 3);
            $table->decimal('lng', 6, 3);
            $table->decimal('point', 8, 1)->default(0);
            $table->foreignId('admin_id')->constrained('users');
            $table->boolean('isActive')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundries');
    }
};
