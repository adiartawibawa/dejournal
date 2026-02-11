<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'kode_kelas',
        'nama_kelas',
        'tingkat',
        'jurusan',
        'kapasitas',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relasi dengan wali kelas (guru)
    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    // Relasi dengan kelas siswa
    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class);
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

    // Get daftar siswa di tahun ajaran aktif
    public function siswaAktif()
    {
        $tahunAktif = TahunAjaran::active()->first();

        return $this->belongsToMany(Siswa::class, 'kelas_siswa')
            ->wherePivot('tahun_ajaran_id', $tahunAktif?->id ?? null)
            ->wherePivot('status', 'aktif')
            ->withPivot('no_absen', 'status')
            ->orderBy('no_absen');
    }

    // Scope untuk kelas aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk filter tingkat
    public function scopeTingkat($query, $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }

    // Get full nama kelas
    public function getFullNamaAttribute(): string
    {
        $jurusan = $this->jurusan ? " {$this->jurusan}" : '';
        return "{$this->nama_kelas}{$jurusan}";
    }

    // Get jumlah siswa aktif
    public function getJumlahSiswaAttribute(): int
    {
        return $this->kelasSiswa()
            ->whereHas('tahunAjaran', fn($q) => $q->active())
            ->where('status', 'aktif')
            ->count();
    }
}
