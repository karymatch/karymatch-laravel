<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = ['id'];

    public static function gratis(): self
    {
        return static::where('slug', 'gratis')->firstOrFail();
    }

    /** true kalau kuota iklan tidak dibatasi (paket Pro) */
    public function unlimitedAds(): bool
    {
        return $this->max_active_ads === null;
    }
}
