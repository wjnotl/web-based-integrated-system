<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

order_expire();

$return_value["errors"] = [
    "order_id" => null
];

$order_id = obtain_post("order_id");

$db->beginTransaction();

$stm = $db->prepare("SELECT * FROM orders WHERE id = ? AND account_id = ? AND (status = 'Preparing' OR status = 'Unpaid') FOR UPDATE");
$stm->execute([$order_id, $account_obj->id]);
$order = $stm->fetchObject();

if ($stm->rowCount() !== 1) {
    $return_value["success"] = false;
    $return_value["errors"]["order_id"] = "Requested order does not exist";
} else if ($order->is_processing == 1) {
    $return_value["success"] = false;
    $return_value["errors"]["order_id"] = "Requested order is processing. Please try again later";
} else {
    $stm = $db->prepare("UPDATE orders SET is_processing = 1 WHERE id = ?");
    $stm->execute([$order_id]);

    // update voucher
    if (isset($order->voucher_id)) {
        $stm = $db->prepare("UPDATE voucher SET is_used = 0 WHERE account_id = ? AND id = ?");
        $stm->execute([$account_obj->id, $order->voucher_id]);
    }

    // update stock
    $stm = $db->prepare("UPDATE product_variant pv
                        JOIN order_item oi ON pv.id = oi.product_variant_id
                        SET pv.stock = pv.stock + oi.quantity
                        WHERE oi.order_id = ?");
    $stm->execute([$order_id]);

    if ($order->status === "Preparing") {
        // refund to wallet
        $stm = $db->prepare("UPDATE account a SET a.wallet_balance = a.wallet_balance + ? WHERE a.id = ?");
        $stm->execute([$order->total_price, $account_obj->id]);

        // add transaction
        do {
            $transaction_id = randomString(30);
        } while (!is_unique($transaction_id, "transaction", "id"));

        $stm = $db->prepare("INSERT INTO transaction (id, value, detail, order_id, account_id) VALUES (?, ?, ?, ?, ?)");
        $stm->execute([$transaction_id, $order->total_price, "Order ID: $order_id Refunded", $order_id, $account_obj->id]);
    }

    $stm = $db->prepare("UPDATE orders SET status = 'Canceled', is_processing = 0, expired_at = NULL WHERE id = ?");
    $stm->execute([$order_id]);
}

if ($return_value["success"]) {
    $db->commit();
} else {
    $db->rollBack();
}

echo json_encode($return_value);
