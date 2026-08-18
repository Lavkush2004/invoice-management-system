<?php
require_once __DIR__ . '/connection_mysql.php';

define('APP_ROOT', dirname(__DIR__));
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
$host = preg_replace('/[^a-zA-Z0-9.:-]/', '', $host);
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') ||
    (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
);
$scheme = $isHttps ? 'https' : 'http';
$scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$subfolder = '';
if ($scriptDir !== '' && $scriptDir !== '.') {
    $subfolder = '/' . $scriptDir;
}
$defaultAppUrl = $scheme . '://' . $host . $subfolder;
define('APP_URL', rtrim(getenv('APP_URL') ?: $defaultAppUrl, '/') . '/');
define('APP_URL_SERVER', $scheme . '://' . $host);
define('APP_ROOT_URL', APP_ROOT . DIRECTORY_SEPARATOR);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(array('httponly' => true, 'secure' => ($scheme === 'https'), 'samesite' => 'Lax'));
    session_start();
}

function request_action()
{
    return isset($_POST['action']) ? (string)$_POST['action'] : '';
}
