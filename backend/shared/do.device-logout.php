<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_verified_account();

$session_id = obtain_post("session_id");
$all = obtain_post("all");

if ($all) {
    $stm = $db->prepare("DELETE FROM session WHERE account_id = ?");
    $stm->execute([$account_obj->id]);
} else if (isset($session_id)) {
    $stm = $db->prepare("DELETE FROM session WHERE id = ? AND account_id = ?");
    $stm->execute([$session_id, $account_obj->id]);
} else {
    $return_value["success"] = false;
}


echo json_encode($return_value);
