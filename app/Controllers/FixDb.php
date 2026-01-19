<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;

class FixDb extends BaseController
{
    public function index()
    {
        // Security check if needed
        
        $db = Database::connect();
        $forge = \Config\Database::forge();
        
        echo "<h1>Database Fixer</h1>";
        echo "<pre>";
        
        // 1. Check if column exists
        $fields = $db->getFieldData('chats');
        $found = false;
        foreach ($fields as $field) {
            if ($field->name === 'notification_sent') {
                $found = true;
                break;
            }
        }
        
        if ($found) {
            echo "Column 'notification_sent' already exists. No action needed.\n";
        } else {
            echo "Column 'notification_sent' MISSING. Attempting to add...\n";
            
            try {
                // Raw SQL is often more reliable for simple alters than Forge in some CI versions
                $sql = "ALTER TABLE chats ADD COLUMN notification_sent TINYINT(1) NOT NULL DEFAULT 0 AFTER is_read";
                $db->query($sql);
                echo "SUCCESS: Executed: $sql\n";
                echo "Please check phpMyAdmin to verify.\n";
            } catch (\Exception $e) {
                echo "ERROR: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\nDone.";
        echo "</pre>";
    }
}
