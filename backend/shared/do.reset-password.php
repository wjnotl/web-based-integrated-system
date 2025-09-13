<?php
require_once __DIR__ . "/../_base.php";

$db->query("DELETE FROM reset_password WHERE expire < NOW() - INTERVAL 1 DAY");

header("Access-Control-Allow-Methods: POST");

$return_value["errors"] = [
    "token" => null,
    "password" => null,
    "confirm_password" => null
];

$token = obtain_post("token");
$password = obtain_post("password");
$confirm_password = obtain_post("confirm_password");

if (!isset($token)) {
    $return_value["success"] = false;
    $return_value["errors"]["token"] = "Token is required";
    temp("toast_message", "Invalid token. Please check your email and try again");
} else {
    $stm = $db->prepare("SELECT * FROM reset_password WHERE token = ?");
    $stm->execute([$token]);
    if ($stm->rowCount() === 1) {
        $reset_password_obj = $stm->fetchObject();
        if (new DateTime($reset_password_obj->expire) < new DateTime()) {
            $stm = $db->prepare("DELETE FROM reset_password WHERE token = ?");
            $stm->execute([$token]);

            $return_value["success"] = false;
            $return_value["errors"]["token"] = "Token has expired";
            temp("toast_message", "This reset password link has expired. Please request a new one");
        } else {
            $stm = $db->prepare("SELECT email, name, password_hash FROM account WHERE id = ?");
            $stm->execute([$reset_password_obj->account_id]);
            if ($stm->rowCount() === 1) {
                $account_obj = $stm->fetchObject();
            } else {
                $return_value["success"] = false;
                $return_value["errors"]["token"] = "Invalid token";
                temp("toast_message", "Invalid token. Please check your email or request a new one");
            }
        }
    } else {
        $return_value["success"] = false;
        $return_value["errors"]["token"] = "Invalid token";
        temp("toast_message", "Invalid token. Please check your email or request a new one");
    }
}

if (!isset($password)) {
    $return_value["success"] = false;
    $return_value["errors"]["password"] = "Password is required";
} else if (strlen($password) < 6) {
    $return_value["success"] = false;
    $return_value["errors"]["password"] = "Password must be at least 8 characters";
} else if (strlen($password) > 20) {
    $return_value["success"] = false;
    $return_value["errors"]["password"] = "Password cannot be longer than 20 characters";
} else if ($return_value["success"] && $account_obj->password_hash === sha1($password)) {
    $return_value["success"] = false;
    $return_value["errors"]["password"] = "New password cannot be the same as current password";
}

if (!isset($confirm_password)) {
    $return_value["success"] = false;
    $return_value["errors"]["confirm_password"] = "Confirm password is required";
} else if ($password != $confirm_password) {
    $return_value["success"] = false;
    $return_value["errors"]["confirm_password"] = "Confirm password does not match";
}

if ($return_value["success"]) {
    $stm = $db->prepare("UPDATE account SET password_hash = SHA1(?) WHERE id = ?");
    $stm->execute([$password, $reset_password_obj->account_id]);

    $stm = $db->prepare("DELETE FROM reset_password WHERE token = ?");
    $stm->execute([$token]);

    $stm = $db->prepare("DELETE FROM session WHERE account_id = ?");
    $stm->execute([$reset_password_obj->account_id]);

    passwordChanged($account_obj->email, $account_obj->name);

    temp("toast_message", "Password has been reset successfully. Please login again");
}

echo json_encode($return_value);
