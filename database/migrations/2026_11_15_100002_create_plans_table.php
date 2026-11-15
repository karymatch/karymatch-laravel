<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 20)->unique();            // gratis, bisnis, pro
            $table->string('name', 50);
            $table->unsignedInteger('price_month')->default(0); // rupiah per bulan
            $table->unsignedInteger('max_active_ads')->nullable(); // null = tanpa batas
            $table->unsignedSmallInteger('max_screening_questions');
            $table->unsignedSmallInteger('max_daily_invites');
            $table->unsignedSmallInteger('ad_duration_days');   // masa tayang tiap iklan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
