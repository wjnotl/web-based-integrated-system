<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_verified_account();

$return_value["errors"] = [
    "current_password" => null,
    "new_password" => null,
    "confirm_password" => null
];

$current_password = obtain_post("current_password");
$new_password = obtain_post("new_password");
$confirm_password = obtain_post("confirm_password");

if (!isset($current_password)) {
    $return_value["success"] = false;
    $return_value["errors"]["current_password"] = "Current password is required";
} else if (sha1($current_password) !== $account_obj->password_hash) {
    $return_value["success"] = false;
    $return_value["errors"]["current_password"] = "Current password is incorrect";
}

if (!isset($new_password)) {
    $return_value["success"] = false;
    $return_value["errors"]["new_password"] = "New password is required";
} else if (strlen($new_password) < 6) {
    $return_value["success"] = false;
    $return_value["errors"]["new_password"] = "New password must be at least 6 characters";
} else if (strlen($new_password) > 20) {
    $return_value["success"] = false;
    $return_value["errors"]["new_password"] = "New password cannot be longer than 20 characters";
} else if (sha1($new_password) === $account_obj->password_hash) {
    $return_value["success"] = false;
    $return_value["errors"]["new_password"] = "New password cannot be the same as current password";
}

if (!isset($confirm_password)) {
    $return_value["success"] = false;
    $return_value["errors"]["confirm_password"] = "Confirm password is required";
} else if ($new_password != $confirm_password) {
    $return_value["success"] = false;
    $return_value["errors"]["confirm_password"] = "Confirm password does not match";
}

if ($return_value["success"]) {
    $stm = $db->prepare("UPDATE account SET password_hash = SHA1(?) WHERE id = ?");
    $stm->execute([$new_password, $account_obj->id]);

    $stm = $db->prepare("UPDATE session SET token = NULL WHERE account_id = ?");
    $stm->execute([$account_obj->id]);

    passwordChanged($account_obj->email, $account_obj->name);

    $return_value["success"] = true;
    temp("toast_message", "Password has been changed successfully. Please login again");
}


echo json_encode($return_value);
