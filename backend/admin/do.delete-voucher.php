<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_voucher");

$return_value["errors"] = [
    "id" => null
];

$id = obtain_post("id");

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
    temp("toast_message", "Invalid ID");
} else if (!is_exist($id, "voucher_template", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID not found";
    temp("toast_message", "Voucher does not exist");
} else {

    $db->prepare("UPDATE orders SET voucher_id = NULL WHERE voucher_id IN (SELECT id FROM voucher WHERE voucher_template_id = ?)")->execute([$id]);
    $db->prepare("DELETE FROM voucher WHERE voucher_template_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM voucher_template WHERE id = ?")->execute([$id]);
    temp("toast_message", "Voucher deleted successfully");
}

echo json_encode($return_value);
