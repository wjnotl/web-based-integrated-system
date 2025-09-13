<?php
require_once __DIR__ . "/../_base.php";

$page = (int)obtain_get("page", "1");
$search = obtain_get("search", "");
$search_type = obtain_get("search_type");
$start_date = obtain_get("start_date");
$end_date = obtain_get("end_date");
$sort = obtain_get("sort");
$desc = obtain_get("desc");

$count_query = "
    SELECT COUNT(*) FROM category c
";

$query = "
    SELECT c.*, COUNT(DISTINCT p.id) AS total_products
    FROM category c
    LEFT JOIN product p ON p.category_id = c.id
";

if ($search_type === "id") {
    $query .= " WHERE c.id LIKE ?";
    $count_query .= " WHERE c.id LIKE ?";
} else {
    $search_type = "";
    $query .= " WHERE c.name LIKE ?";
    $count_query .= " WHERE c.name LIKE ?";
}
$params = ["%$search%"];

if (isset($start_date) && isset($end_date) && isDateFormat($start_date) && isDateFormat($end_date)) {
    $query .= " AND DATE(c.creation_time) BETWEEN ? AND ?";
    $count_query .= " AND DATE(c.creation_time) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
} else if (isset($start_date) && isDateFormat($start_date)) {
    $query .= " AND DATE(c.creation_time) >= ?";
    $count_query .= " AND DATE(c.creation_time) >= ?";
    $params[] = $start_date;
} else if (isset($end_date) && isDateFormat($end_date)) {
    $query .= " AND DATE(c.creation_time) <= ?";
    $count_query .= " AND DATE(c.creation_time) <= ?";
    $params[] = $end_date;
}

$query .= " GROUP BY c.id";

if ($sort === "name") {
    $query .= " ORDER BY c.name";
} else if ($sort === "date") {
    $query .= " ORDER BY c.creation_time";
} else if ($sort === "total_products") {
    $query .= " ORDER BY total_products";
} else {
    $query .= " ORDER BY c.id";
    $sort = "";
}

if ($desc) {
    $query .= " DESC";
}

require_once __DIR__ . "/../../lib/SimplePager.php";
$pager = new SimplePager($query, $params, $count_query, $params, 10, $page);

$title = "Manage Category - Superme Malaysia";
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
        <a href="/add-category" class="link-button button-blue">Add Category</a>
    </div>
</form>

<div class="container manage-container manage-table-container">
    <table>
        <thead>
            <tr>
                <th data-sort="id" class="<?= $sort === "" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">ID</th>
                <th data-sort="name" class="<?= $sort === "name" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Name</th>
                <th data-sort="total_products" class="<?= $sort === "total_products" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Total Products</th>
                <th data-sort="date" class="<?= $sort === "date" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Creation Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pager->result as $category) : ?>
                <tr>
                    <td><?= $category->id ?></td>
                    <td><?= htmlspecialchars($category->name) ?></td>
                    <td><?= $category->total_products ?></td>
                    <td><?= toDateFormat($category->creation_time); ?></td>
                    <td>
                        <div class="button-group-inline">
                            <a href="/edit-category?id=<?= $category->id; ?>" class="link-button button-yellow">Edit</a>
                            <button class="button-red delete-category-button" data-id="<?= $category->id; ?>">Delete</button>
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