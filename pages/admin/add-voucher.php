<?php
require_once __DIR__ . "/../_base.php";

$title = "Add Voucher - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container container-center">
    <div class="title">Add Voucher</div>
    <form>
        <div class="form-group">
            <label for="name">Name</label>
            <div class="form-input">
                <input type="text" id="name" name="name" maxlength="100" />
            </div>
            <div class="form-error" data-error="name"></div>
        </div>

        <div class="form-group">
            <label>Value</label>
            <div class="custom-select">
                <label class="selected-text">Select Value</label>
                <div class="options">
                    <label class="option">
                        <input type="radio" name="value" value="10">
                        <span>RM 10</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="value" value="20">
                        <span>RM 20</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="value" value="50">
                        <span>RM 50</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="value" value="100">
                        <span>RM 100</span>
                    </label>
                </div>
            </div>
            <div class="form-error" data-error="value"></div>
        </div>

        <div class="form-group">
            <label for="claim_limit">Claim Limit (Optional)</label>
            <div class="form-input">
                <input type="text" id="claim_limit" name="claim_limit" placeholder="Leave empty for unlimited" />
            </div>
            <div class="form-error" data-error="claim_limit"></div>
        </div>

        <div class="form-group">
            <label for="valid-days">Valid Days After Claimed (Optional)</label>
            <div class="form-input">
                <input type="number" id="valid-days" name="valid_days" placeholder="Leave empty for unlimited" />
            </div>
            <div class="form-error" data-error="valid_days"></div>
        </div>

        <div class="form-group">
            <label for="expiry-date">Expiry Date (Optional)</label>
            <div class="form-input">
                <input type="date" id="expiry-date" name="expiry_date" />
            </div>
            <div class="form-error" data-error="expiry_date"></div>
        </div>

        <div class="form-group">
            <div class="input-group">
                <label for="for_signup">For Sign Up</label>
                <input type="checkbox" id="for_signup" name="for_signup" />
            </div>
        </div>

        <div class="form-group">
            <button type="submit">Add Voucher</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>