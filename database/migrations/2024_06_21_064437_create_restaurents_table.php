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
        Schema::create('restaurents', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->Integer('restaurent_id');
            $table->string('name');
            $table->string('address');
            $table->string('phone');
            $table->string('email');
            $table->text('avatar')->nullable();
            $table->text('post_code');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->string('website')->nullable();
            $table->enum('online_order',['active', 'inactive'])->default('active');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurents');
    }
};
