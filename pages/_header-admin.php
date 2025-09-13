<?php
require_once __DIR__ . "/_base.php";
?>

<header>
    <div id="header-container">
        <a id="header-home-button" href="/"></a>
        <div id="admin-header-buttons">
            <?php if ($account_type->sales_report) : ?>
                <a href="/sales-report">
                    <div class="header-button" id="header-sales-button"></div>
                    <div class="header-button-text">Sales Report</div>
                </a>
            <?php endif; ?>
            <?php if ($account_type->manage_customer) : ?>
                <a href="/manage-customer">
                    <div class="header-button" id="header-customer-button"></div>
                    <div class="header-button-text">Manage Customer</div>
                </a>
            <?php endif; ?>
            <?php if ($account_type->manage_admin) : ?>
                <a href="/manage-admin">
                    <div class="header-button" id="header-admin-button"></div>
                    <div class="header-button-text">Manage Admin</div>
                </a>
            <?php endif; ?>
            <?php if ($account_type->manage_category) : ?>
                <a href="/manage-category">
                    <div class="header-button" id="header-category-button"></div>
                    <div class="header-button-text">Manage Category</div>
                </a>
            <?php endif; ?>
            <?php if ($account_type->manage_product) : ?>
                <a href="/manage-product">
                    <div class="header-button" id="header-product-button"></div>
                    <div class="header-button-text">Manage Product</div>
                </a>
            <?php endif; ?>
            <?php if ($account_type->manage_order) : ?>
                <a href="/manage-order">
                    <div class="header-button" id="header-order-button"></div>
                    <div class="header-button-text">Manage Order</div>
                </a>
            <?php endif; ?>
            <?php if ($account_type->manage_notice) : ?>
                <a href="/manage-notice">
                    <div class="header-button" id="header-edit-notice-button"></div>
                    <div class="header-button-text">Manage Notice</div>
                </a>
            <?php endif; ?>
            <?php if ($account_type->manage_voucher) : ?>
                <a href="/manage-voucher">
                    <div class="header-button" id="header-voucher-button"></div>
                    <div class="header-button-text">Manage Voucher</div>
                </a>
            <?php endif; ?>
        </div>
        <div class="header-button" id="header-account-button">
            <img id="header-account-image" src="<?= $account_verified && $account_obj->photo ? ("/uploads/account/" . $account_obj?->photo) : "/src/img/icon/pfp.png"; ?>" alt="Profile">
            <div id="header-account-dropdown">
                <a href="/account">Account</a>
                <a href="/device">Device</a>
                <div id="logout-button">Logout</div>
            </div>
        </div>
    </div>
</header>