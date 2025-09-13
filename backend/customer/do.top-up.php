<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

$return_value["errors"] = [
    "amount" => null,
    "card_number" => null,
    "card_cvc" => null,
    "card_expiry" => null
];


$amount = obtain_post("amount");
$card_number = str_replace(" ", "", obtain_post("card_number", ""));
$card_cvc = obtain_post("card_cvc");
$card_expiry = obtain_post("card_expiry");

if (!isset($amount)) {
    $return_value["success"] = false;
    $return_value["errors"]["amount"] = "Amount is required";
} else if (!isRMFormat($amount)) {
    $return_value["success"] = false;
    $return_value["errors"]["amount"] = "Amount must be in RM format";
} else if ((float)$amount < 10) {
    $return_value["success"] = false;
    $return_value["errors"]["amount"] = "Amount must not be less than RM 10.00";
}

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

$card_token = "";

if ($return_value["success"]) {
    $token_result = generate_token_from_card($card_number, $card_cvc, $card_expiry);
    $card_token = $token_result["card_token"];
    $return_value["success"] = $token_result["success"];
    $return_value["errors"]["card_number"] = $token_result["errors"]["card_number"];
    $return_value["errors"]["card_cvc"] = $token_result["errors"]["card_cvc"];
    $return_value["errors"]["card_expiry"] = $token_result["errors"]["card_expiry"];
}

// insert transaction
$inserted = false;
if ($return_value["success"]) {
    do {
        $transaction_id = randomString(30);
    } while (!is_unique($transaction_id, "transaction", "id"));

    $stm = $db->prepare("INSERT INTO transaction (id, value, account_id)
                            VALUES (?, ?, ?)");
    $stm->execute([$transaction_id, $amount, $account_obj->id]);

    $inserted = true;
}

$charged = false;
if ($return_value["success"]) {
    $charge_result = charge_card((float)$amount, $card_token, "Transaction ID: $transaction_id");
    $charged = $charge_result["charged"];
    $return_value["success"] = $charge_result["success"];
    $return_value["errors"]["card_number"] = $charge_result["errors"]["card_number"];
    $return_value["errors"]["card_cvc"] = $charge_result["errors"]["card_cvc"];
    $return_value["errors"]["card_expiry"] = $charge_result["errors"]["card_expiry"];
}

// if charge success, update transaction + update account balance
if ($charged) {
    $stm = $db->prepare("UPDATE transaction SET detail = ? WHERE id = ?");
    $stm->execute(["Top Up: $transaction_id", $transaction_id]);

    $stm = $db->prepare("UPDATE account SET wallet_balance = wallet_balance + ? WHERE id = ?");
    $stm->execute([(float)$amount, $account_obj->id]);

    temp("toast_message", "Topped up RM " . toRMFormat($amount) . " successfully");
} else if ($inserted) {
    $stm = $db->prepare("DELETE FROM transaction WHERE id = ?");
    $stm->execute([$transaction_id]);
}

echo json_encode($return_value);
