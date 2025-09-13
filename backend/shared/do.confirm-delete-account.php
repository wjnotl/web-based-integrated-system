<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

$db->query("DELETE FROM delete_account WHERE expire < NOW() - INTERVAL 1 DAY");

$token = obtain_post("token");

$return_value["errors"] = [
    "token" => null
];

if (!isset($token)) {
    $return_value["success"] = false;
    $return_value["errors"]["token"] = "Token is required";
    temp("toast_message", "Invalid token. Please check your email and try again");
} else {
    $stm = $db->prepare("SELECT 
                            da.expire AS expire,
                            da.account_id AS account_id,
                            da.token AS token,
                            a.email AS email,
                            a.name AS name,
                            a.photo AS photo,
                            at.name AS account_type_name
                        FROM delete_account da
                        JOIN account a ON da.account_id = a.id
                        JOIN account_type at ON a.account_type_id = at.id
                        WHERE da.token = ?");
    $stm->execute([$token]);
    if ($stm->rowCount() === 1) {
        $delete_account_obj = $stm->fetchObject();
        if (new DateTime($delete_account_obj->expire) < new DateTime()) {
            $stm = $db->prepare("DELETE FROM delete_account WHERE token = ?");
            $stm->execute([$token]);

            $return_value["success"] = false;
            $return_value["errors"]["token"] = "Token has expired";
            temp("toast_message", "This token has expired. Please request a new one");
        } else {
            $stm = $db->prepare("DELETE FROM session WHERE account_id = ?");
            $stm->execute([$delete_account_obj->account_id]);

            $stm = $db->prepare("DELETE FROM delete_account WHERE token = ?");
            $stm->execute([$token]);

            if ($delete_account_obj->email === $default_admin_email) {
                $return_value["success"] = false;
                $return_value["errors"]["token"] = "Cannot delete default admin";
                temp("toast_message", "Cannot delete default admin");
            } else if ($delete_account_obj->account_type_name === "Customer") {
                $stm = $db->prepare("UPDATE account SET pending_delete_expire = NOW() + INTERVAL 7 DAY WHERE id = ?");
                $stm->execute([$delete_account_obj->account_id]);

                accountDeletion($delete_account_obj->email, $delete_account_obj->name);
                temp("toast_message", "Your account will be deleted after 7 days");
            } else {
                if (isset($delete_account_obj->photo) && file_exists(__DIR__ . "/../../uploads/account/" . $delete_account_obj->photo)) {
                    unlink(__DIR__ . "/../../uploads/account/" . $delete_account_obj->photo);
                }

                $db->prepare("DELETE FROM session WHERE account_id = ?")->execute([$delete_account_obj->account_id]);
                $db->prepare("DELETE FROM account WHERE id = ?")->execute([$delete_account_obj->account_id]);

                accountDeleted($delete_account_obj->email, $delete_account_obj->name);
                temp("toast_message", "Your account has been deleted");
            }
        }
    } else {
        $return_value["success"] = false;
        $return_value["errors"]["token"] = "Invalid token";
        temp("toast_message", "Invalid token. Please check your email or request a new one");
    }
}

echo json_encode($return_value);
