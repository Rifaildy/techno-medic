<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Rute Autentikasi
$routes->get('/', 'AuthController::login');
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->post('logout', 'AuthController::logout');

// Rute Terproteksi Login (AuthFilter)
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');

    // Modul Pengelolaan Data Pasien (Halaman Utama & Endpoint AJAX)
    $routes->group('patients', static function ($routes) {
        $routes->get('/', 'PatientController::index');
        $routes->get('list', 'PatientController::list');
        $routes->get('show/(:num)', 'PatientController::show/$1');
        $routes->post('store', 'PatientController::store');
        $routes->post('update/(:num)', 'PatientController::update/$1');
        $routes->post('delete/(:num)', 'PatientController::delete/$1');
        $routes->post('restore/(:num)', 'PatientController::restore/$1');
        $routes->post('permanent-delete/(:num)', 'PatientController::permanentDelete/$1');
    });

    // Modul Pendaftaran Pasien Rawat Jalan
    $routes->group('registrations', static function ($routes) {
        $routes->get('/', 'RegistrationController::index');
        $routes->get('list', 'RegistrationController::list');
        $routes->get('show/(:num)', 'RegistrationController::show/$1');
        $routes->get('doctors-by-poly/(:num)', 'RegistrationController::getDoctorsByPoly/$1');
        $routes->get('search-patients', 'RegistrationController::searchPatients');
        $routes->post('store', 'RegistrationController::store');
        $routes->post('update-status/(:num)', 'RegistrationController::updateStatus/$1');
        $routes->post('cancel/(:num)', 'RegistrationController::cancel/$1');
    });

    // Modul Pemeriksaan & Pencatatan Tanda-Tanda Vital
    $routes->group('examinations', static function ($routes) {
        $routes->get('/', 'ExaminationController::index');
        $routes->get('list', 'ExaminationController::list');
        $routes->get('queue-list', 'ExaminationController::queueList');
        $routes->get('show/(:num)', 'ExaminationController::show/$1');
        $routes->get('by-registration/(:num)', 'ExaminationController::getByRegistration/$1');
        $routes->post('save', 'ExaminationController::save');
    });

    // Modul Manajemen Pengguna Sistem (Admin Only)
    $routes->group('users', static function ($routes) {
        $routes->get('/', 'UserController::index');
        $routes->get('list', 'UserController::list');
        $routes->get('show/(:num)', 'UserController::show/$1');
        $routes->post('store', 'UserController::store');
        $routes->post('update/(:num)', 'UserController::update/$1');
        $routes->post('toggle-status/(:num)', 'UserController::toggleStatus/$1');
        $routes->post('delete/(:num)', 'UserController::delete/$1');
    });

    // Modul Manajemen Poliklinik (Admin Only)
    $routes->group('polyclinics', static function ($routes) {
        $routes->get('/', 'PolyclinicController::index');
        $routes->get('list', 'PolyclinicController::list');
        $routes->get('show/(:num)', 'PolyclinicController::show/$1');
        $routes->post('store', 'PolyclinicController::store');
        $routes->post('update/(:num)', 'PolyclinicController::update/$1');
        $routes->post('toggle-status/(:num)', 'PolyclinicController::toggleStatus/$1');
        $routes->post('delete/(:num)', 'PolyclinicController::delete/$1');
    });

    // Modul Manajemen Dokter (Admin Only)
    $routes->group('doctors', static function ($routes) {
        $routes->get('/', 'DoctorController::index');
        $routes->get('list', 'DoctorController::list');
        $routes->get('show/(:num)', 'DoctorController::show/$1');
        $routes->get('available-users', 'DoctorController::getAvailableUsers');
        $routes->get('available-users/(:num)', 'DoctorController::getAvailableUsers/$1');
        $routes->post('store', 'DoctorController::store');
        $routes->post('update/(:num)', 'DoctorController::update/$1');
        $routes->post('toggle-status/(:num)', 'DoctorController::toggleStatus/$1');
        $routes->post('delete/(:num)', 'DoctorController::delete/$1');
    });
});