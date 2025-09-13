<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

$return_value["errors"] = [
    "name" => null,
    "email" => null,
    "gender" => null,
    "password" => null,
    "confirm_password" => null
];

$name = obtain_post("name");
$email = obtain_post("email");
$gender = obtain_post("gender");
$password = obtain_post("password");
$confirm_password = obtain_post("confirm_password");

if (!isset($name)) {
    $return_value["success"] = false;
    $return_value["errors"]["name"] = "Name is required";
} else if (strlen($name) < 2) {
    $return_value["success"] = false;
    $return_value["errors"]["name"] = "Name cannot be shorter than 2 characters";
} else if (strlen($name) > 50) {
    $return_value["success"] = false;
    $return_value["errors"]["name"] = "Name cannot be longer than 50 characters";
}

if (!isset($email)) {
    $return_value["success"] = false;
    $return_value["errors"]["email"] = "Email is required";
} else if ($email != filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $return_value["success"] = false;
    $return_value["errors"]["email"] = "Invalid email format";
} else {
    $stm = $db->prepare("SELECT COUNT(*) FROM account WHERE email = ? AND is_verified = 1");
    $stm->execute([$email]);
    if ($stm->fetchColumn() !== 0) {
        $return_value["success"] = false;
        $return_value["errors"]["email"] = "Email already exists";
    } else if (strlen($email) > 80) {
        $return_value["success"] = false;
        $return_value["errors"]["email"] = "Email cannot be longer than 80 characters";
    }
}

if (!isset($gender)) {
    $return_value["success"] = false;
    $return_value["errors"]["gender"] = "Select a gender";
} else if (!in_array($gender, ["m", "f", "-"])) {
    $return_value["success"] = false;
    $return_value["errors"]["gender"] = "Invalid gender";
}

if (!isset($password)) {
    $return_value["success"] = false;
    $return_value["errors"]["password"] = "Password is required";
} else if (strlen($password) < 6) {
    $return_value["success"] = false;
    $return_value["errors"]["password"] = "Password must be at least 6 characters";
} else if (strlen($password) > 20) {
    $return_value["success"] = false;
    $return_value["errors"]["password"] = "Password cannot be longer than 20 characters";
}

if (!isset($confirm_password)) {
    $return_value["success"] = false;
    $return_value["errors"]["confirm_password"] = "Confirm password is required";
} else if ($password != $confirm_password) {
    $return_value["success"] = false;
    $return_value["errors"]["confirm_password"] = "Confirm password does not match";
}

if ($return_value["success"]) {
    // Delete any existing account with the same email
    $db->prepare("DELETE FROM session WHERE account_id = (SELECT id FROM account WHERE email = ?)")->execute([$email]);
    $db->prepare("DELETE FROM account WHERE email = ?")->execute([$email]);

    $stm = $db->prepare("INSERT INTO account 
    (name, password_hash, email, gender, is_verified, is_banned, wallet_balance, account_type_id) 
    VALUES (?, SHA1(?), ?, ?, 0, 0, 0, 'cus')");
    $stm->execute([$name, $password, $email, $gender]);
    $account_id = $db->lastInsertId();

    $pending_session = create_session($account_id);
    $return_value["session_id"] = $pending_session["session_id"];

    sessionVerification($email, $name, $pending_session["session_id"], $pending_session["otp"], true);

    $expire = 30 * 24 * 60 * 60; // 30 days
    setcookie("session_id", $pending_session["session_id"], time() + $expire, "/");
    setcookie("session_token", $pending_session["session_token"], time() + $expire, "/");
}

echo json_encode($return_value);
