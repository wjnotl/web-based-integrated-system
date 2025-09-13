<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

$db->query("DELETE FROM reset_password WHERE expire < NOW() - INTERVAL 1 DAY");

$return_value["errors"] = [
    "email" => null
];

$email = obtain_post("email");

if (!isset($email)) {
    $return_value["success"] = false;
    $return_value["errors"]["email"] = "Email is required";
} else if ($email != filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $return_value["success"] = false;
    $return_value["errors"]["email"] = "Invalid email format";
} else {
    $stm = $db->prepare("SELECT * FROM account WHERE email = ?");
    $stm->execute([$email]);
    if ($stm->rowCount() === 1) {
        $account_obj = $stm->fetchObject();
        if ($account_obj->is_banned) {
            $return_value["success"] = false;
            $return_value["errors"]["email"] = "Email is banned";
        } else if ($account_obj->is_verified === 0) {
            $return_value["success"] = false;
            $return_value["errors"]["email"] = "Email is not registered";
        }
    } else {
        $return_value["success"] = false;
        $return_value["errors"]["email"] = "Email is not registered";
    }
}

if ($return_value["success"]) {
    do {
        $token = randomString(150);
    } while (!is_unique($token, "reset_password", "token"));

    $stm = $db->prepare("INSERT INTO reset_password (token, expire, account_id) VALUES (?, ADDTIME(NOW(), '00:05:00'), ?)");
    $stm->execute([$token, $account_obj->id]);

    resetPassword($email, $account_obj->name, $token);
    temp("toast_message", "Check your email for the reset link");
}

echo json_encode($return_value);