<?php
require_once __DIR__ . '/includes/auth_check.php';
$userRole = require_login(array('admin', 'vendor'));

$cus_id = isset($_REQUEST['cus_id']) ? (int)$_REQUEST['cus_id'] : 0;
$customerData = array();
if ($cus_id > 0) {
    $companyId = get_current_company_id();
    $customerCondition = 'cus_id = ' . $cus_id . ($userRole === 'admin' ? '' : ' AND company_id = ' . $companyId);
    $cQuery = Select_Some('*', 'customers', $customerCondition);
    if (is_object($cQuery) && mysqli_num_rows($cQuery) > 0) {
        $customerData = mysqli_fetch_assoc($cQuery);
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<body>
    <!-- wrapper -->
    <div id="wrapper">
        <!-- navbar top -->
        <?php require_once __DIR__ . '/includes/upper_menu.php';?>
        <!-- end navbar top -->

        <!-- navbar side -->
        <?php require_once __DIR__ . '/includes/left_menu.php';?>
        <!-- end navbar side -->

        <!-- page-wrapper -->
        <div id="page-wrapper">
            <div class="row">
                <!-- page header -->
                <div class="col-lg-12">
                    <h1 class="page-header"><?php echo !empty($customerData['cus_id']) ? 'Edit Customer' : 'Add New Customer'; ?></h1>
                </div>
                <!--end page header -->
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <!-- Form Elements -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-user"></i> <?php echo !empty($customerData['cus_id']) ? 'Edit Customer Details' : 'Customer Information'; ?>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <form role="form" method="post" action="<?php echo APP_URL.'model/common.php'?>" onsubmit="return update_profile();">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Full Name <span class="text-danger">*</span></label>
                                            <input type="hidden" name="cus_id" id="cus_id" value="<?php echo !empty($customerData['cus_id']) ? (int)$customerData['cus_id'] : ''; ?>" />
                                            <input type="hidden" name="action" id="action" value="update_customer_profile">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(require_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="text" class="form-control" name="name" id="name" placeholder="Enter full name" value="<?php echo !empty($customerData['cus_name']) ? htmlspecialchars($customerData['cus_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                            <span class="error" id="e_name"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Address <span class="text-danger">*</span></label>
                                            <textarea name="address" id="address" class="form-control" rows="4" placeholder="Enter complete billing address"><?php echo !empty($customerData['cus_address']) ? htmlspecialchars($customerData['cus_address'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                            <span class="error" id="e_address"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Mobile Number <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Enter 10-digit mobile number" value="<?php echo !empty($customerData['cus_mobile']) ? htmlspecialchars($customerData['cus_mobile'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                            <span class="error" id="e_mobile"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="password" class="form-control" name="password" id="password" placeholder="Leave blank to keep current (default: 123456)" value="<?php echo !empty($customerData['cus_password']) ? htmlspecialchars($customerData['cus_password'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                            <span class="error" id="e_password"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Email Address</label>
                                            <input type="email" class="form-control" name="email" id="email" placeholder="customer@example.com (optional)" value="<?php echo !empty($customerData['cus_email']) ? htmlspecialchars($customerData['cus_email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                            <span class="error" id="e_email"></span>
                                        </div>

                                        <div style="margin-top: 20px;">
                                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo !empty($customerData['cus_id']) ? 'Update Customer' : 'Save Customer'; ?></button>
                                            <a href="customer_list.php" class="btn btn-default"><i class="fa fa-times"></i> Cancel</a>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- End Form Elements -->
                </div>
            </div>
        </div>
        <!-- end page-wrapper -->
    </div>
    <!-- end wrapper -->

    <!-- Core Scripts - Include with every page -->
    <?php require_once __DIR__ . '/includes/footer.php';?>

    <script type="text/javascript">
        function update_profile()
        {
            var name = $('#name').val().trim();
            var address = $('#address').val().trim();
            var mobile = $('#mobile').val().trim();

            $('#e_name').html('');
            $('#e_address').html('');
            $('#e_mobile').html('');

            if(name=='')
            {
                $('#name').focus();
                $('#e_name').html('Please enter customer name');
                return false;
            }
            else if(address=='')
            {
                $('#address').focus();
                $('#e_address').html('Please enter customer address');
                return false;
            }
            else if(mobile=='')
            {
                $('#mobile').focus();
                $('#e_mobile').html('Please enter mobile number');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>