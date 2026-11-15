<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('city')->nullable();
            $table->text('about')->nullable();
            // menunggu, disetujui, ditolak
            $table->string('verification_status', 20)->default('menunggu')->index();
            $table->text('rejection_reason')->nullable();
            // paket berbayar terakhir; kalau plan_ends_at sudah lewat, akun dihitung Gratis
            $table->string('plan_slug', 20)->nullable();
            $table->timestamp('plan_started_at')->nullable();
            $table->timestamp('plan_ends_at')->nullable();
            $table->timestamps();

            $table->foreign('plan_slug')->references('slug')->on('plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
