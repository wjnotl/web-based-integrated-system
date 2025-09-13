<?php
require_once __DIR__ . "/../_base.php";

$page = (int)obtain_get("page", "1");
$search = obtain_get("search");
$search_type = obtain_get("search_type");
$filter_status = obtain_get("filter_status");
$start_date = obtain_get("start_date");
$end_date = obtain_get("end_date");
$sort = obtain_get("sort");
$desc = obtain_get("desc");

$count_query = "SELECT COUNT(DISTINCT o.id) FROM orders o LEFT JOIN account a ON a.id = o.account_id";
$query = "
    SELECT o.*, a.name AS customer_name,
        (CASE 
            WHEN o.status = 'Unpaid' THEN 'unpaid'
            WHEN o.status = 'Preparing' THEN 'preparing' 
            WHEN o.status = 'In Transit' THEN 'in-transit' 
            WHEN o.status = 'Delivered' THEN 'delivered' 
            WHEN o.status = 'Canceled' THEN 'canceled' 
            ELSE '' 
        END) AS status
    FROM orders o
    LEFT JOIN account a ON a.id = o.account_id";

if ($search_type === "customer_name") {
    $query .= " WHERE a.name LIKE ?";
    $count_query .= " WHERE a.name LIKE ?";
} else if ($search_type === "customer_email") {
    $query .= " WHERE a.email LIKE ?";
    $count_query .= " WHERE a.email LIKE ?";
} else if ($search_type === "receiver_name") {
    $query .= " WHERE o.name LIKE ?";
    $count_query .= " WHERE o.name LIKE ?";
} else if ($search_type === "receiver_contact_number") {
    $query .= " WHERE o.contact_number LIKE ?";
    $count_query .= " WHERE o.contact_number LIKE ?";
} else {
    $search_type = "";
    $query .= " WHERE o.id LIKE ?";
    $count_query .= " WHERE o.id LIKE ?";
}
$params = ["%$search%"];

if ($filter_status === "preparing") {
    $query .= " AND o.status = 'Preparing'";
    $count_query .= " AND o.status = 'Preparing'";
} else if ($filter_status === "in-transit") {
    $query .= " AND o.status = 'In Transit'";
    $count_query .= " AND o.status = 'In Transit'";
} else if ($filter_status === "delivered") {
    $query .= " AND o.status = 'Delivered'";
    $count_query .= " AND o.status = 'Delivered'";
} else if ($filter_status === "canceled") {
    $query .= " AND o.status = 'Canceled'";
    $count_query .= " AND o.status = 'Canceled'";
} else {
    $filter_status = "";
    $query .= " AND o.status <> 'Unpaid'";
    $count_query .= " AND o.status <> 'Unpaid'";
}

if (isset($start_date) && isset($end_date) && isDateFormat($start_date) && isDateFormat($end_date)) {
    $query .= " AND DATE(o.creation_time) BETWEEN ? AND ?";
    $count_query .= " AND DATE(o.creation_time) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
} else if (isset($start_date) && isDateFormat($start_date)) {
    $query .= " AND DATE(o.creation_time) >= ?";
    $count_query .= " AND DATE(o.creation_time) >= ?";
    $params[] = $start_date;
} else if (isset($end_date) && isDateFormat($end_date)) {
    $query .= " AND DATE(o.creation_time) <= ?";
    $count_query .= " AND DATE(o.creation_time) <= ?";
    $params[] = $end_date;
}

$query .= " GROUP BY o.id";

if ($sort === "customer_name") {
    $query .= " ORDER BY a.name IS NULL, a.name";
} else if ($sort === "receiver_name") {
    $query .= " ORDER BY o.name";
} else if ($sort === "status") {
    $query .= " ORDER BY o.status";
} else if ($sort === "order_date") {
    $query .= " ORDER BY o.creation_time";
} else {
    $query .= " ORDER BY o.id";
    $sort = "";
}

if ($desc) {
    $query .= " DESC";
}

require_once __DIR__ . "/../../lib/SimplePager.php";
$pager = new SimplePager($query, $params, $count_query, $params, 10, $page);

$title = "Manage Order - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form class="container manage-container manage-query-container">
    <div class="form-group">
        <div class="input-group search-container">
            <div class="form-input">
                <?php
                if ($search_type === "customer_name") {
                    $placeholder = "Search by Customer Name...";
                } else if ($search_type === "customer_email") {
                    $placeholder = "Search by Customer Email...";
                } else if ($search_type === "receiver_name") {
                    $placeholder = "Search by Receiver Name...";
                } else if ($search_type === "receiver_contact_number") {
                    $placeholder = "Search by Receiver Contact Number...";
                } else {
                    $placeholder = "Search by ID...";
                }
                ?>
                <input type="text" name="search" placeholder="<?= $placeholder; ?>" value="<?= htmlspecialchars($search); ?>" />
                <button type="submit"></button>
            </div>
            <div id="search-type-select" class="custom-select">
                <label class="selected-text"></label>
                <div class="options">
                    <label class="option">
                        <input checked type="radio" name="search_type" value="" <?= $search_type === "" ? "checked" : ""; ?>>
                        <span>Search by ID</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="search_type" value="customer_name" <?= $search_type === "customer_name" ? "checked" : ""; ?>>
                        <span>Search by Customer Name</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="search_type" value="customer_email" <?= $search_type === "customer_email" ? "checked" : ""; ?>>
                        <span>Search by Customer Email</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="search_type" value="receiver_name" <?= $search_type === "receiver_name" ? "checked" : ""; ?>>
                        <span>Search by Receiver Name</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="search_type" value="receiver_contact_number" <?= $search_type === "receiver_contact_number" ? "checked" : ""; ?>>
                        <span>Search by Receiver Contact Number</span>
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
                        <input type="radio" name="filter_status" value="preparing" <?= $filter_status === "preparing" ? "checked" : ""; ?>>
                        <span>Preparing</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="filter_status" value="in-transit" <?= $filter_status === "in-transit" ? "checked" : ""; ?>>
                        <span>In Transit</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="filter_status" value="delivered" <?= $filter_status === "delivered" ? "checked" : ""; ?>>
                        <span>Delivered</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="filter_status" value="canceled" <?= $filter_status === "canceled" ? "checked" : ""; ?>>
                        <span>Canceled</span>
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
                <th data-sort="customer_name" class="<?= $sort === "customer_name" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Customer Name</th>
                <th data-sort="receiver_name" class="<?= $sort === "receiver_name" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Receiver Name</th>
                <th data-sort="status" class="<?= $sort === "status" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Status</th>
                <th data-sort="order_date" class="<?= $sort === "order_date" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Order Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pager->result as $order) : ?>
                <tr>
                    <td><?= $order->id ?></td>
                    <td><?= isset($order->customer_name) ? htmlspecialchars($order->customer_name) : "-" ?></td>
                    <td><?= htmlspecialchars($order->name) ?></td>
                    <td>
                        <div class="status-badge status-<?= $order->status; ?>"></div>
                    </td>
                    <td><?= toDateFormat($order->creation_time); ?></td>
                    <td>
                        <div class="button-group-inline">
                            <a href="/edit-order?id=<?= $order->id; ?>" class="link-button button-yellow">Edit</a>
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