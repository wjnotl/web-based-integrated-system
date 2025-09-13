<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_customer");

$return_value["errors"] = [
    "id" => null
];

$id = obtain_post("id");

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
    temp("toast_message", "Invalid ID");
} else if (!is_exist($id, "account", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID not found";
    temp("toast_message", "Invalid ID");
} else {
    $stm = $db->prepare("UPDATE account SET pending_delete_expire = NULL WHERE id = ?");
    $stm->execute([$id]);
}

echo json_encode($return_value);