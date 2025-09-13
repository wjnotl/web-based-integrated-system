<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

$return_value["errors"] = [
    "product_id" => null,
    "remove" => null
];

$product_id = obtain_post("product_id");
$action = obtain_post("action", "remove");
$force_remove = obtain_post("force_remove");

if (!isset($product_id)) {
    $return_value["success"] = false;
    $return_value["errors"]["product_id"] = "Product not found. Please try again";
} else {
    $stm = $db->prepare("SELECT COUNT(*) FROM product WHERE id = ?");
    $stm->execute([$product_id]);
    if ($stm->fetchColumn() !== 1) {
        $return_value["success"] = false;
        $return_value["errors"]["product_id"] = "Product not found. Please try again";
    }
}

if ($return_value["success"]) {
    $stm = $db->prepare("SELECT COUNT(*) FROM favourite WHERE product_id = ? AND account_id = ?");
    $stm->execute([$product_id, $account_obj->id]);

    if ($action === "remove") {
        if ($stm->fetchColumn() === 0) {
            if (!isset($force_remove)) {
                $return_value["favourite"] = "removed";
            } else {
                $return_value["success"] = false;
                $return_value["errors"]["remove"] = "Product is not in favourite";
            }
        } else {
            $stm = $db->prepare("DELETE FROM favourite WHERE product_id = ? AND account_id = ?");
            $stm->execute([$product_id, $account_obj->id]);
            $return_value["favourite"] = "removed";
        }
    } else {
        if ($stm->fetchColumn() === 0) {
            $stm = $db->prepare("INSERT INTO favourite (product_id, account_id) VALUES (?, ?)");
            $stm->execute([$product_id, $account_obj->id]);
        }
        $return_value["favourite"] = "added";
    }
}

echo json_encode($return_value);
