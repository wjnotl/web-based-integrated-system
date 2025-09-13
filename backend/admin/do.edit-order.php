<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_order");

$return_value["errors"] = [
    "id" => null,
    "status" => null
];

$id = obtain_post("id");
$status = obtain_post("status");

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
    temp("toast_message", "Invalid ID");
} else if (!is_exist($id, "orders", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID not found";
    temp("toast_message", "Invalid ID");
}

if (!isset($status)) {
    $return_value["success"] = false;
    $return_value["errors"]["status"] = "Status is required";
} else if (!in_array($status, ["Preparing", "In Transit", "Delivered", "Canceled"])) {
    $return_value["success"] = false;
    $return_value["errors"]["status"] = "Invalid status";
}

if ($return_value["success"]) {
    $db->beginTransaction();

    $stm = $db->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
    $stm->execute([$id]);
    $order = $stm->fetchObject();

    if ($order->is_processing == 1) {
        $return_value["success"] = false;
        $return_value["errors"]["id"] = "Requested order is processing. Please try again later";
    } else if ($order->status === "Canceled") {
        $return_value["success"] = false;
        $return_value["errors"]["id"] = "Requested order has been canceled. This order no longer able to change status";
    } else {
        $stm = $db->prepare("UPDATE orders SET is_processing = 1 WHERE id = ?");
        $stm->execute([$id]);

        if ($status === "Canceled") {
            // update voucher
            if (isset($order->voucher_id) && isset($order->account_id)) {
                $stm = $db->prepare("UPDATE voucher SET is_used = 0 WHERE account_id = ? AND id = ?");
                $stm->execute([$order->account_id, $order->voucher_id]);
            }

            // update stock
            $stm = $db->prepare("UPDATE product_variant pv
                        JOIN order_item oi ON pv.id = oi.product_variant_id
                        SET pv.stock = pv.stock + oi.quantity
                        WHERE oi.order_id = ?");
            $stm->execute([$id]);

            // refund to wallet
            $stm = $db->prepare("UPDATE account a SET a.wallet_balance = a.wallet_balance + ? WHERE a.id = ?");
            $stm->execute([$order->total_price, $order->account_id]);

            // add transaction
            do {
                $transaction_id = randomString(30);
            } while (!is_unique($transaction_id, "transaction", "id"));

            $stm = $db->prepare("INSERT INTO transaction (id, value, detail, order_id, account_id) VALUES (?, ?, ?, ?, ?)");
            $stm->execute([$transaction_id, $order->total_price, "Order ID: $id Refunded", $id, $order->account_id]);
        }

        $stm = $db->prepare("UPDATE orders SET status = ?, is_processing = 0, expired_at = NULL WHERE id = ?");
        $stm->execute([$status, $id]);
    }

    if ($return_value["success"]) {
        $db->commit();
    } else {
        $db->rollBack();
    }
}

echo json_encode($return_value);
