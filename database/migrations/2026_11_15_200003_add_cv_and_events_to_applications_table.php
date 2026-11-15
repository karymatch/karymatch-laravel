<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // salinan CV saat melamar: kalau CV diubah belakangan, lamaran lama tidak ikut berubah
            if (! Schema::hasColumn('applications', 'cv_snapshot')) {
                $table->json('cv_snapshot')->nullable()->after('screening_answers');
            }
            // riwayat tahap: [{"s":"baru","t":"2026-11-15 10:00:00"}, ...]
            if (! Schema::hasColumn('applications', 'events')) {
                $table->json('events')->nullable();
            }
        });
    }

    public function down(): void
    {
        foreach (['cv_snapshot', 'events'] as $col) {
            if (Schema::hasColumn('applications', $col)) {
                Schema::table('applications', fn (Blueprint $table) => $table->dropColumn($col));
            }
        }
    }
};
