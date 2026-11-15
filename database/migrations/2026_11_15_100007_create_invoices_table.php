<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('plan_slug', 20);
            $table->unsignedTinyInteger('months');         // 1 atau 12
            $table->unsignedInteger('amount');             // nominal termasuk kode unik
            // menunggu, lunas, ditolak, kedaluwarsa
            $table->string('status', 20)->default('menunggu')->index();
            $table->timestamp('expires_at')->nullable();   // batas waktu bayar
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('period_start')->nullable(); // masa aktif yang diberikan
            $table->timestamp('period_end')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamps();

            $table->foreign('plan_slug')->references('slug')->on('plans');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
