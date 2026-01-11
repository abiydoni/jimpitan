<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index', ['filter' => 'authFilter']);
$routes->get('/debug-schema', 'DebugSchema::index');
$routes->get('/login', 'Auth::index');
$routes->post('/auth/login', 'Auth::login');
$routes->get('/logout', 'Auth::logout');

// Profile & Password Management
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
$routes->post('/scan/store', 'Scan::store', ['filter' => 'authFilter']);
$routes->get('/scan/getRecentScans', 'Scan::getRecentScans', ['filter' => 'authFilter']);

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

// Payment Module
$routes->get('/payment', 'Payment::index', ['filter' => 'authFilter']);
$routes->get('/payment/warga/(:segment)', 'Payment::warga/$1', ['filter' => 'authFilter']);
$routes->get('/payment/detail/(:segment)/(:segment)', 'Payment::detail/$1/$2', ['filter' => 'authFilter']);
$routes->post('/payment/process', 'Payment::process', ['filter' => 'authFilter']);
$routes->post('/payment/delete', 'Payment::delete', ['filter' => 'authFilter']);
$routes->get('/payment/history-global/(:segment)', 'Payment::get_global_history/$1', ['filter' => 'authFilter']);
$routes->get('/payment/summary-global', 'Payment::get_global_summary', ['filter' => 'authFilter']);
$routes->get('/payment/history-personal/(:segment)/(:segment)', 'Payment::get_personal_history/$1/$2', ['filter' => 'authFilter']);

// Temporary Debug
$routes->get('debug-warga', 'DebugWarga::index');
