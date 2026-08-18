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
                    <h1 class="page-header">Company Profile</h1>
                </div>
                <!--end page header -->
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <!-- Form Elements -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-building"></i> Company Information & Settings
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <form role="form" method="post" action="<?php echo APP_URL.'model/common.php'?>" onsubmit="return update_profile();">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Company / Vendor Name <span class="text-danger">*</span></label>
                                            <input type="hidden" name="userid" id="userid" value="<?php echo (int)$userid; ?>" />
                                            <input type="hidden" name="action" id="action" value="update_company_profile">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(require_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="text" class="form-control" name="name" id="name" placeholder="Enter company name" value="<?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['name'] ?? '', ENT_QUOTES, 'UTF-8'); }?>">
                                            <span class="error" id="e_name"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Address <span class="text-danger">*</span></label>
                                            <textarea name="address" id="address" class="form-control" rows="4" placeholder="Enter full registered address"><?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['address'] ?? '', ENT_QUOTES, 'UTF-8'); }?></textarea>
                                            <span class="error" id="e_address"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Mobile Number <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Enter phone number" value="<?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['mobile'] ?? '', ENT_QUOTES, 'UTF-8'); }?>">
                                            <span class="error" id="e_mobile"></span>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Email Address <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" id="email" placeholder="vendor@example.com" value="<?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['email'] ?? '', ENT_QUOTES, 'UTF-8'); }?>">
                                            <span class="error" id="e_email"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Website</label>
                                            <input type="text" class="form-control" name="website" id="website" placeholder="https://example.com" value="<?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['website'] ?? '', ENT_QUOTES, 'UTF-8'); }?>">
                                            <span class="error" id="e_website"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>GST Number</label>
                                            <input type="text" class="form-control" name="gst" id="gst" placeholder="GSTIN (optional)" value="<?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['gst_no'] ?? '', ENT_QUOTES, 'UTF-8'); }?>">
                                            <span class="error" id="e_gst"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>PAN Number</label>
                                            <input type="text" class="form-control" name="pan" id="pan" placeholder="PAN number (optional)" value="<?php if(count($vendorDetails)>0){ echo htmlspecialchars($vendorDetails['pan_no'] ?? '', ENT_QUOTES, 'UTF-8'); }?>">
                                            <span class="error" id="e_pan"></span>
                                        </div>
                                    </div>

                                    <div class="col-lg-12" style="margin-top: 15px;">
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Profile</button>
                                        <a href="index.php" class="btn btn-default"><i class="fa fa-times"></i> Cancel</a>
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
            var email = $('#email').val().trim();

            $('#e_name').html('');
            $('#e_address').html('');
            $('#e_mobile').html('');
            $('#e_email').html('');

            if(name=='')
            {
                $('#name').focus();
                $('#e_name').html('Please enter company name');
                return false;
            }
            else if(address=='')
            {
                $('#address').focus();
                $('#e_address').html('Please enter address');
                return false;
            }
            else if(mobile=='')
            {
                $('#mobile').focus();
                $('#e_mobile').html('Please enter mobile');
                return false;
            }
            else if(email=='')
            {
                $('#email').focus();
                $('#e_email').html('Please enter email');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>