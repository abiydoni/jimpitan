<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index', ['filter' => 'authFilter']);
$routes->get('/debug-schema', 'DebugSchema::index');
$routes->get('/login', 'Auth::index');
$routes->post('/auth/login', 'Auth::login');
// Force Deploy Sync
$routes->get('/cek', function() { return 'Sistem Oke - Routes Terbaca'; });
$routes->get('/logout', 'Auth::logout');

// Profile & Password Management
$routes->get('/profile', 'Home::profile', ['filter' => 'authFilter']);
$routes->post('/auth/updateProfile', 'Auth::updateProfile', ['filter' => 'authFilter']);
$routes->post('/auth/updatePassword', 'Auth::updatePassword', ['filter' => 'authFilter']);

// Guard Schedule
$routes->get('/jadwal_jaga', 'Home::jadwal_jaga', ['filter' => 'authFilter']);
$routes->get('/detail_jadwal', 'Home::jadwal_jaga', ['filter' => 'authFilter']);
$routes->post('/jadwal/update', 'Home::updateJadwal', ['filter' => 'authFilter']);
$routes->get('/jadwal/get_users', 'Home::getUsersForJadwal', ['filter' => 'authFilter']);

// User Management
$routes->get('/users', 'Home::users', ['filter' => 'authFilter']);
$routes->post('/users/store', 'Home::storeUser', ['filter' => 'authFilter']);
$routes->post('/users/update', 'Home::updateUser', ['filter' => 'authFilter']);
$routes->post('/users/delete', 'Home::deleteUser', ['filter' => 'authFilter']);

// Scan QR Jimpitan
$routes->get('/scan', 'Scan::index', ['filter' => 'authFilter']);
$routes->get('/jimpitan_manual', 'Scan::manual', ['filter' => 'authFilter']); // Manual Entry
$routes->get('/scan/today', 'Scan::today', ['filter' => 'authFilter']); // Today's Scan Page
$routes->get('/scan_today', 'Scan::today', ['filter' => 'authFilter']); // Alias
$routes->post('/scan/store', 'Scan::store', ['filter' => 'authFilter']);
$routes->post('/scan/storeManual', 'Scan::storeManual', ['filter' => 'authFilter']); // Manual Store
$routes->get('/scan/search_target', 'Scan::searchTarget', ['filter' => 'authFilter']); // Manual Search
$routes->get('/scan/getRecentScans', 'Scan::getRecentScans', ['filter' => 'authFilter']);
$routes->get('/scan/not-scanned', 'Scan::notScanned', ['filter' => 'authFilter']); // Not Scanned Page
$routes->get('/scan/getNotScannedJson', 'Scan::getNotScannedJson', ['filter' => 'authFilter']); // JSON API
$routes->get('/scan/leaderboard', 'Scan::leaderboard', ['filter' => 'authFilter']); // Leaderboard
$routes->get('/scan/Leaderboard', 'Scan::leaderboard', ['filter' => 'authFilter']); // Case Fix
$routes->post('/scan/reset', 'Scan::resetLeaderboard', ['filter' => 'authFilter']); // Reset Leaderboard
$routes->get('/leaderboard', 'Scan::leaderboard', ['filter' => 'authFilter']); // Alias Shortcut

// Announcements
$routes->get('announcement', 'Announcement::index', ['filter' => 'authFilter']);
$routes->get('announcement/create', 'Announcement::create', ['filter' => 'authFilter']);
$routes->post('announcement/store', 'Announcement::store', ['filter' => 'authFilter']);
$routes->get('announcement/edit/(:num)', 'Announcement::edit/$1', ['filter' => 'authFilter']);
$routes->put('announcement/update/(:num)', 'Announcement::update/$1', ['filter' => 'authFilter']);
$routes->delete('announcement/delete/(:num)', 'Announcement::delete/$1', ['filter' => 'authFilter']);

// Data KK Management
$routes->get('/kk', 'KK::index', ['filter' => 'authFilter']);
$routes->get('/kk/search', 'KK::searchWarga', ['filter' => 'authFilter']);
$routes->post('/kk/store', 'KK::store', ['filter' => 'authFilter']);
$routes->post('/kk/update', 'KK::update', ['filter' => 'authFilter']);
$routes->post('/kk/delete', 'KK::delete', ['filter' => 'authFilter']);
// Data Warga Module
$routes->get('warga', 'Warga::index', ['filter' => 'authFilter']);
$routes->post('warga/store', 'Warga::store', ['filter' => 'authFilter']);
$routes->post('warga/update', 'Warga::update', ['filter' => 'authFilter']);
$routes->post('warga/delete/(:num)', 'Warga::delete/$1', ['filter' => 'authFilter']);

// Logs
$routes->get('/logs', 'Log::index', ['filter' => 'authFilter']);

$routes->get('bill-details', 'Home::bill_details', ['filter' => 'authFilter']);

// Payment Module
$routes->get('/payment', 'Payment::index', ['filter' => 'authFilter']);
$routes->get('/payment/warga/(:segment)', 'Payment::warga/$1', ['filter' => 'authFilter']);
$routes->get('/payment/detail/(:segment)/(:segment)', 'Payment::detail/$1/$2', ['filter' => 'authFilter']);
$routes->post('/payment/process', 'Payment::process', ['filter' => 'authFilter']);
$routes->post('/payment/delete', 'Payment::delete', ['filter' => 'authFilter']);
$routes->get('/payment/history-global/(:segment)', 'Payment::get_global_history/$1', ['filter' => 'authFilter']);
$routes->get('/payment/summary-global', 'Payment::get_global_summary', ['filter' => 'authFilter']);
$routes->get('/payment/history-personal/(:segment)/(:segment)', 'Payment::get_personal_history/$1/$2', ['filter' => 'authFilter']);

// Bebas Iuran Module
$routes->get('/bebas-iuran', 'BebasIuran::index', ['filter' => 'authFilter']);
$routes->get('/bebas_iuran', 'BebasIuran::index', ['filter' => 'authFilter']); // Alias for convenience
$routes->post('/bebas-iuran/store', 'BebasIuran::store', ['filter' => 'authFilter']);
$routes->post('/bebas-iuran/delete', 'BebasIuran::delete', ['filter' => 'authFilter']);
$routes->get('/bebas-iuran/search-warga', 'BebasIuran::searchWarga', ['filter' => 'authFilter']);

// Temporary Debug
$routes->get('debug-warga', 'DebugWarga::index');

// Tarif Management
$routes->get('/tarif', 'Tarif::index', ['filter' => 'authFilter']);
$routes->post('/tarif/store', 'Tarif::store', ['filter' => 'authFilter']);
$routes->post('/tarif/update', 'Tarif::update', ['filter' => 'authFilter']);
$routes->post('/tarif/delete', 'Tarif::delete', ['filter' => 'authFilter']);
$routes->post('/tarif/toggleStatus', 'Tarif::toggleStatus', ['filter' => 'authFilter']);

// Menu Management
$routes->get('/menu', 'Menu::index', ['filter' => 'authFilter']);
$routes->post('/menu/store', 'Menu::store', ['filter' => 'authFilter']);
$routes->post('/menu/update', 'Menu::update', ['filter' => 'authFilter']);
$routes->post('/menu/delete', 'Menu::delete', ['filter' => 'authFilter']);
$routes->post('/menu/toggleStatus', 'Menu::toggleStatus', ['filter' => 'authFilter']);

// Role Management
$routes->get('/role', 'Role::index', ['filter' => 'authFilter']);
$routes->post('/role/store', 'Role::store', ['filter' => 'authFilter']);
$routes->post('/role/update', 'Role::update', ['filter' => 'authFilter']);
$routes->post('/role/delete', 'Role::delete', ['filter' => 'authFilter']);

// Pengurus Management
$routes->get('/pengurus', 'Pengurus::index', ['filter' => 'authFilter']);
$routes->post('/pengurus/store', 'Pengurus::store', ['filter' => 'authFilter']);
$routes->get('/pengurus/get/(:num)', 'Pengurus::get/$1', ['filter' => 'authFilter']);
$routes->post('/pengurus/update', 'Pengurus::update', ['filter' => 'authFilter']);
$routes->post('/pengurus/delete', 'Pengurus::delete', ['filter' => 'authFilter']);

// Profil Management
$routes->get('/profil', 'Profil::index', ['filter' => 'authFilter']);
$routes->post('/profil/update', 'Profil::update', ['filter' => 'authFilter']);
$routes->post('/profil/updatePhoto', 'Home::updateMemberPhoto', ['filter' => 'authFilter']);

// Keuangan (Jurnal)
$routes->group('keuangan', ['filter' => 'authFilter'], function($routes) {
    $routes->get('jurnal_sub', 'Keuangan::jurnal_sub');
    $routes->get('jurnal_umum', 'Keuangan::jurnal_umum');
    $routes->post('save_sub', 'Keuangan::save_sub');
    $routes->post('save_umum', 'Keuangan::save_umum');
});

// Inventori Barang
$routes->get('/barang', 'Barang::index', ['filter' => 'authFilter']);
$routes->post('/barang/store', 'Barang::store', ['filter' => 'authFilter']);
$routes->post('/barang/update', 'Barang::update', ['filter' => 'authFilter']);
$routes->post('/barang/delete', 'Barang::delete', ['filter' => 'authFilter']);
$routes->get('/debug-barang', 'CheckBarang::index');

// Peminjaman Barang
$routes->get('/peminjaman', 'Peminjaman::index', ['filter' => 'authFilter']);
$routes->post('/peminjaman/store', 'Peminjaman::store', ['filter' => 'authFilter']);
$routes->post('/peminjaman/returnItem', 'Peminjaman::returnItem', ['filter' => 'authFilter']);
$routes->post('/peminjaman/delete', 'Peminjaman::delete', ['filter' => 'authFilter']);


// Chat Feature
$routes->get('/chat', 'Chat::index', ['filter' => 'authFilter']);
$routes->get('/chat/users', 'Chat::getUsers', ['filter' => 'authFilter']);
$routes->get('/chat/messages', 'Chat::getMessages', ['filter' => 'authFilter']);
$routes->post('/chat/send', 'Chat::sendMessage', ['filter' => 'authFilter']);
$routes->post('/chat/system-send', 'Chat::sendSystemMessage'); // No authFilter because it uses API Key
$routes->get('/chat/poll', 'Chat::pollUpdates', ['filter' => 'authFilter']);

// Push Notifications
$routes->post('/push/subscribe', 'PushSubscription::subscribe', ['filter' => 'authFilter']);
$routes->post('/push/subscribe_fcm', 'PushSubscription::subscribe_fcm', ['filter' => 'authFilter']);
$routes->post('/push/check_fcm', 'PushSubscription::check_fcm', ['filter' => 'authFilter']);
$routes->get('/push/debug-tokens', 'PushSubscription::debugTokens'); // Temporarily public for debugging
$routes->get('/push/test-push', 'PushSubscription::testPush', ['filter' => 'authFilter']);
$routes->post('/push/unsubscribe_all', 'PushSubscription::unsubscribeAll', ['filter' => 'authFilter']);

// Temporary Test Route
$routes->get('/testpush', 'TestPush::index');
