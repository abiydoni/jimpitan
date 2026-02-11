<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['log'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    /**
     * Check if current user has access to a specific menu
     * @param string $menuCode The code/slug of the menu to check
     * @return bool
     */
    protected function hasMenuAccess($menuCode)
    {
        return $this->getMenuAccessType($menuCode) !== null;
    }

    /**
     * Get the access type for a specific menu
     * @param string $menuCode
     * @return string|null 'full', 'view', or null if no access
     */
    protected $menuAccessCache = [];
    
    /**
     * Get the access type for a specific menu
     * @param string $menuCode
     * @return string|null 'full', 'view', or null if no access
     */
    protected function getMenuAccessType($menuCode)
    {
        // Memoization: Check cache first
        if (array_key_exists($menuCode, $this->menuAccessCache)) {
            return $this->menuAccessCache[$menuCode];
        }

        $session = session();
        if (!$session->get('isLoggedIn')) {
             $this->menuAccessCache[$menuCode] = null;
             return null;
        }

        $role = $session->get('role');
        
        // 1. Admin & Super Admin always 'full'
        if ($role === 's_admin' || $role === 'admin') {
            $this->menuAccessCache[$menuCode] = 'full';
            return 'full';
        }

        // 2. Resolve Pengurus
        $db = \Config\Database::connect();
        $userId = $session->get('id_code');
        
        // Optimisation: Cache user/pengurus data later if needed, but for now cache the Result per menu
        $userFresh = $db->table('users')->select('tarif')->where('id_code', $userId)->get()->getRowArray();
        $linkedId = $userFresh['tarif'] ?? 0;
        
        $pengurus = null;
        if ($linkedId > 0) {
            $pengurus = $db->table('tb_pengurus')->where('id', $linkedId)->get()->getRowArray();
        }
        if (!$pengurus) {
            $pengurus = $db->table('tb_pengurus')->where('nama_pengurus', $role)->get()->getRowArray();
        }

        // 3. Resolve Menu Code
        $realMenuCode = null;
        
        // precise match on kode
        $menuItem = $db->table('tb_menu')->where('kode', $menuCode)->get()->getRowArray();
        if ($menuItem) {
            $realMenuCode = $menuItem['kode'];
        } else {
             // precise match on alamat_url (e.g. 'warga' or 'warga/index')
             $menuItem = $db->table('tb_menu')->where('alamat_url', $menuCode)->get()->getRowArray();
             if ($menuItem) {
                 $realMenuCode = $menuItem['kode'];
             }
        }
        
        if (!$realMenuCode) {
             // If we can't find a menu with this code or URL, then Access is logically Denied 
             $this->menuAccessCache[$menuCode] = null;
             return null;
        }

        // 4. Check Assignment
        if ($pengurus) {
            $assignment = $db->table('tb_pengurus_menu')
                ->where('id_pengurus', $pengurus['id'])
                ->where('kode_menu', $realMenuCode)
                ->get()->getRowArray();
            
            if ($assignment) {
                // Return explicitly set type or default to full if empty
                $result = !empty($assignment['tipe_akses']) ? $assignment['tipe_akses'] : 'full';
                $this->menuAccessCache[$menuCode] = $result;
                return $result;
            }
        }

        $this->menuAccessCache[$menuCode] = null;
        return null;
    }
}
