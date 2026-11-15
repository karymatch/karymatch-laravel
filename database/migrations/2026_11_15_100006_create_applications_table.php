<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // pelamar
            // baru, screening, wawancara, penawaran, diterima, ditolak (tahap ATS)
            $table->string('status', 20)->default('baru')->index();
            $table->json('screening_answers')->nullable();
            $table->timestamps();

            $table->unique(['job_post_id', 'user_id']); // satu pelamar, satu lamaran per iklan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
