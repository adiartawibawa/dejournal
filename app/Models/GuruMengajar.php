<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GuruMengajar extends Pivot
{
    use HasFactory, HasUuids;

    protected $table = 'guru_mengajar';

    protected $fillable = [
        'tahun_ajaran_id',
        'guru_id',
        'kelas_id',
        'mata_pelajaran_id',
        'jam_per_minggu',
        'is_active',
    ];

    protected $casts = [
        'jam_per_minggu' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relasi dengan tahun ajaran
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    // Relasi dengan guru
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    // Relasi dengan kelas
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    // Relasi dengan mata pelajaran
    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    // Scope untuk aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk tahun ajaran aktif
    public function scopeTahunAktif($query)
    {
        return $query->whereHas('tahunAjaran', fn($q) => $q->active());
    }

    // Event untuk validasi unique constraint
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $exists = self::where('tahun_ajaran_id', $model->tahun_ajaran_id)
                ->where('guru_id', $model->guru_id)
                ->where('kelas_id', $model->kelas_id)
                ->where('mata_pelajaran_id', $model->mata_pelajaran_id)
                ->where('id', '!=', $model->id)
                ->exists();

            if ($exists) {
                throw new \Exception('Guru sudah mengajar mapel ini di kelas tersebut pada tahun ajaran yang sama.');
            }
        });
    }
}
