<?php
require_once __DIR__ . "/../_base.php";

$stm = $db->prepare("SELECT wallet_balance FROM account WHERE id = ?");
$stm->execute([$account_obj->id]);
$wallet_balance = $stm->fetchColumn();

$query = "SELECT * FROM transaction WHERE account_id = ? AND detail IS NOT NULL ";

$time = obtain_get("time");
if ($time === "today") {
    $query .= "AND (creation_time BETWEEN CURDATE() AND CURDATE() + INTERVAL 1 DAY)";
} else if ($time === "yesterday") {
    $query .= "AND (creation_time BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE())";
} else if ($time === "this-week") {
    $query .= "AND (creation_time BETWEEN CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY AND CURDATE() + INTERVAL 1 DAY)";
} else if ($time === "this-month") {
    $query .= "AND (creation_time BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE() + INTERVAL 1 DAY)";
} else if ($time === "last-month") {
    $query .= "AND (creation_time BETWEEN DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01') AND LAST_DAY(CURDATE() - INTERVAL 1 MONTH) + INTERVAL 1 DAY)";
} else {
    $time = "all";
}

$query .= " ORDER BY creation_time DESC LIMIT 10";

$stm = $db->prepare($query);
$stm->execute([$account_obj->id]);
$transactions = $stm->fetchAll();

$title = "My Wallet - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div id="wallet-balance-container" class="container wallet-container">
    <div>
        <span class="title">Current Balance</span>
        <a href="/top-up"><button id="top-up-button" class="button-blue">Top Up</button></a>
    </div>
    <div id="current-balance"><?= toRMFormat($wallet_balance); ?></div>
</div>

<div id="wallet-transaction-container" class="container wallet-container" data-filter-transaction="<?= $time; ?>">
    <div class="title">Transaction History</div>
    <div id="filter-transaction-container" class="custom-radio-container center-items center-text specified-width">
        <label>
            <input type="radio" name="filter_transaction" value="" <?= $time === "all" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">All</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_transaction" value="today" <?= $time === "today" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">Today</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_transaction" value="yesterday" <?= $time === "yesterday" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">Yesterday</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_transaction" value="this-week" <?= $time === "this-week" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">This Week</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_transaction" value="this-month" <?= $time === "this-month" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">This Month</div>
            </div>
        </label>
        <label>
            <input type="radio" name="filter_transaction" value="last-month" <?= $time === "last-month" ? "checked" : ""; ?>>
            <div>
                <div class="custom-radio-text">Last Month</div>
            </div>
        </label>
    </div>

    <div id="no-transaction">No Transaction Found</div>

    <?php foreach ($transactions as $transaction): ?>
        <?= "<" . (isset($transaction->order_id) ? ("a href=/order-detail?id=" . $transaction->order_id) : "div") . " class=transaction-container data-transaction-id=" . $transaction->id . ">"; ?>
        <div>
            <div class="title"><?= htmlspecialchars($transaction->detail); ?></div>
            <div class="timestamp"><?= $transaction->creation_time; ?></div>
        </div>
        <div class="price <?= $transaction->value < 0 ? "minus" : ""; ?>"><?= toRMFormat((string)abs($transaction->value)); ?></div>
        <?= "</" . (isset($transaction->order_id) ? "a" : "div") . ">"; ?>
    <?php endforeach; ?>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>