<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

order_expire();

$return_value["errors"] = [
    "wallet_balance" => null,
    "card_number" => null,
    "card_cvc" => null,
    "card_expiry" => null,
    "order" => null
];

$db->beginTransaction();

$order_id = obtain_post("id");
$payment_option = obtain_post("payment_option");
$card_number = str_replace(" ", "", obtain_post("card_number", ""));
$card_cvc = obtain_post("card_cvc");
$card_expiry = obtain_post("card_expiry");

$stm = $db->prepare("SELECT * FROM orders WHERE id = ? AND account_id = ? FOR UPDATE");
$stm->execute([$order_id, $account_obj->id]);
$order = $stm->fetchObject();

if ($stm->rowCount() !== 1) {
    $return_value["success"] = false;
    $return_value["errors"]["order"] = "Requested order does not exist";
    temp("toast_message", "Requested order does not exist");
} else if ($order->is_processing == 1) {
    $return_value["success"] = false;
    $return_value["errors"]["order"] = "Requested order is processing";
    temp("toast_message", "Requested order is processing. Please try again later");
} else {
    $stm = $db->prepare("UPDATE orders SET is_processing = 1 WHERE id = ?");
    $stm->execute([$order_id]);

    $total_price = $order->total_price;

    if (!isset($shipping_type) || !in_array($shipping_type, ["Standard", "Express"])) {
        $shipping_type = "Standard";
    }

    if (!isset($payment_option) || !in_array($payment_option, ["Wallet", "Card"])) {
        $payment_option = "Wallet";
    }

    $card_token = "";
    $card_brand = null;
    $card_last4 = null;

    if ($payment_option === "Card") {
        if (!isset($card_number)) {
            $return_value["success"] = false;
            $return_value["errors"]["card_number"] = "Card number is required";
        } else if (!preg_match("/^\d{13,19}$/", $card_number)) {
            $return_value["success"] = false;
            $return_value["errors"]["card_number"] = "Card number is invalid";
        }

        if (!isset($card_cvc)) {
            $return_value["success"] = false;
            $return_value["errors"]["card_cvc"] = "Card CVC is required";
        } else if (!preg_match("/^\d{3,4}$/", $card_cvc)) {
            $return_value["success"] = false;
            $return_value["errors"]["card_cvc"] = "Card CVC is invalid";
        }

        if (!isset($card_expiry)) {
            $return_value["success"] = false;
            $return_value["errors"]["card_expiry"] = "Card expiry is required";
        } else if (!preg_match("/^\d{2}\/\d{2}$/", $card_expiry)) {
            $return_value["success"] = false;
            $return_value["errors"]["card_expiry"] = "Card expiry is invalid";
        }

        if ($return_value["success"]) {
            $token_result = generate_token_from_card($card_number, $card_cvc, $card_expiry);
            $card_token = $token_result["card_token"];
            $card_brand = $token_result["card_brand"];
            $card_last4 = $token_result["card_last4"];
            
            $return_value["success"] = $token_result["success"];
            $return_value["errors"]["card_number"] = $token_result["errors"]["card_number"];
            $return_value["errors"]["card_cvc"] = $token_result["errors"]["card_cvc"];
            $return_value["errors"]["card_expiry"] = $token_result["errors"]["card_expiry"];
        }
    }

    // charge
    $charged = false;
    if ($return_value["success"]) {
        if ($payment_option === "Wallet") {
            $stm = $db->prepare("SELECT wallet_balance FROM account WHERE id = ?");
            $stm->execute([$account_obj->id]);
            if ($stm->rowCount() === 1) {
                $account_balance = $stm->fetchColumn();
                if ($account_balance < $total_price) {
                    $return_value["success"] = false;
                    $return_value["errors"]["wallet_balance"] = "Insufficient balance";
                } else {
                    $stm = $db->prepare("UPDATE account SET wallet_balance = wallet_balance - ? WHERE id = ?");
                    $stm->execute([$total_price, $account_obj->id]);

                    do {
                        $transaction_id = randomString(30);
                    } while (!is_unique($transaction_id, "transaction", "id"));

                    $stm = $db->prepare("INSERT INTO transaction (id, value, detail, order_id, account_id)
                                VALUES (?, ?, ?, ?, ?)");
                    $stm->execute([$transaction_id, -$total_price, "Order ID: $order_id", $order_id, $account_obj->id]);

                    $charged = true;
                }
            } else {
                $return_value["success"] = false;
                $return_value["errors"]["wallet_balance"] = "Unknown error, please try again";
            }
        } else if ($payment_option === "Card") {
            $charge_result = charge_card($total_price, $card_token, "Order ID: $order_id");
            $charged = $charge_result["charged"];
            $return_value["success"] = $charge_result["success"];
            $return_value["errors"]["card_number"] = $charge_result["errors"]["card_number"];
            $return_value["errors"]["card_cvc"] = $charge_result["errors"]["card_cvc"];
            $return_value["errors"]["card_expiry"] = $charge_result["errors"]["card_expiry"];
        }
    }

    // if charge success, update payment info + update order status + update/delete cart + update voucher used + insert transaction
    if ($charged) {
        $stm = $db->prepare("SELECT * FROM order_item WHERE order_id = ?");
        $stm->execute([$order_id]);

        foreach ($stm->fetchAll() as $order_item) {
            $db->prepare("UPDATE cart SET quantity = quantity - ? WHERE account_id = ? AND product_variant_id = ?")->execute([$order_item->quantity, $account_obj->id, $order_item->product_variant_id]);
            $db->prepare("DELETE FROM cart WHERE account_id = ? AND product_variant_id = ? AND quantity <= 0")->execute([$account_obj->id, $order_item->product_variant_id]);
        }

        $stm = $db->prepare("UPDATE orders SET status = 'Preparing', is_processing = 0, payment_option = ?, payment_card_last4 = ?, payment_card_brand = ?, expired_at = NULL WHERE id = ?");
        $stm->execute([$payment_option, $card_last4, $card_brand, $order_id]);

        orderConfirmed($account_obj->email, $account_obj->name, $order_id);

        temp("toast_message", "Your order is confirmed");
    }
}

if ($return_value["success"]) {
    $db->commit();
} else {
    $db->rollBack();
}

echo json_encode($return_value);
