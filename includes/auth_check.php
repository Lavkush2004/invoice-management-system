<?php
require_once __DIR__ . '/common.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function auth_clear_identity()
{
    unset($_SESSION['admin_data'], $_SESSION['vendor_data'], $_SESSION['customer_data']);
}

function get_current_user_role()
{
    $identities = array();
    if (!empty($_SESSION['admin_data']['id'])) { $identities[] = 'admin'; }
    if (!empty($_SESSION['vendor_data']['userid'])) { $identities[] = 'vendor'; }
    if (!empty($_SESSION['customer_data']['cus_id'])) { $identities[] = 'customer'; }
    if (count($identities) !== 1) {
        if (count($identities) > 1) { auth_clear_identity(); }
        return null;
    }
    return $identities[0];
}

function get_current_user_id()
{
    $role = get_current_user_role();
    if ($role === 'admin') return (int)$_SESSION['admin_data']['id'];
    if ($role === 'vendor') return (int)$_SESSION['vendor_data']['userid'];
    if ($role === 'customer') return (int)$_SESSION['customer_data']['cus_id'];
    return 0;
}

function get_current_company_id()
{
    global $con;
    $role = get_current_user_role();
    $userId = get_current_user_id();
    if (!$userId || $role === 'customer') return 0;

    $stmt = mysqli_prepare($con, 'SELECT userid FROM companies WHERE userid = ? LIMIT 1');
    if (!$stmt) return 0;
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (!$result || mysqli_num_rows($result) !== 1) return 0;
    $row = mysqli_fetch_assoc($result);
    $companyId = (int)$row['userid'];
    if ($role === 'admin') $_SESSION['admin_data']['company_id'] = $companyId;
    return $companyId;
}

function require_login($allowedRoles = array())
{
    if (is_string($allowedRoles)) $allowedRoles = array($allowedRoles);
    $role = get_current_user_role();
    if ($role === null) {
        header('Location: ' . (defined('APP_URL') ? APP_URL : '/invoice/') . 'login.php');
        exit;
    }
    if (!empty($allowedRoles) && !in_array($role, $allowedRoles, true)) {
        http_response_code(403);
        render_app_message('Access Denied', 'Your current account role does not have permission to open this page.');
    }
    if ($role !== 'customer' && get_current_company_id() <= 0) {
        auth_clear_identity();
        header('Location: ' . (defined('APP_URL') ? APP_URL : '/invoice/') . 'login.php');
        exit;
    }
    return $role;
}

function require_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token)
{
    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function require_post_csrf()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        render_app_message('Security Check Failed', 'This form has expired or was submitted from an invalid session. Please go back and try again.');
    }
}

function auth_bootstrap_csrf()
{
    return require_csrf_token();
}

function render_app_message($title, $message, $backUrl = '')
{
    if ($backUrl === '') $backUrl = (defined('APP_URL') ? APP_URL : '/invoice/') . 'index.php';
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $safeBackUrl = htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . $safeTitle . '</title><style>body{margin:0;background:#f3f6fb;color:#1f2937;font-family:Arial,sans-serif}.wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}.card{width:100%;max-width:560px;background:#fff;border-radius:18px;padding:42px;box-shadow:0 18px 50px rgba(31,41,55,.12);text-align:center}.icon{width:64px;height:64px;margin:0 auto 20px;border-radius:50%;display:grid;place-items:center;background:#e8f0ff;color:#2563eb;font-size:30px;font-weight:bold}.card h1{margin:0 0 12px;font-size:28px}.card p{margin:0 0 26px;color:#64748b;line-height:1.6}.btn{display:inline-block;padding:12px 22px;border-radius:9px;background:#2563eb;color:#fff;text-decoration:none;font-weight:bold}.brand{margin-top:22px;color:#94a3b8;font-size:12px}</style></head><body><main class="wrap"><section class="card"><div class="icon">!</div><h1>' . $safeTitle . '</h1><p>' . $safeMessage . '</p><a class="btn" href="' . $safeBackUrl . '">Continue to dashboard</a><div class="brand">Invoice Management System</div></section></main></body></html>';
    exit;
}
