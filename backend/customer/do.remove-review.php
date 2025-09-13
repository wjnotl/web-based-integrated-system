<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

$return_value["errors"] = [
    "product_id" => null
];

$product_id = obtain_post("product_id");

if (!isset($product_id) || !is_exist($product_id, "product", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["product_id"] = "Unknown error, try refreshing the page";
} else {
    $stm = $db->prepare("SELECT COUNT(*) FROM review WHERE product_id = ? AND account_id = ?");
    $stm->execute([$product_id, $account_obj->id]);
    if ($stm->fetchColumn() === 0) {
        $return_value["success"] = false;
        $return_value["errors"]["product_id"] = "Unknown error, try refreshing the page";
    } else {
        $stm = $db->prepare("DELETE FROM review WHERE product_id = ? AND account_id = ?");
        $stm->execute([$product_id, $account_obj->id]);

        $stm = $db->prepare("DELETE FROM like_review WHERE product_id = ? AND reviewer_id = ?");
        $stm->execute([$product_id, $account_obj->id]);

        temp("toast_message", "Review deleted");
    }
}

echo json_encode($return_value);
