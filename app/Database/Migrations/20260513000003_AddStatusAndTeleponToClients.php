<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusAndTeleponToClients extends Migration
{
    public function up()
    {
        $fields = [
            'telepon' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'after'      => 'email',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'Aktif',
                'after'      => 'alamat',
            ],
        ];
        $this->forge->addColumn('clients', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('clients', ['telepon', 'status']);
    }
}
