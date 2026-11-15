<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // CV & profil pencari kerja (satu akun satu CV)
        Schema::create('cvs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('summary')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('link')->nullable();
            $table->json('skills')->nullable();        // {"Excel":"Mahir","Desain Grafis":"✓"}
            $table->json('education')->nullable();     // daftar teks, satu butir satu baris
            $table->json('experience')->nullable();
            $table->json('projects')->nullable();
            $table->json('certificates')->nullable();
            $table->json('languages')->nullable();
            $table->unsignedSmallInteger('pref_salary')->nullable(); // harapan gaji, juta rupiah per bulan
            $table->string('pref_location', 100)->nullable();
            $table->string('pref_type', 30)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cvs');
    }
};
