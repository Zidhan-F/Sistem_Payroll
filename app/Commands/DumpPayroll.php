<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DumpPayroll extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'app:dump-payroll';
    protected $description = 'Dumps payroll configurations and schemes';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write("=== CLIENT PAYROLL CONFIGS ===", "yellow");
        $configs = $db->table('client_payroll_configs')->get()->getResultArray();
        foreach ($configs as $c) {
            CLI::write("ID: {$c['id']}, Client: {$c['client_id']}, Div: {$c['division_id']}, Dept: {$c['department_id']}, Pos: {$c['position_id']}, Type: {$c['payroll_type']}, Scheme ID: {$c['payroll_scheme_id']}, Custom: {$c['custom_nominal']}, UMP/UMK ID: {$c['minimum_wage_id']}");
        }

        CLI::write("\n=== PAYROLL SCHEMES ===", "yellow");
        $schemes = $db->table('payroll_schemes')->get()->getResultArray();
        foreach ($schemes as $s) {
            CLI::write("ID: {$s['id']}, Nama: {$s['nama']}, Tipe: {$s['tipe']}");
            $comps = $db->table('payroll_components')->where('scheme_id', $s['id'])->get()->getResultArray();
            foreach ($comps as $co) {
                CLI::write("  Comp ID: {$co['id']}, Nama: {$co['nama']}, Jenis: {$co['jenis_komponen']}, Sumber: {$co['sumber_nilai']}, Nilai: {$co['nilai']}, Is Persentase: {$co['is_persentase']}");
            }
        }

        CLI::write("\n=== WORK LOCATIONS ===", "yellow");
        $locs = $db->table('work_locations')->get()->getResultArray();
        foreach ($locs as $l) {
            CLI::write("ID: {$l['id']}, Client: {$l['client_id']}, Nama: {$l['lokasi_kerja']}, Kota: {$l['kota_kabupaten']}, Prov: {$l['provinsi']}");
        }

        CLI::write("\n=== MINIMUM WAGES ===", "yellow");
        $wages = $db->table('minimum_wages')->get()->getResultArray();
        foreach ($wages as $w) {
            CLI::write("ID: {$w['id']}, Tipe: {$w['tipe']}, Nama: {$w['nama_daerah']}, Nominal: {$w['nominal']}, Tahun: {$w['tahun']}");
        }
    }
}
