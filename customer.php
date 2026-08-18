<?php
require_once __DIR__ . '/includes/auth_check.php';
$userRole = require_login(array('admin', 'vendor'));

require_once __DIR__ . '/includes/header.php';

$cus_id = isset($_REQUEST['cus_id']) ? (int)$_REQUEST['cus_id'] : 0;
$customerData = array();
if ($cus_id > 0) {
    $customerCondition = 'cus_id = ' . $cus_id . ' AND company_id = ' . get_current_company_id();
    $cQuery = Select_Some('*', 'customers', $customerCondition);
    if (is_object($cQuery) && mysqli_num_rows($cQuery) > 0) {
        $customerData = mysqli_fetch_assoc($cQuery);
    }
}
?>
<!DOCTYPE html>
<html>
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
                    <h1 class="page-header">Customer Form</h1>
                </div>
                <!--end page header -->
            </div>

            <div class="row">
                <!-- Page Header -->
                <div class="col-lg-12">
                    <!-- Form Elements -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Basic Form Elements
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <form role="form" method="post" action="<?php echo APP_URL.'model/common.php'?>" onsubmit="return update_profile();">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="hidden" class="form-control" name="cus_id" id="cus_id" value="<?php echo !empty($customerData['cus_id']) ? (int)$customerData['cus_id'] : ''; ?>" />
                                            <input type="hidden" class="form-control" name="action" id="action" value="update_customer_profile">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(require_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="text" class="form-control" name="name" id="name" value="<?php echo !empty($customerData['cus_name']) ? htmlspecialchars($customerData['cus_name']) : ''; ?>">
                                            <span class="error" id="e_name"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Address</label>
                                            <textarea name="address" id="address" class="form-control" rows="5"><?php echo !empty($customerData['cus_address']) ? htmlspecialchars($customerData['cus_address']) : ''; ?></textarea>
                                            <span class="error" id="e_address"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Mobile</label>
                                            <input type="text" class="form-control" name="mobile" id="mobile" value="<?php echo !empty($customerData['cus_mobile']) ? htmlspecialchars($customerData['cus_mobile']) : ''; ?>">
                                            <span class="error" id="e_mobile"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="text" class="form-control" name="password" id="password" value="<?php echo !empty($customerData['cus_password']) ? htmlspecialchars($customerData['cus_password']) : ''; ?>">
                                            <span class="error" id="e_password"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="text" class="form-control" name="email" id="email" value="<?php echo !empty($customerData['cus_email']) ? htmlspecialchars($customerData['cus_email']) : ''; ?>">
                                            <span class="error" id="e_email"></span>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Submit Button</button>
                                        <button type="reset" class="btn btn-success">Reset Button</button>
                                    </div>
                                    <div class="col-lg-6">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- End Form Elements -->
                </div>
                <!--End Page Header -->
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
            var email = $('#email').val().trim();

            if(name=='')
            {
                $('#name').val('');
                $('#name').focus();
                $('#e_name').html('Please enter name');
                return false;
            }
            else if(address=='')
            {
                $('#address').val('');
                $('#address').focus();
                $('#e_address').html('Please enter address');
                return false;
            }
            else if(mobile=='')
            {
                $('#mobile').val('');
                $('#mobile').focus();
                $('#e_mobile').html('Please enter mobile');
                return false;
            }
            else if(email=='')
            {
                $('#email').val('');
                $('#email').focus();
                $('#e_email').html('Please enter email');
                return false;
            }
            else
            {
                return true;
            }
        }
    </script>
</body>
</html>