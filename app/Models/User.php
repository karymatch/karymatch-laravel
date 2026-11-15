<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Profil perusahaan milik akun ini (hanya ada untuk role perusahaan). */
    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    /** CV pencari kerja. Dibuat kosong saat pertama dibuka. */
    public function cv(): HasOne
    {
        return $this->hasOne(Cv::class);
    }

    public function cvOrNew(): Cv
    {
        return $this->cv ?? $this->cv()->create([]);
    }

    public function applications(): HasMany { return $this->hasMany(Application::class); }
    public function savedJobs(): HasMany    { return $this->hasMany(SavedJob::class); }
    public function notices(): HasMany      { return $this->hasMany(Notice::class); }

    public function unreadNotices(): int
    {
        return $this->notices()->whereNull('read_at')->count();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}