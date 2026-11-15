<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cv extends Model
{
    protected $guarded = ['id', 'user_id'];

    /** Bagian CV yang berupa daftar teks (satu butir satu baris di formulir). */
    public const LISTS = [
        'education'    => 'Riwayat pendidikan',
        'experience'   => 'Pengalaman kerja',
        'projects'     => 'Organisasi & proyek',
        'certificates' => 'Sertifikasi & pelatihan',
        'languages'    => 'Bahasa',
    ];

    protected function casts(): array
    {
        return [
            'skills'       => 'array',
            'education'    => 'array',
            'experience'   => 'array',
            'projects'     => 'array',
            'certificates' => 'array',
            'languages'    => 'array',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /**
     * Ubah teks "Excel (Mahir), Public Speaking" jadi ["Excel" => "Mahir", "Public Speaking" => "✓"].
     * Pemisah: koma, titik koma, atau baris baru. Maksimal 40 skill.
     */
    public static function parseSkills(?string $text): array
    {
        $out = [];
        foreach (array_slice(preg_split('/[\n,;]+/', (string) $text), 0, 40) as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }
            if (preg_match('/^(.+?)\s*\((.+?)\)\s*$/u', $item, $m)) {
                $name  = trim($m[1]);
                $level = trim($m[2]);
            } else {
                $name  = $item;
                $level = '✓';
            }
            $name = mb_substr($name, 0, 40);
            if ($name !== '') {
                $out[$name] = mb_substr($level, 0, 20) ?: '✓';
            }
        }

        return $out;
    }

    /** Satu butir per baris → array bersih (maks 15 butir, 200 huruf per butir). */
    public static function parseLines(?string $text): array
    {
        $lines = array_filter(array_map('trim', preg_split('/\R/', (string) $text)));

        return array_values(array_map(fn ($l) => mb_substr($l, 0, 200), array_slice($lines, 0, 15)));
    }

    public function skillsText(): string
    {
        return collect($this->skills ?? [])
            ->map(fn ($lv, $name) => $lv && $lv !== '✓' ? "{$name} ({$lv})" : $name)
            ->implode(', ');
    }

    /** Nama skill dalam huruf kecil, untuk membandingkan dengan syarat lowongan. */
    public function skillKeys(): array
    {
        return array_map('mb_strtolower', array_keys($this->skills ?? []));
    }

    /** Kelengkapan CV dalam persen (10 bagian × 10%). */
    public function completeness(): int
    {
        $filled = collect([
            $this->summary,
            $this->city,
            $this->pref_location,
            $this->skills,
            $this->education,
            $this->experience,
            $this->projects,
            $this->certificates,
            $this->languages,
            $this->pref_salary,
        ])->filter()->count();

        return $filled * 10;
    }

    /** Syarat minimum untuk melamar: ringkasan, HP, dan minimal satu skill. */
    public function readyToApply(): bool
    {
        return filled($this->summary) && filled($this->phone) && ! empty($this->skills);
    }

    public function missingForApply(): array
    {
        $miss = [];
        if (blank($this->summary)) $miss[] = 'ringkasan profil';
        if (blank($this->phone))   $miss[] = 'nomor HP';
        if (empty($this->skills))  $miss[] = 'skill';

        return $miss;
    }
}
