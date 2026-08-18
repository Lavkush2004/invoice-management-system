<?php
require_once __DIR__ . '/includes/auth_check.php';
$userRole = require_login(array('admin', 'vendor', 'customer'));

require_once __DIR__ . '/includes/header.php';
$result = array();
$fields = "*";
$table = 'customers';

if ($userRole === 'customer' && !empty($_SESSION['customer_data']['cus_id'])) {
    $cond = " cus_id = " . (int) $_SESSION['customer_data']['cus_id'];
    $data = Select_Some($fields, $table, $cond);
    if (is_object($data) && mysqli_num_rows($data) > 0) {
        while ($d = mysqli_fetch_assoc($data)) {
            $result[] = $d;
        }
    }
} elseif ($userRole === 'vendor' && !empty($_SESSION['vendor_data']['userid'])) {
    $cond = " company_id = " . (int) $_SESSION['vendor_data']['userid'];
    $data = Select_Some($fields, $table, $cond);
    if (is_object($data) && mysqli_num_rows($data) > 0) {
        while ($d = mysqli_fetch_assoc($data)) {
            $result[] = $d;
        }
    }
} elseif ($userRole === 'admin') {
    $data = Select_Some($fields, $table, '1 = 1 ORDER BY cus_id DESC');
    if (is_object($data) && mysqli_num_rows($data) > 0) {
        while ($d = mysqli_fetch_assoc($data)) {
            $result[] = $d;
        }
    }
}
?>
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
                    <div class="page-header clearfix" style="margin-top: 20px; padding-bottom: 10px;">
                        <h1 class="pull-left" style="margin: 0; border-bottom: none;">Customer List</h1>
                        <?php if ($userRole === 'admin' || $userRole === 'vendor') { ?>
                            <a href="customer.php" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add Customer</a>
                        <?php } ?>
                    </div>
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
                                            <th>Name</th>
                                            <th>Address</th>
                                            <th>Mobile</th>
                                            <th>Email</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(count($result)>0) { $i=1;
                                        foreach ($result as $key => $value) {
                                        ?>
                                        <tr class="<?php if($i%2!=0){ echo 'odd';} else {echo 'even';}?> ">
                                            <td><?php echo htmlspecialchars($value['cus_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($value['cus_address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($value['cus_mobile'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($value['cus_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php if ($userRole === 'admin' || $userRole === 'vendor') { ?><a href="customer.php?cus_id=<?php echo (int)$value['cus_id']; ?>">Edit</a><?php } else { echo 'View only'; } ?></td>
                                        </tr>
                                        <?php $i++; }} else {?>
                                        <tr class="even ">
                                            <td colspan="5" class="center">No Data Available</td>
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
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#dataTables-example').dataTable();
        });
    </script>
</body>
</html>