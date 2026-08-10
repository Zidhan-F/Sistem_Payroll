<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserClients extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => false,
            ],
            'client_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'client_id'], false, true); // Unique key
        $this->forge->createTable('user_clients', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_clients', true);
    }
}
