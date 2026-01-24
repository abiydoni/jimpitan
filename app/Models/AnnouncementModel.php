<?php

namespace App\Models;

use CodeIgniter\Model;

class AnnouncementModel extends Model
{
    protected $table            = 'announcements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['title', 'content', 'image', 'start_date', 'end_date', 'is_active', 'is_transparent', 'hide_text', 'created_at', 'updated_at'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Helper to get active announcements
    public function getActiveAnnouncements()
    {
        $now = date('Y-m-d H:i:s');
        return $this->where('is_active', 1)
                    ->groupStart()
                        ->where('start_date <=', $now)
                        ->orWhere('start_date', null)
                    ->groupEnd()
                    ->groupStart()
                        ->where('end_date >=', $now)
                        ->orWhere('end_date', null)
                    ->groupEnd()
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
