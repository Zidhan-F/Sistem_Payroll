<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FlattenOrgStructure extends Migration
{
    public function up()
    {
        // Add client_id directly to departments
        $this->forge->addColumn('departments', [
            'client_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
        
        // Data Migration: Link departments to client_id via divisions
        $db = \Config\Database::connect();
        $db->query("UPDATE departments 
                    SET client_id = (SELECT client_id FROM divisions WHERE divisions.id = departments.division_id)");
    }

    public function down()
    {
        $this->forge->dropColumn('departments', 'client_id');
    }
}
