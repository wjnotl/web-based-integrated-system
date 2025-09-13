<?php
require_once __DIR__ . "/../_base.php";

$order_id = obtain_get("id");

order_expire();

$stm = $db->prepare("SELECT total_price FROM orders WHERE account_id = ? AND id = ?");
$stm->execute([$account_obj->id, $order_id]);

if ($stm->rowCount() === 1) {
    $total_price = $stm->fetchColumn();
} else {
    redirect("/order-history");
}

$title = "Payment - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container container-center payment-container">
    <form data-id="<?= $order_id; ?>">
        <div class="title">Payment</div>
        <div class="form-group">
            <label>Payment Option</label>
            <div class="custom-radio-container flex">
                <label>
                    <input type="radio" name="payment_option" value="Wallet" checked>
                    <div class="custom-radio-image wallet"></div>
                    <div>
                        <div class="custom-radio-text">Wallet</div>
                    </div>
                </label>
                <label>
                    <input type="radio" name="payment_option" value="Card">
                    <div class="custom-radio-image card"></div>
                    <div>
                        <div class="custom-radio-text">Card</div>
                    </div>
                </label>
            </div>

        </div>

        <div id="wallet-container">
            <div class="form-group">
                <div class="form-error" data-error="wallet_balance"></div>
            </div>
        </div>

        <div id="credit-card-container">
            <div class="form-group">
                <label for="card-number">Card Number</label>
                <div class="form-input">
                    <input type="text" maxlength="23" id="card-number" name="card_number">
                </div>
                <div class="form-error" data-error="card_number"></div>
            </div>
            <div class="form-group">
                <label for="card-cvc">Card CVC</label>
                <div class="form-input">
                    <input type="text" maxlength="4" id="card-cvc" name="card_cvc">
                </div>
                <div class="form-error" data-error="card_cvc"></div>
            </div>
            <div class="form-group">
                <label for="card-expiry">Card Expiry</label>
                <div class="form-input">
                    <input type="text" maxlength="5" id="card-expiry" name="card_expiry" placeholder="MM/YY">
                </div>
                <div class="form-error" data-error="card_expiry"></div>
            </div>
        </div>

        <div class="divider-horizontal"></div>

        <div id="summary-total-container" class="form-group two-column-content">
            <div>Order Total</div>
            <div><?= toRMFormat($total_price); ?></div>
        </div>

        <button type="submit">Pay Now</button>
    </form>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>