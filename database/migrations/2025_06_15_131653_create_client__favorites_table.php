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
        Schema::create('client__favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('service_id')->references('id')->on('services')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client__favorites');
    }
};
