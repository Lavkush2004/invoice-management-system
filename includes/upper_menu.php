<nav class="navbar navbar-default" role="navigation" id="navbar" style="background:#2c3e50; height:50px; width:100%; margin:0;">
    <!-- navbar-header -->
    <div class="navbar-header" style="float: left; display: flex; align-items: center; height: 50px;">
        <button type="button" id="sidebarToggle" style="margin-left: 10px; background: transparent; border: 1px solid rgba(255,255,255,0.35); color: white; padding: 6px 10px; border-radius: 4px; cursor: pointer; line-height: 1;">
            ☰
        </button>
        <a class="navbar-brand" href="<?php echo APP_URL; ?>" style="display: inline-block; padding-left: 10px; margin-right: 0; color:white;">
            <?php
            $username = '';
            if(isset($_SESSION['vendor_data']) && (count($_SESSION['vendor_data'])>0))
            {
                $username = 'First Trade';//$_SESSION['vendor_data']['name'];
            }
            else if(isset($_SESSION['admin_data']) && (count($_SESSION['admin_data'])>0))
            {
                $username = $_SESSION['admin_data']['username'];
            }
            else if(isset($_SESSION['customer_data']) && (count($_SESSION['customer_data'])>0))
            {
                $username = $_SESSION['customer_data']['cus_name'];
            }
            echo "<marquee><font color='White'>Welcome $username</font></marquee>";
            ?>
        </a>
    </div>
    <!-- end navbar-header -->
    
    <!-- navbar-top-links -->
    <ul class="nav navbar-top-links navbar-right" style="margin:0;">
        <!-- main dropdown -->
        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                <i class="fa fa-user fa-3x"></i>
            </a>
            <!-- dropdown user-->
            <ul class="dropdown-menu dropdown-user">
                <li><a href="#"><i class="fa fa-user fa-fw"></i>User Profile</a></li>
                <li class="divider"></li>
                <li><a href="<?php echo APP_URL; ?>logout.php"><i class="fa fa-sign-out fa-fw"></i>Logout</a></li>
            </ul>
            <!-- end dropdown-user -->
        </li>
        <!-- end main dropdown -->
    </ul>
    <!-- end navbar-top-links -->
</nav>