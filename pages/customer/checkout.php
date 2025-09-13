<?php
require_once __DIR__ . "/../_base.php";

order_expire();

$checkout_items = obtain_post("checkout-items");

if (!isset($checkout_items) || !is_array($checkout_items) || empty(array_filter($checkout_items))) {
    redirect("/cart");
}

$total_price = 0;
foreach ($checkout_items as $product_variant_id) {
    $stm = $db->prepare("SELECT quantity, pv.stock, p.price
                        FROM cart c
                        JOIN product_variant pv ON c.product_variant_id = pv.id
                        JOIN product p ON pv.product_id = p.id
                        WHERE c.product_variant_id = ? AND c.account_id = ?");
    $stm->execute([$product_variant_id, $account_obj->id]);
    if ($stm->rowCount() === 1) {
        $item = $stm->fetchObject();
        if ($item->quantity > $item->stock) {
            redirect("/cart");
        }
        $total_price += round($item->price * $item->quantity, 2);
    } else {
        redirect("/cart");
    }
}

$title = "Checkout - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container checkout-container">
    <form data-total-price="<?= $total_price; ?>" data-checkout-items="<?= htmlspecialchars(implode(",", $checkout_items)); ?>">
        <div class="title">Shipping Info</div>
        <div class="form-group">
            <label for="name">Name</label>
            <div class="form-input">
                <input type="text" id="name" name="name">
            </div>
            <div class="form-error" data-error="name"></div>
        </div>
        <div class="form-group">
            <label for="contact-number">Contact Number</label>
            <div class="form-input">
                <input type="text" maxlength="12" id="contact-number" name="contact_number" placeholder="01x-xxxxxxxx">
            </div>
            <div class="form-error" data-error="contact_number"></div>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <div class="form-input">
                <input type="text" id="address" name="address">
            </div>
            <div class="form-error" data-error="address"></div>
        </div>
        <div class="form-group">
            <label for="city">City</label>
            <div class="form-input">
                <input type="text" id="city" name="city">
            </div>
            <div class="form-error" data-error="city"></div>
        </div>
        <div class="form-group">
            <label>State</label>
            <div class="custom-select">
                <label class="selected-text">Select State</label>
                <div class="options">
                    <label class="option">
                        <input type="radio" name="state" value="Johor">
                        <span>Johor</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Kedah">
                        <span>Kedah</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Kelantan">
                        <span>Kelantan</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Kuala Lumpur">
                        <span>Kuala Lumpur</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Labuan">
                        <span>Labuan</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Melaka">
                        <span>Melaka</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Negeri Sembilan">
                        <span>Negeri Sembilan</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Pahang">
                        <span>Pahang</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Perak">
                        <span>Perak</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Perlis">
                        <span>Perlis</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Pulau Pinang">
                        <span>Pulau Pinang</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Putrajaya">
                        <span>Putrajaya</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Sabah">
                        <span>Sabah</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Sarawak">
                        <span>Sarawak</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Selangor">
                        <span>Selangor</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="state" value="Terengganu">
                        <span>Terengganu</span>
                    </label>
                </div>
            </div>
            <div class="form-error" data-error="state"></div>
        </div>
        <div class="form-group">
            <label for="postal-code">Postal Code</label>
            <div class="form-input">
                <input type="text" maxlength="5" id="postal-code" name="postal_code" placeholder="xxxxx">
            </div>
            <div class="form-error" data-error="postal_code"></div>
        </div>
        <div class="form-group">
            <label>Shipping Type</label>
            <div class="custom-radio-container flex">
                <label>
                    <input type="radio" name="shipping_type" value="Standard" checked>
                    <div class="custom-radio-image standard"></div>
                    <div>
                        <div class="custom-radio-text">Standard</div>
                        <div class="custom-radio-description">
                            <div>5-7 Days</div>
                            <div>RM 8.00</div>
                        </div>
                    </div>
                </label>
                <label>
                    <input type="radio" name="shipping_type" value="Express">
                    <div class="custom-radio-image express"></div>
                    <div>
                        <div class="custom-radio-text">Express</div>
                        <div class="custom-radio-description">
                            <div>1-3 Days</div>
                            <div>RM 15.00</div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <div class="divider-horizontal"></div>

        <div class="form-group">
            <label>Voucher</label>
            <div id="voucher-select-container"></div>
            <div class="input-group">
                <button type="button" id="select-voucher-button" class="button-blue"></button>
                <button type="button" id="remove-voucher-button" class="button-red">Remove</button>
            </div>
            <div class="form-error" data-error="voucher_id"></div>
        </div>

        <div class="divider-horizontal"></div>

        <div id="summary-subtotal-container" class="two-column-content">
            <div>Item(s) Total</div>
            <div><?= toRMFormat($total_price); ?></div>
            <div>Shipping Fee</div>
            <div>8.00</div>
        </div>

        <div class="divider-horizontal"></div>

        <div id="summary-total-container" class="form-group two-column-content">
            <div>Order Total</div>
            <div><?= toRMFormat($total_price + 8); ?></div>
        </div>

        <button type="submit">Place Order</button>
    </form>
</div>

<div id="voucher-overlay" class="overlay">
    <div class="content">
        <div class="overlay-header">
            <div class="title">Select a voucher</div>
            <div class="overlay-close"></div>
        </div>
        <div id="voucher-empty">No vouchers found</div>
        <div id="voucher-overlay-list-container">
        </div>
    </div>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>