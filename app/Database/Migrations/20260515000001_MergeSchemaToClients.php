<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MergeSchemaToClients extends Migration
{
    public function up()
    {
        // 1. Add schema fields to clients table
        $this->forge->addColumn('clients', [
            'bpjs_kes_percent' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 1.00, // Default 1%
            ],
            'bpjs_jht_percent' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 2.00, // Default 2%
            ],
            'overtime_rate_per_hour' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0, // 0 means use formula (Salary/173)
            ],
            'tax_method' => [
                'type'       => 'VARCHAR', // SQLSRV doesn't fully support ENUM, so we use VARCHAR
                'constraint' => '20',
                'default'    => 'Gross',
            ],
            'cut_off_start' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 21,
            ],
            'cut_off_end' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 20,
            ],
        ]);

        // 2. Drop the old client_schemas table
        $this->forge->dropTable('client_schemas', true);
    }

    public function down()
    {
        // To reverse, drop the columns from clients
        $this->forge->dropColumn('clients', 'bpjs_kes_percent');
        $this->forge->dropColumn('clients', 'bpjs_jht_percent');
        $this->forge->dropColumn('clients', 'overtime_rate_per_hour');
        $this->forge->dropColumn('clients', 'tax_method');
        $this->forge->dropColumn('clients', 'cut_off_start');
        $this->forge->dropColumn('clients', 'cut_off_end');

        // Recreate the old table
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
            ],
            'bpjs_kes_percent' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 1.00,
            ],
            'bpjs_jht_percent' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 2.00,
            ],
            'overtime_rate_per_hour' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'tax_method' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'Gross',
            ],
            'cut_off_start' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 21,
            ],
            'cut_off_end' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 20,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('client_schemas');
    }
}
