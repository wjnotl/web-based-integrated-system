<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

$return_value["errors"] = [
    "name" => null,
    "contact_number" => null,
    "address" => null,
    "city" => null,
    "state" => null,
    "postal_code" => null,
    "wallet_balance" => null,
    "card_number" => null,
    "card_cvc" => null,
    "card_expiry" => null,
    "voucher_id" => null,
    "checkout_items" => null,
    "order_payment_pending" => null
];

$checkout_items = explode(",", (obtain_post("checkout_items") ?? ""));

if (empty(array_filter($checkout_items))) {
    $return_value["success"] = false;
    $return_value["errors"]["checkout_items"] = "No items selected";
    temp("toast_message", "No items selected");
} else {
    $total_price = 0;
    foreach ($checkout_items as $product_variant_id) {
        $stm = $db->prepare("SELECT c.quantity, pv.stock, p.price
                                FROM cart c
                                JOIN product_variant pv ON c.product_variant_id = pv.id
                                JOIN product p ON pv.product_id = p.id
                                WHERE c.product_variant_id = ? AND c.account_id = ?");
        $stm->execute([$product_variant_id, $account_obj->id]);
        if ($stm->rowCount() === 1) {
            $item = $stm->fetchObject();
            if ($item->quantity > $item->stock) {
                $return_value["success"] = false;
                $return_value["errors"]["checkout_items"] = "Stock not enough";
                temp("toast_message", "Stock not enough");
            }
            $total_price += round($item->price * $item->quantity, 2);
        } else {
            $return_value["success"] = false;
            $return_value["errors"]["checkout_items"] = "Item does not exist";
            temp("toast_message", "Item does not exist");
        }
    }

    if ($return_value["success"]) {
        $name = obtain_post("name");
        $contact_number = obtain_post("contact_number");
        $address = obtain_post("address");
        $city = obtain_post("city");
        $state = obtain_post("state");
        $postal_code = obtain_post("postal_code");
        $shipping_type = obtain_post("shipping_type");
        $voucher_id = obtain_post("voucher_id");

        if (!isset($name)) {
            $return_value["success"] = false;
            $return_value["errors"]["name"] = "Name is required";
        }

        if (!isset($contact_number)) {
            $return_value["success"] = false;
            $return_value["errors"]["contact_number"] = "Contact number is required";
        } else if (!preg_match("/^01\d-\d{7,8}$/", $contact_number)) {
            $return_value["success"] = false;
            $return_value["errors"]["contact_number"] = "Invalid contact number format";
        }

        if (!isset($address)) {
            $return_value["success"] = false;
            $return_value["errors"]["address"] = "Address is required";
        }

        if (!isset($city)) {
            $return_value["success"] = false;
            $return_value["errors"]["city"] = "City is required";
        }

        if (!isset($state)) {
            $return_value["success"] = false;
            $return_value["errors"]["state"] = "State is required";
        } else if (!in_array($state, ["Johor", "Kedah", "Kelantan", "Kuala Lumpur", "Labuan", "Melaka", "Negeri Sembilan", "Pahang", "Perak", "Perlis", "Pulau Pinang", "Putrajaya", "Sabah", "Sarawak", "Selangor", "Terengganu"])) {
            $return_value["success"] = false;
            $return_value["errors"]["state"] = "Invalid state";
        }

        if (!isset($postal_code)) {
            $return_value["success"] = false;
            $return_value["errors"]["postal_code"] = "Postal code is required";
        } else if (!preg_match("/^\d{5}$/", $postal_code)) {
            $return_value["success"] = false;
            $return_value["errors"]["postal_code"] = "Invalid postal code format";
        }

        $verified_voucher_id = null;
        $voucher_value = null;
        if (isset($voucher_id)) {
            $stm = $db->prepare("SELECT v.expiry_date, vt.value, v.is_used
                                    FROM voucher v
                                    JOIN voucher_template vt ON v.voucher_template_id = vt.id
                                    WHERE v.id = ? AND v.account_id = ?");
            $stm->execute([$voucher_id, $account_obj->id]);
            if ($stm->rowCount() === 1) {
                $voucher = $stm->fetchObject();
                if (new DateTime($voucher->expiry_date) < new DateTime()) {
                    $return_value["success"] = false;
                    $return_value["errors"]["voucher_id"] = "Voucher has expired";
                } else if ($total_price < $voucher->value) {
                    $return_value["success"] = false;
                    $return_value["errors"]["voucher_id"] = "Voucher value exceeds total price";
                } else if ($voucher->is_used) {
                    $return_value["success"] = false;
                    $return_value["errors"]["voucher_id"] = "Voucher has already been used";
                } else {
                    $total_price -= $voucher->value;
                    $voucher_value = $voucher->value;
                    $verified_voucher_id = $voucher_id;
                }
            } else {
                $return_value["success"] = false;
                $return_value["errors"]["voucher_id"] = "Voucher does not exist";
            }
        }

        if ($shipping_type === "Standard") {
            $total_price += 8;
        } else if ($shipping_type === "Express") {
            $total_price += 15;
        }
    }

    // insert order
    if ($return_value["success"]) {
        do {
            $order_id = randomString(30);
        } while (!is_unique($order_id, "orders", "id"));

        $db->beginTransaction();

        $stm = $db->prepare("INSERT INTO orders (id, status, name, contact_number, address, city, state, postal_code, shipping_type, voucher_id, voucher_value, is_processing, total_price, expired_at, account_id)
                VALUES (?, 'Unpaid', ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ADDTIME(NOW(), '00:30:00'), ?)");
        $stm->execute([$order_id, $name, $contact_number, $address, $city, $state, $postal_code, $shipping_type, $verified_voucher_id, $voucher_value, $total_price, $account_obj->id]);

        // update voucher
        if ($verified_voucher_id) {
            $stm = $db->prepare("UPDATE voucher SET is_used = 1 WHERE id = ? AND account_id = ?");
            $stm->execute([$verified_voucher_id, $account_obj->id]);
        }

        foreach ($checkout_items as $product_variant_id) {
            $stm = $db->prepare("SELECT p.name, pv.colour, pv.size, p.price, c.quantity, p.id, pv.stock, p.category_id
                    FROM cart c
                    JOIN product_variant pv ON c.product_variant_id = pv.id
                    JOIN product p ON pv.product_id = p.id
                    WHERE c.product_variant_id = ? AND c.account_id = ?");
            $stm->execute([$product_variant_id, $account_obj->id]);

            if ($stm->rowCount() === 1) {
                $item = $stm->fetchObject();
                if ($item->quantity > $item->stock) {
                    $return_value["success"] = false;
                    $return_value["errors"]["checkout_items"] = "Stock not enough";
                    temp("toast_message", "Stock not enough");
                } else {
                    $stm = $db->prepare("UPDATE product_variant SET stock = stock - ? WHERE id = ?");
                    $stm->execute([$item->quantity, $product_variant_id]);

                    $stm = $db->prepare("INSERT INTO order_item (order_id, product_variant_id, name, colour, size, price, quantity, product_id, category_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stm->execute([$order_id, $product_variant_id, $item->name, $item->colour, $item->size, $item->price, $item->quantity, $item->id, $item->category_id]);
                }
            } else {
                $return_value["success"] = false;
                $return_value["errors"]["checkout_items"] = "Item does not exist";
                temp("toast_message", "Item does not exist");
            }
        }

        $return_value["order_id"] = $order_id;

        if ($return_value["success"]) {
            $db->commit();
        } else {
            $db->rollBack();
        }
    }
}

echo json_encode($return_value);
