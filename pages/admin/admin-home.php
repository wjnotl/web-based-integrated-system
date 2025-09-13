<?php
require_once realpath(__DIR__ . "/../_base.php");

$past_months = [];
$orders = [];
$customers = [];
$date = new DateTime(); // now

$date->modify('-7 months'); // go 7 months back

for ($i = 0; $i < 8; $i++) {
    $start_date = $date->format('Y-m-01'); // first day of the month
    $end_date = $date->format('Y-m-t'); // last day of the month

    $stm = $db->prepare("
        SELECT COUNT(*)
        FROM orders
        WHERE status = 'delivered'
        AND DATE(creation_time) BETWEEN ? AND ?
    ");
    $stm->execute([$start_date, $end_date]);
    $orders[] = $stm->fetchColumn();

    $stm = $db->prepare("
        SELECT COUNT(*)
        FROM account a
        JOIN account_type at ON a.account_type_id = at.id
        WHERE DATE(a.creation_time) BETWEEN ? AND ?
        AND at.name = 'Customer'
    ");
    $stm->execute([$start_date, $end_date]);
    $customers[] = $stm->fetchColumn();

    $past_months[] = $date->format('M Y');
    $date->modify('+1 month'); // move forward 1 month
}

$devices = ["Computer", "Mobile Phone", "Tablet"];
$devices_count = [];

foreach ($devices as $device) {
    $stm = $db->prepare("
        SELECT COUNT(*)
        FROM session
        WHERE device_type = ?
    ");
    $stm->execute([$device]);
    $devices_count[] = $stm->fetchColumn();
}

$statuses = ["Preparing", "In Transit", "Delivered"];
$statuses_count = [];

foreach ($statuses as $status) {
    $stm = $db->prepare("
        SELECT COUNT(*)
        FROM orders
        WHERE status = ?
    ");
    $stm->execute([$status]);
    $statuses_count[] = $stm->fetchColumn();
}

$past_months = implode(',', $past_months);
$customers = implode(',', $customers);
$orders = implode(',', $orders);
$devices = implode(',', $devices);
$devices_count = implode(',', $devices_count);
$statuses = implode(',', $statuses);
$statuses_count = implode(',', $statuses_count);

$title = "Admin Home - Superme Malaysia";
require_once realpath(__DIR__ . "/../_head.php");
?>

<div id="home-container">
    <div class="admin-home-container container">
        <div class="title">New Customers (Past 8 months)</div>
        <canvas id="new-customers" width="710" height="400" data-customers="<?= $customers ?>" data-months="<?= $past_months ?>"></canvas>
    </div>

    <div class="admin-home-container container">
        <div class="title">Login Devices</div>
        <canvas id="login-devices" data-devices="<?= $devices ?>" data-devices-count="<?= $devices_count ?>"></canvas>
    </div>

    <div class="admin-home-container container">
        <div class="title">Order Frequency (Past 8 months)</div>
        <canvas id="order-frequency" width="710" height="400" data-orders="<?= $orders ?>" data-months="<?= $past_months ?>"></canvas>
    </div>

    <div class="admin-home-container container">
        <div class="title">Order Status</div>
        <canvas id="order-status" data-statuses="<?= $statuses ?>" data-statuses-count="<?= $statuses_count ?>"></canvas>
    </div>
    
</div>

<?php
require_once realpath(__DIR__ . "/../_foot.php");
?>