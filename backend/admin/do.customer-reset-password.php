<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_customer");

$return_value["errors"] = [
    "id" => null
];

$id = obtain_post("id");

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
    temp("toast_message", "Invalid ID");
} else if (!is_exist($id, "account", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID not found";
    temp("toast_message", "Invalid ID");
} else {
    $stm = $db->prepare("SELECT a.email FROM account a JOIN account_type t ON a.account_type_id = t.id WHERE a.id = ? AND t.name = 'Customer'");
    $stm->execute([$id]);
    if ($stm->rowCount() !== 1) {
        $return_value["success"] = false;
        $return_value["errors"]["id"] = "Admin not found";
    } else {
        $email = $stm->fetchColumn();

        do {
            $token = randomString(150);
        } while (!is_unique($token, "reset_password", "token"));

        $stm = $db->prepare("INSERT INTO reset_password (token, expire, account_id) VALUES (?, ADDTIME(NOW(), '00:05:00'), ?)");
        $stm->execute([$token, $id]);

        resetPassword($email, $id, $token);
    }
}

echo json_encode($return_value);
