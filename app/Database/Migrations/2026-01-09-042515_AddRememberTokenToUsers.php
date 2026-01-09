<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRememberTokenToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'remember_token' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'shift',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'remember_token');
    }
}
