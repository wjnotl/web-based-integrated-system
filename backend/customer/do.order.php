<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

$status = obtain_post("status");
$page = (int)obtain_post("page", "1");

if ($status === "unpaid") {
    $continue_query = "AND o.status = 'Unpaid'";
} else if ($status === "preparing") {
    $continue_query = "AND o.status = 'Preparing'";
} else if ($status === "in_transit") {
    $continue_query = "AND o.status = 'In Transit'";
} else if ($status === "delivered") {
    $continue_query = "AND o.status = 'Delivered'";
} else if ($status === "canceled") {
    $continue_query = "AND o.status = 'Canceled'";
} else {
    $continue_query = "";
}

$select_query = "SELECT 
            o.id,
            o.status,
            CASE 
                WHEN o.status = 'Unpaid' THEN 'unpaid'
                WHEN o.status = 'Preparing' THEN 'preparing' 
                WHEN o.status = 'In Transit' THEN 'in-transit' 
                WHEN o.status = 'Delivered' THEN 'delivered' 
                WHEN o.status = 'Canceled' THEN 'canceled' 
                ELSE '' 
            END AS status_code,
            o.creation_time,
            SUM(oi.price * oi.quantity) + (CASE WHEN o.shipping_type = 'Standard' THEN 8 ELSE 15 END) - COALESCE(o.voucher_value, 0) AS total_price
        FROM orders o
        JOIN order_item oi ON oi.order_id = o.id
        WHERE o.account_id = ?";

$select_count_query = "SELECT COUNT(*) FROM orders o WHERE o.account_id = ?";

$select_group_by_query = "GROUP BY o.id, o.status, o.creation_time, o.shipping_type, o.voucher_value, o.account_id";
$count_group_by_query = "GROUP BY o.account_id";
$order_query = "ORDER BY o.creation_time DESC";
$params = [$account_obj->id];

require_once __DIR__ . "/../../lib/SimplePager.php";
$pager = new SimplePager(
    "$select_query $continue_query $select_group_by_query $order_query",
    $params,
    "$select_count_query $continue_query $count_group_by_query",
    $params,
    15,
    $page
);

$return_value["last"] = $pager->page >= $pager->page_count;
$return_value["result"] = $pager->result;

echo json_encode($return_value);
