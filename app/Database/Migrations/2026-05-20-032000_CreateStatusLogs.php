<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStatusLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'user_action' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->createTable('status_logs');
    }

    public function down()
    {
        $this->forge->dropTable('status_logs');
    }
}
