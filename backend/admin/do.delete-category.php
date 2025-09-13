<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_category");

$return_value["errors"] = [
    "id" => null
];

$id = obtain_post("id");

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
    temp("toast_message", "Invalid ID");
} else if (!is_exist($id, "category", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID not found";
    temp("toast_message", "Category does not exist");
} else {
    $stm = $db->prepare("SELECT * FROM category WHERE id = ?");
    $stm->execute([$id]);
    $category = $stm->fetchObject();

    if (isset($category->photo) && file_exists(__DIR__ . "/../../uploads/category/" . $category->photo)) {
        unlink(__DIR__ . "/../../uploads/category/" . $category->photo);
    }

    $db->prepare("UPDATE order_item SET category_id = NULL WHERE category_id = ?")->execute([$id]);
    $db->prepare("UPDATE product SET category_id = NULL WHERE category_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM category WHERE id = ?")->execute([$id]);
    temp("toast_message", "Category deleted successfully");
}

echo json_encode($return_value);