<?php
require_once __DIR__ . "/../_base.php";

$page = (int)obtain_get("page", "1");
$search = obtain_get("search", "");
$search_type = obtain_get("search_type");
$start_date = obtain_get("start_date");
$end_date = obtain_get("end_date");
$expiry_start_date = obtain_get("expiry_start_date");
$expiry_end_date = obtain_get("expiry_end_date");
$sort = obtain_get("sort");
$desc = obtain_get("desc");

$query = "
    SELECT vt.*
    FROM voucher_template vt
";

$count_query = "
    SELECT COUNT(*)
    FROM voucher_template vt
";

if ($search_type === "id") {
    $query .= " WHERE vt.id LIKE ?";
    $count_query .= " WHERE vt.id LIKE ?";
} else {
    $query .= " WHERE vt.name LIKE ?";
    $count_query .= " WHERE vt.name LIKE ?";
    $search_type = "";
}
$params = ["%$search%"];

if (isset($start_date) && isset($end_date) && isDateFormat($start_date) && isDateFormat($end_date)) {
    $query .= " AND DATE(vt.creation_time) BETWEEN ? AND ?";
    $count_query .= " AND DATE(vt.creation_time) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
} else if (isset($start_date) && isDateFormat($start_date)) {
    $query .= " AND DATE(vt.creation_time) >= ?";
    $count_query .= " AND DATE(vt.creation_time) >= ?";
    $params[] = $start_date;
} else if (isset($end_date) && isDateFormat($end_date)) {
    $query .= " AND DATE(vt.creation_time) <= ?";
    $count_query .= " AND DATE(vt.creation_time) <= ?";
    $params[] = $end_date;
}

if (isset($expiry_start_date) && isset($expiry_end_date) && isDateFormat($expiry_start_date) && isDateFormat($expiry_end_date)) {
    $query .= " AND vt.expiry_date IS NOT NULL AND DATE(vt.expiry_date) BETWEEN ? AND ?";
    $count_query .= " AND vt.expiry_date IS NOT NULL AND DATE(vt.expiry_date) BETWEEN ? AND ?";
    $params[] = $expiry_start_date;
    $params[] = $expiry_end_date;
} else if (isset($expiry_start_date) && isDateFormat($expiry_start_date)) {
    $query .= " AND vt.expiry_date IS NOT NULL AND DATE(vt.expiry_date) >= ?";
    $count_query .= " AND vt.expiry_date IS NOT NULL AND DATE(vt.expiry_date) >= ?";
    $params[] = $expiry_start_date;
} else if (isset($expiry_end_date) && isDateFormat($expiry_end_date)) {
    $query .= " AND vt.expiry_date IS NOT NULL AND DATE(vt.expiry_date) <= ?";
    $count_query .= " AND vt.expiry_date IS NOT NULL AND DATE(vt.expiry_date) <= ?";
    $params[] = $expiry_end_date;
}

$query .= " GROUP BY vt.id";

if ($sort === "name") {
    $query .= " ORDER BY vt.name";
} else if ($sort === "value") {
    $query .= " ORDER BY vt.value";
} else if ($sort === "claim_limit") {
    if ($desc) {
        $query .= " ORDER BY vt.claim_limit IS NOT NULL, vt.claim_limit";
    } else {
        $query .= " ORDER BY vt.claim_limit IS NULL, vt.claim_limit";
    }
} else if ($sort === "total_claimed") {
    $query .= " ORDER BY vt.total_claimed";
} else if ($sort === "valid_days") {
    $query .= " ORDER BY vt.valid_days IS NULL, vt.valid_days";
} else if ($sort === "expiry_date") {
    $query .= " ORDER BY vt.expiry_date IS NULL, vt.expiry_date";
} else if ($sort === "date") {
    $query .= " ORDER BY vt.creation_time";
} else {
    $query .= " ORDER BY vt.id";
    $sort = "";
}

if ($desc) {
    $query .= " DESC";
}

require_once __DIR__ . "/../../lib/SimplePager.php";
$pager = new SimplePager($query, $params, $count_query, $params, 10, $page);

$title = "Manage Voucher - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form class="container manage-container manage-query-container">
    <div class="form-group">
        <div class="input-group search-container">
            <div class="form-input">
                <?php
                if ($search_type === "id") {
                    $placeholder = "Search by ID...";
                } else {
                    $placeholder = "Search by Name...";
                }
                ?>
                <input type="text" name="search" placeholder="<?= $placeholder; ?>" value="<?= htmlspecialchars($search); ?>" />
                <button type="submit"></button>
            </div>
            <div class="custom-select">
                <label class="selected-text"></label>
                <div class="options">
                    <label class="option">
                        <input checked type="radio" name="search_type" value="" <?= $search_type === "" ? "checked" : ""; ?>>
                        <span>Search by Name</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="search_type" value="id" <?= $search_type === "id" ? "checked" : ""; ?>>
                        <span>Search by ID</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="input-group filter-container">
            <span>Expiry Date Filter: </span>
            <div class="input-group">
                <div class="form-input">
                    <input type="date" name="expiry_start_date" value="<?= htmlspecialchars($expiry_start_date); ?>">
                </div>
                <span>to</span>
                <div class="form-input">
                    <input type="date" name="expiry_end_date" value="<?= htmlspecialchars($expiry_end_date); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="input-group filter-container">
            <span>Creation Date Filter: </span>
            <div class="input-group">
                <div class="form-input">
                    <input type="date" name="start_date" value="<?= htmlspecialchars($start_date); ?>">
                </div>
                <span>to</span>
                <div class="form-input">
                    <input type="date" name="end_date" value="<?= htmlspecialchars($end_date); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <a href="/add-voucher" class="link-button button-blue">Add Voucher</a>
    </div>
</form>

<div class="container manage-container manage-table-container">
    <table>
        <thead>
            <tr>
                <th data-sort="" class="<?= $sort === "" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">ID</th>
                <th data-sort="name" class="<?= $sort === "name" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Name</th>
                <th data-sort="value" class="<?= $sort === "value" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Value</th>
                <th data-sort="claim_limit" class="<?= $sort === "claim_limit" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Claim Limit</th>
                <th data-sort="total_claimed" class="<?= $sort === "total_claimed" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Total Claimed</th>
                <th data-sort="valid_days" class="<?= $sort === "valid_days" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Valid Days</th>
                <th data-sort="expiry_date" class="<?= $sort === "expiry_date" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Expiry Date</th>
                <th data-sort="date" class="<?= $sort === "date" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Creation Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pager->result as $voucher) : ?>
                <tr>
                    <td><?= $voucher->id ?></td>
                    <td><?= htmlspecialchars($voucher->name) ?></td>
                    <td><?= $voucher->value ?></td>
                    <td><?= isset($voucher->claim_limit) ? $voucher->claim_limit : "Infinity" ?></td>
                    <td><?= $voucher->total_claimed ?></td>
                    <td><?= isset($voucher->valid_days) ? $voucher->valid_days : "-" ?></td>
                    <td><?= isset($voucher->expiry_date) ? toDateFormat($voucher->expiry_date) : "-" ?></td>
                    <td><?= toDateFormat($voucher->creation_time); ?></td>
                    <td>
                        <div class="button-group-inline">
                            <?php if ($voucher->token) : ?>
                                <button class="button-blue copy-link-button" data-link="/get-voucher?token=<?= $voucher->token ?>">Copy Link</button>
                            <?php endif; ?>
                            <button class="button-red delete-voucher-button" data-id="<?= $voucher->id ?>">Delete</button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="no-result">No result found</div>
</div>

<div class="paging-container">
    <?php $pager->html($_GET); ?>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>