<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsTransparentToAnnouncements extends Migration
{
    public function up()
    {
        $this->forge->addColumn('announcements', [
            'is_transparent' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1, // Default to transparent as per latest user preference
                'after'      => 'is_active'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('announcements', 'is_transparent');
    }
}
