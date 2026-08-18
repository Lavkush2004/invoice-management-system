<?php
require_once __DIR__ . '/includes/auth_check.php';
$userType = require_login(array('admin', 'vendor', 'customer'));
require_once __DIR__ . '/includes/connection_mysql.php';

if (isset($_GET['logout'])) {
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ' . (defined('APP_URL') ? APP_URL . 'login.php' : '/invoice/login.php'));
    exit;
}

$userName = 'User';
if ($userType === 'admin') {
    $userName = $_SESSION['admin_data']['username'] ?? 'User';
} elseif ($userType === 'vendor') {
    $userName = $_SESSION['vendor_data']['name'] ?? 'User';
} elseif ($userType === 'customer') {
    $userName = $_SESSION['customer_data']['cus_name'] ?? 'User';
}

function dashboard_value($connection, $sql, $field) {
    $result = mysqli_query($connection, $sql);
    if (!$result) {
        return 0;
    }
    $row = mysqli_fetch_assoc($result);
    return isset($row[$field]) ? $row[$field] : 0;
}

$adminStats = array();
$vendorStats = array();
$recentInvoices = array();
$todayStart = strtotime('today');
$monthStart = strtotime(date('Y-m-01 00:00:00'));
$yearStart = strtotime(date('Y-01-01 00:00:00'));

if ($userType === 'admin') {
    // System-wide statistics for Admin
    $adminStats['vendors'] = dashboard_value($con, "SELECT COUNT(*) AS value FROM companies c LEFT JOIN users u ON u.id = c.userid WHERE (u.role = 2 OR u.id IS NULL OR c.userid != 2)", 'value');
    $adminStats['customers'] = dashboard_value($con, 'SELECT COUNT(*) AS value FROM customers', 'value');
    $adminStats['invoices'] = dashboard_value($con, 'SELECT COUNT(*) AS value FROM invoice', 'value');
    $adminStats['today_sales'] = dashboard_value($con, 'SELECT COALESCE(SUM(total_amt), 0) AS value FROM invoice WHERE created_date >= ' . (int)$todayStart, 'value');
    $adminStats['month_sales'] = dashboard_value($con, 'SELECT COALESCE(SUM(total_amt), 0) AS value FROM invoice WHERE created_date >= ' . (int)$monthStart, 'value');
    $adminStats['year_sales'] = dashboard_value($con, 'SELECT COALESCE(SUM(total_amt), 0) AS value FROM invoice WHERE created_date >= ' . (int)$yearStart, 'value');
    $adminStats['total_sales'] = dashboard_value($con, 'SELECT COALESCE(SUM(total_amt), 0) AS value FROM invoice', 'value');

    $topProductResult = mysqli_query($con, "SELECT d.product_name, COALESCE(SUM(d.quantity), 0) AS units FROM invoice_details d INNER JOIN invoice i ON i.id = d.inv_id WHERE d.product_name IS NOT NULL AND d.product_name <> '' GROUP BY d.product_name ORDER BY units DESC, d.product_name ASC LIMIT 1");
    $adminStats['top_product'] = array('product_name' => 'No product sales yet', 'units' => 0);
    if ($topProductResult && mysqli_num_rows($topProductResult) > 0) {
        $adminStats['top_product'] = mysqli_fetch_assoc($topProductResult);
    }

    $vendorQuery = "SELECT c.userid, c.name, c.email, c.mobile,
        (SELECT COUNT(*) FROM customers cu WHERE cu.company_id = c.userid) AS customer_count,
        (SELECT COUNT(*) FROM invoice i WHERE i.comp_id = c.userid) AS invoice_count,
        (SELECT COALESCE(SUM(i.total_amt), 0) FROM invoice i WHERE i.comp_id = c.userid AND i.created_date >= " . (int)$todayStart . ") AS today_sales,
        (SELECT COALESCE(SUM(i.total_amt), 0) FROM invoice i WHERE i.comp_id = c.userid AND i.created_date >= " . (int)$monthStart . ") AS month_sales,
        (SELECT COALESCE(SUM(i.total_amt), 0) FROM invoice i WHERE i.comp_id = c.userid AND i.created_date >= " . (int)$yearStart . ") AS year_sales,
        (SELECT COALESCE(SUM(i.total_amt), 0) FROM invoice i WHERE i.comp_id = c.userid) AS total_sales
        FROM companies c
        LEFT JOIN users u ON u.id = c.userid
        WHERE (u.role = 2 OR u.id IS NULL OR c.userid != 2)
        ORDER BY total_sales DESC, c.name ASC";
    $vendorResult = mysqli_query($con, $vendorQuery);
    if ($vendorResult) {
        while ($vendor = mysqli_fetch_assoc($vendorResult)) {
            $vendorStats[] = $vendor;
        }
    }

    $recentInvoicesQuery = "SELECT i.id, i.total_amt, i.created_date, cu.cus_name, cu.cus_mobile, c.name AS company_name
        FROM invoice i
        LEFT JOIN customers cu ON cu.cus_id = i.cust_id
        LEFT JOIN companies c ON c.userid = i.comp_id
        ORDER BY i.id DESC LIMIT 10";
    $recentResult = mysqli_query($con, $recentInvoicesQuery);
    if ($recentResult) {
        while ($row = mysqli_fetch_assoc($recentResult)) {
            $recentInvoices[] = $row;
        }
    }
} elseif ($userType === 'vendor') {
    $companyId = get_current_company_id();
    $adminStats['customers'] = dashboard_value($con, 'SELECT COUNT(*) AS value FROM customers WHERE company_id = ' . $companyId, 'value');
    $adminStats['invoices'] = dashboard_value($con, 'SELECT COUNT(*) AS value FROM invoice WHERE comp_id = ' . $companyId, 'value');
    $adminStats['today_sales'] = dashboard_value($con, 'SELECT COALESCE(SUM(total_amt), 0) AS value FROM invoice WHERE comp_id = ' . $companyId . ' AND created_date >= ' . (int)$todayStart, 'value');
    $adminStats['month_sales'] = dashboard_value($con, 'SELECT COALESCE(SUM(total_amt), 0) AS value FROM invoice WHERE comp_id = ' . $companyId . ' AND created_date >= ' . (int)$monthStart, 'value');
    $adminStats['year_sales'] = dashboard_value($con, 'SELECT COALESCE(SUM(total_amt), 0) AS value FROM invoice WHERE comp_id = ' . $companyId . ' AND created_date >= ' . (int)$yearStart, 'value');
    $adminStats['total_sales'] = dashboard_value($con, 'SELECT COALESCE(SUM(total_amt), 0) AS value FROM invoice WHERE comp_id = ' . $companyId, 'value');

    $topProductResult = mysqli_query($con, "SELECT d.product_name, COALESCE(SUM(d.quantity), 0) AS units FROM invoice_details d INNER JOIN invoice i ON i.id = d.inv_id WHERE i.comp_id = " . $companyId . " AND d.product_name IS NOT NULL AND d.product_name <> '' GROUP BY d.product_name ORDER BY units DESC, d.product_name ASC LIMIT 1");
    $adminStats['top_product'] = array('product_name' => 'No product sales yet', 'units' => 0);
    if ($topProductResult && mysqli_num_rows($topProductResult) > 0) {
        $adminStats['top_product'] = mysqli_fetch_assoc($topProductResult);
    }

    $recentInvoicesQuery = "SELECT i.id, i.total_amt, i.created_date, cu.cus_name, cu.cus_mobile
        FROM invoice i
        LEFT JOIN customers cu ON cu.cus_id = i.cust_id
        WHERE i.comp_id = " . $companyId . "
        ORDER BY i.id DESC LIMIT 10";
    $recentResult = mysqli_query($con, $recentInvoicesQuery);
    if ($recentResult) {
        while ($row = mysqli_fetch_assoc($recentResult)) {
            $recentInvoices[] = $row;
        }
    }
} elseif ($userType === 'customer') {
    $customerId = (int)($_SESSION['customer_data']['cus_id'] ?? 0);
    $adminStats['invoices'] = dashboard_value($con, 'SELECT COUNT(*) AS value FROM invoice WHERE cust_id = ' . $customerId, 'value');
    $adminStats['total_spent'] = dashboard_value($con, 'SELECT COALESCE(SUM(total_amt), 0) AS value FROM invoice WHERE cust_id = ' . $customerId, 'value');

    $recentInvoicesQuery = "SELECT i.id, i.total_amt, i.created_date, c.name AS company_name
        FROM invoice i
        LEFT JOIN companies c ON c.userid = i.comp_id
        WHERE i.cust_id = " . $customerId . "
        ORDER BY i.id DESC LIMIT 10";
    $recentResult = mysqli_query($con, $recentInvoicesQuery);
    if ($recentResult) {
        while ($row = mysqli_fetch_assoc($recentResult)) {
            $recentInvoices[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f5f7;
            margin: 0;
        }
        .topbar {
            background: #2c3e50;
            color: #fff;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar a {
            color: #fff;
            text-decoration: none;
        }
        .container {
            padding: 30px 20px;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 20px;
            margin-bottom: 20px;
        }
        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }
        .nav-box {
            display: block;
            background: #2c3e50;
            color: #fff;
            text-align: center;
            padding: 25px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }
        .nav-box:nth-child(2n) { background: #1f9d8a; }
        .nav-box:nth-child(3n) { background: #e67e22; }
        .nav-box:nth-child(4n) { background: #34495e; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin: 20px 0; }
        .stat-card { border-radius: 10px; padding: 18px 20px; color: #fff; background: #2c3e50; box-shadow: 0 5px 16px rgba(0,0,0,.1); }
        .stat-card:nth-child(1) { background: linear-gradient(135deg, #1e293b, #334155); }
        .stat-card:nth-child(2) { background: linear-gradient(135deg, #0d9488, #14b8a6); }
        .stat-card:nth-child(3) { background: linear-gradient(135deg, #2563eb, #3b82f6); }
        .stat-card:nth-child(4) { background: linear-gradient(135deg, #d97706, #f59e0b); }
        .stat-card:nth-child(5) { background: linear-gradient(135deg, #7c3aed, #8b5cf6); }
        .stat-card:nth-child(6) { background: linear-gradient(135deg, #059669, #10b981); }
        .stat-card:nth-child(7) { background: linear-gradient(135deg, #db2777, #ec4899); }
        .stat-card:nth-child(8) { background: linear-gradient(135deg, #475569, #64748b); }
        .stat-label { display: block; font-size: 13px; font-weight: 600; letter-spacing: 0.3px; opacity: .95; }
        .stat-value { display: block; margin-top: 6px; font-size: 26px; font-weight: bold; }
        .stat-note { display: block; margin-top: 5px; font-size: 12px; opacity: .9; }
        .table-wrap { overflow-x: auto; }
        .analytics-table { width: 100%; min-width: 720px; border-collapse: collapse; }
        .analytics-table th, .analytics-table td { padding: 12px; border-bottom: 1px solid #e9edf1; text-align: left; }
        .analytics-table th { color: #52616b; font-size: 12px; text-transform: uppercase; }
        .analytics-table td:not(:first-child) { font-weight: 600; }
    </style>
</head>
<body>
    <div class="topbar">
        <div><strong>Invoice Dashboard</strong></div>
        <div>
            <span>Welcome <?php echo htmlspecialchars($userName); ?> &nbsp;|&nbsp;</span>
            <a href="?logout=1">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($userType === 'customer') { ?>
            <div class="card">
                <h2>Customer Portal</h2>
                <p>Welcome, <strong><?php echo htmlspecialchars($userName); ?></strong>. Here is your recent purchase activity.</p>
                <div class="stats-grid">
                    <div class="stat-card"><span class="stat-label">Total Bills / Invoices</span><span class="stat-value"><?php echo (int) $adminStats['invoices']; ?></span></div>
                    <div class="stat-card"><span class="stat-label">Total Purchases</span><span class="stat-value">₹<?php echo number_format((float) $adminStats['total_spent'], 2); ?></span></div>
                </div>
            </div>

            <div class="card">
                <h3>My Recent Purchases</h3>
                <div class="table-wrap">
                    <table class="analytics-table">
                        <thead><tr><th>Invoice #</th><th>Vendor / Company</th><th>Total Amount</th><th>Date</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php if (empty($recentInvoices)) { ?>
                            <tr><td colspan="5">No purchases or bills found yet.</td></tr>
                        <?php } else { foreach ($recentInvoices as $inv) { ?>
                            <tr>
                                <td>#<?php echo (int) $inv['id']; ?></td>
                                <td><?php echo htmlspecialchars($inv['company_name'] ?: 'Vendor'); ?></td>
                                <td>₹<?php echo number_format((float) $inv['total_amt'], 2); ?></td>
                                <td><?php echo date('d M Y, h:i A', (int) $inv['created_date']); ?></td>
                                <td><a href="invoiceDetail.php?inv_id=<?php echo (int) $inv['id']; ?>" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye-open"></i> View Invoice</a></td>
                            </tr>
                        <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="nav-grid">
                <a class="nav-box" href="customer_invoice_search.php">Search Invoice</a>
                <a class="nav-box" href="customer_invoice_search.php#customerTab">Search by My Details</a>
            </div>
        <?php } elseif ($userType === 'vendor') { ?>
            <div class="card">
                <h2>Vendor Dashboard</h2>
                <p>Live business overview for <strong><?php echo htmlspecialchars($userName); ?></strong>.</p>
                <div class="stats-grid">
                    <div class="stat-card"><span class="stat-label">My Customers</span><span class="stat-value"><?php echo (int) $adminStats['customers']; ?></span></div>
                    <div class="stat-card"><span class="stat-label">My Total Invoices</span><span class="stat-value"><?php echo (int) $adminStats['invoices']; ?></span></div>
                    <div class="stat-card"><span class="stat-label">Today's Sales</span><span class="stat-value">₹<?php echo number_format((float) $adminStats['today_sales'], 2); ?></span></div>
                    <div class="stat-card"><span class="stat-label">This Month's Sales</span><span class="stat-value">₹<?php echo number_format((float) $adminStats['month_sales'], 2); ?></span></div>
                    <div class="stat-card"><span class="stat-label">Total Revenue</span><span class="stat-value">₹<?php echo number_format((float) $adminStats['total_sales'], 2); ?></span></div>
                    <div class="stat-card"><span class="stat-label">Top Selling Product</span><span class="stat-value" style="font-size:20px;"><?php echo htmlspecialchars($adminStats['top_product']['product_name']); ?></span><span class="stat-note"><?php echo number_format((float) $adminStats['top_product']['units'], 0); ?> units sold</span></div>
                </div>
            </div>

            <div class="card">
                <h3>My Recent Invoices</h3>
                <div class="table-wrap">
                    <table class="analytics-table">
                        <thead><tr><th>Invoice #</th><th>Customer Name</th><th>Mobile</th><th>Amount</th><th>Date</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php if (empty($recentInvoices)) { ?>
                            <tr><td colspan="6">No invoices created yet.</td></tr>
                        <?php } else { foreach ($recentInvoices as $inv) { ?>
                            <tr>
                                <td>#<?php echo (int) $inv['id']; ?></td>
                                <td><?php echo htmlspecialchars($inv['cus_name'] ?: 'Customer'); ?></td>
                                <td><?php echo htmlspecialchars($inv['cus_mobile'] ?: '-'); ?></td>
                                <td>₹<?php echo number_format((float) $inv['total_amt'], 2); ?></td>
                                <td><?php echo date('d M Y, h:i A', (int) $inv['created_date']); ?></td>
                                <td><a href="invoiceDetail.php?inv_id=<?php echo (int) $inv['id']; ?>" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye-open"></i> View</a></td>
                            </tr>
                        <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="nav-grid">
                <a class="nav-box" href="create_bill.php"><i class="glyphicon glyphicon-plus"></i> Create Invoice</a>
                <a class="nav-box" href="invoice_list.php"><i class="glyphicon glyphicon-list-alt"></i> Invoice List</a>
                <a class="nav-box" href="customer_list.php"><i class="glyphicon glyphicon-user"></i> Customer List</a>
                <a class="nav-box" href="vendor.php"><i class="glyphicon glyphicon-briefcase"></i> My Profile</a>
            </div>
        <?php } else { ?>
            <div class="card" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <h2 style="margin:0 0 5px;">Admin Control Center</h2>
                    <p style="margin:0; color:#64748b;">Real-time synchronized data across all vendors, customer registrations, and sales.</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-sm btn-info" style="font-weight:bold;"><i class="glyphicon glyphicon-refresh"></i> Refresh Live Data</a>
                </div>
            </div>

            <div class="card">
                <h3>System-wide Business Overview</h3>
                <p>All metrics update automatically and include all registered vendors, customers, and invoice sales.</p>
                <div class="stats-grid">
                    <div class="stat-card"><span class="stat-label">Total Active Vendors</span><span class="stat-value"><?php echo (int) $adminStats['vendors']; ?></span></div>
                    <div class="stat-card"><span class="stat-label">Total System Customers</span><span class="stat-value"><?php echo (int) $adminStats['customers']; ?></span></div>
                    <div class="stat-card"><span class="stat-label">Total Invoices Created</span><span class="stat-value"><?php echo (int) $adminStats['invoices']; ?></span></div>
                    <div class="stat-card"><span class="stat-label">Today's Total Sales</span><span class="stat-value">₹<?php echo number_format((float) $adminStats['today_sales'], 2); ?></span></div>
                    <div class="stat-card"><span class="stat-label">This Month's Total Sales</span><span class="stat-value">₹<?php echo number_format((float) $adminStats['month_sales'], 2); ?></span></div>
                    <div class="stat-card"><span class="stat-label">This Year's Total Sales</span><span class="stat-value">₹<?php echo number_format((float) $adminStats['year_sales'], 2); ?></span></div>
                    <div class="stat-card"><span class="stat-label">All-Time Total Revenue</span><span class="stat-value">₹<?php echo number_format((float) $adminStats['total_sales'], 2); ?></span></div>
                    <div class="stat-card"><span class="stat-label">Highest-Demand Product</span><span class="stat-value" style="font-size:20px;"><?php echo htmlspecialchars($adminStats['top_product']['product_name']); ?></span><span class="stat-note"><?php echo number_format((float) $adminStats['top_product']['units'], 0); ?> total units sold</span></div>
                </div>
            </div>

            <div class="card">
                <h3>Live Vendor Performance & Sales</h3>
                <div class="table-wrap">
                    <table class="analytics-table">
                        <thead><tr><th>Vendor / Company</th><th>Contact</th><th>Customers Added</th><th>Invoices Created</th><th>Today Sales</th><th>This Month</th><th>Total Revenue</th></tr></thead>
                        <tbody>
                        <?php if (empty($vendorStats)) { ?>
                            <tr><td colspan="7">No vendors registered yet.</td></tr>
                        <?php } else { foreach ($vendorStats as $vendor) { ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($vendor['name'] ?: 'Unnamed vendor'); ?></strong></td>
                                <td><?php echo htmlspecialchars($vendor['email'] ?: $vendor['mobile'] ?: '-'); ?></td>
                                <td><span class="badge" style="background:#2c3e50;"><?php echo (int) $vendor['customer_count']; ?></span></td>
                                <td><span class="badge" style="background:#1f9d8a;"><?php echo (int) $vendor['invoice_count']; ?></span></td>
                                <td>₹<?php echo number_format((float) $vendor['today_sales'], 2); ?></td>
                                <td>₹<?php echo number_format((float) $vendor['month_sales'], 2); ?></td>
                                <td><strong>₹<?php echo number_format((float) $vendor['total_sales'], 2); ?></strong></td>
                            </tr>
                        <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3>Recent System Sales & Invoices</h3>
                <div class="table-wrap">
                    <table class="analytics-table">
                        <thead><tr><th>Invoice #</th><th>Vendor / Company</th><th>Customer Name</th><th>Mobile</th><th>Amount</th><th>Date & Time</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php if (empty($recentInvoices)) { ?>
                            <tr><td colspan="7">No sales recorded in the system yet.</td></tr>
                        <?php } else { foreach ($recentInvoices as $inv) { ?>
                            <tr>
                                <td>#<?php echo (int) $inv['id']; ?></td>
                                <td><span class="label label-info"><?php echo htmlspecialchars($inv['company_name'] ?: 'Vendor'); ?></span></td>
                                <td><?php echo htmlspecialchars($inv['cus_name'] ?: 'Customer'); ?></td>
                                <td><?php echo htmlspecialchars($inv['cus_mobile'] ?: '-'); ?></td>
                                <td><strong>₹<?php echo number_format((float) $inv['total_amt'], 2); ?></strong></td>
                                <td><?php echo date('d M Y, h:i A', (int) $inv['created_date']); ?></td>
                                <td><a href="invoiceDetail.php?inv_id=<?php echo (int) $inv['id']; ?>" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye-open"></i> View / Print</a></td>
                            </tr>
                        <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="nav-grid">
                <a class="nav-box" href="create_bill.php"><i class="glyphicon glyphicon-plus"></i> Create Invoice</a>
                <a class="nav-box" href="invoice_list.php"><i class="glyphicon glyphicon-list-alt"></i> All Invoices</a>
                <a class="nav-box" href="customer_list.php"><i class="glyphicon glyphicon-user"></i> All Customers</a>
                <a class="nav-box" href="vendor_list.php"><i class="glyphicon glyphicon-briefcase"></i> All Vendors</a>
            </div>
        <?php } ?>
    </div>
    <!-- Logout Confirmation Modal UI -->
    <div id="logoutModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:12px; max-width:400px; width:90%; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.25); text-align:center;">
            <div style="width:50px; height:50px; background:#fee2e2; color:#ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; font-size:22px; font-weight:bold;">&#10007;</div>
            <h3 style="margin:0 0 8px; color:#1e293b; font-size:20px; font-weight:600;">Confirm Logout</h3>
            <p style="margin:0 0 20px; color:#64748b; font-size:14px; line-height:1.5;">Are you sure you want to log out of your session?</p>
            <div style="display:flex; gap:12px; justify-content:center;">
                <button id="cancelLogoutBtn" type="button" style="flex:1; padding:10px 16px; border:1px solid #cbd5e1; background:#fff; color:#334155; border-radius:6px; font-weight:600; cursor:pointer;">Cancel</button>
                <a href="?logout=1" style="flex:1; padding:10px 16px; background:#ef4444; color:#fff; border-radius:6px; font-weight:600; text-decoration:none; display:inline-block; line-height:1.4;">Logout</a>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/chatbot_widget.php'; ?>
    <script>
        (function () {
            var modal = document.getElementById('logoutModal');
            var cancelBtn = document.getElementById('cancelLogoutBtn');

            function showModal() {
                if (modal) modal.style.display = 'flex';
            }

            function hideModal() {
                if (modal) modal.style.display = 'none';
            }

            if (cancelBtn) {
                cancelBtn.addEventListener('click', function () {
                    hideModal();
                    history.pushState(null, document.title, location.href);
                });
            }

            // Intercept browser back button
            history.pushState(null, document.title, location.href);
            window.addEventListener('popstate', function () {
                showModal();
            });

            // Also attach custom UI to topbar logout link
            var logoutLink = document.querySelector('.topbar a[href="?logout=1"]');
            if (logoutLink) {
                logoutLink.addEventListener('click', function (e) {
                    e.preventDefault();
                    showModal();
                });
            }
        }());
    </script>
</body>
</html>
