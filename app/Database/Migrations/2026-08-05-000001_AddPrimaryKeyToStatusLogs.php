<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPrimaryKeyToStatusLogs extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('id', 'status_logs')) {
            $this->db->query("ALTER TABLE status_logs ADD id INT IDENTITY(1,1) PRIMARY KEY");
        }
    }

    public function down()
    {
        // No-op down migration for safety
    }
}
