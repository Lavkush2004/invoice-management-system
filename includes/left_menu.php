<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebarMenu" class="navbar-default navbar-static-side" role="navigation">
    <!-- sidebar-collapse -->
    <div class="sidebar-collapse">
        <!-- side-menu -->
        <ul class="nav" id="side-menu">
            <li class="sidebar-search">
                <!-- search section-->
                <div class="input-group custom-search-form">
                </div>
                <!--end search section-->
            </li>
            <li class="<?php echo ($currentPage == 'index.php' || $currentPage == '') ? 'selected' : ''; ?>">
                <a href="<?php echo APP_URL;?>"><i class="fa fa-dashboard fa-fw"></i>Dashboard</a>
            </li>
            <li class="<?php echo ($currentPage == 'customer_list.php' || $currentPage == 'customer.php') ? 'selected' : ''; ?>">
                <a href="<?php echo APP_URL;?>customer_list.php"><i class="fa fa-list"></i>Customer List</a>
            </li>
            <li class="<?php echo ($currentPage == 'invoice_list.php' || $currentPage == 'invoiceDetail.php') ? 'selected' : ''; ?>">
                <a href="<?php echo APP_URL;?>invoice_list.php"><i class="fa fa-list"></i>Invoice List</a>
            </li>
            <li class="<?php echo ($currentPage == 'create_bill.php') ? 'selected' : ''; ?>">
                <a href="<?php echo APP_URL;?>create_bill.php"><i class="fa fa-files-o"></i>Create Invoice</a>
            </li>
            <?php if(isset($_SESSION['admin_data'])) {?>
            <li class="<?php echo ($currentPage == 'vendor_list.php' || $currentPage == 'vendor.php') ? 'selected' : ''; ?>">
                <a href="<?php echo APP_URL;?>vendor_list.php"><i class="fa fa-list"></i>Vendor List</a>
            </li>
            <?php }?>
        </ul>
        <!-- end side-menu -->
    </div>
    <!-- end sidebar-collapse -->
</nav>