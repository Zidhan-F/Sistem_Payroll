<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollSchemeTemplates extends Migration
{
    public function up()
    {
        // Tabel untuk menyimpan multiple skema payroll per kombinasi divisi-departemen-posisi
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'client_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'ID Klien'
            ],
            'division_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID Divisi (opsional, null = berlaku untuk semua divisi)'
            ],
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID Departemen (opsional, null = berlaku untuk semua departemen)'
            ],
            'position_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID Posisi (opsional, null = berlaku untuk semua posisi)'
            ],
            'nama_skema' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'comment'    => 'Nama skema payroll (contoh: Skema Manager IT, Skema Staff Admin)'
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Deskripsi atau catatan skema'
            ],
            
            // Sumber Gaji Pokok
            'sumber_gaji' => [
                'type'       => 'ENUM',
                'constraint' => ['ump', 'umk', 'nominal'],
                'default'    => 'nominal',
                'comment'    => 'Sumber gaji: ump (UMP Provinsi), umk (UMK Kota), nominal (Custom)'
            ],
            'nilai_gaji_pokok' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'comment'    => 'Nilai gaji pokok jika sumber = nominal'
            ],
            'minimum_wage_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID UMP/UMK jika sumber = ump/umk'
            ],
            
            // Komponen Kompensasi
            'tunjangan_transport' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'tunjangan_makan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'tunjangan_komunikasi' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'tunjangan_jabatan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'tunjangan_kehadiran' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'tunjangan_kinerja' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            
            // Potongan
            'potongan_pinjaman' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'potongan_kasbon' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'potongan_lainnya' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            
            // Konfigurasi Absensi
            'potongan_per_alpa' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'comment'    => 'Potongan per hari alpa (0 = otomatis gaji/22)'
            ],
            'bonus_per_hadir' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            
            // Konfigurasi Lembur
            'rate_lembur_per_jam' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'comment'    => '0 = otomatis (gaji/173) x 1.5'
            ],
            
            // BPJS & Pajak
            'bpjs_kes_karyawan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 1.00,
                'comment'    => 'Persentase BPJS Kesehatan Karyawan'
            ],
            'bpjs_kes_perusahaan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 4.00,
            ],
            'bpjs_jht_karyawan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 2.00,
            ],
            'bpjs_jht_perusahaan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 3.70,
            ],
            'bpjs_jp_karyawan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 1.00,
            ],
            'bpjs_jp_perusahaan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 2.00,
            ],
            'bpjs_jkk_perusahaan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.24,
            ],
            'bpjs_jkm_perusahaan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.30,
            ],
            'metode_pajak' => [
                'type'       => 'ENUM',
                'constraint' => ['Gross', 'Net', 'Gross Up'],
                'default'    => 'Gross',
            ],
            'ptkp_status' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'default'    => 'TK/0',
                'comment'    => 'Status PTKP default untuk skema ini'
            ],
            
            // Status
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1 = Aktif, 0 = Nonaktif'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('client_id');
        $this->forge->addKey(['division_id', 'department_id', 'position_id']);
        $this->forge->createTable('payroll_scheme_templates');
    }

    public function down()
    {
        $this->forge->dropTable('payroll_scheme_templates');
    }
}
