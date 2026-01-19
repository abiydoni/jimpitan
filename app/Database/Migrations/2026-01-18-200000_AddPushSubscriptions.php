<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPushSubscriptions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '11', 
            ],
            'endpoint' => [
                'type' => 'TEXT', // Browser endpoint URL
            ],
            'p256dh' => [
                'type' => 'TEXT', // Public key
            ],
            'auth' => [
                'type' => 'TEXT', // Auth secret
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id_code', 'CASCADE', 'CASCADE');
        $attributes = ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci'];
        $this->forge->createTable('push_subscriptions', true, $attributes);
    }

    public function down()
    {
        $this->forge->dropTable('push_subscriptions', true);
    }
}
