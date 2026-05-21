<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserNameToSystemLogs extends Migration
{
    public function up()
    {
        // Add user_name column if it does not exist
        $fields = [
            'user_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ];
        $this->forge->addColumn('system_logs', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('system_logs', 'user_name');
    }
}
?>
