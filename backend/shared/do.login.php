<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

$return_value["errors"] = [
    "email" => null,
    "password" => null
];

$email = obtain_post("email");
$password = obtain_post("password");

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
        $account = $stm->fetchObject();
        if ($account->is_banned) {
            $return_value["success"] = false;
            $return_value["errors"]["email"] = "Email is banned";
        } else if ($account->is_verified === 0) {
            $return_value["success"] = false;
            $return_value["errors"]["email"] = "Email is not verified. Please check your email for verification link";
        }
    } else {
        $return_value["success"] = false;
        $return_value["errors"]["email"] = "Email is not registered";
    }
}

if (!isset($password)) {
    $return_value["success"] = false;
    $return_value["errors"]["password"] = "Password is required";
}

if ($return_value["success"]) {
    $stm = $db->prepare("SELECT id, name FROM account WHERE email = ? AND password_hash = SHA1(?)");
    $stm->execute([$email, $password]);

    if ($stm->rowCount() === 1) {
        $account_obj = $stm->fetchObject();
        $session = get_session($account_obj->id);
        $session_id = null;
        $session_token = null;
        if ($session) {
            do {
                $session_token = randomString(150);
            } while (!is_unique($session_token, "session", "token"));

            $stm = $db->prepare("UPDATE session SET last_login_time = NOW(), expire = NOW() + INTERVAL 30 DAY, token = ? WHERE id = ?");
            $stm->execute([$session_token, $session->id]);

            $session_id = $session->id;
            $session_token = $session_token;
        } else {
            $pending_session = create_session($account_obj->id);
            $session_id = $pending_session["session_id"];
            $session_token = $pending_session["session_token"];

            $return_value["verify"] = true;
            $return_value["session_id"] = $pending_session["session_id"];
            sessionVerification($email, $account_obj->name, $pending_session["session_id"], $pending_session["otp"], false);
        }

        $stm = $db->prepare("UPDATE account SET pending_delete_expire = NULL WHERE id = ?");
        $stm->execute([$account_obj->id]);

        $expire = 30 * 24 * 60 * 60; // 30 days
        setcookie("session_id", $session_id, time() + $expire, "/");
        setcookie("session_token", $session_token, time() + $expire, "/");
    } else {
        $return_value["success"] = false;
        $return_value["errors"]["password"] = "Password is incorrect";
    }
}

echo json_encode($return_value);
