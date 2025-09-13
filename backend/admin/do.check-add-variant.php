<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_product");

$return_value["errors"] = [
    "new_variant_colour" => null,
    "new_variant_size" => null,
    "new_variant_stock" => null
];

$colour = obtain_post("new_variant_colour");
$size = obtain_post("new_variant_size");
$stock = obtain_post("new_variant_stock");

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

echo json_encode($return_value);
