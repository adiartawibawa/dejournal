<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'nisn',
        'tempat_lahir',
        'agama',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_orang_tua',
        'alamat_orang_tua',
        'no_telp_orang_tua',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relasi dengan user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi dengan kelas siswa
    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class);
    }

    // // Relasi dengan absensi jurnal
    // public function absensi(): HasMany
    // {
    //     return $this->hasMany(JurnalAbsensi::class);
    // }

    // Get kelas saat ini (tahun ajaran aktif)
    public function kelasSaatIni()
    {
        $tahunAktif = TahunAjaran::active()->first();

        return $this->belongsToMany(Kelas::class, 'kelas_siswa')
            ->wherePivot('tahun_ajaran_id', $tahunAktif?->id ?? null)
            ->wherePivot('status', 'aktif')
            ->withPivot('no_absen', 'status')
            ->withTimestamps();
    }

    // Get semua riwayat kelas
    public function riwayatKelas()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_siswa')
            ->withPivot('tahun_ajaran_id', 'no_absen', 'status')
            ->withTimestamps()
            ->orderByPivot('tahun_ajaran_id', 'desc');
    }

    // Scope untuk siswa aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })->orWhere('nisn', 'like', "%{$search}%");
    }

    // Get nama siswa dari user
    public function getNamaAttribute(): string
    {
        return $this->user->name;
    }

    // Get email dari user
    public function getEmailAttribute(): string
    {
        return $this->user->email;
    }

    // Get tanggal lahir dari user
    public function getTanggalLahirAttribute()
    {
        return $this->user->tanggal_lahir;
    }

    // Get alamat dari user
    public function getAlamatAttribute()
    {
        return $this->user->alamat;
    }
}
