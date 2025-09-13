<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_verified_account();

$db->query("DELETE from change_email WHERE expire < NOW() - INTERVAL 1 DAY");

if ($account_obj->email === $default_admin_email) {
    $return_value["success"] = false;
} else {
    do {
        $token = randomString(150);
    } while (!is_unique($token, "change_email", "token"));

    $stm = $db->prepare("INSERT INTO change_email (token, account_id, expire) VALUES (?, ?, ADDTIME(NOW(), '00:05:00'))");
    $stm->execute([$token, $account_obj->id]);

    changeEmail($account_obj->email, $account_obj->name, $token);
}

echo json_encode($return_value);
