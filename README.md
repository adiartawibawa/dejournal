# **ALUR PROGRAM DETAIL - SISTEM JURNAL PEMBELAJARAN DIGITAL**

Terima kasih atas revisinya! Sebagai developer Laravel berpengalaman, saya akan memperbaiki dan menyempurnakan alur program dengan mempertimbangkan **skalabilitas, maintainability, dan kebutuhan riil sekolah**. Berikut revisi lengkap:

---

## **REVISI & PERBAIKAN SISTEM**

### **1. Struktur Role & Permission (RBAC)**

```php
Role Hierarchy:
- Super Admin (Kepsek) → assign Admin, full access
- Admin → manage data, setting sekolah, assign guru-kelas
- Guru → input jurnal, lihat rekap
- Guru juga bisa punya multiple role (Guru + Admin)
```

### **2. Perubahan Flow Guru dengan Absensi Detail**

#### **A. Persiapan Data (Master):**

- Admin input **siswa per kelas** (tahun ajaran aktif)
- Guru di-**assign** mengajar kelas & mapel tertentu
- Setting **jam efektif** per hari (contoh: Senin=8 jam, Sabtu=5 jam)

#### **B. Alur Input Jurnal (Guru):**

```
Login → Dashboard → "Jurnal Hari Ini"
    ↓
Sistem cek:
- Apakah sudah input jurnal hari ini?
- Kelas apa yang diajar hari ini (berdasarkan jadwal)?
    ↓
Tampilkan form untuk SETIAP KELAS yang diajar:
├── 1. Tanggal: [Auto, tidak bisa diubah] (today())
├── 2. Hari: [Auto]
├── 3. Jam Ke: [Dropdown 1-8] (max sesuai setting jam efektif)
├── 4. Mata Pelajaran: [Auto sesuai jadwal, bisa diganti]
├── 5. Materi Pembelajaran
├── 6. Kegiatan Pembelajaran
├── 7. **Absensi Detail**:
│   ├── Tampilkan daftar siswa di kelas tersebut
│   ├── Status per siswa: Hadir ✅ / Sakit 🤒 / Izin 📝 / Alpha ❌ / Dispensasi 🏫
│   └── Auto-count: Total hadir, sakit, izin, alpha, dispensasi
├── 8. Catatan
└── 9. Dokumentasi (multiple upload)
    ↓
Simpan per kelas → Redirect ke dashboard
```

### **3. Validasi & Aturan Bisnis**

```php
// Rules dalam StoreJurnalRequest:
1. Satu guru hanya bisa input SATU jurnal per kombinasi:
   - Tanggal (hari ini)
   - Kelas
   - Jam ke
2. Jam ke tidak boleh lebih dari jam efektif hari tersebut
3. Jika siswa sakit/izin/alpha/dispensasi, wajib isi catatan (opsional)
4. Maksimal 5 foto dokumentasi per jurnal
5. Mapel default sesuai jadwal, tapi bisa diganti dengan mapel lain yang diajarkan guru tersebut
```

### **4. Sistem Tahun Ajaran & Kelas**

```php
// Tabel tambahan untuk skalabilitas:
1. tahun_ajarans
   - id, tahun_ajaran (2023/2024), semester (1/2), is_active

2. kelas_siswas (siswa per kelas per tahun)
   - id, tahun_ajaran_id, kelas_id, siswa_id, no_absen

3. guru_mengajars (assign guru ke kelas & mapel)
   - id, tahun_ajaran_id, guru_id, kelas_id, mata_pelajaran_id

4. jadwal_pembelajarans (opsional untuk tahap 2)
   - id, hari, jam_ke, guru_id, kelas_id, mata_pelajaran_id
```

### **5. Alur Admin Lengkap**

#### **Menu Admin:**

```
Dashboard Admin →
├── 1. Master Data
│   ├── Tahun Ajaran (set active)
│   ├── Kelas
│   ├── Mata Pelajaran
│   ├── Siswa (import Excel)
│   └── Guru/Staff
├── 2. Assignments
│   ├── Siswa ke Kelas (per tahun ajaran)
│   ├── Guru mengajar Kelas & Mapel
│   └── Jadwal Mengajar (basic)
├── 3. Setting Sekolah
│   ├── Identitas (nama, alamat, logo)
│   ├── Jam efektif per hari
│   ├── Jam pelajaran per hari
│   └── Template PDF report
├── 4. Monitoring
│   ├── Jurnal per periode
│   ├── Rekap absensi
│   └── Aktivitas guru
└── 5. User Management
    ├── Tambah user
    ├── Assign role (kecuali Admin)
    └── Reset password
```

### **6. Alur Super Admin (Kepsek)**

```
Super Admin memiliki semua hak akses Admin, PLUS:
1. Assign role Admin ke user mana pun
2. Audit log (siapa mengubah apa)
3. Approval untuk perubahan kritis
4. Laporan khusus (grafik kinerja guru, tingkat ketidakhadiran)
```

### **7. Fitur Export/Report**

#### **Untuk Guru:**

```php
1. Report Harian (PDF)
   - Format formal dengan kop sekolah
   - Per kelas yang diajar hari itu
   - Tanda tangan guru

2. Report Periodik (Excel)
   - Filter by: bulan, kelas, mapel
   - Kolom sama dengan Excel lama + kolom tambahan
   - Summary absensi per siswa
```

#### **Untuk Admin/Kepsek:**

```php
1. Report All Guru (PDF/Excel)
2. Rekap Absensi Siswa (peringkat kelas dengan alpha tertinggi)
3. Grafik: Persentase kehadiran per bulan
```

## **I. AUTHENTICATION & AUTHORIZATION FLOW**

### **1.1 Login Flow**

```
1. User akses: https://jurnal.sekolah.sch.id
2. System check maintenance mode (jika ada update)
3. Form login:
   - Email/NIP
   - Password
   - Captcha (setelah 3x gagal)
4. Validasi:
   - User aktif?
   - Password match?
   - Role sesuai akses aplikasi?
5. Success:
   - Create session
   - Log activity
   - Redirect ke dashboard sesuai role
6. Failed:
   - Counter attempt++
   - Jika >5 attempts: lock 15 menit
   - Notifikasi ke admin jika suspect brute force
```

### **1.2 Multi-Role Assignment Flow (Kepsek → Admin)**

```
Kepsek Login → User Management → Pilih User (guru)
    ↓
Modal: "Assign Additional Role"
    ↓
Checkbox:
☐ Administrator (bisa manage data sekolah)
☐ Kurikulum (akses khusus laporan akademik)
☐ Wali Kelas (akses data kelas tertentu)
    ↓
Konfirmasi → System:
1. Update tabel `role_user`
2. Log: "User X diassign role Admin oleh Kepsek Y"
3. Generate email notifikasi ke user
4. Auto-generate default permissions untuk role baru
```

## **II. GURU FLOW (DETAILED)**

#### **B. Input Jurnal Flow (Per Kelas Per Jam)**

```
STEP 1: Pilih Kelas & Jam
--------------------------------
Dashboard → Klik "Isi Jurnal" pada card kelas tertentu
    ↓
System load:
1. Data kelas (X IPA 1)
2. Jam mengajar yang tersedia (07:00-07:45, 07:45-08:30, dst)
3. Mapel default dari jadwal
4. Daftar siswa (dari kelas_siswa tahun ajaran aktif)
    ↓
User pilih: Jam Ke [3-4] (multiple select bisa)

STEP 2: Form Input Detail
--------------------------------
[Section A: Pembelajaran]
1. Mata Pelajaran: [Dropdown - mapel yang diajar guru di kelas ini]
2. Materi: [Textarea + template picker]
   - Bisa pilih template: "Pendahuluan, Inti, Penutup"
   - Bisa upload RPP (PDF)
3. Kegiatan: [Checklist + custom]
   ☐ Ceramah     ☐ Diskusi     ☐ Praktikum
   ☐ Presentasi  ☐ Evaluasi    ☐ Lainnya: ______
4. Media Pembelajaran: [Multi-select]
   □ Papan Tulis  □ LCD Proyektor  □ Alat Praktikum
   □ Video        □ Modul          □ LKS

[Section B: Absensi Detail]
5. Tabel Absensi Siswa (25 siswa per halaman, pagination)
   ┌────────────────┬───────────────┐
   │ No │ Nama Siswa │ Status       │
   ├────────────────┼───────────────┤
   │ 1  │ Andi       │ [✅] Hadir   │
   │    │            │ [🤒] Sakit   │
   │    │            │ [📝] Izin    │
   │    │            │ [❌] Alpha   │
   │    │            │ [🏫] Dispensasi│
   └────────────────┴───────────────┘
   * Klik status untuk ganti
   * Jika Sakit/Izin/Dispensasi: modal untuk input keterangan
   * Auto-calculate summary

[Section C: Dokumentasi]
6. Upload Bukti:
   - Drag & drop max 5 foto
   - Auto compress (max 1MB per foto)
   - Auto rename: TGL_KELAS_JAM_001.jpg
   - Bisa preview sebelum upload
7. Catatan Khusus: [Textarea]
   - Masalah teknis?
   - Siswa yang perlu perhatian?
   - Hal penting lainnya

STEP 3: Validasi & Submit
--------------------------------
Validasi Client-side:
1. Materi wajib diisi (min 10 karakter)
2. Minimal 1 kegiatan dipilih
3. Semua siswa harus punya status
4. Jika alpha > 3 siswa: warning "Perlu lapor BK?"
    ↓
Submit → Server-side validation:
1. Duplikasi check: guru+kelas+tanggal+jam_ke
2. Jam ke valid (tidak melebihi jam efektif)
3. File type valid (jpg, png, pdf)
4. Size total < 10MB
    ↓
Success Response:
1. Save ke database (transaction)
2. Generate notification untuk wali kelas jika alpha > 20%
3. Update statistic dashboard
4. Redirect ke summary page
```

#### **C. Daily Summary Flow (Setelah Submit)**

```
Success Page menampilkan:
1. Ringkasan jurnal yang baru diinput
2. Daftar jurnal hari ini (yang sudah diisi)
3. Progress: 3/5 jam terisi (60%)
4. Tombol:
   - [Edit Jurnal Terakhir]
   - [Isi Jurnal Kelas Lainnya]
   - [Lihat Rekap Harian (PDF)]
   - [Kembali ke Dashboard]
```

### **2.2 View & Report Flow (Guru)**

#### **A. Rekap Jurnal Per Periode**

```
Menu: "Rekap Jurnal Saya" → Filter:
- Periode: [Bulan-Tahun] / Custom Range
- Kelas: [All / Pilih tertentu]
- Mapel: [All / Pilih tertentu]
    ↓
Tampilkan dalam 3 view:
1. Table View (default)
   ┌──────────┬──────┬───────┬───────────────┬─────────┐
   │ Tanggal  │Kelas │ Jam   │ Materi        │ Status  │
   ├──────────┼──────┼───────┼───────────────┼─────────┤
   │ 01/03/24 │X IPA1│ 1-2   │ Struktur Atom │ ✅ Lengkap
   │ 01/03/24 │X IPA2│ 3-4   │ Struktur Atom │ ⚠ Kurang dokumen
   └──────────┴──────┴───────┴───────────────┴─────────┘

2. Calendar View
   [Calendar bulan Maret 2024]
   - Hijau: semua jurnal lengkap
   - Kuning: ada yang belum lengkap
   - Merah: belum input jurnal
   - Klik tanggal: detail jurnal hari itu

3. Statistic View
   - Grafik: Jurnal per minggu
   - Persentase kelengkapan: 92%
   - Total siswa sakit/izin/alpha bulan ini
   - Rata-rata kehadiran per kelas

Actions per row:
- [Lihat Detail] → Modal dengan full data
- [Edit] → Jika hari ini masih bisa edit
- [Export PDF] → Single jurnal PDF
- [Copy] → Duplikat ke tanggal lain (untuk materi sama)
```

#### **B. Export System (Guru)**

```
Export Harian (PDF):
1. Pilih tanggal (default hari ini)
2. Pilih format:
   □ Format Formal (dengan kop sekolah)
   □ Format Simple (ringkas)
   □ Dengan tanda tangan digital
3. Generate PDF → Download/Preview
4. PDF berisi:
   - Kop sekolah (logo, nama, alamat)
   - Identitas guru & kelas
   - Detail jurnal per jam
   - Tabel absensi siswa
   - Dokumentasi thumbnail
   - Catatan
   - QR code verifikasi (link ke sistem)

Export Periodik (Excel):
1. Filter: Tanggal mulai - selesai
2. Pilih kolom (select all/select tertentu):
   □ Tanggal □ Kelas □ Mapel □ Materi
   □ Jumlah Hadir □ Sakit □ Izin □ Alpha
   □ Dokumentasi □ Catatan
3. Format Excel:
   - Sheet 1: Summary data
   - Sheet 2: Detail absensi per siswa
   - Sheet 3: Statistik (auto-calculated)
   - Hyperlink ke jurnal online (jika perlu)
4. Proses background (queue) jika data besar
5. Notifikasi ketika siap download
```

## **III. ADMIN FLOW (DETAILED)**

### **3.1 Master Data Management Flow**

#### **A. Setting Sekolah**

```
Menu: Setting → Identitas Sekolah
    ↓
Form dengan tabs:
Tab 1: Basic Info
- Nama Sekolah
- NPSN
- Alamat
- Telepon
- Email
- Website
- Logo (upload, crop, preview)

Tab 2: Academic Setting
- Tahun Ajaran Aktif [Dropdown]
- Semester Aktif [1/2]
- Jam efektif per hari (Senin-Sabtu)
- Waktu mulai pelajaran (default: 07:00)
- Durasi per jam pelajaran (default: 45 menit)
- Jumlah max jam per guru per hari

Tab 3: Jurnal Setting
- Waktu batas input jurnal (default: H+1)
- Required fields: [Checklist]
  ☐ Materi ☐ Kegiatan ☐ Dokumentasi ☐ Catatan jika alpha
- Template catatan default
- Auto-reminder time (jam 14:00)

Tab 4: Report Template
- Header/Footer PDF
- Tanda tangan default
- Watermark
- Format tanggal (DD/MM/YYYY atau lainnya)

Validation & Save:
1. Cek NPSN format
2. Validasi logo (ratio, size)
3. Tahun ajaran tidak overlap
4. Jam efektif valid
5. Create backup sebelum update
```

#### **B. Manage Siswa & Kelas**

```
Flow: Import Siswa Massal
1. Download template Excel
2. Isi template: NIS, Nama, Jenis Kelamin, Kelas
3. Upload → System validation:
   - NIS unique
   - Kelas tersedia
   - Format data valid
4. Preview sebelum import
5. Confirm → Import dengan queue
6. Hasil: Success X, Failed Y (download error log)

Flow: Assign Siswa ke Kelas
1. Pilih Tahun Ajaran
2. Pilih Kelas
3. D&D siswa dari "Belum diassign" ke "Sudah diassign"
4. Set No Absen (auto sequential, bisa edit manual)
5. Validasi: Satu siswa hanya di satu kelas per tahun
6. History tracking: Pindah kelas (audit log)

Flow: Assign Guru ke Kelas & Mapel
1. Pilih Tahun Ajaran
2. Pilih Guru
3. Tampilkan matrix:
   ┌──────────┬────────┬────────┬────────┐
   │ Mapel    │ X IPA1 │ X IPA2 │ X IPA3 │
   ├──────────┼────────┼────────┼────────┤
   │ Matematika│   ✅   │   ✅   │        │
   │ Fisika   │        │   ✅   │   ✅   │
   └──────────┴────────┴────────┴────────┘
4. Bisa bulk assign: Guru A mengajar semua kelas X untuk Mapel M
5. Validation: Guru tidak bentrok jam (jika jadwal sudah ada)
```

### **3.2 Monitoring Flow**

#### **A. Live Dashboard Admin**

```
Widgets:
1. Statistik Hari Ini
   - Total guru: 45
   - Sudah input: 32 (71%)
   - Belum input: 13
   - List guru belum input (bisa kirim reminder)

2. Rekap Absensi Siswa
   - Total alpha hari ini: 15 siswa
   - Kelas dengan alpha tertinggi: XII IPS 2 (5 siswa)
   - Trend 7 hari terakhir (grafik)

3. Guru Performance
   - Top 3 guru terlengkap: A(98%), B(97%), C(96%)
   - Guru perlu perhatian: X(45%), Y(50%)
   - Persentase kelengkapan dokumen

4. Recent Activities
   - User X input jurnal kelas Y
   - User Z edit data siswa
   - System backup completed
```

#### **B. Detail Monitoring per Guru**

```
Pilih Guru → Tampilkan:
1. Profile card: foto, data kontak, kelas/mapel yang diampu
2. Jurnal completion rate (bulanan)
3. Grafik: Kehadiran siswa di kelasnya
4. List jurnal yang belum lengkap
5. Ability untuk:
   - Kirim pesan langsung via sistem
   - Set reminder
   - View detail jurnal (read-only)
   - Generate report khusus untuk guru tersebut
```

### **3.3 Reporting System (Admin)**

#### **A. Laporan Periodik Otomatis**

```
Schedule (Cron Job):
1. Setiap Senin pagi:
   - Generate report minggu lalu
   - Email ke Kepsek & Waka Kurikulum
   - Auto-calculate: guru compliance rate

2. Akhir bulan:
   - Rekap absensi siswa per kelas
   - Flag siswa dengan alpha > 25%
   - Generate surat peringatan otomatis (draft)

3. Akhir semester:
   - Statistik comprehensive
   - Export semua data backup
   - Reset beberapa counter

Report Types:
1. Jurnal Compliance Report
2. Student Attendance Report
3. Teaching Activity Report
4. Documentation Completeness Report
```

## **IV. SYSTEM ARCHITECTURE FLOW**

### **4.1 Data Flow Diagram**

```
[Guru Input] → [API Gateway] → [Validation Service]
                                      ↓
                              [Journal Service]
                                      ↓
                    ┌───────────────┼───────────────┐
                    ↓               ↓               ↓
            [Absensi Service] [File Service] [Notification Service]
                    ↓               ↓               ↓
            [Database]       [Storage S3]     [Email/WhatsApp]
                    └───────────────┼───────────────┘
                                      ↓
                              [Audit Log Service]
                                      ↓
                              [Analytics Service]
                                      ↓
                              [Dashboard Cache]
```
