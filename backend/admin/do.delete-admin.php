<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_admin");

$return_value["errors"] = [
    "id" => null
];

$id = obtain_post("id");

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
} else {
    $stm = $db->prepare("SELECT a.* FROM account a JOIN account_type at ON a.account_type_id = at.id WHERE a.id = ? AND at.name <> 'Customer'");
    $stm->execute([$id]);
    if ($stm->rowCount() !== 1) {
        $return_value["success"] = false;
        $return_value["errors"]["id"] = "Admin not found";
    } else {
        $admin = $stm->fetchObject();
        if ($admin->email === $default_admin_email) {
            $return_value["success"] = false;
            $return_value["errors"]["id"] = "Cannot delete default admin";
        }
    }
}

if ($return_value["success"]) {
    if (isset($admin->photo) && file_exists(__DIR__ . "/../../uploads/account/" . $admin->photo)) {
        unlink(__DIR__ . "/../../uploads/account/" . $admin->photo);
    }

    $db->prepare("DELETE FROM session WHERE account_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM account WHERE id = ?")->execute([$id]);
    temp("toast_message", "Admin deleted successfully");

    accountDeleted($admin->email, $admin->name);
}

echo json_encode($return_value);
