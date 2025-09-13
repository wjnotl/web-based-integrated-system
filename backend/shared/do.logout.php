<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_verified_account();

if (isset($_COOKIE['session_id']) && isset($_COOKIE['session_token'])) {
    $stm = $db->prepare("UPDATE session SET token = NULL WHERE id = ? AND token = ?");
    $stm->execute([$session_id, $session_token]);
    temp("toast_message", "Logged out successfully");
}

echo json_encode($return_value);
