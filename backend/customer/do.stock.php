<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

$product_id = obtain_post("product_id");
$colour = obtain_post("colour");
$size = obtain_post("size");

if (!isset($colour)) {
    $return_value["success"] = false;
} else if (!isset($size)) {
    $return_value["success"] = false;
} else {
    $stm = $db->prepare("SELECT stock FROM product_variant WHERE colour = ? AND size = ? AND product_id = ?");
    $stm->execute([$colour, $size, $product_id]);
    $stock = $stm->fetchColumn();
    if ($stock !== false) {
        $return_value["stock"] = $stock;
    } else {
        $return_value["success"] = false;
    }
}

echo json_encode($return_value);
