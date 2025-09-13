<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_product");

$return_value["errors"] = [
    "id" => null
];

$id = obtain_post("id");

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
} else if (!is_exist($id, "product_variant", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "Invalid ID";
} else {
    
    $db->prepare("DELETE FROM cart WHERE product_variant_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM product_variant WHERE id = ?")->execute([$id]);
}

echo json_encode($return_value);