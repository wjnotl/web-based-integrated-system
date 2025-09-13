<?php
require_once __DIR__ . "/../_base.php";

$order_id = obtain_get("id");
if (!isset($order_id)) {
    redirect("/manage-order");
}

$stm = $db->prepare("SELECT o.*,
                        a.name AS account_name,
                        a.email AS account_email
                    FROM orders o 
                    LEFT JOIN account a ON a.id = o.account_id 
                    WHERE o.id = ? AND o.status <> 'Unpaid'");
$stm->execute([$order_id]);
if ($stm->rowCount() !== 1) {
    redirect("/manage-order");
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

$title = "Edit Order #" . $order->id . " - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form class="container detail-container edit-order-container" data-id="<?= $order->id; ?>">
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
        <?php if ($order->status === "Canceled"): ?>
            <div>Canceled</div>
        <?php else: ?>
            <div class="form-group">
                <div class="custom-select">
                    <label class="selected-text"></label>
                    <div class="options">
                        <label class="option">
                            <input type="radio" name="status" value="Preparing" <?= $order->status === "Preparing" ? "checked" : ""; ?>>
                            <span>Preparing</span>
                        </label>
                        <label class="option">
                            <input type="radio" name="status" value="In Transit" <?= $order->status === "In Transit" ? "checked" : ""; ?>>
                            <span>In Transit</span>
                        </label>
                        <label class="option">
                            <input type="radio" name="status" value="Delivered" <?= $order->status === "Delivered" ? "checked" : ""; ?>>
                            <span>Delivered</span>
                        </label>
                        <label class="option">
                            <input type="radio" name="status" value="Canceled" <?= $order->status === "Canceled" ? "checked" : ""; ?>>
                            <span>Canceled</span>
                        </label>
                    </div>
                </div>
                <div class="form-error" data-error="status"></div>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($order->status !== "Canceled"): ?>
        <div class="button-group">
            <button type="submit" disabled>Save Changes</button>
        </div>
    <?php endif; ?>
</form>

<?php if (isset($order->account_id)): ?>
    <div class="container edit-order-container">
        <div class="title">Customer Info</div>
        <div class="content three-column-content">
            <div>ID</div>
            <div>:</div>
            <div><?= $order->account_id; ?></div>
            <div>Name</div>
            <div>:</div>
            <div><?= htmlspecialchars($order->account_name); ?></div>
            <div>Email</div>
            <div>:</div>
            <div><?= $order->account_email; ?></div>
        </div>
    </div>
<?php endif; ?>

<div class="container edit-order-container">
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
        <div><?= htmlspecialchars($order->state); ?></div>
        <div>Postal Code</div>
        <div>:</div>
        <div><?= htmlspecialchars($order->postal_code); ?></div>
        <div>Shipping Type</div>
        <div>:</div>
        <div><?= $order->shipping_type; ?></div>
    </div>
</div>

<?php if (isset($order->payment_option)): ?>
    <div class="container edit-order-container">
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

<div class="container edit-order-container">
    <div class="title">Order Summary</div>
    <div id="summary-items-container">
        <?php $items_total = 0; ?>
        <?php foreach ($order_items as $item): ?>
            <?php $items_total += $item->sub_total; ?>
            <div class="summary-item">
                <?php if (!isset($item->product_id)): ?>
                    <img src="/src/img/icon/product_removed.png" alt="<?= htmlspecialchars($item->name); ?>" class="product-image">
                <?php else: ?>
                    <img src="<?= isset($item->photo) ? "/uploads/product/" . preg_split("/\r\n|\n|\r/", $item->photo)[0] : "/src/img/icon/product_no_image.png"; ?>" alt="<?= htmlspecialchars($item->name); ?>" class="product-image">
                <?php endif; ?>
                <div class="content three-column-content">
                    <?php if (isset($item->product_id)): ?>
                        <div>Product ID</div>
                        <div>:</div>
                        <div><?= $item->product_id; ?></div>
                    <?php endif; ?>
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
                </div>
                <?= !isset($item->product_id) ? "</div>" : "</a>"; ?>
            </div>
        <?php endforeach; ?>

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