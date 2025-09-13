<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

$return_value["errors"] = [
    "product_id" => null,
    "reviewer_id" => null
];

$product_id = obtain_post("product_id");
$reviewer_id = obtain_post("reviewer_id");

if (!isset($product_id) || !is_exist($product_id, "product", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["product_id"] = "Unknown error, try refreshing the page";
} else if (!isset($reviewer_id) || !is_exist($reviewer_id, "account", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["reviewer_id"] = "Unknown error, try refreshing the page";
} else {
    $action = obtain_post("action", "remove");

    if ($action === "remove") {
        $stm = $db->prepare("DELETE FROM like_review WHERE reviewer_id = ? AND product_id = ? AND account_id = ?");
        $stm->execute([$reviewer_id, $product_id, $account_obj->id]);
    } else {
        $stm = $db->prepare("SELECT COUNT(*) FROM like_review WHERE reviewer_id = ? AND product_id = ? AND account_id = ?");
        $stm->execute([$reviewer_id, $product_id, $account_obj->id]);
        if ($stm->fetchColumn() === 0) {
            $stm = $db->prepare("INSERT INTO like_review (reviewer_id, product_id, account_id) VALUES (?, ?, ?)");
            $stm->execute([$reviewer_id, $product_id, $account_obj->id]);
        }
    }
}

echo json_encode($return_value);
