<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPost extends Model
{
    // "jobs" sudah dipakai antrean bawaan Laravel, jadi tabel kita bernama job_posts
    protected $table = 'job_posts';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
            'ends_at'   => 'datetime',
            'is_closed' => 'boolean',
            'skills'    => 'array',
        ];
    }

    public function company(): BelongsTo     { return $this->belongsTo(Company::class); }
    public function questions(): HasMany     { return $this->hasMany(ScreeningQuestion::class)->orderBy('sort'); }
    public function applications(): HasMany  { return $this->hasMany(Application::class); }

    public function saves(): HasMany         { return $this->hasMany(SavedJob::class); }

    /** Teks "PHP, Laravel" → ["PHP","Laravel"] (pemisah koma atau baris baru, maks 15, tanpa duplikat). */
    public static function parseSkills(?string $text): array
    {
        $items = array_filter(array_map('trim', preg_split('/[\n,;]+/', (string) $text)));

        return array_values(array_unique(array_map(fn ($s) => mb_substr($s, 0, 40), array_slice($items, 0, 15))));
    }

    /** Persen skill lowongan yang dimiliki pemilik CV. Lowongan tanpa daftar skill → 0. */
    public function matchScore(?Cv $cv): int
    {
        $need = $this->skills ?? [];
        if (! $cv || ! $need) {
            return 0;
        }
        $have = $cv->skillKeys();
        $hit  = collect($need)->filter(fn ($s) => in_array(mb_strtolower($s), $have, true))->count();

        return (int) round($hit / count($need) * 100);
    }

    /** Masa tayang sudah habis. Iklan tetap tersimpan, tapi dikunci (arsip). */
    public function isExpired(): bool
    {
        return $this->ends_at->lte(now());
    }

    public function isLive(): bool
    {
        return ! $this->is_closed && ! $this->isExpired() && $this->company->isApproved();
    }

    /** Iklan yang sedang tayang dan perusahaannya sudah terverifikasi */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('job_posts.is_closed', false)
            ->where('job_posts.ends_at', '>', now())
            ->whereHas('company', fn ($q) => $q->where('verification_status', 'disetujui'));
    }

    public function daysLeft(): int
    {
        return $this->isExpired() ? 0 : (int) ceil(now()->floatDiffInDays($this->ends_at));
    }

    /**
     * Up ulang: tanggal tayang direset ke hari ini, masa tayang dihitung dari hari ini
     * sesuai paket (Gratis 15 hari, Bisnis 45 hari, Pro 60 hari). Sisa hari lama tidak ditambahkan.
     */
    public function renew(): void
    {
        $days = $this->company->effectivePlan()->ad_duration_days;

        $this->update([
            'posted_at' => now(),
            'ends_at'   => now()->addDays($days),
            'is_closed' => false,
        ]);
    }

    /** Teks gaji untuk poster, misalnya "Rp 4,5–7 jt" */
    public function salaryLabel(): string
    {
        if (! $this->salary_min && ! $this->salary_max) {
            return 'Gaji dirahasiakan';
        }

        $fmt = fn ($n) => rtrim(rtrim(number_format($n / 1000000, 1, ',', '.'), '0'), ',');

        if ($this->salary_min && $this->salary_max) {
            return 'Rp '.$fmt($this->salary_min).'–'.$fmt($this->salary_max).' jt';
        }

        return 'Rp '.$fmt($this->salary_min ?: $this->salary_max).' jt';
    }
}
