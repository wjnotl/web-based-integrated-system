<?php
require_once __DIR__ . "/../_base.php";

$title = "Session Verification - Superme Malaysia";
require_once __DIR__ . "/../_head.php";

$db->query("DELETE from session WHERE expire < NOW() - INTERVAL 1 DAY");

$id = obtain_get("id");
$otp = obtain_get("otp");

$content = "cross";

if (isset($id)) {
    $stm = $db->prepare("SELECT * FROM session WHERE id = ? AND is_verified = 0");
    $stm->execute([$id]);
    $session_obj = $stm->fetchObject();
    if ($session_obj) {
        if (new DateTime($session_obj->expire) < new DateTime()) {
            $content = "expired";
            $stm = $db->prepare("DELETE FROM session WHERE id = ?");
            $stm->execute([$id]);
        } else if (!isset($otp)) {
            $content = "link";
        } else if ($session_obj->otp === $otp) {
            // New account
            $stm = $db->prepare("SELECT * FROM account WHERE id = ?");
            $stm->execute([$session_obj->account_id]);
            $account_obj = $stm->fetchObject();
            if (!$account_obj->is_verified) {
                $stm = $db->prepare("UPDATE account SET is_verified = 1 WHERE id = ?");
                $stm->execute([$session_obj->account_id]);

                // sign up voucher
                $stm = $db->query("SELECT * FROM voucher_template WHERE for_signup = 1");
                if ($stm->rowCount() > 0) {
                    foreach ($stm->fetchAll() as $voucher_template) {
                        $stm = $db->prepare("SELECT COUNT(*) FROM voucher WHERE voucher_template_id = ? AND account_email = ?");
                        $stm->execute([$voucher_template->id, $account_obj->email]);
                        if ($stm->fetchColumn() > 0) {
                            continue;
                        }

                        if (isset($voucher_template->expiry_date) && $voucher_template->expiry_date < date("Y-m-d")) {
                            continue;
                        }

                        if (isset($voucher_template->claim_limit) && $voucher_template->total_claimed >= $voucher_template->claim_limit) {
                            continue;
                        }

                        $expiry_date = null;

                        if (isset($voucher_template->expiry_date)) {
                            $expiry_date = new DateTime($voucher_template->expiry_date);
                        }

                        if (isset($voucher_template->valid_days)) {
                            $valid_days_expiry = new DateTime(); // today
                            $valid_days_expiry->add(new DateInterval("P{$voucher_template->valid_days}D"));

                            if (isset($expiry_date)) {
                                $expiry_date = ($expiry_date < $valid_days_expiry) ? $expiry_date : $valid_days_expiry;
                            } else {
                                $expiry_date = $valid_days_expiry;
                            }
                        }

                        do {
                            $voucher_id = randomString(13);
                        } while (!is_unique($voucher_id, "voucher", "id"));

                        $stm = $db->prepare("INSERT INTO voucher (id, expiry_date, is_used, account_id, voucher_template_id) VALUES (?, ?, 0, ?, ?)");
                        $stm->execute([$voucher_id, $expiry_date->format("Y-m-d"), $account_obj->id, $voucher_template->id]);

                        $stm = $db->prepare("UPDATE voucher_template SET total_claimed = total_claimed + 1 WHERE id = ?");
                        $stm->execute([$voucher_template->id]);
                    }
                }
            }

            $stm = $db->prepare("UPDATE session SET is_verified = 1, expire = NOW() + INTERVAL 30 DAY WHERE id = ?");
            $stm->execute([$id]);

            $address = get_address();
            $device_info = get_device_info();

            if ($session_obj->address === $address && $session_obj->device_os === $device_info["os"] && $session_obj->device_type === $device_info["device"] && $session_obj->browser === $device_info["browser"]) {
                $content = "check";
            } else {
                $content = "check home";
            }

            $content = "check";
        } else {
            $content = "incorrect";
        }
    }
}
?>

<div id="session-verification" class="container container-center <?= $content ?>">
    <div class="image"></div>
    <div class="status-title"></div>
    <div class="title"></div>

    <div id="otp-container">
        <input type="text" maxlength="1">
        <input type="text" maxlength="1">
        <input type="text" maxlength="1">
        <input type="text" maxlength="1">
        <input type="text" maxlength="1">
        <input type="text" maxlength="1">
    </div>

    <?php if ($content === "check") {
        $next_href = temp("next") ?? "/";
        echo '<a href="' . $next_href . '" id="session-button" class="link-button">' . ($next_href === "/" ? "Back to Homepage" : "Continue") . '</a>';
    } else if ($content === "check home") {
        echo '<a href="/" id="session-button" class="link-button">Back to Homepage</a>';
    } else if ($content === "incorrect") {
        echo '<button id="try-again-button" class="button-yellow">Try Again</button>';
    } else if ($content !== "link") {
        echo '<a href="/" id="session-button" class="link-button">Back to Homepage</a>';
    } ?>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>