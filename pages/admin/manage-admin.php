<?php
require_once __DIR__ . "/../_base.php";

$page = (int)obtain_get("page", "1");
$search = obtain_get("search", "");
$search_type = obtain_get("search_type");
$filter_admin_type = obtain_get("filter_admin_type");
$start_date = obtain_get("start_date");
$end_date = obtain_get("end_date");
$sort = obtain_get("sort");
$desc = obtain_get("desc");

$account_types = $db->query("
    SELECT id, name 
    FROM account_type WHERE name <> 'Customer'
    ORDER BY name
")->fetchAll();

$query = "
    FROM account a 
    JOIN account_type t ON a.account_type_id = t.id
    WHERE t.name <> 'Customer'
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

if (in_array($filter_admin_type, array_map(fn($row) => $row->id, $account_types))) {
    $query .= " AND a.account_type_id = ?";
    $params[] = $filter_admin_type;
} else {
    $filter_admin_type = "";
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
} else if ($sort === "admin_type") {
    $query .= " ORDER BY t.name";
} else if ($sort === "date") {
    $query .= " ORDER BY a.creation_time DESC";
} else {
    $query .= " ORDER BY a.id";
    $sort = "";
}

if ($desc) {
    $query .= " DESC";
}

require_once __DIR__ . "/../../lib/SimplePager.php";
$pager = new SimplePager("SELECT a.*, t.name AS admin_type $query", $params, "SELECT COUNT(*) $query", $params, 10, $page);

$title = "Manage Admin - Superme Malaysia";
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
                        <input checked type="radio" name="filter_admin_type" value="" <?= $filter_admin_type === "" ? "checked" : ""; ?>>
                        <span>All Admin Types</span>
                    </label>
                    <?php foreach ($account_types as $account_type): ?>
                        <label class="option">
                            <input type="radio" name="filter_admin_type" value="<?= $account_type->id; ?>" <?= $filter_admin_type === $account_type->id ? "checked" : ""; ?>>
                            <span><?= $account_type->name; ?></span>
                        </label>
                    <?php endforeach; ?>
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

    <div class="form-group">
        <a href="/add-admin" class="link-button button-blue">Add Admin</a>
    </div>
</form>

<div class="container manage-container manage-table-container">
    <table>
        <thead>
            <tr>
                <th data-sort="" class="<?= $sort === "" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">ID</th>
                <th data-sort="name" class="<?= $sort === "name" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Name</th>
                <th data-sort="email" class="<?= $sort === "email" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Email</th>
                <th data-sort="admin_type" class="<?= $sort === "admin_type" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Admin Type</th>
                <th data-sort="date" class="<?= $sort === "date" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Creation Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pager->result as $admin) : ?>
                <tr>
                    <td><?= $admin->id ?></td>
                    <td><?= htmlspecialchars($admin->name) ?></td>
                    <td><?= $admin->email ?></td>
                    <td class="admin-type"><?= $admin->admin_type; ?></td>
                    <td><?= toDateFormat($admin->creation_time); ?></td>
                    <td>
                        <div class="button-group-inline">
                            <a href="/edit-admin?id=<?= $admin->id; ?>" class="link-button button-yellow">Edit</a>
                            <?php if ($admin->email !== $default_admin_email) : ?>
                                <button class="button-red delete-admin-button" data-id="<?= $admin->id; ?>">Delete</button>
                            <?php endif; ?>
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