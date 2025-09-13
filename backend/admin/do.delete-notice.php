<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_notice");

$return_value["errors"] = [
    "id" => null
];

$id = obtain_post("id");

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
    temp("toast_message", "Invalid ID");
} else if (!is_exist($id, "notice", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID not found";
    temp("toast_message", "Notice does not exist");
} else {
    $stm = $db->prepare("SELECT * FROM notice WHERE id = ?");
    $stm->execute([$id]);
    $notice = $stm->fetchObject();

    if (isset($notice->photo) && file_exists(__DIR__ . "/../../uploads/notice/" . $notice->photo)) {
        unlink(__DIR__ . "/../../uploads/notice/" . $notice->photo);
    }

    $db->prepare("DELETE FROM poll_vote WHERE notice_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM poll_option WHERE notice_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM notice WHERE id = ?")->execute([$id]);
    temp("toast_message", "Notice deleted successfully");
}

echo json_encode($return_value);
