<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // notifikasi sederhana: paket hampir habis, kuota penuh, verifikasi, dll.
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('message');
            $table->string('dedupe_key', 60)->nullable(); // supaya peringatan yang sama tidak dikirim berulang
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'dedupe_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
