<?php
require_once __DIR__ . "/../_base.php";

order_expire();

$stm = $db->prepare("SELECT pv.id as product_variant_id, pv.colour, pv.size, pv.stock,
                            c.quantity, 
                            p.id, p.name, p.price, p.photo,
                            EXISTS(SELECT 1 FROM favourite f WHERE f.product_id = p.id AND f.account_id = c.account_id) AS is_favourite
                    FROM cart c
                    JOIN product_variant pv ON c.product_variant_id = pv.id
                    JOIN product p ON pv.product_id = p.id
                    WHERE c.account_id = ?");
$stm->execute([$account_obj->id]);
$cart = $stm->fetchAll();

$reorders = temp("reorder_products") ?? [];

$title = "My Cart - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form id="cart-form" action="/checkout" method="post">
    <div id="cart-form-left-container">
        <div class="container cart-container">
            <div id="cart-empty">No items in cart yet</div>
            <div id="select-all-container">
                <input type="checkbox" id="select-all-items">
                <label for="select-all-items">Select All</label>
            </div>
            <div id="bulk-remove-button" class="cart-remove-button"></div>
        </div>

        <?php foreach ($cart as $item):
            $item->isEnoughStock = $item->quantity > $item->stock;
            $item->isOutOfStock = $item->stock === 0;
        ?>
            <label class="container cart-container cart-items-container" data-product-id="<?= $item->id; ?>" data-product-variant-id="<?= $item->product_variant_id; ?>">
                <div class="left">
                    <input type="checkbox" name="checkout-items[]" value="<?= $item->product_variant_id; ?>" <?= in_array($item->product_variant_id, $reorders) ? "checked" : ""; ?>>
                    <img class="product-image" src="<?= isset($item->photo) ? "/uploads/product/" . preg_split("/\r\n|\n|\r/", $item->photo)[0] : "/src/img/icon/product_no_image.png"; ?>" alt="<?= htmlspecialchars($item->name); ?>">
                    <div class="product-details">
                        <div class="product-name-container">
                            <a href="/product-detail?id=<?= $item->id; ?>" class="product-name"><?= htmlspecialchars($item->name); ?></a>
                            <div class="product-variation">
                                <div class="product-colour"><?= htmlspecialchars($item->colour); ?></div>
                                <div class="product-size"><?= htmlspecialchars($item->size); ?></div>
                            </div>
                        </div>
                        <div class="product-details-bottom">
                            <div class="product-price"><?= toRMFormat($item->price); ?></div>
                            <div class="form-group">
                                <div class="form-error <?= ($item->isEnoughStock || $item->isOutOfStock) ? "show" : ""; ?>" data-error="quantity_<?= $item->product_variant_id; ?>"><?= $item->isOutOfStock ? "Out of stock" : "Not enough stock, only " . $item->stock . " available"; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="right">
                    <div class="cart-buttons-container">
                        <div class="cart-favourite-button <?= $item->is_favourite ? "favourite" : ""; ?>"></div>
                        <div class="cart-remove-button"></div>
                    </div>
                    <div class="form-group">
                        <div class="form-input">
                            <input type="number" name="quantity_<?= $item->product_variant_id; ?>" value="<?= $item->quantity; ?>">
                        </div>
                    </div>
                </div>
            </label>
        <?php endforeach; ?>
    </div>
    <div id="cart-form-right-container">
        <div id="cart-summary-container" class="container cart-container">
            <div class="title">Order Summary</div>

            <div id="no-cart-summary-items">No items selected</div>

            <div id="cart-summary-items-container"></div>

            <div class="divider-horizontal"></div>

            <div id="cart-total-container" class="two-column-content">
                <div>Total</div>
                <div>0.00</div>
            </div>

            <button type="submit" disabled>Check Out</button>
        </div>
    </div>
</form>

<?php
require_once __DIR__ . "/../_foot.php";
?>