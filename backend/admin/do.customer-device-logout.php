<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_customer");
only_admin("manage_admin");

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
    $stm = $db->prepare("SELECT COUNT(*) FROM account a JOIN account_type t ON a.account_type_id = t.id WHERE a.id = ? AND t.name = 'Customer'");
    $stm->execute([$id]);
    if ($stm->fetchColumn() !== 1) {
        $return_value["success"] = false;
        $return_value["errors"]["id"] = "Customer not found";
    } else {
        $stm = $db->prepare("DELETE FROM session WHERE account_id = ?");
        $stm->execute([$id]);
    }
}

echo json_encode($return_value);