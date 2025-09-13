<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_product");

$id = obtain_post("id");
$stock = obtain_post("stock");

$return_value["errors"] = [
    "id" => null,
    ("variant_stock_" . $id) => null
];

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
} else if (!is_exist($id, "product_variant", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "Invalid ID";
} else {
    if (!isset($stock)) {
        $return_value["success"] = false;
        $return_value["errors"]["variant_stock_" . $id] = "Stock is required";
    } else if (filter_var($stock, FILTER_VALIDATE_INT) === false) {
        $return_value["success"] = false;
        $return_value["errors"]["variant_stock_" . $id] = "Stock must be an integer";
    } else if ((int)$stock < 0) {
        $return_value["success"] = false;
        $return_value["errors"]["variant_stock_" . $id] = "Stock cannot be negative";
    }

    if ($return_value["success"]) {
        $stm = $db->prepare("UPDATE product_variant SET stock = ? WHERE id = ?");
        $stm->execute([$stock, $id]);
    }
}

echo json_encode($return_value);
