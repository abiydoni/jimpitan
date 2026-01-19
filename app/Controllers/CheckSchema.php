<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;

class CheckSchema extends BaseController
{
    public function index()
    {
        $db = Database::connect();
        echo "<pre>";
        echo "Tables:\n";
        print_r($db->listTables());
        
        echo "\nColumns in 'users':\n";
        print_r($db->getFieldNames('users'));

        echo "\nColumns in 'chats':\n";
        print_r($db->getFieldNames('chats'));
        
        // Check if there are other relevant tables
        // like chat_groups, chat_reads, etc.
        
        echo "</pre>";
    }
}
