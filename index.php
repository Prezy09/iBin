<?php
session_start();

// Global app constants — defined here so they're available to controllers before any view loads.
if (!defined('APP_BRAND_SHORT')) {
    define('APP_BRAND_SHORT', 'iBin');
}
if (!defined('APP_BRAND_FULL')) {
    define('APP_BRAND_FULL', 'iBin - IoT - Based Smart Waste management for Himamaylan City');
}

// Basic autoloader
spl_autoload_register(function ($class) {
    // Map namespaces to directories
    $prefix = '';
    $base_dir = __DIR__ . '/';
    
    // For App\Controllers -> app/Controllers
    if (strpos($class, 'App\\') === 0) {
        $class = str_replace('App\\', 'app/', $class);
    }
    // For Core\Router -> core/Router
    if (strpos($class, 'Core\\') === 0) {
        $class = str_replace('Core\\', 'core/', $class);
    }

    $file = $base_dir . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load the router
$router = new Core\Router();

// Define routes
$router->get('/', 'DashboardController@index');
$router->get('', 'DashboardController@index');
$router->get('dashboard.php', 'DashboardController@index');
$router->get('dashboard', 'DashboardController@index');

$router->get('login', 'AuthController@login');
$router->get('login.php', 'AuthController@login');
$router->post('login.php', 'AuthController@postLogin');
$router->post('login', 'AuthController@postLogin');

$router->get('logout', 'AuthController@logout');
$router->get('logout.php', 'AuthController@logout');

$router->get('register', 'AuthController@register');
$router->get('register.php', 'AuthController@register');
$router->post('register.php', 'AuthController@postRegister');

$router->get('bins', 'BinsController@index');
$router->get('bins.php', 'BinsController@index');

$router->get('collection', 'CollectionController@index');
$router->get('collection.php', 'CollectionController@index');

$router->get('analytics', 'AnalyticsController@index');
$router->get('analytics.php', 'AnalyticsController@index');

$router->get('reports', 'ReportsController@index');
$router->get('reports.php', 'ReportsController@index');

$router->get('settings', 'SettingsController@index');
$router->get('settings.php', 'SettingsController@index');
$router->post('settings', 'SettingsController@index');
$router->post('settings.php', 'SettingsController@index');

$router->get('manage_admins', 'AdminController@index');
$router->get('manage_admins.php', 'AdminController@index');
$router->post('manage_admins', 'AdminController@index');
$router->post('manage_admins.php', 'AdminController@index');

// Dispatch
// Supports both:
//   - Apache .htaccess rewrite  → $_GET['url'] is set
//   - Railway / PHP built-in server → parse REQUEST_URI directly
if (isset($_GET['url']) && $_GET['url'] !== '') {
    $url = $_GET['url'];
} else {
    $rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $url = trim($rawUri ?? '', '/');
}

$requestMethod = $_SERVER['REQUEST_METHOD'];

$router->dispatch($url, $requestMethod);
