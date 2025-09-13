<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_product");

$return_value["errors"] = [
    "id" => null,
    "new_variant_colour" => null,
    "new_variant_size" => null,
    "new_variant_stock" => null
];

$id = obtain_post("id");
$colour = obtain_post("new_variant_colour");
$size = obtain_post("new_variant_size");
$stock = obtain_post("new_variant_stock");

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
    temp("toast_message", "ID is required");
} else if (!is_exist($id, "product", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "Invalid ID";
    temp("toast_message", "Invalid ID");
} else {
    if (!isset($colour)) {
        $return_value["success"] = false;
        $return_value["errors"]["new_variant_colour"] = "Colour is required";
    } else if (strlen($colour) > 50) {
        $return_value["success"] = false;
        $return_value["errors"]["new_variant_colour"] = "Colour cannot be longer than 50 characters";
    }

    if (!isset($size)) {
        $return_value["success"] = false;
        $return_value["errors"]["new_variant_size"] = "Size is required";
    } else if (strlen($size) > 30) {
        $return_value["success"] = false;
        $return_value["errors"]["new_variant_size"] = "Size cannot be longer than 30 characters";
    }

    if (!isset($stock)) {
        $return_value["success"] = false;
        $return_value["errors"]["new_variant_stock"] = "Stock is required";
    } else if (filter_var($stock, FILTER_VALIDATE_INT) === false) {
        $return_value["success"] = false;
        $return_value["errors"]["new_variant_stock"] = "Stock must be an integer";
    } else if ((int)$stock < 0) {
        $return_value["success"] = false;
        $return_value["errors"]["new_variant_stock"] = "Stock cannot be negative";
    }

    if ($return_value["success"]) {
        $stm = $db->prepare("SELECT COUNT(*) FROM product_variant WHERE product_id = ? AND colour = ? AND size = ?");
        $stm->execute([$id, $colour, $size]);
        if ($stm->fetchColumn() !== 0) {
            $return_value["success"] = false;
            $return_value["errors"]["new_variant_colour"] = "Variant already exists";
            $return_value["errors"]["new_variant_size"] = "Variant already exists";
        } else {
            $stm = $db->prepare("INSERT INTO product_variant (product_id, colour, size, stock) VALUES (?, ?, ?, ?)");
            $stm->execute([$id, $colour, $size, $stock]);

            $stm = $db->prepare("SELECT * FROM product_variant WHERE product_id = ? ORDER BY colour");
            $stm->execute([$id]);
            $variants = $stm->fetchAll();

            $return_value["variants"] = $variants;
        }
    }
}

echo json_encode($return_value);