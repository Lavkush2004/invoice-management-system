<nav class="navbar navbar-default" role="navigation" id="navbar" style="background:#2c3e50; min-height:50px; width:100%; margin:0; border:none; border-radius:0;">
    <!-- navbar-header -->
    <div class="navbar-header" style="float: left; display: flex; align-items: center; height: 50px;">
        <button type="button" id="sidebarToggle" style="margin-left: 15px; background: transparent; border: 1px solid rgba(255,255,255,0.35); color: white; padding: 6px 10px; border-radius: 4px; cursor: pointer; line-height: 1;">
            <i class="fa fa-bars"></i>
        </button>
        <a class="navbar-brand" href="<?php echo APP_URL; ?>" style="display: flex; align-items: center; padding-left: 12px; margin-right: 0; color:white; font-size: 15px; font-weight: 600;">
            <?php
            $username = 'User';
            if (isset($_SESSION['vendor_data']['name']) && !empty($_SESSION['vendor_data']['name'])) {
                $username = $_SESSION['vendor_data']['name'];
            } elseif (isset($_SESSION['admin_data']['username']) && !empty($_SESSION['admin_data']['username'])) {
                $username = $_SESSION['admin_data']['username'];
            } elseif (isset($_SESSION['customer_data']['cus_name']) && !empty($_SESSION['customer_data']['cus_name'])) {
                $username = $_SESSION['customer_data']['cus_name'];
            }
            ?>
            <span style="color:#38bdf8; margin-right:6px;"><i class="fa fa-building-o"></i></span> Welcome, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>
        </a>
    </div>
    <!-- end navbar-header -->

    <!-- navbar-top-links -->
    <ul class="nav navbar-top-links navbar-right" style="margin:0; padding-right:15px; display: flex; align-items: center; height: 50px;">
        <li>
            <a href="<?php echo APP_URL; ?>logout.php" class="btn btn-danger btn-sm" style="color:#fff !important; background-color:#d9534f; border-color:#d43f3a; padding:5px 12px; margin-right:10px; border-radius:4px; font-weight:bold; font-size:13px; text-decoration:none;">
                <i class="fa fa-sign-out"></i> Logout
            </a>
        </li>
        <!-- main dropdown -->
        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="color:#fff; padding:8px 12px; font-size:16px;">
                <i class="fa fa-user-circle"></i> <i class="fa fa-caret-down"></i>
            </a>
            <!-- dropdown user-->
            <ul class="dropdown-menu dropdown-user dropdown-menu-right" style="margin-top: 5px;">
                <li><a href="<?php echo APP_URL; ?>vendor.php"><i class="fa fa-user fa-fw"></i> Profile / Settings</a></li>
                <li class="divider"></li>
                <li><a href="<?php echo APP_URL; ?>logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a></li>
            </ul>
            <!-- end dropdown-user -->
        </li>
        <!-- end main dropdown -->
    </ul>
    <!-- end navbar-top-links -->
</nav>