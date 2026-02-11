<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Guru extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'nuptk',
        'status_kepegawaian',
        'tanggal_masuk',
        'bidang_studi',
        'pendidikan_terakhir',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
    ];

    // Relasi dengan user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi dengan guru mengajar
    public function guruMengajar(): HasMany
    {
        return $this->hasMany(GuruMengajar::class);
    }

    // // Relasi dengan jurnal
    // public function jurnals(): HasMany
    // {
    //     return $this->hasMany(Jurnal::class);
    // }

    // Relasi sebagai wali kelas
    public function waliKelas(): HasOne
    {
        return $this->hasOne(Kelas::class, 'wali_kelas_id');
    }

    // Get kelas yang diajar di tahun ajaran aktif
    public function kelasYangDiajar()
    {
        $tahunAktif = TahunAjaran::active()->first();

        return $this->belongsToMany(Kelas::class, 'guru_mengajar')
            ->wherePivot('tahun_ajaran_id', $tahunAktif?->id ?? null)
            ->wherePivot('is_active', true)
            ->withPivot('mata_pelajaran_id', 'jam_per_minggu')
            ->withTimestamps();
    }

    // Get mata pelajaran yang diajar
    public function mataPelajaranYangDiajar()
    {
        $tahunAktif = TahunAjaran::active()->first();

        return $this->belongsToMany(MataPelajaran::class, 'guru_mengajar')
            ->wherePivot('tahun_ajaran_id', $tahunAktif?->id ?? null)
            ->wherePivot('is_active', true)
            ->withPivot('kelas_id', 'jam_per_minggu')
            ->withTimestamps();
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })->orWhere('nuptk', 'like', "%{$search}%");
    }

    // Get nama guru dari user
    public function getNamaAttribute(): string
    {
        return $this->user->name;
    }

    // Get email dari user
    public function getEmailAttribute(): string
    {
        return $this->user->email;
    }

    // Cek apakah guru aktif
    public function getIsActiveAttribute(): bool
    {
        return $this->user->is_active;
    }
}
