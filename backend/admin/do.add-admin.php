<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_admin");

$return_value["errors"] = [
    "admin_type" => null,
    "name" => null,
    "email" => null,
    "gender" => null
];

$admin_type = obtain_post("admin_type");
$name = obtain_post("name");
$email = obtain_post("email");
$gender = obtain_post("gender");

if (!isset($admin_type)) {
    $return_value["success"] = false;
    $return_value["errors"]["admin_type"] = "Admin type is required";
} else if (!is_exist($admin_type, "account_type", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["admin_type"] = "Admin type not found";
}

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

if ($return_value["success"]) {
    // Delete any existing account with the same email
    $db->prepare("DELETE FROM session WHERE account_id = (SELECT id FROM account WHERE email = ?)")->execute([$email]);
    $db->prepare("DELETE FROM account WHERE email = ?")->execute([$email]);

    $password = randomString(15);

    $stm = $db->prepare("INSERT INTO account 
    (name, password_hash, email, gender, is_verified, is_banned, wallet_balance, account_type_id) 
    VALUES (?, SHA1(?), ?, ?, 1, 0, 0, ?)");
    $stm->execute([$name, $password, $email, $gender, $admin_type]);

    addAdmin($name, $email, $password);
    temp("toast_message", "Admin added successfully");
}

echo json_encode($return_value);
