<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles, SoftDeletes;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'is_active' => 'boolean',
        ];
    }

    // Relationship dengan detail guru
    public function guru(): HasOne
    {
        return $this->hasOne(Guru::class);
    }

    // Relationship dengan detail siswa
    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class);
    }

    // Cek apakah user adalah guru
    public function isGuru()
    {
        return $this->guru()->exists();
    }

    // Cek apakah user adalah siswa
    public function isSiswa()
    {
        return $this->siswa()->exists();
    }

    // Cek apakah user adalah admin/kepsek
    public function isAdministrator()
    {
        return $this->hasRole(['admin', 'kepsek']);
    }

    // Get profile berdasarkan role
    public function getProfileAttribute()
    {
        if ($this->isGuru()) {
            return $this->guru;
        } elseif ($this->isSiswa()) {
            return $this->siswa;
        }

        return null;
    }

    // Scope untuk user aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->orWhere('username', 'like', "%{$search}%");
    }
}
