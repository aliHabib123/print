<?php

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set default timezone
date_default_timezone_set('Asia/Beirut');

// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Required environment variables
$dotenv->required(['API_KEY', 'PRINTER_NAME', 'RECEIPT_WIDTH'])->notEmpty();

// Define constants from environment variables
define('API_KEY', $_ENV['API_KEY']);
define('PRINTER_NAME', $_ENV['PRINTER_NAME']);
define('RECEIPT_WIDTH', (int)$_ENV['RECEIPT_WIDTH']);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
