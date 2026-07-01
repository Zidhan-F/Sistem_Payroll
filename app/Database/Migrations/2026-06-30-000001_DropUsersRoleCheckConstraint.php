<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropUsersRoleCheckConstraint extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // Find and drop CHECK constraints on the 'role' column of 'users' table
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

        // Alter role column size to VARCHAR(50) to prevent truncation errors for longer roles
        $db->query("ALTER TABLE users ALTER COLUMN role VARCHAR(50)");
    }

    public function down()
    {
        // Re-enforcing the constraint is not desired because multiple roles like 'payroll', 'staff', 'pending' are required by the application.
    }
}
