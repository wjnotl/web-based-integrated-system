<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

$db->query("DELETE from change_email WHERE expire < NOW() - INTERVAL 1 DAY");

$return_value["errors"] = [
    "token" => null,
    "email" => null,
    "confirm_email" => null
];

$token = obtain_post("token");
$email = obtain_post("email");
$confirm_email = obtain_post("confirm_email");

if (!isset($token)) {
    $return_value["success"] = false;
    $return_value["errors"]["token"] = "Token is required";
    temp("toast_message", "Invalid token. Please check your email and try again");
} else {
    $stm = $db->prepare("SELECT * FROM change_email WHERE token = ?");
    $stm->execute([$token]);
    if ($stm->rowCount() === 1) {
        $change_email_obj = $stm->fetchObject();
        if (new DateTime($change_email_obj->expire) < new DateTime()) {
            $stm = $db->prepare("DELETE FROM change_email WHERE token = ?");
            $stm->execute([$token]);

            $return_value["success"] = false;
            $return_value["errors"]["token"] = "Token has expired";
            temp("toast_message", "This token has expired. Please request a new one");
        } else {
            $stm = $db->prepare("SELECT email, name FROM account WHERE id = ?");
            $stm->execute([$change_email_obj->account_id]);
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

if (!isset($email)) {
    $return_value["success"] = false;
    $return_value["errors"]["email"] = "Email is required";
} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $return_value["success"] = false;
    $return_value["errors"]["email"] = "Invalid email format";
} else if ($return_value["success"] && $account_obj->email === $email) {
    $return_value["success"] = false;
    $return_value["errors"]["email"] = "Email cannot be the same as current email";
}

if (!isset($confirm_email)) {
    $return_value["success"] = false;
    $return_value["errors"]["confirm_email"] = "Confirm email is required";
} else if ($email != $confirm_email) {
    $return_value["success"] = false;
    $return_value["errors"]["confirm_email"] = "Confirm email does not match";
}

if ($return_value["success"]) {
    $stm = $db->prepare("UPDATE account SET email = ? WHERE id = ?");
    $stm->execute([$email, $change_email_obj->account_id]);

    $stm = $db->prepare("DELETE FROM change_email WHERE token = ?");
    $stm->execute([$token]);

    $stm = $db->prepare("UPDATE session SET token = NULL WHERE account_id = ?");
    $stm->execute([$change_email_obj->account_id]);

    emailChanged($account_obj->email, $account_obj->name, $email);

    temp("toast_message", "Email has been changed successfully. Please login again");
}

echo json_encode($return_value);
