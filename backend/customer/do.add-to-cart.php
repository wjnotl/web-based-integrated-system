<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

$return_value["errors"] = [
    "product_quantity" => null
];

$product_id = obtain_post("product_id");
$colour = obtain_post("product_colour");
$size = obtain_post("product_size");
$quantity = obtain_post("product_quantity");

if (!isset($product_id)) {
    $return_value["success"] = false;
    $return_value["errors"]["product_id"] = "Product ID is required";
}

if (!isset($colour)) {
    $return_value["success"] = false;
    $return_value["errors"]["colour"] = "Colour is required";
}

if (!isset($size)) {
    $return_value["success"] = false;
    $return_value["errors"]["size"] = "Size is required";
}

if (!isset($quantity)) {
    $return_value["success"] = false;
    $return_value["errors"]["product_quantity"] = "Please enter quantity";
} else if (filter_var($quantity, FILTER_VALIDATE_INT) === false) {
    $return_value["success"] = false;
    $return_value["errors"]["product_quantity"] = "Quantity must be an integer";
} else if ((int)$quantity < 1) {
    $return_value["success"] = false;
    $return_value["errors"]["product_quantity"] = "Quantity must be greater than 0";
} else {
    $stm = $db->prepare("SELECT * FROM product_variant WHERE colour = ? AND size = ? AND product_id = ?");
    $stm->execute([$colour, $size, $product_id]);
    if ($stm->rowCount() === 1) {
        $product_variant = $stm->fetchObject();
        $stock = $product_variant->stock;
        $return_value["stock"] = $stock;

        if ($stock === 0) {
            $return_value["success"] = false;
            $return_value["errors"]["product_quantity"] = "Out of stock";
        } else if ((int)$quantity > $stock) {
            $return_value["success"] = false;
            $return_value["errors"]["product_quantity"] = "Quantity exceeds stock";
        }
    } else {
        $return_value["success"] = false;
        $return_value["errors"]["product"] = "Product not found";
    }
}

if ($return_value["success"]) {
    $stm = $db->prepare("SELECT quantity FROM cart WHERE product_variant_id = ? AND account_id = ?");
    $stm->execute([$product_variant->id, $account_obj->id]);
    $existing_quantity = $stm->fetchColumn();

    if ($existing_quantity) {
        if ($existing_quantity + (int)$quantity > $stock) {
            $return_value["success"] = false;
            $return_value["errors"]["product_quantity"] = "You have some in your cart. This exceeds available stock";
        } else {
            $stm = $db->prepare("UPDATE cart SET quantity = quantity + ? WHERE product_variant_id = ? AND account_id = ?");
            $stm->execute([(int)$quantity, $product_variant->id, $account_obj->id]);
        }
    } else {
        $stm = $db->prepare("INSERT INTO cart (product_variant_id, account_id, quantity) VALUES (?, ?, ?)");
        $stm->execute([$product_variant->id, $account_obj->id, (int)$quantity]);
    }
}

echo json_encode($return_value);
