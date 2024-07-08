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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('guest_information_id');
            $table->foreign('guest_information_id')->references('id')->on('guest_informaions')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('table_master_id')->nullable();
            $table->foreign('table_master_id')->references('id')->on('table_masters')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('restaurant_id');
            $table->foreign('restaurant_id')->references('id')->on('restaurents')->onDelete('cascade')->onUpdate('cascade');
            $table->string('reservation_date');
            $table->string('reservation_time');
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->string('day');
            $table->string('number_of_people');
            $table->enum('status', ['pending', 'cancelled', 'confirmed', 'completed','hold'])->default('pending');
            $table->Integer('updated_by')->nullable();
            $table->String('noted')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
