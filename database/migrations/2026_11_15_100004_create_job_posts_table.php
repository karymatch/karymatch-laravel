<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('city');
            $table->decimal('lat', 10, 7)->nullable();   // untuk peta lokasi
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('type', 30)->default('Full-time');
            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('posted_at');              // tanggal tayang (di-reset saat Up ulang)
            $table->dateTime('ends_at');                // tanggal berakhir
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->index(['company_id', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
