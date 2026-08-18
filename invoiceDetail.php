<?php
require_once __DIR__ . '/includes/auth_check.php';
$userRole = require_login(array('admin', 'vendor', 'customer'));
require_once __DIR__ . '/includes/connection_mysql.php';

$invoiceId = isset($_GET['inv_id']) ? (int) $_GET['inv_id'] : 0;
$returnPage = isset($_GET['return']) ? basename((string)$_GET['return']) : 'invoice_list.php';
$allowedReturnPages = array('invoice_list.php', 'customer_invoice_search.php');
if (!in_array($returnPage, $allowedReturnPages, true)) {
    $returnPage = 'invoice_list.php';
}
if ($invoiceId <= 0) {
    header('Location: ' . APP_URL . $returnPage);
    exit;
}

$companyId = get_current_company_id();
$invoiceCondition = 'id = ' . $invoiceId;
if ($userRole === 'customer') {
    $invoiceCondition .= ' AND cust_id = ' . (int) $_SESSION['customer_data']['cus_id'];
} elseif ($userRole === 'vendor') {
    $invoiceCondition .= ' AND comp_id = ' . $companyId;
}
$invoiceResult = Select_Some('*', 'invoice', $invoiceCondition);
if (!is_object($invoiceResult) || mysqli_num_rows($invoiceResult) <= 0) {
    render_app_message('Invoice Not Found', 'The invoice may have been removed or you may not have access to it.', APP_URL . $returnPage);
    exit;
}

$invoice = mysqli_fetch_assoc($invoiceResult);

// The invoice query is tenant/customer scoped; keep a defensive authorization check.
if ($userRole === 'customer' && (int)$invoice['cust_id'] !== (int)$_SESSION['customer_data']['cus_id']) {
    http_response_code(403);
    render_app_message('Access Denied', 'You do not have permission to view this invoice.', APP_URL . $returnPage);
    exit;
}

if ($userRole === 'vendor' && (int)$invoice['comp_id'] !== (int)$_SESSION['vendor_data']['userid']) {
    http_response_code(403);
    render_app_message('Access Denied', 'You do not have permission to view this invoice.', APP_URL . $returnPage);
    exit;
}

require_once __DIR__ . '/includes/header.php';
$customer = null;
$company = null;

if (!empty($invoice['cust_id'])) {
    $customerResult = Select_Some('*', 'customers', 'cus_id = ' . (int) $invoice['cust_id'] . ' AND company_id = ' . (int)$invoice['comp_id']);
    if (is_object($customerResult) && mysqli_num_rows($customerResult) > 0) {
        $customer = mysqli_fetch_assoc($customerResult);
    }
}

if (!empty($invoice['comp_id'])) {
    $companyResult = Select_Some('*', 'companies', 'userid = ' . (int) $invoice['comp_id']);
    if (is_object($companyResult) && mysqli_num_rows($companyResult) > 0) {
        $company = mysqli_fetch_assoc($companyResult);
    }
}

$itemsResult = Select_Some('*', 'invoice_details', 'inv_id = ' . $invoiceId . ' AND EXISTS (SELECT 1 FROM invoice WHERE invoice.id = invoice_details.inv_id AND invoice.comp_id = ' . (int)$invoice['comp_id'] . ')');
$items = array();
if (is_object($itemsResult) && mysqli_num_rows($itemsResult) > 0) {
    while ($row = mysqli_fetch_assoc($itemsResult)) {
        $items[] = $row;
    }
}

$subTotal = 0;
foreach ($items as $item) {
    $subTotal += (float) $item['total_amt'];
}

$pdfMode = isset($_GET['pdf']) && $_GET['pdf'] == '1';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        body { background: #fff; }
        .invoice-box {
            max-width: 980px;
            margin: 30px auto;
            padding: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
        }
        .invoice-header { border-bottom: 1px solid #ddd; padding-bottom: 15px; margin-bottom: 30px; }
        .invoice-meta { margin-top: 20px; }
        .table > tbody > tr > td, .table > thead > tr > th { vertical-align: middle; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .invoice-box { box-shadow: none; border: none; margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div id="wrapper" class="invoice-box">
        <div class="row invoice-header">
            <div class="col-sm-6">
                <h2><?php echo !empty($company['name']) ? htmlspecialchars($company['name']) : 'Company'; ?></h2>
                <p><?php echo !empty($company['address']) ? htmlspecialchars($company['address']) : ''; ?></p>
                <p>Email: <?php echo !empty($company['email']) ? htmlspecialchars($company['email']) : ''; ?></p>
                <p>Phone: <?php echo !empty($company['mobile']) ? htmlspecialchars($company['mobile']) : ''; ?></p>
            </div>
            <div class="col-sm-6 text-right">
                <h3>Invoice</h3>
                <p><strong>Invoice ID:</strong> <?php echo (int) $invoice['id']; ?></p>
                <p><strong>Date:</strong> <?php echo date('Y-m-d', (int) $invoice['created_date']); ?></p>
            </div>
        </div>

        <div class="row invoice-meta">
            <div class="col-sm-6">
                <h4>Bill To</h4>
                <p><strong><?php echo !empty($customer['cus_name']) ? htmlspecialchars($customer['cus_name']) : 'Customer'; ?></strong></p>
                <p><?php echo !empty($customer['cus_address']) ? htmlspecialchars($customer['cus_address']) : ''; ?></p>
                <p>Phone: <?php echo !empty($customer['cus_mobile']) ? htmlspecialchars($customer['cus_mobile']) : ''; ?></p>
            </div>
            <div class="col-sm-6 text-right">
                <h4>Payment Summary</h4>
                <p><strong>Total Amount:</strong> <?php echo number_format((float) $invoice['total_amt'], 2); ?></p>
            </div>
        </div>

        <div class="clearfix" style="height:20px;"></div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>GST</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)) { $i = 1; foreach ($items as $item) { ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo number_format((float) $item['price'], 2); ?></td>
                        <td><?php echo (int) $item['quantity']; ?></td>
                        <td><?php echo number_format((float) $item['gst'], 2); ?>%</td>
                        <td><?php echo number_format((float) $item['total_amt'], 2); ?></td>
                    </tr>
                <?php $i++; } } else { ?>
                    <tr>
                        <td colspan="6" class="text-center">No item data found for this invoice.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="row" style="margin-top:20px;">
            <div class="col-sm-8"></div>
            <div class="col-sm-4">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <p><strong>Sub Total:</strong> <?php echo number_format($subTotal, 2); ?></p>
                        <p><strong>Grand Total:</strong> <?php echo number_format((float) $invoice['total_amt'], 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="no-print" style="margin-top:20px;">
            <a href="<?php echo htmlspecialchars(APP_URL . $returnPage, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back to Invoice</a>
            <a href="invoiceDetail.php?inv_id=<?php echo $invoiceId; ?>&pdf=1&return=<?php echo urlencode($returnPage); ?>" class="btn btn-primary"><i class="fa fa-download"></i> Download PDF</a>
        </div>
    </div>

    <?php if ($pdfMode) { ?>
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.print();
                }, 300);
            });
        </script>
    <?php } ?>
    <?php if (!$pdfMode) { require_once __DIR__ . '/includes/footer.php'; } ?>
</body>
</html>
