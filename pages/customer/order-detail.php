<?php
require_once __DIR__ . "/../_base.php";

$order_id = obtain_get("id");
if (!isset($order_id)) {
    redirect("/");
}

$stm = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stm->execute([$order_id]);
if ($stm->rowCount() !== 1) {
    redirect("/");
}
$order = $stm->fetchObject();
list($date, $time) = explode(" ", $order->creation_time);
$shipping_fee = $order->shipping_type === "Standard" ? 8 : 15;

$stm = $db->prepare("SELECT
                        oi.name,
                        oi.price,
                        oi.quantity,
                        oi.colour,
                        oi.size,
                        oi.product_id,
                        (CASE WHEN oi.product_id IS NULL THEN '' ELSE p.photo END) AS photo,
                        SUM(oi.price * oi.quantity) AS sub_total
                    FROM order_item oi 
                    LEFT JOIN product_variant pv ON oi.product_variant_id = pv.id
                    LEFT JOIN product p ON pv.product_id = p.id
                    WHERE oi.order_id = ?
                    GROUP BY oi.name, oi.price, oi.quantity, oi.colour, oi.size, oi.product_id, p.photo");
$stm->execute([$order_id]);
$order_items = $stm->fetchAll();

$title = "Order #" . $order->id . " - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container order-detail-container">
    <div class="title">Order Details</div>
    <div class="content three-column-content">
        <div>ID</div>
        <div>:</div>
        <div><?= $order->id; ?></div>
        <div>Date</div>
        <div>:</div>
        <div><?= $date; ?></div>
        <div>Time</div>
        <div>:</div>
        <div><?= $time; ?></div>
        <div>Status</div>
        <div>:</div>
        <div><?= $order->status; ?></div>
    </div>
</div>

<div class="container order-detail-container">
    <div class="title">Shipping Info</div>
    <div class="content three-column-content">
        <div>Name</div>
        <div>:</div>
        <div><?= htmlspecialchars($order->name); ?></div>
        <div>Contact Number</div>
        <div>:</div>
        <div><?= htmlspecialchars($order->contact_number); ?></div>
        <div>Address</div>
        <div>:</div>
        <div><?= htmlspecialchars($order->address); ?></div>
        <div>City</div>
        <div>:</div>
        <div><?= htmlspecialchars($order->city); ?></div>
        <div>State</div>
        <div>:</div>
        <div><?= $order->state; ?></div>
        <div>Postal Code</div>
        <div>:</div>
        <div><?= htmlspecialchars($order->postal_code); ?></div>
        <div>Shipping Type</div>
        <div>:</div>
        <div><?= $order->shipping_type; ?></div>
    </div>
</div>

<?php if (isset($order->payment_option)): ?>
    <div class="container order-detail-container">
        <div class="title">Payment Details</div>
        <div class="content three-column-content">
            <div>Option</div>
            <div>:</div>
            <div><?= $order->payment_option; ?></div>
            <?php if ($order->payment_option === "Card"): ?>
                <div>Card Number</div>
                <div>:</div>
                <div><?= $order->payment_card_brand . " •••• " . $order->payment_card_last4; ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="container order-detail-container">
    <div class="title">Order Summary</div>
    <div id="summary-items-container">
        <?php
        $items_total = 0;
        foreach ($order_items as $item):
            $items_total += $item->sub_total;
        ?>
            <?php if (!isset($item->product_id)): ?>
                <div class="summary-item">
                    <img src="/src/img/icon/product_removed.png" alt="<?= htmlspecialchars($item->name); ?>" class="product-image">
                <?php else: ?>
                    <a href="/product-detail?id=<?= $item->product_id; ?>" class="summary-item">
                        <img src="<?= isset($item->photo) ? "/uploads/product/" . preg_split("/\r\n|\n|\r/", $item->photo)[0] : "/src/img/icon/product_no_image.png"; ?>" alt="<?= htmlspecialchars($item->name); ?>" class="product-image">
                    <?php endif; ?>
                    <div class="content three-column-content">
                        <div>Name</div>
                        <div>:</div>
                        <div><?= htmlspecialchars($item->name); ?></div>
                        <div>Colour</div>
                        <div>:</div>
                        <div><?= htmlspecialchars($item->colour); ?></div>
                        <div>Size</div>
                        <div>:</div>
                        <div><?= htmlspecialchars($item->size); ?></div>
                        <div>Price</div>
                        <div>:</div>
                        <div>RM <?= toRMFormat($item->price); ?></div>
                        <div>Quantity</div>
                        <div>:</div>
                        <div><?= $item->quantity; ?></div>
                        <div>Sub Total</div>
                        <div>:</div>
                        <div>RM <?= toRMFormat($item->sub_total); ?></div>
                    </div>
                    <?= !isset($item->product_id) ? "</div>" : "</a>"; ?>
                <?php endforeach; ?>
                </div>

                <div class="divider-horizontal"></div>

                <div id="summary-subtotal-container" class="content two-column-content">
                    <div>Item(s) Total</div>
                    <div>RM <?= toRMFormat($items_total); ?></div>
                    <?php if (isset($order->voucher_value)):
                        $items_total -= $order->voucher_value;
                    ?>
                        <div>Voucher (RM <?= $order->voucher_value; ?> OFF)</div>
                        <div>-RM <?= toRMFormat($order->voucher_value); ?></div>
                    <?php endif; ?>
                    <div>Shipping Fee</div>
                    <div>RM <?= toRMFormat($shipping_fee); ?></div>
                </div>

                <div class="divider-horizontal"></div>

                <div id="summary-total-container" class="content two-column-content">
                    <div>Order Total</div>
                    <div>RM <?= toRMFormat($items_total + $shipping_fee); ?></div>
                </div>
    </div>

    <?php
    require_once __DIR__ . "/../_foot.php";
    ?>