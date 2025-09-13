<?php
require_once __DIR__ . "/_base.php";

$request = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/");

$navigations = [
    "admin" => [
        "admin-home" => 3,
        "manage-customer" => 3,
        "edit-customer" => 3,
        "manage-admin" => 3,
        "edit-admin" => 3,
        "add-admin" => 3,
        "manage-category" => 3,
        "edit-category" => 3,
        "add-category" => 3,
        "manage-product" => 3,
        "edit-product" => 3,
        "add-product" => 3,
        "manage-order" => 3,
        "edit-order" => 3,
        "manage-notice" => 3,
        "edit-notice" => 3,
        "add-notice" => 3,
        "manage-voucher" => 3,
        "add-voucher" => 3,
        "sales-report" => 3,
    ],
    "customer" => [
        "home" => 0,
        "search" => 0,
        "favourite" => 1,
        "cart" => 1,
        "checkout" => 1,
        "payment" => 1,
        "notice" => 0,
        "signup" => 2,
        "product-detail" => 0,
        "voucher" => 1,
        "get-voucher" => 1,
        "wallet" => 1,
        "top-up" => 1,
        "order-history" => 1,
        "order-detail" => 0
    ],
    "shared" => [
        "about-us" => 0,
        "contact-us" => 0,
        "privacy-policy" => 0,
        "account" => 1,
        "device" => 1,
        "login" => 2,
        "session-verification" => 0,
        "forgot-password" => 0,
        "reset-password" => 0,
        "change-email" => 0,
        "delete-account" => 0
    ]
];

$role = $account_verified && $account_type?->name !== "Customer" ? "admin" : "customer";
if (empty($request)) {
    $request = $role === "admin" ? "admin-home" : "home";
}

$navigate_access = $navigations[$role][$request] ?? $navigations["shared"][$request] ?? 0;
$navigate_to = null;

if (isset($navigations[$role][$request]) && file_exists(__DIR__ . "/pages/$role/$request.php")) {
    $navigate_to = "$role/$request";
} else if (isset($navigations["shared"][$request]) && file_exists(__DIR__ . "/pages/shared/$request.php")) {
    $navigate_to = "shared/$request";
}

if ($navigate_access) {
    if ($request == "sales-report") {
        if ($account_type->sales_report == 0) {
            $navigate_to = null;
        }
    } else if (in_array($request, ["manage-customer", "edit-customer"])) {
        if ($account_type->manage_customer == 0) {
            $navigate_to = null;
        }
    } else if (in_array($request, ["manage-admin", "edit-admin", "add-admin"])) {
        if ($account_type->manage_admin == 0) {
            $navigate_to = null;
        }
    } else if (in_array($request, ["manage-category", "edit-category", "add-category"])) {
        if ($account_type->manage_category == 0) {
            $navigate_to = null;
        }
    } else if (in_array($request, ["manage-product", "edit-product", "add-product"])) {
        if ($account_type->manage_product == 0) {
            $navigate_to = null;
        }
    } else if (in_array($request, ["manage-order", "edit-order"])) {
        if ($account_type->manage_order == 0) {
            $navigate_to = null;
        }
    } else if (in_array($request, ["manage-notice", "edit-notice", "add-notice"])) {
        if ($account_type->manage_notice == 0) {
            $navigate_to = null;
        }
    } else if (in_array($request, ["manage-voucher", "add-voucher"])) {
        if ($account_type->manage_voucher == 0) {
            $navigate_to = null;
        }
    }
}

if ($navigate_to) {
    $allow_access = true;
    require __DIR__ . "/pages/$navigate_to.php";
    exit;
}

$navigate_to = "shared/404error";

http_response_code(404);
if (file_exists(__DIR__ . "/pages/$navigate_to.php")) {
    $allow_access = true;
    require __DIR__ . "/pages/$navigate_to.php";
    exit;
}

echo "404 Not Found";
