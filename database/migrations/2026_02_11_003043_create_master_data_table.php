<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tahun_ajarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tahun_ajaran', 9); // 2023/2024
            $table->enum('semester', ['1', '2']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['tahun_ajaran', 'semester']);
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_kelas', 10)->unique();
            $table->string('nama_kelas'); // X IPA 1, XII TKJ 2
            $table->tinyInteger('tingkat');
            $table->string('jurusan')->nullable(); // IPA, IPS, TKJ, dll
            $table->integer('kapasitas')->default(40);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mata_pelajarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel');
            $table->enum('kelompok', ['A', 'B', 'C'])->default('A'); // A: Wajib, B: Jurusan, C: Peminatan
            $table->integer('kkm')->default(75);
            $table->integer('jam_per_minggu')->default(2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('gurus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('nuptk')->unique()->nullable();
            $table->string('status_kepegawaian')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->string('bidang_studi')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->timestamps();
        });

        Schema::create('siswas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('nisn')->unique()->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->string('agama')->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('pekerjaan_orang_tua')->nullable();
            $table->string('alamat_orang_tua')->nullable();
            $table->string('no_telp_orang_tua')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('kelas_siswa', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('tahun_ajaran_id')
                ->constrained('tahun_ajarans')
                ->cascadeOnDelete();

            $table->foreignUuid('kelas_id')
                ->constrained('kelas')
                ->cascadeOnDelete();

            $table->foreignUuid('siswa_id')
                ->constrained('siswas')
                ->cascadeOnDelete();

            $table->integer('no_absen')->nullable();
            $table->enum('status', ['aktif', 'pindah', 'lulus', 'dropout'])->default('aktif');
            $table->timestamps();

            $table->unique(['tahun_ajaran_id', 'kelas_id', 'siswa_id']);
        });

        Schema::create('guru_mengajar', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('tahun_ajaran_id')
                ->constrained('tahun_ajarans')
                ->cascadeOnDelete();

            $table->foreignUuid('guru_id')
                ->constrained('gurus')
                ->cascadeOnDelete();

            $table->foreignUuid('kelas_id')
                ->constrained('kelas')
                ->cascadeOnDelete();

            $table->foreignUuid('mata_pelajaran_id')
                ->constrained('mata_pelajarans')
                ->cascadeOnDelete();

            $table->integer('jam_per_minggu')->default(2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['tahun_ajaran_id', 'guru_id', 'kelas_id', 'mata_pelajaran_id'],
                'gm_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guru_mengajar');
        Schema::dropIfExists('kelas_siswa');
        Schema::dropIfExists('siswas');
        Schema::dropIfExists('gurus');
        Schema::dropIfExists('mata_pelajarans');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('tahun_ajarans');
    }
};
