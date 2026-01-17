<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SetupChat extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'setup:chat';
    protected $description = 'Creates the chats table manually.';

    public function run(array $params)
    {
        $forge = \Config\Database::forge();
        
        if ($forge->createTable('chats', true, [
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sender_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'receiver_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'is_read' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ])) {
            CLI::write('Table chats created successfully!', 'green');
        } else {
             // CreateTable returns void/bool depending on implementation, but forge usually handles it. 
             // If we are here, we passed 'true' for IF NOT EXISTS.
             // We need to add fields first.
        }
        
        $forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sender_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'receiver_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'is_read' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
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
        $forge->addKey('id', true);
        if ($forge->createTable('chats', true)) {
             CLI::write('Table chats created/checked successfully!', 'green');
        } else {
             CLI::error('Failed to create table.');
        }
    }
}
