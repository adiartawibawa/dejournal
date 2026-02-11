<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'kelompok',
        'kkm',
        'jam_per_minggu',
        'is_active',
    ];

    protected $casts = [
        'kkm' => 'integer',
        'jam_per_minggu' => 'integer',
        'is_active' => 'boolean',
    ];

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

    // Scope untuk mapel aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk kelompok mapel
    public function scopeKelompok($query, $kelompok)
    {
        return $query->where('kelompok', $kelompok);
    }

    // Get nama mapel dengan kode
    public function getNamaLengkapAttribute(): string
    {
        return "{$this->kode_mapel} - {$this->nama_mapel}";
    }
}
