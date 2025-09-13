<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_customer();

$time = obtain_post("time");
$page = (int)obtain_post("page", "1");

if ($time === "today") {
    $continue_query = "AND (creation_time BETWEEN CURDATE() AND CURDATE() + INTERVAL 1 DAY)";
} else if ($time === "yesterday") {
    $continue_query = "AND (creation_time BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE())";
} else if ($time === "this-week") {
    $continue_query = "AND (creation_time BETWEEN CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY AND CURDATE() + INTERVAL 1 DAY)";
} else if ($time === "this-month") {
    $continue_query = "AND (creation_time BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE() + INTERVAL 1 DAY)";
} else if ($time === "last-month") {
    $continue_query = "AND (creation_time BETWEEN DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01') AND LAST_DAY(CURDATE() - INTERVAL 1 MONTH) + INTERVAL 1 DAY)";
} else {
    $continue_query = "";
}

$select_query = "SELECT * FROM transaction WHERE account_id = ? AND detail IS NOT NULL";
$select_count_query = "SELECT COUNT(*) FROM transaction WHERE account_id = ? AND detail IS NOT NULL";
$order_query = "ORDER BY creation_time DESC";
$params = [$account_obj->id];

require_once __DIR__ . "/../../lib/SimplePager.php";
$pager = new SimplePager(
    "$select_query $continue_query $order_query",
    $params,
    "$select_count_query $continue_query",
    $params,
    15,
    $page
);

$return_value["last"] = $pager->page >= $pager->page_count;
$return_value["result"] = $pager->result;


echo json_encode($return_value);
