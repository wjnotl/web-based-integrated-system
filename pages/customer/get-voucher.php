<?php
require_once __DIR__ . "/../_base.php";

$token = obtain_get("token");

if (!isset($token)) {
    temp("toast_message", "No token provided");
    redirect("/voucher");
} else if (!is_exist($token, "voucher_template", "token")) {
    temp("toast_message", "Invalid voucher");
    redirect("/voucher");
} else {
    $stm = $db->prepare("SELECT * FROM voucher_template WHERE token = ? AND for_signup = 0");
    $stm->execute([$token]);
    $voucher_template = $stm->fetchObject();

    if (isset($voucher_template->expiry_date) && $voucher_template->expiry_date < date("Y-m-d")) {
        temp("toast_message", "Voucher expired");
        redirect("/voucher");
    }

    if (isset($voucher_template->claim_limit) && $voucher_template->total_claimed >= $voucher_template->claim_limit) {
        temp("toast_message", "Voucher claim limit exceeded");
        redirect("/voucher");
    }

    $stm = $db->prepare("SELECT COUNT(*) FROM voucher WHERE voucher_template_id = ? AND account_id = ?");
    $stm->execute([$voucher_template->id, $account_obj->id]);
    if ($stm->fetchColumn() > 0) {
        temp("toast_message", "You already have this voucher");
        redirect("/voucher");
    }

    $stm = $db->prepare("SELECT COUNT(*) FROM voucher WHERE voucher_template_id = ? AND account_email = ?");
    $stm->execute([$voucher_template->id, $account_obj->email]);
    if ($stm->fetchColumn() > 0) {
        temp("toast_message", "You already have this voucher");
        redirect("/voucher");
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
        $id = randomString(13);
    } while (!is_unique($id, "voucher", "id"));

    $stm = $db->prepare("INSERT INTO voucher (id, expiry_date, is_used, account_id, voucher_template_id) VALUES (?, ?, 0, ?, ?)");
    $stm->execute([$id, $expiry_date->format("Y-m-d"), $account_obj->id, $voucher_template->id]);

    $stm = $db->prepare("UPDATE voucher_template SET total_claimed = total_claimed + 1 WHERE id = ?");
    $stm->execute([$voucher_template->id]);

    temp("toast_message", "Voucher claimed successfully");
    redirect("/voucher");
}
