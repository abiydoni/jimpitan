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
