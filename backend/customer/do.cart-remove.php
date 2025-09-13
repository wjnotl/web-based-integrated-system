<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

$return_value["errors"] = [
    "product_variant" => null
];

$product_variant_id = explode(",", obtain_post("product_variant_id", ""));

if (!isset($product_variant_id)) {
    $return_value["success"] = false;
    $return_value["errors"]["product_variant"] = "Product variant ID is required";
} else {
    foreach ($product_variant_id as $id) {
        $stm = $db->prepare("SELECT * FROM cart WHERE product_variant_id = ? AND account_id = ?");
        $stm->execute([$id, $account_obj->id]);
        if ($stm->rowCount() === 1) {
            $stm = $db->prepare("DELETE FROM cart WHERE product_variant_id = ? AND account_id = ?");
            $stm->execute([$id, $account_obj->id]);
        } else {
            $return_value["success"] = false;
            $return_value["errors"]["product_variant_$id"] = "Product variant does not exist in cart";
        }
    }
}

echo json_encode($return_value);
