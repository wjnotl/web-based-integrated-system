<?php
require_once __DIR__ . "/../_base.php";

$current_device = get_device_info();
$current_address = get_address();

$stm = $db->prepare("SELECT id, device_type, device_os, browser, address, last_login_time
                    FROM session WHERE account_id = ? AND is_verified = 1 AND id != ?");
$stm->execute([$account_obj->id, $session_id]);
$sessions = $stm->fetchAll();

$title = "My Devices - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div id="device-container" class="container">
    <div class="title">My Devices</div>

    <div class="device-container">
        <div class="device-title">Current Device</div>
        <div class="device-group">
            <div class="device-image <?= get_device_classname($current_device["device"]); ?>"></div>
            <div class="device-info">
                <div class="device-name"><?= $current_device["os"] . " · " . $current_device["browser"]; ?></div>
                <div class="device-detail"><?= htmlspecialchars($current_address); ?></div>
            </div>
        </div>
    </div>

    <div id="other-devices" class="device-container">
        <div class="device-title">Other Devices</div>
        <div id="no-device">No other devices found</div>

        <?php foreach ($sessions as $session):  ?>
            <div class="device-group">
                <div class="device-image <?= get_device_classname($session->device_type); ?>"></div>
                <div class="device-info">
                    <div class="device-name"><?= $session->device_os . " · " . $session->browser; ?></div>
                    <div class="device-detail"><?= htmlspecialchars($session->address) . " · " . timeAgo($session->last_login_time); ?></div>
                </div>
                <div class="device-remove" data-session-id="<?= $session->id; ?>"></div>
            </div>
        <?php endforeach; ?>

        <button id="logout-all-devices" class="button-red">Logout All Known Devices</button>
    </div>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>