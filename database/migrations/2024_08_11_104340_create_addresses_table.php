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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('address')->default('0');
            $table->string('address_line_1')->default('0');
            $table->string('address_line_2')->default('0');
            $table->string('email')->default('0');
            $table->string('country')->default('0');
            $table->string('city')->default('0');
            $table->string('postcode')->default('0');
            $table->string('contact_number')->default('0');
            $table->string('full_name')->default('0');
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('lat', 6, 3)->default('0');
            $table->decimal('lng', 6, 3)->default('0');
            $table->boolean('isActive')->default('0');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
