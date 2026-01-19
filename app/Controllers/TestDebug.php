<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\PushService;

class TestDebug extends BaseController
{
    public function index()
    {
        // Prevent access in production if strictly needed, but handy for debug
        // if (environment !== 'development') die('Debug only');
        
        echo "<h1>CodeIgniter 4 Environment Debug</h1>";
        echo "<pre>";
        
        echo "CI_VERSION: " . \CodeIgniter\CodeIgniter::CI_VERSION . "\n";
        echo "OS: " . PHP_OS . "\n";
        echo "ROOTPATH: " . ROOTPATH . "\n";
        echo "FCPATH: " . FCPATH . "\n";
        
        echo "\n--- .env FILE CHECK ---\n";
        $envPath = ROOTPATH . '.env';
        if (file_exists($envPath)) {
            echo ".env found at: $envPath\n";
            // Check content readability
            $content = file_get_contents($envPath, false, null, 0, 100);
            echo "First 100 bytes check: " . (strlen($content) > 0 ? "OK" : "EMPTY") . "\n";
        } else {
            echo ".env NOT FOUND at ROOTPATH.\n";
        }
        
        echo "\n--- VAPID KEYS (via env() helper) ---\n";
        $pub = env('VAPID_PUBLIC_KEY');
        $priv = env('VAPID_PRIVATE_KEY');
        
        echo "VAPID_PUBLIC_KEY: " . ($pub ? "LOADED (Len: " . strlen($pub) . ")" : "NULL/EMPTY") . "\n";
        echo "VAPID_PRIVATE_KEY: " . ($priv ? "LOADED (Len: " . strlen($priv) . ")" : "NULL/EMPTY") . "\n";
        
        echo "\n--- VAPID KEYS (via getenv()) ---\n";
        echo "getenv('VAPID_PUBLIC_KEY'): " . (getenv('VAPID_PUBLIC_KEY') ? "LOADED" : "NULL") . "\n";
        
        echo "\n--- PushService Test ---\n";
        $ps = new PushService();
        echo "PushService Instantiated.\n";
        
        // Reflection to inspect private/protected logger logs? Hard.
        // But we can try to manual parse logic here to see if it works IN CI.
        
        if (empty($pub)) {
             echo "ATTEMPTING MANUAL PARSE IN CONTROLLER:\n";
             $envContent = file_get_contents($envPath);
             preg_match('/^VAPID_PUBLIC_KEY=(.*)$/m', $envContent, $m);
             echo "Matches: " . print_r($m, true) . "\n";
        }
        
        echo "</pre>";
    }
}
