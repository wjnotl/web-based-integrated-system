<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

$return_value["errors"] = [
    "product_id" => null
];

$product_id = obtain_post("product_id");

if (isset($product_id)) {
    $star = obtain_post("star");
    $page = (int)obtain_post("page", "1");
    $account_id = obtain_post("account_id");

    if ($star === "5") {
        $continue_query = "AND r.rating = 5";
    } else if ($star === "4") {
        $continue_query = "AND r.rating = 4";
    } else if ($star === "3") {
        $continue_query = "AND r.rating = 3";
    } else if ($star === "2") {
        $continue_query = "AND r.rating = 2";
    } else if ($star === "1") {
        $continue_query = "AND r.rating = 1";
    } else {
        $continue_query = "";
    }

    if (isset($account_id)) {
        if ($account_verified) {
            $query = "SELECT
                a.name,
                a.photo,
                r.account_id,
                r.content,
                r.rating,
                r.creation_time,
                COUNT(DISTINCT lr.account_id) AS likes,
                MAX(
                    CASE
                        WHEN lr.account_id = ? THEN 1
                        ELSE 0
                    END
                ) AS liked_by_me,
                CASE
                    WHEN r.account_id = ? THEN 1
                    ELSE 2
                END AS my_review
            FROM review r
            JOIN account a ON r.account_id = a.id
            LEFT JOIN like_review lr ON (r.account_id = lr.reviewer_id AND r.product_id = lr.product_id)
            WHERE r.product_id = ?";

            $params = [$account_id, $account_id, $product_id];
        } else {
            $query = "SELECT
                a.name,
                a.photo,
                r.account_id,
                r.content,
                r.rating,
                r.creation_time,
                COUNT(DISTINCT lr.account_id) AS likes,
                MAX(0) AS liked_by_me,
                CASE
                    WHEN r.account_id = ? THEN 1
                    ELSE 2
                END AS my_review
            FROM review r
            JOIN account a ON r.account_id = a.id
            LEFT JOIN like_review lr ON (r.account_id = lr.reviewer_id AND r.product_id = lr.product_id)
            WHERE r.product_id = ?";

            $params = [$account_id, $product_id];
        }

        $order_query = "ORDER BY my_review, likes DESC, r.creation_time DESC";
    } else {
        $query = "SELECT
            a.name,
            a.photo,
            r.account_id,
            r.content,
            r.rating,
            r.creation_time,
            COUNT(DISTINCT lr.account_id) AS likes,
            MAX(0) AS liked_by_me
        FROM review r
        JOIN account a ON r.account_id = a.id
        LEFT JOIN like_review lr ON (r.account_id = lr.reviewer_id AND r.product_id = lr.product_id)
        WHERE r.product_id = ?";

        $params = [$product_id];

        $order_query = "ORDER BY likes DESC, r.creation_time DESC";
    }

    $group_by_query = "GROUP BY r.account_id, r.content, r.rating, r.creation_time, a.name, a.photo";

    $count_query = "SELECT COUNT(*) FROM review r WHERE r.product_id = ?";
    $count_params = [$product_id];

    require_once __DIR__ . "/../../lib/SimplePager.php";
    $pager = new SimplePager(
        "$query $continue_query $group_by_query $order_query",
        $params,
        "$count_query $continue_query",
        $count_params,
        15,
        $page
    );

    $return_value["last"] = $pager->page >= $pager->page_count;
    $return_value["result"] = $pager->result;
} else {
    $return_value["success"] = false;
    $return_value["errors"]["product_id"] = "Unknown error while getting reviews, try refreshing the page";
}

echo json_encode($return_value);
