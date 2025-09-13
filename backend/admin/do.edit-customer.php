<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_customer");

$return_value["errors"] = [
    "id" => null,
    "email" => null
];

$id = obtain_post("id");
$email = obtain_post("email");
$banned = obtain_post("banned");

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
        if ($stm->fetchColumn() != $email && is_exist($email, "account", "email")) {
            $return_value["success"] = false;
            $return_value["errors"]["email"] = "Email already exists";
        }
    }
}

if ($return_value["success"]) {
    $stm = $db->prepare("UPDATE account SET email = ?, is_banned = ? WHERE id = ?");
    $stm->execute([$email, $banned ? 1 : 0, $id]);
}

echo json_encode($return_value);
