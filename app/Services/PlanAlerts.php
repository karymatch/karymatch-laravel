<?php

namespace App\Services;

use App\Models\Company;

class PlanAlerts
{
    /**
     * Daftar peringatan untuk satu perusahaan: paket hampir habis atau habis,
     * kuota iklan hampir habis atau penuh, batas undangan, dan iklan yang akan berakhir.
     * Tiap item: ['level' => 'warn'|'danger', 'text' => '...', 'plan' => true|false]
     */
    public static function for(Company $company): array
    {
        $alerts = [];
        $plan   = $company->effectivePlan();

        if ($company->planIsActive()) {
            $d = $company->daysLeft();
            if ($d <= 7) {
                $when = $d <= 1 ? ($d === 0 ? 'berakhir hari ini' : 'berakhir besok') : "sisa {$d} hari";
                $alerts[] = ['level' => 'warn', 'plan' => true,
                    'text' => "Paket {$plan->name} hampir habis: {$when} (berakhir ".$company->plan_ends_at->locale('id')->translatedFormat('j M Y').'). Perpanjang agar badge, urutan iklan, dan kuota tidak turun ke Gratis.'];
            }
        } elseif ($company->planHasLapsed()) {
            $alerts[] = ['level' => 'danger', 'plan' => true,
                'text' => 'Paket '.ucfirst($company->plan_slug).' sudah habis sejak '.$company->plan_ends_at->locale('id')->translatedFormat('j M Y').'. Akun kembali ke paket Gratis. Iklan yang sedang tayang tetap jalan sampai masa tayangnya selesai; pilih paket lagi untuk mendapat kuota penuh.'];
        }

        if (! $plan->unlimitedAds()) {
            $used = $company->liveAdsCount();
            if ($used >= $plan->max_active_ads) {
                $alerts[] = ['level' => 'danger', 'plan' => true,
                    'text' => "Kuota iklan paket {$plan->name} habis ({$used}/{$plan->max_active_ads}). Tutup satu iklan atau upgrade paket untuk memasang iklan baru."];
            } elseif ($used >= (int) ceil($plan->max_active_ads * 0.8)) {
                $alerts[] = ['level' => 'warn', 'plan' => true,
                    'text' => "Kuota iklan paket {$plan->name} hampir habis ({$used}/{$plan->max_active_ads})."];
            }
        }

        $invites = $company->invitesLast24h();
        if ($invites >= $plan->max_daily_invites) {
            $alerts[] = ['level' => 'warn', 'plan' => true,
                'text' => "Batas undangan kandidat paket {$plan->name} tercapai ({$invites}/{$plan->max_daily_invites} dalam 24 jam terakhir)."];
        }

        $soon = $company->jobPosts()->live()->where('job_posts.ends_at', '<=', now()->addDays(3))->count();
        if ($soon > 0) {
            $alerts[] = ['level' => 'warn', 'plan' => false,
                'text' => "{$soon} iklan akan berakhir dalam 3 hari. Tekan Up ulang agar tetap tayang."];
        }

        return $alerts;
    }
}
