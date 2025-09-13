<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

$product_variant_id = obtain_post("product_variant_id");
$quantity = obtain_post("quantity");

if (!isset($product_variant_id)) {
    $return_value["success"] = false;
    $return_value["errors"] = [];
    $return_value["errors"]["product_variant"] = "Product variant ID is required";
} else {
    $return_value["errors"] = [
        ("quantity_" . $product_variant_id) => null
    ];

    if (!isset($quantity)) {
        $return_value["success"] = false;
        $return_value["errors"]["quantity_" . $product_variant_id] = "Please enter quantity";
    } else if (filter_var($quantity, FILTER_VALIDATE_INT) === false) {
        $return_value["success"] = false;
        $return_value["errors"]["quantity_" . $product_variant_id] = "Quantity must be an integer";
    } else if ((int)$quantity < 0) {
        $return_value["success"] = false;
        $return_value["errors"]["quantity_" . $product_variant_id] = "Quantity cannot be negative";
    } else {
        $stm = $db->prepare("SELECT * FROM product_variant WHERE id = ?");
        $stm->execute([$product_variant_id]);

        if ($stm->rowCount() === 1) {
            $product_variant = $stm->fetchObject();
            $stock = $product_variant->stock;

            if ($stock === 0) {
                $return_value["errors"]["quantity_" . $product_variant_id] = "Out of stock";
            } else if ((int)$quantity > $stock) {
                $return_value["errors"]["quantity_" . $product_variant_id] = "Not enough stock, only " . $stock . " available";
            }
        } else {
            $return_value["success"] = false;
            $return_value["errors"]["product_variant"] = "Product variant not found";
        }
    }
}

if ($return_value["success"]) {
    $stm = $db->prepare("UPDATE cart SET quantity = ? WHERE product_variant_id = ? AND account_id = ?");
    $stm->execute([(int)$quantity, $product_variant_id, $account_obj->id]);
}

echo json_encode($return_value);
