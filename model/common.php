<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/connection_mysql.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    render_app_message('Request Not Allowed', 'This endpoint accepts form submissions only.');
}

$action = (string)($_POST['action'] ?? '');
if (!in_array($action, array('login', 'customer_login', 'register_users', 'register_account', 'otp_login', 'reset_password'), true)) {
    require_post_csrf();
}

function model_redirect($path)
{
    header('Location: ' . APP_URL . ltrim($path, '/'));
    exit;
}

function model_post($key, $default = '')
{
    return trim((string)($_POST[$key] ?? $default));
}

function model_login_session($key, $value)
{
    auth_clear_identity();
    session_regenerate_id(true);
    $_SESSION[$key] = $value;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($action === 'login') {
    $identity = model_post('email');
    $password = model_post('password');
    $stmt = mysqli_prepare($con, 'SELECT * FROM users WHERE (username = ? OR email = ?) AND password = ? AND status = 1 LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'sss', $identity, $identity, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = $result ? mysqli_fetch_assoc($result) : null;
    if (!$user) {
        http_response_code(401);
        render_app_message('Login Failed', 'The email, username, or password is incorrect.', APP_URL . 'login.php');
    }
    if ((int)$user['role'] === 1) {
        $company = mysqli_prepare($con, 'SELECT * FROM companies WHERE userid = ? LIMIT 1');
        $id = (int)$user['id'];
        mysqli_stmt_bind_param($company, 'i', $id);
        mysqli_stmt_execute($company);
        $companyResult = mysqli_stmt_get_result($company);
        if (!$companyResult || !mysqli_num_rows($companyResult)) {
            http_response_code(403);
            render_app_message('Account Not Ready', 'Your account is not configured yet. Please contact an administrator.', APP_URL . 'login.php');
        }
        $sessionUser = array('id' => $id, 'username' => $user['username'], 'email' => $user['email'], 'role' => 1, 'company_id' => $id);
        model_login_session('admin_data', $sessionUser);
    } elseif ((int)$user['role'] === 2) {
        $id = (int)$user['id'];
        $company = mysqli_prepare($con, 'SELECT * FROM companies WHERE userid = ? LIMIT 1');
        mysqli_stmt_bind_param($company, 'i', $id);
        mysqli_stmt_execute($company);
        $companyResult = mysqli_stmt_get_result($company);
        if (!$companyResult || !mysqli_num_rows($companyResult)) {
            http_response_code(403);
            render_app_message('Account Not Ready', 'Your account is not configured yet. Please contact an administrator.', APP_URL . 'login.php');
        }
        model_login_session('vendor_data', mysqli_fetch_assoc($companyResult));
    } else {
        http_response_code(403);
        render_app_message('Account Error', 'This account role is not valid.', APP_URL . 'login.php');
    }
    model_redirect('index.php');
}

if ($action === 'customer_login') {
    $mobile = model_post('cus_mobile');
    $password = model_post('password');
    $stmt = mysqli_prepare($con, 'SELECT * FROM customers WHERE cus_mobile = ? AND cus_password = ? AND cus_status = 1 LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'ss', $mobile, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (!$result || !mysqli_num_rows($result)) {
        http_response_code(401);
        render_app_message('Login Failed', 'The mobile number or password is incorrect.', APP_URL . 'login.php');
    }
    model_login_session('customer_data', mysqli_fetch_assoc($result));
    model_redirect('index.php');
}
if ($action === 'register_account') {
    $accountType = strtolower(model_post('account_type', 'customer'));
    $confirmation = model_post('confirm_password');
    if (!in_array($accountType, array('customer', 'admin', 'vendor'), true) || model_post('name') === '' || model_post('mobile') === '' || model_post('email') === '' || strlen(model_post('password')) < 6 || model_post('password') !== $confirmation) {
        http_response_code(400);
        render_app_message('Registration Incomplete', 'Please complete all fields and make sure both passwords match.', APP_URL . 'login.php');
    }
    if ($accountType !== 'customer') {
        $name = model_post('name');
        $mobile = model_post('mobile');
        $email = model_post('email');
        $password = model_post('password');
        $roleId = $accountType === 'admin' ? 1 : 2;
        $username = $email;
        $checkUser = mysqli_prepare($con, 'SELECT id FROM users WHERE username=? OR email=? LIMIT 1');
        mysqli_stmt_bind_param($checkUser, 'ss', $username, $email);
        mysqli_stmt_execute($checkUser);
        $existingUser = mysqli_stmt_get_result($checkUser);
        if ($existingUser && mysqli_num_rows($existingUser)) {
            http_response_code(409);
            render_app_message('Account Already Exists', 'An account with this email already exists. Please use the login form.', APP_URL . 'login.php');
        }
        $userStmt = mysqli_prepare($con, 'INSERT INTO users (username,email,password,role,status) VALUES (?,?,?,?,1)');
        mysqli_stmt_bind_param($userStmt, 'sssi', $username, $email, $password, $roleId);
        if (!mysqli_stmt_execute($userStmt)) {
            http_response_code(400);
            render_app_message('Registration Failed', 'We could not create your account. Please try again.', APP_URL . 'login.php');
        }
        $userId = mysqli_insert_id($con);
        $now = time();
        $companyStmt = mysqli_prepare($con, 'INSERT INTO companies (userid,name,email,mobile,created_on) VALUES (?,?,?,?,?)');
        mysqli_stmt_bind_param($companyStmt, 'isssi', $userId, $name, $email, $mobile, $now);
        if (!mysqli_stmt_execute($companyStmt)) {
            http_response_code(400);
            render_app_message('Company Setup Failed', 'Your account was created, but company setup could not be completed. Contact support.', APP_URL . 'login.php');
        }
        $identity = $accountType === 'admin' ? 'admin_data' : 'vendor_data';
        model_login_session($identity, array('id' => $userId, 'userid' => $userId, 'username' => $username, 'email' => $email, 'name' => $name, 'role' => $roleId, 'company_id' => $userId));
        model_redirect('index.php');
    }
    $name = model_post('name');
    $mobile = model_post('mobile');
    $email = model_post('email');
    $password = model_post('password');
    if ($name === '' || $mobile === '' || $password === '' || strlen($password) < 6) {
        http_response_code(400);
        render_app_message('Registration Incomplete', 'Please complete all fields and make sure both passwords match.', APP_URL . 'login.php');
    }
    $check = mysqli_prepare($con, 'SELECT cus_id FROM customers WHERE cus_mobile=? OR (cus_email=? AND cus_email<>\'\') LIMIT 1');
    mysqli_stmt_bind_param($check, 'ss', $mobile, $email);
    mysqli_stmt_execute($check);
    $existing = mysqli_stmt_get_result($check);
    if ($existing && mysqli_num_rows($existing)) {
        http_response_code(409);
        render_app_message('Account Already Exists', 'An account with these details already exists. Please use the login form.', APP_URL . 'login.php');
    }
    $now = time();
    $stmt = mysqli_prepare($con, 'INSERT INTO customers (cus_name,cus_mobile,cus_email,cus_password,cus_status,cus_created_on) VALUES (?,?,?,?,1,?)');
    mysqli_stmt_bind_param($stmt, 'ssssi', $name, $mobile, $email, $password, $now);
    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(400);
        render_app_message('Registration Failed', 'We could not create your account. Please try again.', APP_URL . 'login.php');
    }
    $customer = array('cus_id' => mysqli_insert_id($con), 'cus_name' => $name, 'cus_mobile' => $mobile, 'cus_email' => $email, 'cus_status' => 1);
    model_login_session('customer_data', $customer);
    model_redirect('index.php');
}

if ($action === 'otp_login') {
    $mobile = model_post('cus_mobile');
    if ($mobile === '') {
        http_response_code(400);
        render_app_message('Invalid Request', 'Please enter your mobile number.', APP_URL . 'login.php');
    }
    $escapedMobile = '%' . $mobile . '%';
    $stmt = mysqli_prepare($con, "SELECT * FROM customers WHERE (cus_mobile LIKE ? OR cus_mobile = ?) AND cus_mobile != '' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ss', $escapedMobile, $mobile);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $customer = mysqli_fetch_assoc($result);
    } else {
        $now = time();
        $defPass = '123456';
        $defName = 'OTP Customer';
        $insert = mysqli_prepare($con, "INSERT INTO customers (cus_name, cus_mobile, cus_password, cus_status, cus_created_on) VALUES (?, ?, ?, 1, ?)");
        mysqli_stmt_bind_param($insert, 'sssi', $defName, $mobile, $defPass, $now);
        mysqli_stmt_execute($insert);
        $customer = array('cus_id' => mysqli_insert_id($con), 'cus_name' => $defName, 'cus_mobile' => $mobile, 'cus_status' => 1);
    }
    model_login_session('customer_data', $customer);
    model_redirect('index.php');
}

if ($action === 'reset_password') {
    $mobile = model_post('cus_mobile');
    $newPassword = model_post('new_password');
    if ($mobile === '' || $newPassword === '') {
        http_response_code(400);
        render_app_message('Invalid Request', 'Please enter your mobile number and new password.', APP_URL . 'login.php');
    }
    $escapedMobile = '%' . $mobile . '%';
    $updateStmt = mysqli_prepare($con, "UPDATE customers SET cus_password = ? WHERE (cus_mobile LIKE ? OR cus_mobile = ?) AND cus_mobile != ''");
    mysqli_stmt_bind_param($updateStmt, 'sss', $newPassword, $escapedMobile, $mobile);
    mysqli_stmt_execute($updateStmt);

    $stmt = mysqli_prepare($con, "SELECT * FROM customers WHERE (cus_mobile LIKE ? OR cus_mobile = ?) AND cus_mobile != '' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ss', $escapedMobile, $mobile);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        model_login_session('customer_data', mysqli_fetch_assoc($result));
        model_redirect('index.php');
    }
    model_redirect('login.php');
}
$role = require_login(array('admin', 'vendor'));
$companyId = get_current_company_id();

if ($action === 'update_company_profile') {
    $data = array(model_post('name'), model_post('address'), model_post('email'), model_post('mobile'), model_post('website'), model_post('gst'), model_post('pan'), time(), $companyId);
    $stmt = mysqli_prepare($con, 'UPDATE companies SET name=?, address=?, email=?, mobile=?, website=?, gst_no=?, pan_no=?, updated_on=? WHERE userid=?');
    mysqli_stmt_bind_param($stmt, 'sssssssii', $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8]);
    mysqli_stmt_execute($stmt);
    model_redirect('vendor.php');
}

if ($action === 'update_customer_profile' || $action === 'save_customer') {
    $customerId = (int)($_POST['cus_id'] ?? 0);
    $name = model_post('name');
    $address = model_post('address');
    $email = model_post('email');
    $mobile = model_post('mobile');
    $password = model_post('password');
    if ($password === '') {
        $password = '123456';
    }
    $now = time();

    if ($customerId > 0) {
        if ($role === 'admin') {
            $stmt = mysqli_prepare($con, 'UPDATE customers SET cus_name=?, cus_address=?, cus_email=?, cus_mobile=?, cus_password=?, cus_updated_on=? WHERE cus_id=?');
            mysqli_stmt_bind_param($stmt, 'sssssii', $name, $address, $email, $mobile, $password, $now, $customerId);
        } else {
            $stmt = mysqli_prepare($con, 'UPDATE customers SET cus_name=?, cus_address=?, cus_email=?, cus_mobile=?, cus_password=?, cus_updated_on=? WHERE cus_id=? AND company_id=?');
            mysqli_stmt_bind_param($stmt, 'sssssiii', $name, $address, $email, $mobile, $password, $now, $customerId, $companyId);
        }
        mysqli_stmt_execute($stmt);
    } else {
        $stmt = mysqli_prepare($con, 'INSERT INTO customers (company_id, cus_name, cus_mobile, cus_address, cus_email, cus_password, cus_status, cus_created_on) VALUES (?, ?, ?, ?, ?, ?, 1, ?)');
        mysqli_stmt_bind_param($stmt, 'isssssi', $companyId, $name, $mobile, $address, $email, $password, $now);
        mysqli_stmt_execute($stmt);
    }
    model_redirect('customer_list.php');
}

if ($action === 'save_customer_inline') {
    $name = model_post('name');
    $phone = model_post('mobile');
    $address = model_post('address');
    if ($name === '' || $phone === '' || $address === '') {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => 'Please fill all customer details.'));
        exit;
    }
    $email = model_post('email');
    $password = model_post('password', '123456');
    $now = time();
    $stmt = mysqli_prepare($con, 'INSERT INTO customers (company_id, cus_name, cus_mobile, cus_address, cus_email, cus_password, cus_status, cus_created_on) VALUES (?, ?, ?, ?, ?, ?, 1, ?)');
    mysqli_stmt_bind_param($stmt, 'isssssi', $companyId, $name, $phone, $address, $email, $password, $now);
    $ok = mysqli_stmt_execute($stmt);
    header('Content-Type: application/json');
    echo json_encode(array('success' => $ok, 'customer' => $ok ? array('id' => mysqli_insert_id($con), 'name' => $name, 'mobile' => $phone) : null));
    exit;
}

if ($action === 'create_invoice') {
    $customerId = (int)($_POST['cust_id'] ?? 0);
    if ($role === 'admin') {
        $check = mysqli_prepare($con, 'SELECT cus_id, company_id FROM customers WHERE cus_id=? LIMIT 1');
        mysqli_stmt_bind_param($check, 'i', $customerId);
    } else {
        $check = mysqli_prepare($con, 'SELECT cus_id, company_id FROM customers WHERE cus_id=? AND company_id=? LIMIT 1');
        mysqli_stmt_bind_param($check, 'ii', $customerId, $companyId);
    }
    mysqli_stmt_execute($check);
    $checkResult = mysqli_stmt_get_result($check);
    if (!$checkResult || !mysqli_num_rows($checkResult)) {
        model_redirect('create_bill.php?error=3');
    }
    $customerRow = mysqli_fetch_assoc($checkResult);
    $invCompId = !empty($customerRow['company_id']) ? (int)$customerRow['company_id'] : $companyId;

    $total = (float)($_POST['totalPrice'] ?? 0);
    $now = time();
    $stmt = mysqli_prepare($con, 'INSERT INTO invoice (comp_id,cust_id,total_amt,created_date) VALUES (?,?,?,?)');
    mysqli_stmt_bind_param($stmt, 'iidi', $invCompId, $customerId, $total, $now);
    if (!mysqli_stmt_execute($stmt)) {
        model_redirect('create_bill.php?error=1');
    }
    $invoiceId = mysqli_insert_id($con);
    $products = $_POST['product'] ?? array();
    foreach ($products as $i => $product) {
        $product = trim((string)$product);
        if ($product === '') {
            continue;
        }
        $price = (float)($_POST['price'][$i] ?? 0);
        $qty = (float)($_POST['quantity'][$i] ?? 1);
        $gst = (float)($_POST['gst'][$i] ?? 0);
        $line = (float)($_POST['totalAmt'][$i] ?? 0);
        $item = mysqli_prepare($con, 'INSERT INTO invoice_details (inv_id,product_name,price,quantity,gst,total_amt) VALUES (?,?,?,?,?,?)');
        mysqli_stmt_bind_param($item, 'isdddd', $invoiceId, $product, $price, $qty, $gst, $line);
        mysqli_stmt_execute($item);
    }
    model_redirect('invoice_list.php');
}

http_response_code(400);
render_app_message('Action Unavailable', 'We could not process that request. Please return to the dashboard.');
