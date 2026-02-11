<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TahunAjaran extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

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

    // Scope untuk tahun ajaran aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Get formatted tahun ajaran
    public function getFormatAttribute(): string
    {
        return "{$this->tahun_ajaran} - Semester {$this->semester}";
    }

    // Method untuk mengaktifkan tahun ajaran ini dan menonaktifkan lainnya
    public function activate()
    {
        DB::transaction(function () {
            self::where('id', '!=', $this->id)->update(['is_active' => false]);
            $this->update(['is_active' => true]);
        });
    }
}
