# Fitur Multiple Skema Payroll per Struktur Organisasi

## Deskripsi
Fitur ini memungkinkan sistem untuk menyimpan dan mengelola beberapa skema payroll yang berbeda untuk setiap kombinasi **Divisi, Departemen, dan Posisi** di setiap klien.

## Keunggulan
- **Fleksibel**: Setiap divisi, departemen, atau posisi dapat memiliki skema payroll yang berbeda
- **Hierarki**: Sistem akan memilih skema yang paling spesifik (Posisi > Departemen > Divisi > Default)
- **Lengkap**: Mencakup gaji pokok, tunjangan, potongan, BPJS, pajak, dan konfigurasi absensi/lembur

## Struktur Database

### Tabel: `payroll_scheme_templates`
Tabel utama yang menyimpan skema payroll dengan kolom-kolom:

#### Identifikasi & Organisasi
- `id`: Primary key
- `client_id`: ID klien (required)
- `division_id`: ID divisi (nullable - null = berlaku untuk semua divisi)
- `department_id`: ID departemen (nullable - null = berlaku untuk semua departemen)
- `position_id`: ID posisi (nullable - null = berlaku untuk semua posisi)
- `nama_skema`: Nama skema (contoh: "Skema Manager IT")
- `deskripsi`: Deskripsi skema

#### Gaji Pokok
- `sumber_gaji`: Enum ('ump', 'umk', 'nominal')
- `nilai_gaji_pokok`: Nilai gaji jika sumber = nominal
- `minimum_wage_id`: ID UMP/UMK jika sumber = ump/umk

#### Tunjangan
- `tunjangan_transport`
- `tunjangan_makan`
- `tunjangan_komunikasi`
- `tunjangan_jabatan`
- `tunjangan_kehadiran`
- `tunjangan_kinerja`

#### Potongan
- `potongan_pinjaman`
- `potongan_kasbon`
- `potongan_lainnya`

#### Absensi & Lembur
- `potongan_per_alpa`: Potongan per hari alpa (0 = otomatis gaji/22)
- `bonus_per_hadir`: Bonus per hari hadir
- `rate_lembur_per_jam`: Rate lembur per jam (0 = otomatis (gaji/173) x 1.5)

#### BPJS
- `bpjs_kes_karyawan`: Persentase BPJS Kesehatan Karyawan (default: 1.00%)
- `bpjs_kes_perusahaan`: Persentase BPJS Kesehatan Perusahaan (default: 4.00%)
- `bpjs_jht_karyawan`: Persentase BPJS JHT Karyawan (default: 2.00%)
- `bpjs_jht_perusahaan`: Persentase BPJS JHT Perusahaan (default: 3.70%)
- `bpjs_jp_karyawan`: Persentase BPJS JP Karyawan (default: 1.00%)
- `bpjs_jp_perusahaan`: Persentase BPJS JP Perusahaan (default: 2.00%)
- `bpjs_jkk_perusahaan`: Persentase BPJS JKK Perusahaan (default: 0.24%)
- `bpjs_jkm_perusahaan`: Persentase BPJS JKM Perusahaan (default: 0.30%)

#### Pajak
- `metode_pajak`: Enum ('Gross', 'Net', 'Gross Up')
- `ptkp_status`: Status PTKP default (contoh: 'TK/0', 'K/1')

#### Status
- `is_active`: Status aktif/nonaktif (1 = aktif, 0 = nonaktif)
- `created_at`: Tanggal dibuat
- `updated_at`: Tanggal diupdate

## API Endpoints

### 1. Get All Schemes for Client
```
GET /api/payroll-schemes?client_id={id}
```
Mengembalikan semua skema payroll untuk klien tertentu dengan informasi divisi, departemen, dan posisi.

### 2. Get Schemes by Org Structure
```
GET /api/payroll-schemes/by-org?client_id={id}&division_id={div_id}&department_id={dept_id}&position_id={pos_id}
```
Filter skema berdasarkan struktur organisasi.

### 3. Get Scheme for Employee
```
GET /api/payroll-schemes/for-employee?client_id={id}&division_id={div_id}&department_id={dept_id}&position_id={pos_id}
```
Mendapatkan skema yang paling cocok untuk karyawan berdasarkan hierarki:
1. Exact match (divisi + departemen + posisi)
2. Departemen + posisi (any divisi)
3. Posisi only
4. Departemen only
5. Divisi only
6. Client default (all null)

### 4. Get Single Scheme
```
GET /api/payroll-schemes/{id}
```

### 5. Create Scheme
```
POST /api/payroll-schemes
Body: JSON dengan semua field skema
```

### 6. Update Scheme
```
PUT /api/payroll-schemes/{id}
Body: JSON dengan field yang diupdate
```

### 7. Delete Scheme
```
DELETE /api/payroll-schemes/{id}
```

### 8. Toggle Active Status
```
POST /api/payroll-schemes/toggle-active/{id}
```

## Cara Penggunaan

### 1. Membuat Skema Baru
1. Buka workspace klien
2. Klik tab "Pilihan Skema"
3. Klik tombol "Tambah Skema" (tombol biru di sebelah dropdown)
4. Isi form:
   - **Nama Skema**: Nama deskriptif (contoh: "Skema Manager IT")
   - **Struktur Organisasi**: Pilih divisi, departemen, dan/atau posisi (bisa dikosongkan untuk berlaku umum)
   - **Gaji Pokok**: Pilih sumber (Nominal/UMP/UMK) dan nilai
   - **Tunjangan**: Isi nilai tunjangan yang berlaku
   - **Potongan**: Isi nilai potongan yang berlaku
   - **Absensi & Lembur**: Konfigurasi aturan absensi dan lembur
   - **BPJS**: Atur persentase BPJS (default sudah terisi)
   - **Pajak**: Pilih metode pajak dan status PTKP
5. Klik "Simpan Skema"

### 2. Melihat Daftar Skema
Di tab "Pilihan Skema", terdapat tabel yang menampilkan:
- Nama skema
- Struktur organisasi yang berlaku
- Sumber gaji (UMP/UMK/Nominal)
- Nilai gaji pokok
- Status (Aktif/Nonaktif)
- Tombol aksi (Lihat, Edit, Toggle Status, Hapus)

### 3. Menggunakan Skema
Saat setup payroll untuk karyawan:
1. Sistem akan otomatis memilih skema yang paling spesifik berdasarkan divisi, departemen, dan posisi karyawan
2. Atau bisa dipilih manual dari dropdown "Skema Payroll"

### 4. Mengedit Skema
1. Klik tombol edit (ikon pensil) pada skema yang ingin diedit
2. Form akan terbuka dengan data yang sudah terisi
3. Ubah data yang diperlukan
4. Klik "Simpan Skema"

### 5. Menonaktifkan Skema
1. Klik tombol toggle (ikon toggle) pada skema
2. Skema akan dinonaktifkan dan tidak muncul di dropdown pilihan
3. Klik lagi untuk mengaktifkan kembali

## Contoh Skenario

### Skenario 1: Skema Default untuk Semua Karyawan
- Nama: "Skema Standar"
- Divisi: (kosong)
- Departemen: (kosong)
- Posisi: (kosong)
- Gaji Pokok: UMK Jakarta (Rp 5.000.000)

### Skenario 2: Skema Khusus untuk Manager IT
- Nama: "Skema Manager IT"
- Divisi: IT
- Departemen: Development
- Posisi: Manager
- Gaji Pokok: Nominal (Rp 15.000.000)
- Tunjangan Jabatan: Rp 3.000.000
- Tunjangan Komunikasi: Rp 500.000

### Skenario 3: Skema untuk Semua Staff Admin
- Nama: "Skema Staff Admin"
- Divisi: (kosong)
- Departemen: (kosong)
- Posisi: Staff Admin
- Gaji Pokok: UMK Jakarta (Rp 5.000.000)
- Tunjangan Transport: Rp 500.000

## Prioritas Pemilihan Skema

Ketika sistem mencari skema untuk karyawan, prioritasnya adalah:

1. **Paling Spesifik**: Divisi + Departemen + Posisi
2. **Departemen + Posisi**: Berlaku untuk posisi tertentu di departemen tertentu, semua divisi
3. **Posisi Only**: Berlaku untuk posisi tertentu di semua departemen dan divisi
4. **Departemen Only**: Berlaku untuk semua posisi di departemen tertentu
5. **Divisi Only**: Berlaku untuk semua posisi di divisi tertentu
6. **Default**: Berlaku untuk semua (divisi, departemen, posisi = null)

## File-file yang Terlibat

### Backend
- `app/Database/Migrations/2026-05-25-000001_CreatePayrollSchemeTemplates.php` - Migration
- `app/Models/PayrollSchemeTemplateModel.php` - Model
- `app/Controllers/PayrollScheme.php` - Controller
- `app/Config/Routes.php` - Routes (updated)

### Frontend
- `public/js/modules/app-payroll-scheme-templates.js` - JavaScript module
- `app/Views/partials/_modals.php` - Modal form (updated)
- `app/Views/partials/_view_workspace.php` - View workspace (updated)
- `app/Views/partials/_scripts.php` - Script loader (updated)
- `public/js/modules/app-client.js` - Client module (updated)

## Migrasi Database

Untuk membuat tabel, jalankan:
```bash
php spark migrate
```

## Desain Skema Divisi, Departemen, dan Posisi

Sebelum membuat skema payroll, hierarki organisasi (Divisi → Departemen → Posisi) **harus** dibangun terlebih dahulu. Berikut langkah-langkahnya:

### Langkah 1: Buat Skema Tiap Divisi
- Mulailah dengan **satu divisi utama** (contoh: **STO**). Divisi pertama ini otomatis dibuat oleh sistem.
- Untuk menambahkan divisi lain, gunakan opsi **"Tambah Divisi"** secara manual.

### Langkah 2: Masukkan Divisi Baru
- Pilih **divisi induk** yang tepat saat menambahkan divisi baru (misalnya: menambahkan "Gelobang" di bawah "STO").
- Nama divisi boleh bebas, namun **selalu pastikan ia terhubung ke node induk** supaya hierarki tidak terputus.

### Langkah 3: Tambah Posisi di Dalam Masing-masing Divisi
- Posisi harus **mencerminkan data sumber** (seperti daftar karyawan).
- Jika nama posisi tidak cocok dengan sumber, **perbaiki dulu data sumbernya** sebelum menambahkan posisi ke skema.

### Langkah 4: Hubungkan Semua Divisi ke Hierarki STO
- Setiap node yang **belum terhubung** akan memunculkan error **"belum ke hubung"**.
- Pastikan setiap divisi dan posisi memiliki **parent-child relationship** yang jelas.

### Langkah 5: Validasi Hubungan
- Jalankan pemeriksaan untuk memastikan **tidak ada node terisolasi**.
- Node yang terputus akan menampilkan pesan **"error nih"**.

### Langkah 6: Simpan dan Perbarui Skema
- Setelah memperbaiki semua tautan, **simpan perubahan** sebelum melanjutkan ke perhitungan payroll atau integrasi lainnya.

### ⚠️ Hal-hal Penting yang Harus Diingat

1. **Data lokasi diambil dari klien, bukan dari skema lokal** — Pilih skema regional yang sesuai (contoh: Jakarta → WMP) agar data lokasi dan skema tidak konflik.
2. **"Sudah bikin skema-kema?"** — Pertanyaan ini berarti semua langkah di atas (Langkah 1–6) **harus selesai terlebih dahulu** sebelum melanjutkan ke proses selanjutnya (payroll, dsb).
3. **Validasi rutin** membantu mencegah error **"belum ke hubung"** atau **"error nih"** yang dapat menghambat proses payroll.

---

## Catatan Penting

1. **Validasi**: Pastikan kombinasi divisi-departemen-posisi valid (departemen harus dalam divisi yang benar)
2. **Konflik**: Jika ada multiple skema dengan spesifisitas yang sama, sistem akan mengambil yang paling baru (created_at DESC)
3. **Performa**: Index sudah ditambahkan pada kolom client_id dan kombinasi division_id, department_id, position_id
4. **Logging**: Semua operasi CRUD dicatat di system_logs

## Pengembangan Selanjutnya

Fitur yang bisa ditambahkan:
1. **Duplikasi Skema**: Tombol untuk menduplikasi skema yang sudah ada
2. **Import/Export**: Export skema ke Excel/CSV dan import kembali
3. **Versioning**: Menyimpan history perubahan skema
4. **Approval Workflow**: Skema baru perlu approval sebelum aktif
5. **Bulk Assignment**: Assign skema ke multiple karyawan sekaligus
6. **Comparison Tool**: Membandingkan 2 skema side-by-side
