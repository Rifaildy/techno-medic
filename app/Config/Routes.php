<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ── Halaman Utama → redirect ke login ───────────────────────────────────────
$routes->get('/', static fn() => redirect()->to('/login'));

// ── Auth ─────────────────────────────────────────────────────────────────────
$routes->get('login',  'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->post('logout','AuthController::logout');

// ── Dashboard & area terproteksi ─────────────────────────────────────────────
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');
});
