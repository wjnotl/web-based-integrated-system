<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

$return_value["errors"] = [
    "own_comment" => null,
    "own_rating" => null,
    "product_id" => null
];

$product_id = obtain_post("product_id");
if (!isset($product_id) || !is_exist($product_id, "product", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["product_id"] = "Unknown error, try refreshing the page";
} else {
    $stm = $db->prepare("SELECT COUNT(*) FROM order_item oi JOIN orders o ON oi.order_id = o.id 
        WHERE o.account_id = ? AND o.status = 'Delivered' AND oi.product_id = ?");
    $stm->execute([$account_obj->id, $product_id]);
    if ($stm->fetchColumn() === 0) {
        $return_value["success"] = false;
        $return_value["errors"]["product_id"] = "You don't have a complete purchase of this product yet";
    } else {
        $own_comment = obtain_post("own_comment");
        $own_rating = obtain_post("own_rating");

        if (!isset($own_comment)) {
            $return_value["success"] = false;
            $return_value["errors"]["own_comment"] = "Comment is required";
        } else if (strlen($own_comment) > 200) {
            $return_value["success"] = false;
            $return_value["errors"]["own_comment"] = "Comment cannot be longer than 200 characters";
        }

        if (!isset($own_rating)) {
            $return_value["success"] = false;
            $return_value["errors"]["own_rating"] = "Rating is required";
        } else if (!in_array($own_rating, ["5", "4", "3", "2", "1"])) {
            $return_value["success"] = false;
            $return_value["errors"]["own_rating"] = "Invalid rating";
        }

        if ($return_value["success"]) {
            $stm = $db->prepare("SELECT COUNT(*) FROM review WHERE account_id = ? AND product_id = ?");
            $stm->execute([$account_obj->id, $product_id]);
            if ($stm->fetchColumn() === 0) {
                $stm = $db->prepare("INSERT INTO review (account_id, product_id, content, rating) VALUES (?, ?, ?, ?)");
                $stm->execute([$account_obj->id, $product_id, $own_comment, $own_rating]);

                temp("toast_message", "Review sent");
            } else {
                $return_value["success"] = false;
                $return_value["errors"]["product_id"] = "Unknown error, try refreshing the page";
            }
        }
    }
}

echo json_encode($return_value);
