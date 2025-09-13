<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

$return_value["errors"] = [
    "notice_id" => null,
    "option_id" => null
];

$notice_id = obtain_post("notice_id");
$option_id = obtain_post("option_id");

if (!isset($notice_id) || !is_exist($notice_id, "notice", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["notice_id"] = "Notice ID is required";
} else {
    $stm = $db->prepare("DELETE FROM poll_vote WHERE notice_id = ? AND account_id = ?");
    $stm->execute([$notice_id, $account_obj->id]);

    if (isset($option_id)) {
        $stm = $db->prepare("INSERT INTO poll_vote (notice_id, account_id, poll_option_id) VALUES (?, ?, ?)");
        $stm->execute([$notice_id, $account_obj->id, $option_id]);
    }
}

echo json_encode($return_value);
