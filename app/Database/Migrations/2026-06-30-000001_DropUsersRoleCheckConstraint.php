<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropUsersRoleCheckConstraint extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        if ($db->DBDriver === 'SQLSRV') {
            $query = "
                SELECT 
                    cc.name AS constraint_name
                FROM sys.check_constraints cc
                INNER JOIN sys.tables t ON cc.parent_object_id = t.object_id
                WHERE t.name = 'users' AND (cc.name LIKE 'CK__users__role%' OR cc.definition LIKE '%[role]%')
            ";
            
            $constraints = $db->query($query)->getResultArray();
            
            foreach ($constraints as $c) {
                $name = $c['constraint_name'];
                $db->query("ALTER TABLE users DROP CONSTRAINT [{$name}]");
            }

            $db->query("ALTER TABLE users ALTER COLUMN role VARCHAR(50)");
        } else {
            if ($db->fieldExists('role', 'users')) {
                $this->forge->modifyColumn('users', [
                    'role' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 50,
                        'null'       => true,
                        'default'    => 'staff',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        // Re-enforcing the constraint is not desired because multiple roles like 'payroll', 'staff', 'pending' are required by the application.
    }
}
