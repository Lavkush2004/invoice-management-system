<?php
require_once __DIR__ . '/includes/auth_check.php';
$userRole = require_login(array('admin', 'vendor'));

$vendorDetails = array();
$userid = 0;

if ($userRole === 'vendor') {
    $userid = (int)$_SESSION['vendor_data']['userid'];
} elseif ($userRole === 'admin') {
    $userid = get_current_company_id();
}

if ($userid > 0) {
    $cond = " userid = " . $userid . " AND userid = " . get_current_company_id();
    $vendorData = Select_Some("*", "companies", $cond);
    if (is_object($vendorData) && mysqli_num_rows($vendorData) > 0) {
        $vendorDetails = mysqli_fetch_assoc($vendorData);
    }
}

require_once __DIR__ . '/includes/header.php';
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
                    <h1 class="page-header">Vendor Form</h1>
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
                                            <input type="hidden" class="form-control" name="userid" id="userid" value="<?php echo (int)$userid; ?>" />
                                            <input type="hidden" class="form-control" name="action" id="action" value="update_company_profile">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(require_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="text" class="form-control" name="name" id="name" value="<?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['name']); }?>">
                                            <span class="error" id="e_name"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Address</label>
                                            <textarea name="address" id="address" class="form-control" rows="5"><?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['address']); }?></textarea>
                                            <span class="error" id="e_address"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Mobile</label>
                                            <input type="text" class="form-control" name="mobile" id="mobile" value="<?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['mobile']); }?>">
                                            <span class="error" id="e_mobile"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Logo-Image</label>
                                            <input type="file" class="form-control" name="logo" id="logo" value="<?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['logo_img']); }?>">
                                            <span class="error" id="e_logo"></span>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Submit Button</button>
                                        <button type="reset" class="btn btn-success">Reset Button</button>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="text" class="form-control" name="email" id="email" value="<?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['email']); }?>">
                                            <span class="error" id="e_email"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Website</label>
                                            <input type="text" class="form-control" name="website" id="website" value="<?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['website']); }?>">
                                            <span class="error" id="e_website"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>GST No</label>
                                            <input type="text" class="form-control" name="gst" id="gst" value="<?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['gst_no']); }?>">
                                            <span class="error" id="e_gst"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Pan No</label>
                                            <input type="text" class="form-control" name="pan" id="pan" value="<?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['pan_no']); }?>">
                                            <span class="error" id="e_pan"></span>
                                        </div>

                                        <div class="form-group">
                                            <img />
                                        </div>
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
            var logo = $('#logo').val().trim();
            var email = $('#email').val().trim();
            var website = $('#website').val().trim();
            var gst = $('#gst').val().trim();
            var pan = $('#pan').val().trim();

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
            else if(logo=='')
            {
                $('#logo').val('');
                $('#logo').focus();
                $('#e_logo').html('Please enter logo-image');
                return false;
            }
            else if(email=='')
            {
                $('#email').val('');
                $('#email').focus();
                $('#e_email').html('Please enter email');
                return false;
            }
            else if(website=='')
            {
                $('#website').val('');
                $('#website').focus();
                $('#e_website').html('Please enter website');
                return false;
            }
            else if(gst=='')
            {
                $('#gst').val('');
                $('#gst').focus();
                $('#e_gst').html('Please enter gst no');
                return false;
            }
            else if(pan=='')
            {
                $('#pan').val('');
                $('#pan').focus();
                $('#e_pan').html('Please enter pan no');
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