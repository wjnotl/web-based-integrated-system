<?php
require_once __DIR__ . "/../_base.php";

$page = (int)obtain_get("page", "1");
$search = obtain_get("search", "");
$search_type = obtain_get("search_type");
$filter_status = obtain_get("filter_status");
$start_date = obtain_get("start_date");
$end_date = obtain_get("end_date");
$sort = obtain_get("sort");
$desc = obtain_get("desc");

$query = "
    FROM account a 
    JOIN account_type t ON a.account_type_id = t.id
    WHERE t.name = 'Customer'
";

if ($search_type === "email") {
    $query .= " AND a.email LIKE ?";
} else if ($search_type === "id") {
    $query .= " AND a.id LIKE ?";
} else {
    $search_type = "";
    $query .= " AND a.name LIKE ?";
}
$params = ["%$search%"];

if ($filter_status === "verified") {
    $query .= " AND a.pending_delete_expire IS NULL AND a.is_banned = 0 AND a.is_verified = 1";
} else if ($filter_status === "unverified") {
    $query .= " AND a.pending_delete_expire IS NULL AND a.is_banned = 0 AND a.is_verified = 0";
} else if ($filter_status === "banned") {
    $query .= " AND a.pending_delete_expire IS NULL AND a.is_banned = 1";
} else if ($filter_status === "to_delete") {
    $query .= " AND a.pending_delete_expire IS NOT NULL";
} else {
    $filter_status = "";
}

if (isset($start_date) && isset($end_date) && isDateFormat($start_date) && isDateFormat($end_date)) {
    $query .= " AND DATE(a.creation_time) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
} else if (isset($start_date) && isDateFormat($start_date)) {
    $query .= " AND DATE(a.creation_time) >= ?";
    $params[] = $start_date;
} else if (isset($end_date) && isDateFormat($end_date)) {
    $query .= " AND DATE(a.creation_time) <= ?";
    $params[] = $end_date;
}

if ($sort === "name") {
    $query .= " ORDER BY a.name";
} else if ($sort === "email") {
    $query .= " ORDER BY a.email";
} else if ($sort === "status") {
    $query .= " ORDER BY status";
} else if ($sort === "date") {
    $query .= " ORDER BY a.creation_time";
} else {
    $query .= " ORDER BY a.id";
    $sort = "";
}

if ($desc) {
    $query .= " DESC";
}

require_once __DIR__ . "/../../lib/SimplePager.php";
$pager = new SimplePager("
SELECT a.*, (CASE 
    WHEN a.pending_delete_expire IS NOT NULL THEN 'to_delete'
    WHEN a.is_banned = 1 THEN 'banned'
    WHEN a.is_verified = 0 THEN 'unverified'
    ELSE 'verified'
    END) AS status
$query", $params, "SELECT COUNT(*) $query", $params, 10, $page);

$title = "Manage Customer - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form class="container manage-container manage-query-container">
    <div class="form-group">
        <div class="input-group search-container">
            <div class="form-input">
                <?php
                if ($search_type === "email") {
                    $placeholder = "Search by Email...";
                } else if ($search_type === "id") {
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
                        <input type="radio" name="search_type" value="" <?= $search_type === "" ? "checked" : ""; ?>>
                        <span>Search by Name</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="search_type" value="email" <?= $search_type === "email" ? "checked" : ""; ?>>
                        <span>Search by Email</span>
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
            <div class="custom-select">
                <label class="selected-text"></label>
                <div class="options">
                    <label class="option">
                        <input checked type="radio" name="filter_status" value="" <?= $filter_status === "" ? "checked" : ""; ?>>
                        <span>All Statuses</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="filter_status" value="verified" <?= $filter_status === "verified" ? "checked" : ""; ?>>
                        <span>Verified</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="filter_status" value="unverified" <?= $filter_status === "unverified" ? "checked" : ""; ?>>
                        <span>Unverified</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="filter_status" value="banned" <?= $filter_status === "banned" ? "checked" : ""; ?>>
                        <span>Banned</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="filter_status" value="to_delete" <?= $filter_status === "to_delete" ? "checked" : ""; ?>>
                        <span>To Delete</span>
                    </label>
                </div>
            </div>
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
</form>

<div class="container manage-container manage-table-container">
    <table>
        <thead>
            <tr>
                <th data-sort="" class="<?= $sort === "" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">ID</th>
                <th data-sort="name" class="<?= $sort === "name" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Name</th>
                <th data-sort="email" class="<?= $sort === "email" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Email</th>
                <th data-sort="status" class="<?= $sort === "status" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Status</th>
                <th data-sort="date" class="<?= $sort === "date" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Creation Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pager->result as $customer) : ?>
                <tr>
                    <td><?= $customer->id ?></td>
                    <td><?= htmlspecialchars($customer->name) ?></td>
                    <td><?= $customer->email ?></td>
                    <td>
                        <div class="status-badge status-<?= $customer->status; ?>"></div>
                    </td>
                    <td><?= toDateFormat($customer->creation_time); ?></td>
                    <td>
                        <div class="button-group-inline">
                            <a href="/edit-customer?id=<?= $customer->id; ?>" class="link-button button-yellow">Edit</a>
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