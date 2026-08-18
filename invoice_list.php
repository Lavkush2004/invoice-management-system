<?php
require_once __DIR__ . '/includes/auth_check.php';
$userRole = require_login(array('admin', 'vendor', 'customer'));

require_once __DIR__ . '/includes/header.php';
$result = array();

$fields = " i.*, cu.cus_name as customer, cm.name as company";
$table = " invoice as i ";
$table .= " LEFT JOIN customers as cu ON cu.cus_id = i.cust_id ";
$table .= " LEFT JOIN companies as cm ON cm.userid = i.comp_id ";

if ($userRole === 'customer' && !empty($_SESSION['customer_data']['cus_id'])) {
    $cond = " i.cust_id = " . (int) $_SESSION['customer_data']['cus_id'] . " ORDER BY i.id DESC";
    $data = Select_Some($fields, $table, $cond);
    if (is_object($data) && mysqli_num_rows($data) > 0) {
        while ($d = mysqli_fetch_assoc($data)) {
            $result[] = $d;
        }
    }
} elseif ($userRole === 'vendor' && !empty($_SESSION['vendor_data']['userid'])) {
    $cond = " i.comp_id = " . (int) $_SESSION['vendor_data']['userid'] . " ORDER BY i.id DESC";
    $data = Select_Some($fields, $table, $cond);
    if (is_object($data) && mysqli_num_rows($data) > 0) {
        while ($d = mysqli_fetch_assoc($data)) {
            $result[] = $d;
        }
    }
} elseif ($userRole === 'admin') {
    $cond = " 1 = 1 ORDER BY i.id DESC";
    $data = Select_Some($fields, $table, $cond);
    if (is_object($data) && mysqli_num_rows($data) > 0) {
        while ($d = mysqli_fetch_assoc($data)) {
            $result[] = $d;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<body>
    <!-- wrapper -->
    <div id="wrapper">
        <!-- navbar top -->
        <?php require_once __DIR__ . '/includes/upper_menu.php'; ?>
        <!-- end navbar top -->

        <!-- navbar side -->
        <?php require_once __DIR__ . '/includes/left_menu.php';?>
        <!-- end navbar side -->

        <!-- page-wrapper -->
        <div id="page-wrapper">
            <div class="row">
                <!-- page header -->
                <div class="col-lg-12">
                    <h1 class="page-header">Invoice List</h1>
                </div>
                <!-- end page header -->
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <!-- Advanced Tables -->
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>Invoice ID</th>
                                            <th>Customer Name</th>
                                            <th>Company Name</th>
                                            <th>Total Amt</th>
                                            <th>Created Date & Time</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(count($result)>0) { $i=1;
                                        foreach ($result as $key => $value) {
                                        ?>
                                        <tr class="<?php if($i%2!=0){ echo 'odd';} else {echo 'even';}?> ">
                                            <td><?php echo $value['id'];?></td>
                                            <td><?php echo $value['customer'];?></td>
                                            <td><?php echo $value['company'];?></td>
                                            <td><?php echo $value['total_amt'];?></td>
                                            <td><?php echo date('Y-m-d H:i:s',$value['created_date']);?></td>
                                            <td>
                                                <a href="invoiceDetail.php?inv_id=<?php echo (int)$value['id']; ?>&return=invoice_list.php">View Detail</a>
                                                |
                                                <a href="invoiceDetail.php?inv_id=<?php echo (int)$value['id']; ?>&pdf=1&return=invoice_list.php">Download PDF</a>
                                            </td>
                                        </tr>
                                        <?php $i++; }} else {?>
                                        <tr class="even ">
                                            <td colspan="6" class="center">No Data Available</td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--End Advanced Tables -->
                </div>
            </div>
        </div>
        <!-- end page-wrapper -->
    </div>
    <!-- end wrapper -->

    <!-- Core Scripts - Include with every page -->
    <?php require_once __DIR__ . '/includes/footer.php';?>

    <!-- Page-Level Plugin Scripts-->
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script>
        $(document).ready(function () {
            $('#dataTables-example').dataTable();
        });
    </script>
</body>
</html>