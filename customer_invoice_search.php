<?php
require_once __DIR__ . '/includes/auth_check.php';
$userRole = require_login(array('admin', 'vendor', 'customer'));
require_once __DIR__ . '/includes/connection_mysql.php';

$searchPerformed = false;
$invoices = array();
$searchError = '';

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchType = isset($_POST['search_type']) ? trim($_POST['search_type']) : '';

    if ($searchType === 'invoice_number') {
        $invoiceNumber = isset($_POST['invoice_number']) ? trim($_POST['invoice_number']) : '';

        if (empty($invoiceNumber)) {
            $searchError = 'Please enter an invoice number.';
        } else {
            $invoiceNumber = (int) $invoiceNumber;
            $invoiceCondition = 'i.id = ' . $invoiceNumber;
            if ($userRole === 'customer') {
                $invoiceCondition .= ' AND i.cust_id = ' . (int)$_SESSION['customer_data']['cus_id'];
            } else {
                $invoiceCondition .= ' AND i.comp_id = ' . get_current_company_id();
            }
            $result = Select_Some('i.*', 'invoice AS i', $invoiceCondition);

            if (is_object($result) && mysqli_num_rows($result) > 0) {
                $invoice = mysqli_fetch_assoc($result);

                // Get customer details
                $customerResult = Select_Some('*', 'customers', 'cus_id = ' . (int) $invoice['cust_id'] . ' AND company_id = ' . (int)$invoice['comp_id']);
                if (is_object($customerResult) && mysqli_num_rows($customerResult) > 0) {
                    $customer = mysqli_fetch_assoc($customerResult);
                    $invoice['customer'] = $customer;
                }

                // Get company details
                $companyResult = Select_Some('*', 'companies', 'userid = ' . (int) $invoice['comp_id']);
                if (is_object($companyResult) && mysqli_num_rows($companyResult) > 0) {
                    $company = mysqli_fetch_assoc($companyResult);
                    $invoice['company'] = $company;
                }

                $invoices[] = $invoice;
            } else {
                $searchError = 'No invoice found with this number.';
            }
        }
    } elseif ($searchType === 'customer_details') {
        $mobileNumber = isset($_POST['mobile_number']) ? trim($_POST['mobile_number']) : '';
        $buyerName = isset($_POST['buyer_name']) ? trim($_POST['buyer_name']) : '';

        if (empty($mobileNumber) || empty($buyerName)) {
            $searchError = 'Please enter both mobile number and buyer name.';
        } else {
            // Search customers by mobile and name
            $mobileNumber = mysqli_real_escape_string($GLOBALS['con'], $mobileNumber);
            $buyerName = mysqli_real_escape_string($GLOBALS['con'], $buyerName);

            $customerQuery = "SELECT * FROM customers WHERE cus_mobile = '$mobileNumber' AND cus_name LIKE '%$buyerName%' AND company_id = " . get_current_company_id();
            if ($userRole === 'customer') {
                $customerQuery .= " AND cus_id = " . (int)$_SESSION['customer_data']['cus_id'];
            }
            $customerResult = mysqli_query($GLOBALS['con'], $customerQuery);

            if (is_object($customerResult) && mysqli_num_rows($customerResult) > 0) {
                while ($customer = mysqli_fetch_assoc($customerResult)) {
                    // Get all invoices for this customer
                    $invoiceQuery = "SELECT * FROM invoice WHERE cust_id = " . (int) $customer['cus_id'] . " AND comp_id = " . get_current_company_id() . " ORDER BY created_date DESC";
                    $invoiceResult = mysqli_query($GLOBALS['con'], $invoiceQuery);

                    if (is_object($invoiceResult) && mysqli_num_rows($invoiceResult) > 0) {
                        while ($invoice = mysqli_fetch_assoc($invoiceResult)) {
                            // Get company details
                            $companyResult = Select_Some('*', 'companies', 'userid = ' . (int) $invoice['comp_id']);
                            if (is_object($companyResult) && mysqli_num_rows($companyResult) > 0) {
                                $company = mysqli_fetch_assoc($companyResult);
                                $invoice['company'] = $company;
                            }

                            $invoice['customer'] = $customer;
                            $invoices[] = $invoice;
                        }
                    }
                }

                if (empty($invoices)) {
                    $searchError = 'No invoices found for this customer.';
                }
            } else {
                $searchError = 'No customer found with this mobile number and name.';
            }
        }
    }

    $searchPerformed = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Search Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f4f4;
            padding-top: 20px;
        }
        .search-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .search-box {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
        }
        .tab-content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .nav-tabs > li > a {
            color: #333;
            font-weight: 500;
        }
        .nav-tabs > li.active > a {
            background: #3498db;
            border: 1px solid #3498db;
            color: white;
        }
        .alert {
            margin-top: 20px;
        }
        .invoice-result {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background: #fafafa;
        }
        .invoice-result h4 {
            color: #2c3e50;
            margin-top: 0;
        }
        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 10px 0;
        }
        .invoice-info p {
            margin: 5px 0;
            font-size: 13px;
        }
        .invoice-info strong {
            color: #333;
        }
        .btn-view {
            margin-top: 10px;
        }
        .no-results {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="search-container">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0;"><i class="fa fa-search"></i> Search Your Invoice</h1>
                <p style="color: #666; margin-top: 5px; margin-bottom: 0;">Find your invoice by invoice number or by your details</p>
            </div>
            <a href="<?php echo htmlspecialchars(APP_URL . 'invoice_list.php', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back to Invoices</a>
        </div>

        <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 0;">
            <li role="presentation" class="active">
                <a href="#invoiceTab" aria-controls="invoiceTab" role="tab" data-toggle="tab">
                    <i class="fa fa-file-text"></i> By Invoice Number
                </a>
            </li>
            <li role="presentation">
                <a href="#customerTab" aria-controls="customerTab" role="tab" data-toggle="tab">
                    <i class="fa fa-user"></i> By My Details
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Search by Invoice Number -->
            <div role="tabpanel" class="tab-pane active" id="invoiceTab">
                <form method="POST" action="">
                    <input type="hidden" name="search_type" value="invoice_number">

                    <div class="form-group">
                        <label for="invoice_number">Invoice Number</label>
                        <input type="number" class="form-control" id="invoice_number" name="invoice_number"
                               placeholder="Enter invoice number (e.g., 9)" required>
                        <small class="form-text text-muted">Enter the invoice ID you want to search for</small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Search Invoice
                    </button>
                </form>
            </div>

            <!-- Search by Customer Details -->
            <div role="tabpanel" class="tab-pane" id="customerTab">
                <form method="POST" action="">
                    <input type="hidden" name="search_type" value="customer_details">

                    <div class="form-group">
                        <label for="mobile_number">Mobile Number</label>
                        <input type="text" class="form-control" id="mobile_number" name="mobile_number"
                               placeholder="Enter your mobile number (e.g., 9999999999)" required>
                    </div>

                    <div class="form-group">
                        <label for="buyer_name">Buyer Name</label>
                        <input type="text" class="form-control" id="buyer_name" name="buyer_name"
                               placeholder="Enter your name (full or partial)" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Search My Invoices
                    </button>
                </form>
            </div>
        </div>

        <!-- Search Results -->
        <?php if ($searchPerformed) { ?>
            <?php if (!empty($searchError)) { ?>
                <div class="alert alert-warning alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($searchError); ?>
                </div>
            <?php } elseif (!empty($invoices)) { ?>
                <div class="alert alert-success alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <i class="fa fa-check-circle"></i> Found <?php echo count($invoices); ?> invoice(s)
                </div>

                <?php foreach ($invoices as $invoice) { ?>
                    <div class="invoice-result">
                        <h4>Invoice #<?php echo (int) $invoice['id']; ?></h4>

                        <div class="invoice-info">
                            <div>
                                <p><strong>Date:</strong> <?php echo date('d-M-Y', (int) $invoice['created_date']); ?></p>
                                <p><strong>Amount:</strong> ₹<?php echo number_format((float) $invoice['total_amt'], 2); ?></p>
                            </div>
                            <div>
                                <p><strong>Company:</strong> <?php echo !empty($invoice['company']['name']) ? htmlspecialchars($invoice['company']['name']) : 'N/A'; ?></p>
                                <p><strong>Customer:</strong> <?php echo !empty($invoice['customer']['cus_name']) ? htmlspecialchars($invoice['customer']['cus_name']) : 'N/A'; ?></p>
                            </div>
                        </div>

                        <a href="invoiceDetail.php?inv_id=<?php echo (int) $invoice['id']; ?>&return=customer_invoice_search.php"
                           class="btn btn-sm btn-info btn-view">
                            <i class="fa fa-eye"></i> View Details
                        </a>
                        <a href="invoiceDetail.php?inv_id=<?php echo (int) $invoice['id']; ?>&pdf=1&return=customer_invoice_search.php"
                           class="btn btn-sm btn-success btn-view">
                            <i class="fa fa-download"></i> Download PDF
                        </a>
                    </div>
                <?php } ?>
            <?php } ?>
        <?php } ?>

        <div style="margin-top: 30px; text-align: center; border-top: 1px solid #ddd; padding-top: 20px;">
            <p style="color: #666;">
                <i class="fa fa-info-circle"></i>
                Need help? Contact us for support.
            </p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
    <script>
        (function () {
            function openTabFromHash() {
                var hash = window.location.hash;
                if (hash === '#customerTab' || hash === '#invoiceTab') {
                    $('a[href="' + hash + '"]').tab('show');
                }
            }

            $(openTabFromHash);
            $(window).on('hashchange', openTabFromHash);
        }());
    </script>
<?php require_once __DIR__ . '/includes/chatbot_widget.php'; ?>
</body>
</html>
