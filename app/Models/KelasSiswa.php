<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class KelasSiswa extends Pivot
{
    use HasFactory, HasUuids;

    protected $table = 'kelas_siswa';

    protected $fillable = [
        'tahun_ajaran_id',
        'kelas_id',
        'siswa_id',
        'no_absen',
        'status',
    ];

    protected $casts = [
        'no_absen' => 'integer',
    ];

    // Relasi dengan tahun ajaran
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    // Relasi dengan kelas
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    // Relasi dengan siswa
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    // Scope untuk status aktif
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    // Scope untuk tahun ajaran aktif
    public function scopeTahunAktif($query)
    {
        return $query->whereHas('tahunAjaran', fn($q) => $q->active());
    }

    // Event untuk mengurutkan no absen otomatis
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->no_absen)) {
                $maxAbsen = self::where('kelas_id', $model->kelas_id)
                    ->where('tahun_ajaran_id', $model->tahun_ajaran_id)
                    ->max('no_absen');

                $model->no_absen = ($maxAbsen ?? 0) + 1;
            }
        });
    }
}
