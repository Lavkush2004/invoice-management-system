<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$userRole = get_current_user_role();
?>
<nav id="sidebarMenu" class="navbar-default navbar-static-side" role="navigation">
    <!-- sidebar-collapse -->
    <div class="sidebar-collapse">
        <!-- side-menu -->
        <ul class="nav" id="side-menu">
            <li class="sidebar-search" style="padding: 10px 15px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                <div style="color: #cbd5e1; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;">
                    <i class="fa fa-user-circle"></i> <?php echo ucfirst($userRole ?: 'User'); ?> Menu
                </div>
            </li>
            <li class="<?php echo ($currentPage == 'index.php' || $currentPage == '') ? 'selected' : ''; ?>">
                <a href="<?php echo APP_URL;?>"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
            </li>
            <?php if ($userRole === 'customer') { ?>
                <li class="<?php echo ($currentPage == 'customer_invoice_search.php') ? 'selected' : ''; ?>">
                    <a href="<?php echo APP_URL;?>customer_invoice_search.php"><i class="fa fa-search fa-fw"></i> Search Invoices</a>
                </li>
            <?php } else { ?>
                <li class="<?php echo ($currentPage == 'create_bill.php') ? 'selected' : ''; ?>">
                    <a href="<?php echo APP_URL;?>create_bill.php"><i class="fa fa-plus-circle fa-fw"></i> Create Invoice</a>
                </li>
                <li class="<?php echo ($currentPage == 'invoice_list.php' || $currentPage == 'invoiceDetail.php') ? 'selected' : ''; ?>">
                    <a href="<?php echo APP_URL;?>invoice_list.php"><i class="fa fa-file-text-o fa-fw"></i> Invoice List</a>
                </li>
                <li class="<?php echo ($currentPage == 'customer_list.php' || $currentPage == 'customer.php') ? 'selected' : ''; ?>">
                    <a href="<?php echo APP_URL;?>customer_list.php"><i class="fa fa-users fa-fw"></i> Customer List</a>
                </li>
                <?php if ($userRole === 'admin') { ?>
                <li class="<?php echo ($currentPage == 'vendor_list.php') ? 'selected' : ''; ?>">
                    <a href="<?php echo APP_URL;?>vendor_list.php"><i class="fa fa-building fa-fw"></i> Vendor List</a>
                </li>
                <?php } ?>
                <li class="<?php echo ($currentPage == 'vendor.php') ? 'selected' : ''; ?>">
                    <a href="<?php echo APP_URL;?>vendor.php"><i class="fa fa-cog fa-fw"></i> <?php echo ($userRole === 'admin') ? 'Company Settings' : 'My Profile'; ?></a>
                </li>
            <?php } ?>
        </ul>
        <!-- end side-menu -->
    </div>
    <!-- end sidebar-collapse -->
</nav>