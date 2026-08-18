<?php
require_once __DIR__ . '/includes/auth_check.php';
$userRole = require_login(array('admin'));

require_once __DIR__ . '/includes/header.php';

$vendorList = array();
// The companies table also stores admin tenants. Join users and keep only active vendor accounts.
global $con;
$vendorQuery = "SELECT c.* FROM companies c LEFT JOIN users u ON u.id = c.userid WHERE (u.role = 2 OR u.id IS NULL OR c.userid != 2) ORDER BY c.userid DESC";
$data = mysqli_query($con, $vendorQuery);
if (is_object($data) && mysqli_num_rows($data) > 0) {
    while ($d = mysqli_fetch_assoc($data)) {
        $vendorList[] = $d;
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
                        <h1 class="pull-left" style="margin: 0; border-bottom: none;">Vendor List</h1>
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
                                            <th>ID</th>
                                            <th>Company Name</th>
                                            <th>Mobile</th>
                                            <th>Email</th>
                                            <th>Address</th>
                                            <th>GST No</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($vendorList)) { $i = 1;
                                        foreach ($vendorList as $v) {
                                        ?>
                                        <tr class="<?php echo ($i % 2 !== 0) ? 'odd' : 'even'; ?>">
                                            <td><?php echo (int) $v['userid']; ?></td>
                                            <td><?php echo htmlspecialchars($v['name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($v['mobile'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($v['email'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($v['address'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($v['gst_no'] ?? ''); ?></td>
                                            <td>
                                                <a href="vendor.php?userid=<?php echo (int) $v['userid']; ?>" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Edit</a>
                                            </td>
                                        </tr>
                                        <?php $i++; } } else { ?>
                                        <tr class="even">
                                            <td colspan="7" class="center text-center">No Vendor Data Available</td>
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