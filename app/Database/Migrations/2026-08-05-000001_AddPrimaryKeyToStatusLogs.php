<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPrimaryKeyToStatusLogs extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('status_logs')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'user_action' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('status_logs');
        } else if (!$this->db->fieldExists('id', 'status_logs')) {
            $this->db->query("ALTER TABLE status_logs ADD id INT IDENTITY(1,1) PRIMARY KEY");
        }
    }

    public function down()
    {
        // No-op down migration for safety
    }
}
