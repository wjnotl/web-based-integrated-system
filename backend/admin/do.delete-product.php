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
    temp("toast_message", "Invalid ID");
} else if (!is_exist($id, "product", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID not found";
    temp("toast_message", "Product does not exist");
} else {
    $stm = $db->prepare("SELECT * FROM product WHERE id = ?");
    $stm->execute([$id]);
    $product = $stm->fetchObject();

    if (isset($product->photo)) {
        foreach (preg_split("/\r\n|\n|\r/", $product->photo) as $image) {
            if (file_exists(__DIR__ . "/../../uploads/product/" . $image)) {
                unlink(__DIR__ . "/../../uploads/product/" . $image);
            }
        }
    }

    $db->prepare("DELETE FROM product_keyword WHERE product_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM cart WHERE product_variant_id IN (SELECT id FROM product_variant WHERE product_id = ?)")->execute([$id]);
    $db->prepare("DELETE FROM product_variant WHERE product_id = ?")->execute([$id]);
    $db->prepare("UPDATE order_item SET product_id = NULL WHERE product_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM favourite WHERE product_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM review WHERE product_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM like_review WHERE product_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM product WHERE id = ?")->execute([$id]);
    temp("toast_message", "Product deleted successfully");
}

echo json_encode($return_value);
