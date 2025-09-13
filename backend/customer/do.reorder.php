<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

$return_value["errors"] = [
    "order_id" => null,
    "no_product" => null
];

$order_id = obtain_post("order_id");

$stm = $db->prepare("SELECT COUNT(*) FROM orders WHERE id = ? AND account_id = ?");
$stm->execute([$order_id, $account_obj->id]);
if ($stm->fetchColumn() !== 1) {
    $return_value["success"] = false;
    $return_value["errors"]["order_id"] = "Unknown error, try refreshing the page";
} else {
    $stm = $db->prepare("SELECT 
                                oi.product_variant_id,
                                oi.quantity,
                                (CASE 
                                    WHEN oi.product_id IS NULL THEN 0
                                    ELSE 1  
                                END) AS is_product_exist,
                                (CASE 
                                    WHEN c.product_variant_id IS NULL THEN 0
                                    ELSE 1
                                END) AS is_in_cart
                            FROM order_item oi
                            JOIN orders o ON oi.order_id = o.id
                            LEFT JOIN cart c ON (oi.product_variant_id = c.product_variant_id AND o.account_id = c.account_id)
                            WHERE o.id = ? AND o.account_id = ?");
    $stm->execute([$order_id, $account_obj->id]);
    if ($stm->rowCount() === 0) {
        $return_value["success"] = false;
        $return_value["errors"]["no_product"] = "No product found";
    } else {
        $products = $stm->fetchAll();

        $existing_products = [];
        foreach ($products as $product) {
            if ($product->is_product_exist === 1) {
                $existing_products[] = $product->product_variant_id;
                if ($product->is_in_cart === 1) {
                    $stm = $db->prepare("UPDATE cart SET quantity = ? WHERE product_variant_id = ? AND account_id = ?");
                    $stm->execute([$product->quantity, $product->product_variant_id, $account_obj->id]);
                } else {
                    $stm = $db->prepare("INSERT INTO cart (product_variant_id, quantity, account_id) VALUES (?, ?, ?)");
                    $stm->execute([$product->product_variant_id, $product->quantity, $account_obj->id]);
                }
            }
        }

        if (count($existing_products) === 0) {
            $return_value["success"] = false;
            $return_value["errors"]["no_product"] = "No product found";
        } else {
            temp("reorder_products", $existing_products);
            if (count($existing_products) !== count($products)) {
                temp("toast_message", "Some items are not available");
            }
        }
    }
}

echo json_encode($return_value);
