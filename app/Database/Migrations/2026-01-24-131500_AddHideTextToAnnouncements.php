<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHideTextToAnnouncements extends Migration
{
    public function up()
    {
        $this->forge->addColumn('announcements', [
            'hide_text' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_transparent'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('announcements', 'hide_text');
    }
}
