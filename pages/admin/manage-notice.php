<?php
require_once __DIR__ . "/../_base.php";

$page = (int)obtain_get("page", "1");
$search = obtain_get("search", "");
$search_type = obtain_get("search_type");
$filter_text_checkbox = obtain_get("filter_text_checkbox");
$filter_link_checkbox = obtain_get("filter_link_checkbox");
$filter_photo_checkbox = obtain_get("filter_photo_checkbox");
$filter_poll_checkbox = obtain_get("filter_poll_checkbox");
$start_date = obtain_get("start_date");
$end_date = obtain_get("end_date");
$sort = obtain_get("sort");
$desc = obtain_get("desc");

$query = "SELECT n.*,
(CASE
    WHEN n.content IS NOT NULL AND n.photo IS NOT NULL AND COUNT(po.id) > 0 AND n.link IS NOT NULL AND n.link_text IS NOT NULL THEN  'Text + Link + Photo + Poll'
    WHEN n.content IS NOT NULL AND n.photo IS NOT NULL AND COUNT(po.id) > 0 THEN 'Text + Photo + Poll'
    WHEN n.content IS NOT NULL AND n.photo IS NOT NULL AND n.link IS NOT NULL AND n.link_text IS NOT NULL THEN  'Text + Link + Photo'
    WHEN n.content IS NOT NULL AND COUNT(po.id) > 0 AND n.link IS NOT NULL AND n.link_text IS NOT NULL THEN 'Text + Link + Poll'
    WHEN n.photo IS NOT NULL AND COUNT(po.id) > 0 AND n.link IS NOT NULL AND n.link_text IS NOT NULL THEN  'Link + Photo + Poll'
    WHEN n.content IS NOT NULL AND n.photo IS NOT NULL THEN 'Text + Photo'
    WHEN n.content IS NOT NULL AND COUNT(po.id) > 0 THEN 'Text + Poll'
    WHEN n.content IS NOT NULL AND n.link IS NOT NULL AND n.link_text IS NOT NULL THEN  'Text + Link'
    WHEN n.photo IS NOT NULL AND COUNT(po.id) > 0 THEN 'Photo + Poll'
    WHEN n.photo IS NOT NULL AND n.link IS NOT NULL AND n.link_text IS NOT NULL THEN  'Link + Photo'
    WHEN COUNT(po.id) > 0 AND n.link IS NOT NULL AND n.link_text IS NOT NULL THEN  'Link + Poll'
    WHEN n.content IS NOT NULL THEN 'Text'
    WHEN n.photo IS NOT NULL THEN 'Photo'
    WHEN COUNT(po.id) > 0 THEN 'Poll'
    WHEN n.link IS NOT NULL THEN 'Link'
    ELSE 'None'
END) AS content_type
FROM notice n
LEFT JOIN poll_option po ON po.notice_id = n.id";

$count_query = "SELECT COUNT(DISTINCT n.id)
FROM notice n
LEFT JOIN poll_option po ON po.notice_id = n.id";

if ($search_type === "id") {
    $query .= " WHERE n.id LIKE ?";
    $count_query .= " WHERE n.id LIKE ?";
} else {
    $search_type = "";
    $query .= " WHERE n.title LIKE ?";
    $count_query .= " WHERE n.title LIKE ?";
}
$params = ["%$search%"];

if (isset($start_date) && isset($end_date) && isDateFormat($start_date) && isDateFormat($end_date)) {
    $query .= " AND DATE(n.creation_time) BETWEEN ? AND ?";
    $count_query .= " AND DATE(n.creation_time) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
} else if (isset($start_date) && isDateFormat($start_date)) {
    $query .= " AND DATE(n.creation_time) >= ?";
    $count_query .= " AND DATE(n.creation_time) >= ?";
    $params[] = $start_date;
} else if (isset($end_date) && isDateFormat($end_date)) {
    $query .= " AND DATE(n.creation_time) <= ?";
    $count_query .= " AND DATE(n.creation_time) <= ?";
    $params[] = $end_date;
}

if ($filter_text_checkbox) {
    $query .= " AND n.content IS NOT NULL";
    $count_query .= " AND n.content IS NOT NULL";
}

if ($filter_link_checkbox) {
    $query .= " AND n.link IS NOT NULL AND n.link_text IS NOT NULL";
    $count_query .= " AND n.link IS NOT NULL AND n.link_text IS NOT NULL";
}

if ($filter_photo_checkbox) {
    $query .= " AND n.photo IS NOT NULL";
    $count_query .= " AND n.photo IS NOT NULL";
}

if ($filter_poll_checkbox) {
    $query .= " AND po.id IS NOT NULL";
    $count_query .= " AND po.id IS NOT NULL";
}

$query .= " GROUP BY n.id";

if ($sort === "title") {
    $query .= " ORDER BY n.title";
} else if ($sort === "content_type") {
    $query .= " ORDER BY content_type";
} else if ($sort === "date") {
    $query .= " ORDER BY n.creation_time";
} else {
    $query .= " ORDER BY n.id";
    $sort = "";
}

if ($desc) {
    $query .= " DESC";
}

require_once __DIR__ . "/../../lib/SimplePager.php";
$pager = new SimplePager($query, $params, $count_query, $params, 10, $page);

$title = "Manage Notice - Superme Malaysia";
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
                    $placeholder = "Search by Title...";
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
                        <span>Search by Title</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="search_type" value="id" <?= $search_type === "id" ? "checked" : ""; ?>>
                        <span>Search by ID</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div id="filter-notice-container" class="form-group">
        <div id="filter-notice-checkbox-container" class="input-group">
            <div class="input-group">
                <input type="checkbox" id="filter-text-checkbox" name="filter_text_checkbox" <?= $filter_text_checkbox ? "checked" : ""; ?>>
                <label for="filter-text-checkbox">Text</label>
            </div>
            <div class="input-group">
                <input type="checkbox" id="filter-link-checkbox" name="filter_link_checkbox" <?= $filter_link_checkbox ? "checked" : ""; ?>>
                <label for="filter-link-checkbox">Link</label>
            </div>
            <div class="input-group">
                <input type="checkbox" id="filter-photo-checkbox" name="filter_photo_checkbox" <?= $filter_photo_checkbox ? "checked" : ""; ?>>
                <label for="filter-photo-checkbox">Photo</label>
            </div>
            <div class="input-group">
                <input type="checkbox" id="filter-poll-checkbox" name="filter_poll_checkbox" <?= $filter_poll_checkbox ? "checked" : ""; ?>>
                <label for="filter-poll-checkbox">Poll</label>
            </div>
        </div>
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
        <a href="/add-notice" class="link-button button-blue">Add Notice</a>
    </div>
</form>

<div class="container manage-container manage-table-container">
    <table>
        <thead>
            <tr>
                <th data-sort="" class="<?= $sort === "" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">ID</th>
                <th data-sort="title" class="<?= $sort === "title" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Title</th>
                <th data-sort="content_type" class="<?= $sort === "content_type" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Content Type</th>
                <th data-sort="date" class="<?= $sort === "date" ? "selected" : "" ?> <?= $desc ? "desc" : "" ?>">Creation Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pager->result as $notice) : ?>
                <tr>
                    <td><?= $notice->id ?></td>
                    <td><?= htmlspecialchars($notice->title) ?></td>
                    <td class="content-type"><?= $notice->content_type; ?></td>
                    <td><?= toDateFormat($notice->creation_time); ?></td>
                    <td>
                        <div class="button-group-inline">
                            <a href="/edit-notice?id=<?= $notice->id ?>" class="link-button button-yellow">Edit</a>
                            <button class="button-red delete-notice-button" data-id="<?= $notice->id ?>">Delete</button>
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