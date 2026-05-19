<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNoKlienToClients extends Migration
{
    public function up()
    {
        $fields = [
            'no_klien' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'id',
            ],
        ];
        $this->forge->addColumn('clients', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('clients', 'no_klien');
    }
}
