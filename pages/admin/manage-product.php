<?php
require_once __DIR__ . "/../_base.php";

$page = (int)obtain_get("page", "1");
$search = obtain_get("search", "");
$search_type = obtain_get("search_type");
$filter_category = obtain_get("filter_category");
$filter_stock_status = obtain_get("filter_stock_status");
$start_date = obtain_get("start_date");
$end_date = obtain_get("end_date");
$sort = obtain_get("sort");
$desc = obtain_get("desc");

$categories = $db->query("SELECT id, name FROM category ORDER BY name");

$count_query = "
    SELECT COUNT(DISTINCT p.id)
    FROM product p
    LEFT JOIN category c ON c.id = p.category_id
    LEFT JOIN product_variant pv ON pv.product_id = p.id
";

$query = "
    SELECT p.*, c.name AS category_name,
        (CASE
            WHEN COALESCE(MIN(pv.stock), 0) = 0 AND COALESCE(MAX(pv.stock), 0) = 0 THEN 'unavailable'
            WHEN MIN(pv.stock) = 0 THEN 'partially-available'
            WHEN MIN(pv.stock) < 10 THEN 'limited'
            WHEN MIN(pv.stock) >= 10 THEN 'available'
            ELSE 'unknown'
        END) AS stock_status
    FROM product p
    LEFT JOIN category c ON c.id = p.category_id
    LEFT JOIN product_variant pv ON pv.product_id = p.id
";

if ($search_type === "id") {
    $query .= " WHERE p.id LIKE ?";
    $count_query .= " WHERE p.id LIKE ?";
} else {
    $search_type = "";
    $query .= " WHERE p.name LIKE ?";
    $count_query .= " WHERE p.name LIKE ?";
}
$params = ["%$search%"];

if ($filter_category === "-") {
    $query .= " AND p.category_id IS NULL";
    $count_query .= " AND p.category_id IS NULL";
} else if (isset($filter_category)) {
    $query .= " AND p.category_id = ?";
    $params[] = $filter_category;
    $count_query .= " AND p.category_id = ?";
}

if (isset($start_date) && isset($end_date) && isDateFormat($start_date) && isDateFormat($end_date)) {
    $query .= " AND DATE(p.creation_time) BETWEEN ? AND ?";
    $count_query .= " AND DATE(p.creation_time) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
} else if (isset($start_date) && isDateFormat($start_date)) {
    $query .= " AND DATE(p.creation_time) >= ?";
    $count_query .= " AND DATE(p.creation_time) >= ?";
    $params[] = $start_date;
} else if (isset($end_date) && isDateFormat($end_date)) {
    $query .= " AND DATE(p.creation_time) <= ?";
    $count_query .= " AND DATE(p.creation_time) <= ?";
    $params[] = $end_date;
}

$query .= " GROUP BY p.id";

if ($filter_stock_status === "unavailable") {
    $query .= " HAVING COALESCE(MIN(pv.stock), 0) = 0 AND COALESCE(MAX(pv.stock), 0) = 0";
    $count_query .= " HAVING COALESCE(MIN(pv.stock), 0) = 0 AND COALESCE(MAX(pv.stock), 0) = 0";
} else if ($filter_stock_status === "partially_available") {
    $query .= " HAVING MIN(pv.stock) = 0 AND MAX(pv.stock) <> 0";
    $count_query .= " HAVING MIN(pv.stock) = 0 AND MAX(pv.stock) <> 0";
} else if ($filter_stock_status === "limited") {
    $query .= " HAVING MIN(pv.stock) < 10 AND MAX(pv.stock) <> 0 AND MIN(pv.stock) <> 0";
    $count_query .= " HAVING MIN(pv.stock) < 10 AND MAX(pv.stock) <> 0 AND MIN(pv.stock) <> 0";
} else if ($filter_stock_status === "available") {
    $query .= " HAVING MIN(pv.stock) >= 10";
    $count_query .= " HAVING MIN(pv.stock) >= 10";
}

if ($sort === "name") {
    $query .= " ORDER BY p.name";
} else if ($sort === "category") {
    $query .= " ORDER BY c.name IS NULL, c.name";
} else if ($sort === "stock_status") {
    $query .= " ORDER BY stock_status";
} else if ($sort === "date") {
    $query .= " ORDER BY p.creation_time";
} else {
    $query .= " ORDER BY p.id";
    $sort = "";
}

if ($desc) {
    $query .= " DESC";
}

require_once __DIR__ . "/../../lib/SimplePager.php";
$pager = new SimplePager($query, $params, $count_query, $params, 10, $page);

$title = "Manage Product - Superme Malaysia";
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
            <div id="filter-category-select" class="custom-select">
                <label class="selected-text"></label>
                <div class="options">
                    <label class="option">
                        <input checked type="radio" name="filter_category" value="" <?= $filter_category === "" ? "checked" : ""; ?>>
                        <span>All Categories</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="filter_category" value="-" <?= $filter_category === "-" ? "checked" : ""; ?>>
                        <span>-</span>
                    </label>
                    <?php foreach ($categories as $category) : ?>
                        <label class="option">
                            <input type="radio" name="filter_category" value="<?= $category->id; ?>" <?= $filter_category == $category->id ? "checked" : ""; ?>>
                            <span><?= htmlspecialchars($category->name); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="custom-select">
                <label class="selected-text"></label>
                <div class="options">
                    <label class="option">
                        <input checked type="radio" name="filter_stock_status" value="" <?= $filter_stock_status === "" ? "checked" : ""; ?>>
                        <span>All Stock Statuses</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="filter_stock_status" value="available" <?= $filter_stock_status === "available" ? "checked" : ""; ?>>
                        <span>Available</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="filter_stock_status" value="limited" <?= $filter_stock_status === "limited" ? "checked" : ""; ?>>
                        <span>Limited</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="filter_stock_status" value="partially_available" <?= $filter_stock_status === "partially_available" ? "checked" : ""; ?>>
                        <span>Partially Available</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="filter_stock_status" value="unavailable" <?= $filter_stock_status === "unavailable" ? "checked" : ""; ?>>
                        <span>Unavailable</span>
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

    <div class="form-group">
        <a href="/add-product" class="link-button button-blue">Add Product</a>
    </div>
</form>

<div class="container manage-container manage-table-container">
    <table>
        <thead>
            <tr>
                <th data-sort="" class="<?= $sort === "" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">ID</th>
                <th data-sort="name" class="<?= $sort === "name" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Name</th>
                <th data-sort="category" class="<?= $sort === "category" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Category</th>
                <th data-sort="stock_status" class="<?= $sort === "stock_status" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Stock Status</th>
                <th data-sort="date" class="<?= $sort === "date" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Creation Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pager->result as $product) : ?>
                <tr>
                    <td><?= $product->id ?></td>
                    <td><?= htmlspecialchars($product->name) ?></td>
                    <td><?= isset($product->category_name) ? htmlspecialchars($product->category_name) : "-" ?></td>
                    <td>
                        <div class="status-badge status-<?= $product->stock_status; ?>"></div>
                    </td>
                    <td><?= toDateFormat($product->creation_time); ?></td>
                    <td>
                        <div class="button-group-inline">
                            <a href="/edit-product?id=<?= $product->id; ?>" class="link-button button-yellow">Edit</a>
                            <button type="button" class="button-red delete-product-button" data-id="<?= $product->id; ?>">Delete</button>
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