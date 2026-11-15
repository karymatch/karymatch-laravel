<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'plan_started_at' => 'datetime',
            'plan_ends_at'    => 'datetime',
        ];
    }

    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function jobPosts(): HasMany   { return $this->hasMany(JobPost::class); }
    public function invoices(): HasMany   { return $this->hasMany(Invoice::class); }
    public function invitations(): HasMany { return $this->hasMany(Invitation::class); }
    public function planModel(): BelongsTo { return $this->belongsTo(Plan::class, 'plan_slug', 'slug'); }

    /** 'pro', 'bisnis', atau null (Gratis atau paket habis). Untuk badge dan urutan iklan. */
    public function planBadge(): ?string
    {
        return $this->planIsActive() ? $this->plan_slug : null;
    }

    public function isApproved(): bool
    {
        return $this->verification_status === 'disetujui';
    }

    /** Paket berbayar masih berlaku? */
    public function planIsActive(): bool
    {
        return $this->plan_slug && $this->plan_ends_at && $this->plan_ends_at->isFuture();
    }

    /** Paket yang dipakai sekarang. Kalau paket berbayar habis, otomatis Gratis. */
    public function effectivePlan(): Plan
    {
        return $this->planIsActive() ? $this->planModel : Plan::gratis();
    }

    /** Paket berbayar sudah pernah ada tapi masa aktifnya habis */
    public function planHasLapsed(): bool
    {
        return $this->plan_slug && $this->plan_ends_at && $this->plan_ends_at->isPast();
    }

    public function daysLeft(): int
    {
        return $this->planIsActive() ? (int) ceil(now()->floatDiffInDays($this->plan_ends_at)) : 0;
    }

    public function liveAdsCount(): int
    {
        return $this->jobPosts()->live()->count();
    }

    public function invitesLast24h(): int
    {
        return $this->invitations()->where('created_at', '>', now()->subDay())->count();
    }

    /** Masih boleh memasang iklan baru (atau menayangkan lagi iklan yang sudah berakhir)? */
    public function hasAdQuota(): bool
    {
        $plan = $this->effectivePlan();

        return $plan->unlimitedAds() || $this->liveAdsCount() < $plan->max_active_ads;
    }

    /**
     * Terapkan paket dari invoice yang sudah lunas.
     * - Paket sama dan masih aktif  : masa baru ditambahkan setelah masa lama (tidak hangus)
     * - Paket baru atau sudah habis : dihitung mulai hari pembayaran
     * - 1 bulan = bulan kalender (31 Jan + 1 bulan = 28 Feb), 12 bulan = tanggal sama tahun depan
     */
    public function applyPlan(Invoice $invoice): void
    {
        $same = $this->plan_slug === $invoice->plan_slug && $this->planIsActive();
        $from = $same ? $this->plan_ends_at->copy() : now();

        $this->plan_slug       = $invoice->plan_slug;
        $this->plan_started_at = $same ? ($this->plan_started_at ?? now()) : now();
        $this->plan_ends_at    = $from->copy()->addMonthsNoOverflow($invoice->months);
        $this->save();

        $invoice->update([
            'status'       => 'lunas',
            'paid_at'      => now(),
            'period_start' => $from,
            'period_end'   => $this->plan_ends_at,
        ]);
    }
}
