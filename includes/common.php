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
$appRootNorm = str_replace('\\', '/', realpath(APP_ROOT) ?: APP_ROOT);
$docRootNorm = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']) : '';

$subfolder = '';
if (!empty($docRootNorm) && strpos($appRootNorm, $docRootNorm) === 0) {
    $subfolder = trim(substr($appRootNorm, strlen($docRootNorm)), '/');
} else {
    $scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($scriptDir !== '' && $scriptDir !== '.') {
        $parts = explode('/', $scriptDir);
        $filtered = array();
        foreach ($parts as $p) {
            if (in_array(strtolower($p), array('model', 'includes', 'api', 'views', 'controllers', 'assets'))) {
                break;
            }
            $filtered[] = $p;
        }
        $subfolder = implode('/', $filtered);
    }
}

$subfolderPath = ($subfolder !== '') ? '/' . $subfolder : '';
$defaultAppUrl = $scheme . '://' . $host . $subfolderPath;
define('APP_PATH', ($subfolderPath !== '' ? $subfolderPath : '') . '/');
define('APP_URL', rtrim(getenv('APP_URL') ?: $defaultAppUrl, '/') . '/');
define('APP_URL_SERVER', $scheme . '://' . $host);
define('APP_ROOT_URL', APP_ROOT . DIRECTORY_SEPARATOR);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(array(
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => ($scheme === 'https'),
        'httponly' => true,
        'samesite' => 'Lax'
    ));
    session_start();
}

function request_action()
{
    return isset($_POST['action']) ? (string)$_POST['action'] : '';
}
