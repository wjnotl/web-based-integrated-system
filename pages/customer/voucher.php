<?php
require_once __DIR__ . "/../_base.php";

order_expire();

$db->query("DELETE v FROM voucher v
            JOIN voucher_template vt ON vt.id = v.voucher_template_id
            WHERE v.expiry_date < CURDATE()
            AND vt.expiry_date IS NOT NULL
            AND vt.expiry_date < CURDATE()
");

$value = obtain_get("value");

if (!isset($value) || !in_array($value, ["10", "20", "50", "100"])) {
    $value = "all";
    $stm = $db->prepare("SELECT v.id, v.expiry_date, vt.value
                        FROM voucher v
                        JOIN voucher_template vt ON v.voucher_template_id = vt.id
                        WHERE account_id = ? AND v.is_used = 0
                        ORDER BY vt.value DESC, v.expiry_date ASC");
    $stm->execute([$account_obj->id]);
    $vouchers = $stm->fetchAll();
} else {
    $stm = $db->prepare("SELECT v.id, v.expiry_date, vt.value
                        FROM voucher v
                        JOIN voucher_template vt ON v.voucher_template_id = vt.id
                        WHERE account_id = ? AND vt.value = ? AND v.is_used = 0
                        ORDER BY v.expiry_date ASC");
    $stm->execute([$account_obj->id, $value]);
    $vouchers = $stm->fetchAll();
}

$title = "My Voucher - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container voucher-container">
    <div class="custom-radio-container center-items center-text specified-width">
        <label>
            <input type="radio" name="filter_voucher" value="" <?= $value === "all" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">All</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_voucher" value="10" <?= $value === "10" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">RM 10 OFF</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_voucher" value="20" <?= $value === "20" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">RM 20 OFF</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_voucher" value="50" <?= $value === "50" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">RM 50 OFF</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_voucher" value="100" <?= $value === "100" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">RM 100 OFF</div>
            </div>
        </label>
    </div>
</div>

<div id="voucher-list-container" class="container voucher-container">
    <div id="voucher-empty">No vouchers found</div>

    <?php foreach ($vouchers as $voucher): ?>
        <div class="voucher">
            <div class="left">
                <div class="tear-off"><span>Enjoy your voucher</span></div>
                <div class="content">
                    <div data-voucher-value="<?= $voucher->value; ?>" class="value"></div>
                    <div class="voucher-text"></div>
                    <div class="expiry-date"><?= toDateFormat($voucher->expiry_date); ?></div>
                </div>
            </div>
            <div class="right">
                <span><?= $voucher->id; ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>