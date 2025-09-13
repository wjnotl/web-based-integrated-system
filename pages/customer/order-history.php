<?php
require_once __DIR__ . "/../_base.php";

order_expire();

$query = "SELECT 
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
        WHERE o.account_id = ? ";

$status = obtain_get("status");
if ($status === "unpaid") {
    $query .= "AND o.status = 'Unpaid'";
} else if ($status === "preparing") {
    $query .= "AND o.status = 'Preparing'";
} else if ($status === "in_transit") {
    $query .= "AND o.status = 'In Transit'";
} else if ($status === "delivered") {
    $query .= "AND o.status = 'Delivered'";
} else if ($status === "canceled") {
    $query .= "AND o.status = 'Canceled'";
} else {
    $status = "all";
}

$query .= " GROUP BY o.id, o.status, o.creation_time, o.shipping_type, o.voucher_value, o.account_id
        ORDER BY o.creation_time DESC
        LIMIT 15";

$stm = $db->prepare($query);
$stm->execute([$account_obj->id]);
$orders = $stm->fetchAll();

$title = "Order History - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div id="filter-order-container" class="container order-history-container" data-status="<?= $status; ?>">
    <div class="custom-radio-container center-items center-text specified-width">
        <label>
            <input type="radio" name="filter_order" value="" <?= $status === "all" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">All</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_order" value="unpaid" <?= $status === "unpaid" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">Unpaid</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_order" value="preparing" <?= $status === "preparing" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">Preparing</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_order" value="in_transit" <?= $status === "in_transit" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">In Transit</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_order" value="delivered" <?= $status === "delivered" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">Delivered</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_order" value="canceled" <?= $status === "canceled" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">Canceled</div>
            </div>
        </label>
    </div>
</div>

<div id="no-order" class="container">No orders found</div>

<?php foreach ($orders as $order): ?>
    <div class="container order-history-container" data-order-id="<?= $order->id; ?>">
        <span class="title"><?= $order->id; ?></span>
        <span class="float-right status <?= $order->status_code; ?>"></span>
        <div class="timestamp"><?= $order->creation_time; ?></div>
        <div class="price"><?= toRMFormat($order->total_price); ?></div>
        <div class="buttons-container">
            <a href="/order-detail?id=<?= $order->id; ?>">
                <button class="button-blue">Details</button>
            </a>
            <div>
                <button data-button-type="cancel" class="button-red">Cancel</button>
                <a href="/payment?id=<?= $order->id ?>" data-button-type="pay" class="link-button">Pay</a>
                <button data-button-type="reorder" class="button-yellow">Reorder</button>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php
require_once __DIR__ . "/../_foot.php";
?>