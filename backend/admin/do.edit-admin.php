<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_admin");

$return_value["errors"] = [
    "id" => null,
    "email" => null,
    "admin_type" => null
];

$id = obtain_post("id");
$email = obtain_post("email");
$admin_type = obtain_post("admin_type");

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
    temp("toast_message", "Invalid ID");
} else if (!is_exist($id, "account", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID not found";
    temp("toast_message", "Invalid ID");
} else {
    if (!isset($email)) {
        $return_value["success"] = false;
        $return_value["errors"]["email"] = "Email is required";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $return_value["success"] = false;
        $return_value["errors"]["email"] = "Invalid email format";
    } else {
        $stm = $db->prepare("SELECT email FROM account WHERE id = ?");
        $stm->execute([$id]);
        $current_email = $stm->fetchColumn();
        if ($current_email === $default_admin_email) {
            $return_value["success"] = false;
            $return_value["errors"]["email"] = "Cannot change default admin email";
        } else if ($current_email != $email && is_exist($email, "account", "email")) {
            $return_value["success"] = false;
            $return_value["errors"]["email"] = "Email already exists";
        }
    }

    if (!isset($admin_type)) {
        $return_value["success"] = false;
        $return_value["errors"]["admin_type"] = "Admin type is required";
    } else if (!is_exist($admin_type, "account_type", "id")) {
        $return_value["success"] = false;
        $return_value["errors"]["admin_type"] = "Admin type not found";
    } else if (!isset($current_email) && $current_email === $default_admin_email) {
        $return_value["success"] = false;
        $return_value["errors"]["admin_type"] = "Cannot change default admin type";
    }
}

if ($return_value["success"]) {
    $stm = $db->prepare("UPDATE account SET email = ?, account_type_id = ? WHERE id = ?");
    $stm->execute([$email, $admin_type, $id]);
}

echo json_encode($return_value);
