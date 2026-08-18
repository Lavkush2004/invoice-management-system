<?php
require_once __DIR__ . '/includes/common.php';
require_once __DIR__ . '/includes/connection_mysql.php';
require_once __DIR__ . '/includes/auth_check.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Authentication is handled centrally so login cannot diverge from authorization rules.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        render_app_message('Session Expired', 'Your security token expired or was invalid. Please return to the login page and try again.', APP_URL . 'login.php');
    }
    require_once __DIR__ . '/model/common.php';
    exit;
}

$csrfToken = require_csrf_token();

if (isset($_SESSION['admin_data']) || isset($_SESSION['vendor_data']) || isset($_SESSION['customer_data'])) {
    header('Location: ' . APP_URL . 'index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Invoice System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }
        .login-box {
            width: 100%;
            max-width: 980px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .panel-login {
            padding: 30px 25px;
            min-height: 420px;
        }
        .login-brand {
            background: #2c3e50;
            color: #fff;
            padding: 30px 25px;
            min-height: 420px;
        }
        .login-brand h2 { margin-top: 30px; }
        .form-control { height: 42px; }
        .btn-primary { background: #2c3e50; border-color: #2c3e50; }
        .btn-primary:hover { background: #1e2a38; border-color: #1e2a38; }
        .alert { margin-top: 15px; }
        .login-footer {
            margin: 16px 0 0;
            color: #52616b;
            font-size: 13px;
            text-align: center;
        }
        .login-links { display: flex; justify-content: space-between; gap: 10px; margin-top: 12px; font-size: 13px; }
        .login-links button { padding: 0; border: 0; background: transparent; color: #286090; cursor: pointer; }
        .auth-extra { display: none; margin-top: 20px; padding: 20px; border: 1px solid #d9e2ec; border-radius: 8px; background: #f8fafc; }
        .auth-extra.is-visible { display: block; }
        .auth-extra h4 { margin-top: 0; color: #2c3e50; }
        .auth-notice { margin: 0 0 15px; font-size: 13px; color: #52616b; }
        .auth-close { float: right; border: 0; background: transparent; color: #52616b; font-size: 22px; line-height: 1; }
        #firebase-recaptcha { margin: 12px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="row" style="margin: 0;">
                <div class="col-md-5 login-brand">
                    <h2>Invoice System</h2>
                    <p>Manage customers, invoices, and billing in one place.</p>
                    <hr style="border-color: rgba(255,255,255,0.3);">
                    <ul style="padding-left: 18px; line-height: 2;">
                        <li>Admin / Vendor login</li>
                        <li>Customer login</li>
                        <li>Invoice and billing control</li>
                    </ul>
                </div>
                <div class="col-md-7 panel-login">
                    <div class="row">
                        <div class="col-md-6">
                            <h3>Admin / Vendor</h3>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                <div class="form-group">
                                    <label>Email / Username</label>
                                    <input type="text" class="form-control" name="email" placeholder="Enter email or username" required>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                                </div>
                                <input type="hidden" name="action" value="login">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-primary btn-block">Login</button>
                            </form>
                            <div class="login-links">
                                <button type="button" data-auth-panel="register">New user? Register</button>
                                <button type="button" data-auth-panel="forgot">Forgot password?</button>
                            </div>
                            <?php if (isset($adminLoginError)) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($adminLoginError); ?></div><?php } ?>
                        </div>
                        <div class="col-md-6">
                            <h3>Customer</h3>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                <div class="form-group">
                                    <label>Mobile Number</label>
                                    <input type="text" class="form-control" name="cus_mobile" placeholder="Enter mobile number" required>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                                </div>
                                <input type="hidden" name="action" value="customer_login">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-success btn-block">Customer Login</button>
                            </form>
                            <div class="login-links">
                                <button type="button" data-auth-panel="register">Create customer account</button>
                                <button type="button" data-auth-panel="otp">Login with OTP</button>
                            </div>
                            <?php if (isset($customerLoginError)) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($customerLoginError); ?></div><?php } ?>
                        </div>
                    </div>
                    <section id="register-panel" class="auth-extra" aria-hidden="true">
                        <button class="auth-close" type="button" aria-label="Close">&times;</button>
                        <h4>Create an account</h4>
                        <p class="auth-notice">Enter your details below to register and log in instantly.</p>
                        <form id="register-account-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <input type="hidden" name="action" value="register_account">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(require_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label>Account type</label>
                                    <select class="form-control" name="account_type" id="reg_account_type">
                                        <option value="customer">Customer</option>
                                        <option value="admin">Admin</option>
                                        <option value="vendor">Vendor</option>
                                    </select>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label>Full name / Business name</label>
                                    <input class="form-control" type="text" name="name" id="reg_name" placeholder="Enter your name" required>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label>Mobile number</label>
                                    <input class="form-control" type="tel" name="mobile" id="reg_mobile" placeholder="e.g. 9876543210" required>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label>Email address</label>
                                    <input class="form-control" type="email" name="email" id="reg_email" placeholder="Enter email" required>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label>Create password</label>
                                    <input class="form-control" type="password" name="password" id="reg_password" placeholder="Minimum 6 characters" required>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label>Confirm password</label>
                                    <input class="form-control" type="password" name="confirm_password" id="reg_confirm_password" placeholder="Re-enter password" required>
                                </div>
                            </div>
                            <button class="btn btn-primary" type="submit">Register and Login</button>
                        </form>
                    </section>
                    <section id="forgot-panel" class="auth-extra" aria-hidden="true">
                        <button class="auth-close" type="button" aria-label="Close">&times;</button>
                        <h4>Reset your password</h4>
                        <p class="auth-notice">Enter your registered mobile number to receive a password reset OTP.</p>
                        <form id="forgot-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <input type="hidden" name="action" value="reset_password">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-group">
                                <label>Registered mobile number</label>
                                <input class="form-control" type="tel" name="cus_mobile" id="forgot_mobile" placeholder="e.g. 9876543210" required>
                            </div>
                            <button class="btn btn-info btn-block" type="button" id="send-forgot-otp-btn">Send reset OTP</button>
                            <div id="firebase-recaptcha-forgot"></div>
                            <div id="forgot-otp-status" class="alert alert-info" style="display:none; margin-top:10px; padding:8px 12px; font-size:13px;"></div>
                            <div id="forgot-otp-section" style="display:none; margin-top: 15px;">
                                <div class="form-group">
                                    <label>Enter 6-digit OTP</label>
                                    <input class="form-control" type="text" id="forgot_otp_code" inputmode="numeric" maxlength="6" placeholder="Enter OTP (e.g. 123456)">
                                </div>
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input class="form-control" type="password" name="new_password" id="forgot_new_password" placeholder="Enter new password" required>
                                </div>
                                <button class="btn btn-primary btn-block" type="submit">Update Password and Login</button>
                            </div>
                        </form>
                    </section>
                    <section id="otp-panel" class="auth-extra" aria-hidden="true">
                        <button class="auth-close" type="button" aria-label="Close">&times;</button>
                        <h4>Login with OTP</h4>
                        <p class="auth-notice">Enter your registered mobile number to log in via SMS OTP.</p>
                        <form id="otp-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <input type="hidden" name="action" value="otp_login">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-group">
                                <label>Mobile number</label>
                                <input class="form-control" type="tel" name="cus_mobile" id="otp_mobile" placeholder="e.g. 9876543210" required>
                            </div>
                            <button class="btn btn-success btn-block" type="button" id="send-otp-btn">Send OTP</button>
                            <div id="firebase-recaptcha"></div>
                            <div id="otp-status" class="alert alert-info" style="display:none; margin-top:10px; padding:8px 12px; font-size:13px;"></div>
                            <div id="otp-code-section" style="display:none; margin-top: 15px;">
                                <div class="form-group">
                                    <label>OTP code</label>
                                    <input class="form-control" type="text" id="otp_code" inputmode="numeric" maxlength="6" placeholder="Enter 6-digit OTP (e.g. 123456)">
                                </div>
                                <button class="btn btn-primary btn-block" type="submit" id="verify-otp-btn">Verify and login</button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . '/includes/chatbot_widget.php'; ?>
<!-- Firebase Web SDK Compat -->
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-auth-compat.js"></script>
<?php if (file_exists(__DIR__ . '/includes/firebase_config.js')) { ?>
<script src="<?php echo APP_URL; ?>includes/firebase_config.js"></script>
<?php } ?>
<script src="<?php echo APP_URL; ?>assets/js/firebase_auth.js"></script>
<script>
    (function () {
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        if (typeof firebaseConfig !== 'undefined' && typeof firebase !== 'undefined' && !firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
            console.log("Firebase initialized successfully with project:", firebaseConfig.projectId);
        }

        var panels = document.querySelectorAll('.auth-extra');
        function showPanel(name) {
            panels.forEach(function (panel) {
                var visible = panel.id === name + '-panel';
                panel.classList.toggle('is-visible', visible);
                panel.setAttribute('aria-hidden', String(!visible));
            });
        }
        document.querySelectorAll('[data-auth-panel]').forEach(function (button) {
            button.addEventListener('click', function () { showPanel(button.getAttribute('data-auth-panel')); });
        });
        document.querySelectorAll('.auth-close').forEach(function (button) {
            button.addEventListener('click', function () { showPanel(''); });
        });
        var regForm = document.getElementById('register-account-form');
        if (regForm) {
            regForm.addEventListener('submit', function (event) {
                var p1 = document.getElementById('reg_password').value;
                var p2 = document.getElementById('reg_confirm_password').value;
                if (p1 !== p2) {
                    event.preventDefault();
                    alert('Passwords do not match. Please check and try again.');
                    return false;
                }
            });
        }

        // Helper to format phone number to E.164 standard
        function formatPhone(mobile) {
            mobile = mobile.trim();
            if (mobile.startsWith('+')) return mobile;
            var digitsOnly = mobile.replace(/\D/g, '');
            if (digitsOnly.length === 10) return '+91' + digitsOnly;
            return '+' + digitsOnly;
        }

        // Send OTP handler for OTP Login
        var sendOtpBtn = document.getElementById('send-otp-btn');
        if (sendOtpBtn) {
            sendOtpBtn.addEventListener('click', function () {
                var mobile = document.getElementById('otp_mobile').value.trim();
                var statusDiv = document.getElementById('otp-status');
                var codeSec = document.getElementById('otp-code-section');

                if (!mobile) {
                    alert('Please enter your mobile number first.');
                    return;
                }

                var formattedPhone = formatPhone(mobile);
                statusDiv.style.display = 'block';
                statusDiv.className = 'alert alert-info';
                statusDiv.innerHTML = 'Sending OTP via SMS to <strong>' + formattedPhone + '</strong>...';

                if (typeof sendFirebaseOtp === 'function') {
                    sendFirebaseOtp(formattedPhone, 'firebase-recaptcha', function(confirmationResult) {
                        statusDiv.className = 'alert alert-success';
                        statusDiv.innerHTML = 'OTP sent successfully to <strong>' + formattedPhone + '</strong>! Please check your SMS and enter the code below.';
                        codeSec.style.display = 'block';
                    }, function(err) {
                        var msg = (err && err.message) ? err.message : String(err);
                        console.error('Send OTP Error:', err);
                        statusDiv.className = 'alert alert-warning';
                        statusDiv.innerHTML = '<strong>SMS Gateway Notice:</strong> ' + msg + '<br><small>Test fallback active: Enter <strong>123456</strong> to verify and continue.</small>';
                        codeSec.style.display = 'block';
                    });
                } else {
                    statusDiv.className = 'alert alert-success';
                    statusDiv.innerHTML = 'Demo mode: Enter <strong>123456</strong> as your OTP code.';
                    codeSec.style.display = 'block';
                }
            });
        }

        // Verify OTP submit handler for OTP Login form
        var otpForm = document.getElementById('otp-form');
        if (otpForm) {
            otpForm.addEventListener('submit', function (event) {
                var code = document.getElementById('otp_code').value.trim();
                var statusDiv = document.getElementById('otp-status');

                if (!code) {
                    event.preventDefault();
                    alert('Please enter the 6-digit OTP code.');
                    return false;
                }

                if (typeof windowConfirmationResult !== 'undefined' && windowConfirmationResult) {
                    event.preventDefault();
                    statusDiv.style.display = 'block';
                    statusDiv.className = 'alert alert-info';
                    statusDiv.innerHTML = 'Verifying OTP code...';

                    verifyFirebaseOtp(code, function(user) {
                        statusDiv.className = 'alert alert-success';
                        statusDiv.innerHTML = 'OTP verified! Logging in...';
                        otpForm.submit();
                    }, function(error) {
                        if (code === '123456') {
                            // Allow test code bypass
                            otpForm.submit();
                        } else {
                            statusDiv.className = 'alert alert-danger';
                            statusDiv.innerHTML = '<strong>Verification failed:</strong> ' + ((error && error.message) ? error.message : 'Invalid OTP code.');
                        }
                    });
                }
            });
        }

        // Send Forgot Password OTP handler
        var sendForgotBtn = document.getElementById('send-forgot-otp-btn');
        if (sendForgotBtn) {
            sendForgotBtn.addEventListener('click', function () {
                var mobile = document.getElementById('forgot_mobile').value.trim();
                var statusDiv = document.getElementById('forgot-otp-status');
                var sec = document.getElementById('forgot-otp-section');

                if (!mobile) {
                    alert('Please enter your registered mobile number first.');
                    return;
                }

                var formattedPhone = formatPhone(mobile);
                statusDiv.style.display = 'block';
                statusDiv.className = 'alert alert-info';
                statusDiv.innerHTML = 'Sending reset OTP to <strong>' + formattedPhone + '</strong>...';

                if (typeof sendFirebaseOtp === 'function') {
                    sendFirebaseOtp(formattedPhone, 'firebase-recaptcha-forgot', function(confirmationResult) {
                        statusDiv.className = 'alert alert-success';
                        statusDiv.innerHTML = 'Reset OTP sent successfully to <strong>' + formattedPhone + '</strong>!';
                        sec.style.display = 'block';
                    }, function(err) {
                        var msg = (err && err.message) ? err.message : String(err);
                        console.error('Send Reset OTP Error:', err);
                        statusDiv.className = 'alert alert-warning';
                        statusDiv.innerHTML = '<strong>SMS Gateway Notice:</strong> ' + msg + '<br><small>Test fallback active: Enter <strong>123456</strong> as your reset OTP.</small>';
                        sec.style.display = 'block';
                    });
                } else {
                    statusDiv.className = 'alert alert-success';
                    statusDiv.innerHTML = 'Reset OTP sent! Enter <strong>123456</strong> as your OTP code.';
                    sec.style.display = 'block';
                }
            });
        }

        // Verify Forgot Password Form
        var forgotForm = document.getElementById('forgot-form');
        if (forgotForm) {
            forgotForm.addEventListener('submit', function (event) {
                var code = document.getElementById('forgot_otp_code').value.trim();
                var statusDiv = document.getElementById('forgot-otp-status');

                if (!code) {
                    event.preventDefault();
                    alert('Please enter the OTP code.');
                    return false;
                }

                if (typeof windowConfirmationResult !== 'undefined' && windowConfirmationResult) {
                    event.preventDefault();
                    statusDiv.style.display = 'block';
                    statusDiv.className = 'alert alert-info';
                    statusDiv.innerHTML = 'Verifying OTP code...';

                    verifyFirebaseOtp(code, function(user) {
                        statusDiv.className = 'alert alert-success';
                        statusDiv.innerHTML = 'OTP verified! Updating password...';
                        forgotForm.submit();
                    }, function(error) {
                        if (code === '123456') {
                            forgotForm.submit();
                        } else {
                            statusDiv.className = 'alert alert-danger';
                            statusDiv.innerHTML = '<strong>Verification failed:</strong> ' + ((error && error.message) ? error.message : 'Invalid OTP code.');
                        }
                    });
                }
            });
        }
    }());
</script>
</body>
</html>
