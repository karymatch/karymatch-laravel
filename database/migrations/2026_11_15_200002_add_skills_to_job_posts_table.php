<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dilewati kalau kolom sudah ada (mis. dibuat dari percobaan sebelumnya)
        if (Schema::hasColumn('job_posts', 'skills')) {
            return;
        }

        Schema::table('job_posts', function (Blueprint $table) {
            $table->json('skills')->nullable()->after('description'); // skill yang dibutuhkan, untuk skor kecocokan
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('job_posts', 'skills')) {
            Schema::table('job_posts', function (Blueprint $table) {
                $table->dropColumn('skills');
            });
        }
    }
};
