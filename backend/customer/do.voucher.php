<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

$total_price = (float)(obtain_post("total_price") ?? "1000");

only_customer();

$db->query("DELETE v FROM voucher v
            JOIN voucher_template vt ON vt.id = v.voucher_template_id
            WHERE v.expiry_date < NOW()
            AND vt.expiry_date IS NOT NULL
            AND vt.expiry_date < NOW()
");

$stm = $db->prepare("SELECT v.id, v.expiry_date, vt.value
                        FROM voucher v
                        JOIN voucher_template vt ON v.voucher_template_id = vt.id
                        WHERE account_id = ? AND vt.value <= ? AND v.is_used = 0
                        ORDER BY vt.value DESC, v.expiry_date ASC");
$stm->execute([$account_obj->id, $total_price]);
$return_value["vouchers"] = $stm->fetchAll();


echo json_encode($return_value);
