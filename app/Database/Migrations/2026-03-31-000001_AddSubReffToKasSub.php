<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSubReffToKasSub extends Migration
{
    public function up()
    {
        $fields = [
            'sub_reff' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'reff'
            ],
        ];
        $this->forge->addColumn('kas_sub', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('kas_sub', 'sub_reff');
    }
}
