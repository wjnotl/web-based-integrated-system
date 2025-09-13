<?php
require_once __DIR__ . "/../_base.php";

$db->query("DELETE FROM delete_account WHERE expire < NOW() - INTERVAL 1 DAY");

header("Access-Control-Allow-Methods: POST");

only_verified_account();

if ($account_obj->email === $default_admin_email) {
    $return_value["success"] = false;
} else {
    do {
        $token = randomString(150);
    } while (!is_unique($token, "delete_account", "token"));

    $stm = $db->prepare("INSERT INTO delete_account (account_id, expire, token) VALUES (?, ADDTIME(NOW(), '00:05:00'), ?)");
    $stm->execute([$account_obj->id, $token]);

    requestDeleteAccount($account_obj->email, $account_obj->name, $token);
}

echo json_encode($return_value);
