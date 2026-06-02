<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBpjsAndTipeToTaxSchemes extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Add tipe to tax_schemes
        if (!$db->fieldExists('tipe', 'tax_schemes')) {
            $this->forge->addColumn('tax_schemes', [
                'tipe' => [
                    'type'       => 'NVARCHAR',
                    'constraint' => '20',
                    'default'    => 'pph21',
                    'null'       => true,
                ]
            ]);
            // Update existing data to default 'pph21'
            $db->query("UPDATE tax_schemes SET tipe = 'pph21' WHERE tipe IS NULL");
        }
        
        // Add bpjs_scheme_id to client_payroll_configs
        if (!$db->fieldExists('bpjs_scheme_id', 'client_payroll_configs')) {
            $this->forge->addColumn('client_payroll_configs', [
                'bpjs_scheme_id' => [
                    'type'       => 'INT',
                    'null'       => true,
                ]
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('tax_schemes', 'tipe');
        $this->forge->dropColumn('client_payroll_configs', 'bpjs_scheme_id');
    }
}
