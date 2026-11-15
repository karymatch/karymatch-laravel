<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $guarded = ['id'];

    /** Urutan tahap normal. "screening" adalah nama lama untuk "ditinjau". */
    public const FLOW = ['baru', 'ditinjau', 'lolos', 'wawancara', 'penawaran', 'diterima'];

    public const LABELS = [
        'baru'      => 'Dikirim',
        'ditinjau'  => 'Ditinjau',
        'screening' => 'Ditinjau',
        'lolos'     => 'Lolos berkas',
        'wawancara' => 'Interview',
        'penawaran' => 'Penawaran',
        'diterima'  => 'Diterima',
        'ditolak'   => 'Ditolak',
        'ditarik'   => 'Ditarik',
    ];

    protected function casts(): array
    {
        return [
            'screening_answers' => 'array',
            'cv_snapshot'       => 'array',
            'events'            => 'array',
        ];
    }

    public function jobPost(): BelongsTo { return $this->belongsTo(JobPost::class); }
    public function applicant(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }

    public function label(?string $status = null): string
    {
        $status ??= $this->status;

        return self::LABELS[$status] ?? ucfirst($status);
    }

    /** Persen untuk bar progres. Ditolak dan ditarik dipenuhi supaya jelas sudah selesai. */
    public function progress(): int
    {
        $i = array_search($this->status === 'screening' ? 'ditinjau' : $this->status, self::FLOW, true);

        return $i === false ? 100 : (int) round($i / (count(self::FLOW) - 1) * 100);
    }

    /** Catat perpindahan tahap ke riwayat dan ubah status. */
    public function moveTo(string $status): void
    {
        $events   = $this->events ?? [];
        $events[] = ['s' => $status, 't' => now()->toDateTimeString()];

        $this->update(['status' => $status, 'events' => $events]);
    }

    /** Pelamar hanya boleh menarik lamaran selama masih "Dikirim" (belum disentuh perusahaan). */
    public function canWithdraw(): bool
    {
        return $this->status === 'baru';
    }

    public function isFinal(): bool
    {
        return in_array($this->status, ['diterima', 'ditolak', 'ditarik'], true);
    }
}
