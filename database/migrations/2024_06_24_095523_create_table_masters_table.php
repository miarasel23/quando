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
        Schema::create('table_masters', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('restaurant_id');
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade')->onUpdate('cascade');
            $table->string('table_id');
            $table->string('table_name');
            $table->string('capacity');
            $table->string('description')->nullable();
            $table->string('min_seats');
            $table->string('max_seats');
            $table->enum('reservation_online',['yes','no'])->default('no');
            $table->unsignedBigInteger('floor_area_id');
            $table->foreign('floor_area_id')->references('id')->on('floor_areas')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_masters');
    }
};
