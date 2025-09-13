<?php
require_once __DIR__ . "/../_base.php";

$title = "Top Up - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form class="container container-center">
    <div class="title">Top Up Wallet</div>
    <div class="form-group">
        <label for="amount">Amount (RM)</label>
        <div class="form-input">
            <input type="text" id="amount" name="amount" placeholder="0.00" data-rm-format="true">
        </div>
        <div class="form-error" data-error="amount"></div>
    </div>
    <div class="form-group">
        <label for="card-number">Card Number</label>
        <div class="form-input">
            <input type="text" id="card-number" name="card_number" maxlength="23">
        </div>
        <div class="form-error" data-error="card_number"></div>
    </div>
    <div class="form-group">
        <label for="card-cvc">Card CVC</label>
        <div class="form-input">
            <input type="text" id="card-cvc" name="card_cvc" maxlength="4">
        </div>
        <div class="form-error" data-error="card_cvc"></div>
    </div>
    <div class="form-group">
        <label for="card-expiry">Card Expiry</label>
        <div class="form-input">
            <input type="text" id="card-expiry" name="card_expiry" placeholder="MM/YY" maxlength="5">
        </div>
        <div class="form-error" data-error="card_expiry"></div>
    </div>

    <button type="submit">Top Up</button>
</form>

<?php
require_once __DIR__ . "/../_foot.php";
?>