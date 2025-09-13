<?php
require_once __DIR__ . "/../_base.php";

$keyword = obtain_get("keyword");
$filter_category = obtain_get("category");
$filter_real_category_id = [];
$filter_real_category_name = [];
$available_filter_category = [];
$min_price = obtain_get("min_price");
$max_price = obtain_get("max_price");
$rating = obtain_get("rating");
$page = (int)obtain_get("page", "1");
$sort = obtain_get("sort");
$sort_option = [
    'popular' => 'total_sold DESC',
    'latest' => 'p.creation_time DESC',
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC'
];

if (isset($filter_category) && is_array($filter_category) && !empty(array_filter($filter_category))) {
    $category_placeholder = implode(', ', array_fill(0, count($filter_category), '?'));
    $stm = $db->prepare("SELECT id, name FROM category WHERE name != '' AND name IN ($category_placeholder)");
    $stm->execute([...$filter_category]);
    $category_result = $stm->fetchAll();
    $filter_real_category_id = array_map(fn($category) => $category->id, $category_result);
    $filter_real_category_name = array_map(fn($category) => $category->name, $category_result);
}

if (!isset($rating) || !in_array($rating, ["1", "2", "3", "4", "5"])) {
    $rating = "";
}

if (!isset($sort) || !in_array($sort, ["latest", "popular", "price_low", "price_high"])) {
    $sort = "";
}

if (isset($max_price)) {
    if (!isRMFormat($max_price)) {
        $max_price_error = "Invalid max price format";
    } else if ((float)$max_price < 0) {
        $max_price_error = "Max price cannot be negative";
    }
}

if (isset($min_price)) {
    if (!isRMFormat($min_price)) {
        $min_price_error = "Invalid min price format";
    } else if ((float)$min_price < 0) {
        $min_price_error = "Min price cannot be negative";
    } else if (isset($max_price) && (float)$min_price > (float)$max_price) {
        $min_price_error = "Min price cannot be higher than max price";
    }
}

$search_result_for = "";
if (!isset($keyword)) {
    if (empty($filter_real_category_name)) {
        redirect("/");
    } else {
        $category_placeholder = implode(',', array_fill(0, count($filter_real_category_id), '?'));
        $query = "SELECT 
                    p.id, 
                    p.name, 
                    p.photo, 
                    p.price,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                COALESCE(
                    SUM(CASE WHEN o.status <> 'Delivered' THEN 0
                    ELSE oi.quantity END),
                0) AS total_sold
            FROM product p
            JOIN product_variant pv ON p.id = pv.product_id
            LEFT JOIN review r ON p.id = r.product_id
            LEFT JOIN order_item oi ON pv.id = oi.product_variant_id
            LEFT JOIN orders o ON oi.order_id = o.id
            WHERE p.category_id IN ($category_placeholder)";

        $count_query = "SELECT COUNT(DISTINCT p.id) FROM product p
            JOIN product_variant pv ON p.id = pv.product_id
            LEFT JOIN review r ON p.id = r.product_id
            WHERE p.category_id IN ($category_placeholder)";

        $params = [...$filter_real_category_id];

        // filter price
        if (!isset($min_price_error) && !isset($max_price_error)) {
            if (isset($max_price) && isset($min_price)) {
                $query .= " AND p.price BETWEEN ? AND ?";
                $count_query .= " AND p.price BETWEEN ? AND ?";
                $params[] = $min_price;
                $params[] = $max_price;
            } else if (isset($min_price)) {
                $query .= " AND p.price >= ?";
                $count_query .= " AND p.price >= ?";
                $params[] = $min_price;
            } else if (isset($max_price)) {
                $query .= " AND p.price <= ?";
                $count_query .= " AND p.price <= ?";
                $params[] = $max_price;
            }
        }

        // Group by product ID to calculate average rating
        $query .= " GROUP BY p.id, p.name, p.photo, p.price";

        // Filter rating
        $query .= " HAVING COALESCE(AVG(r.rating), 0) >= ?";
        $count_query .= " HAVING COALESCE(AVG(r.rating), 0) >= ?";
        $params[] = (int)$rating ?? 0;

        // sort
        $query .= " ORDER BY " . ($sort_option[$sort] ?? 'p.name ASC');

        // execute search query
        require_once __DIR__ . "/../../lib/SimplePager.php";
        $pager = new SimplePager($query, $params, $count_query, $params, 20, $page);

        // get available category
        $stm = $db->prepare("SELECT name FROM category WHERE name IS NOT NULL");
        $stm->execute();
        $available_filter_category = $stm->fetchAll(PDO::FETCH_COLUMN);

        $search_result_for = implode(", ", $filter_real_category_name);
    }
} else {
    $query = "SELECT p.id, p.name, p.photo, p.price,
            COALESCE(AVG(r.rating), 0) AS avg_rating,
            CASE
                WHEN p.name = ? THEN 7
                WHEN p.name LIKE ? THEN 6
                WHEN p.name LIKE ? THEN 5
                WHEN pk.keyword = ? THEN 4
                WHEN pk.keyword LIKE ? THEN 3
                WHEN pk.keyword LIKE ? THEN 2
                WHEN p.description LIKE ? THEN 1
                ELSE 0
            END AS relevance_score,
            COALESCE(
                SUM(CASE WHEN o.status <> 'Delivered' THEN 0
                ELSE oi.quantity END),
            0) AS total_sold
        FROM product p
        JOIN product_variant pv ON p.id = pv.product_id
        LEFT JOIN product_keyword pk ON p.id = pk.product_id
        LEFT JOIN review r ON p.id = r.product_id
        LEFT JOIN order_item oi ON pv.id = oi.product_variant_id
        LEFT JOIN orders o ON oi.order_id = o.id
        WHERE (p.name LIKE ? OR pk.keyword LIKE ? OR p.description LIKE ?)";

    $count_query = "SELECT COUNT(DISTINCT p.id), COALESCE(AVG(r.rating), 0) AS avg_rating
        FROM product p
        JOIN product_variant pv ON p.id = pv.product_id
        LEFT JOIN product_keyword pk ON p.id = pk.product_id
        LEFT JOIN review r ON p.id = r.product_id
        WHERE (p.name LIKE ? OR pk.keyword LIKE ? OR p.description LIKE ?)";

    $search_term = "%$keyword%";
    $search_term_start = "$keyword%";
    $params = [$keyword, $search_term_start, $search_term, $keyword, $search_term_start, $search_term, $search_term, $search_term, $search_term, $search_term];
    $count_params = [$search_term, $search_term, $search_term];

    // filter category
    if (!empty($filter_real_category_id)) {
        $category_placeholder = implode(',', array_fill(0, count($filter_real_category_id), '?'));
        $query .= " AND p.category_id IN ($category_placeholder)";
        $count_query .= " AND p.category_id IN ($category_placeholder)";
        array_push($params, ...$filter_real_category_id);
        array_push($count_params, ...$filter_real_category_id);
    }

    // filter price
    if (!isset($min_price_error) && !isset($max_price_error)) {
        if (isset($max_price) && isset($min_price)) {
            $query .= " AND p.price BETWEEN ? AND ?";
            $count_query .= " AND p.price BETWEEN ? AND ?";
            $params[] = $min_price;
            $params[] = $max_price;
            $count_params[] = $min_price;
            $count_params[] = $max_price;
        } else if (isset($min_price)) {
            $query .= " AND p.price >= ?";
            $count_query .= " AND p.price >= ?";
            $params[] = $min_price;
            $count_params[] = $min_price;
        } else if (isset($max_price)) {
            $query .= " AND p.price <= ?";
            $count_query .= " AND p.price <= ?";
            $params[] = $max_price;
            $count_params[] = $max_price;
        }
    }

    $query .= " GROUP BY p.id";

    // Filter rating
    $query .= " HAVING avg_rating >= ?";
    $count_query .= " HAVING COALESCE(AVG(r.rating), 0) >= ?";
    $params[] = (int)$rating ?? 0;
    $count_params[] = (int)$rating ?? 0;

    // sort
    $query .= " ORDER BY " . ($sort_option[$sort] ?? 'relevance_score DESC, p.name ASC');

    // execute search query
    require_once __DIR__ . "/../../lib/SimplePager.php";
    $pager = new SimplePager($query, $params, $count_query, $count_params, 20, $page);

    // get available category
    $stm = $db->prepare("
        SELECT DISTINCT c.name
        FROM category c
        JOIN product p ON p.category_id = c.id
        WHERE p.id IN (
            SELECT p.id
            FROM product p
            LEFT JOIN product_keyword pk ON p.id = pk.product_id
            WHERE (p.name LIKE ? OR pk.keyword LIKE ? OR p.description LIKE ?)
        )");
    $stm->execute([$search_term, $search_term, $search_term]);
    $available_filter_category = $stm->fetchAll(PDO::FETCH_COLUMN);

    $search_result_for = $keyword;
}

$stm = $db->prepare("
    SELECT p.* FROM product p
    JOIN product_keyword pk ON p.id = pk.product_id
    WHERE pk.keyword = ?
");
$stm->execute([$keyword]);
$results = $stm->fetchAll();

$title = "$search_result_for - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form id="search-form" action="" method="get">
    <div id="search-form-left-container">
        <div id="category-filter-container" class="filter-container">
            <div class="title">Category</div>
            <div class="content">
                <?php foreach ($available_filter_category as $category): ?>
                    <div>
                        <label>
                            <input type="checkbox" name="category[]" value="<?= htmlspecialchars($category); ?>" <?= in_array($category, $filter_real_category_name) ? "checked" : ""; ?>>
                            <span><?= htmlspecialchars($category); ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="category-filter-divider" class="divider-horizontal"></div>

        <div class="filter-container">
            <div class="title">Price Range</div>
            <div class="content">
                <div class="form-group">
                    <div class="form-input">
                        <input type="number" placeholder="RM MIN" name="min_price" value="<?= htmlspecialchars($min_price); ?>">
                    </div>
                    <span>—</span>
                    <div class="form-input">
                        <input type="number" placeholder="RM MAX" name="max_price" value="<?= htmlspecialchars($max_price); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <div class="form-error <?= $min_price_error ? "show" : ""; ?>"><?= $min_price_error; ?></div>
                        <div class="form-error <?= $max_price_error ? "show" : ""; ?>"><?= $max_price_error; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="divider-horizontal"></div>

        <div class="filter-container">
            <div class="title">Rating</div>
            <div class="content">
                <div>
                    <input type="radio" id="filter_rating_5" name="rating" value="5" <?= $rating === "5" ? "checked" : ""; ?>>
                    <label for="filter_rating_5">
                        <div class="filter_rating_number">5</div>
                        <div class="filter_rating_star"></div>
                    </label>
                </div>
                <div>
                    <input type="radio" id="filter_rating_4" name="rating" value="4" <?= $rating === "4" ? "checked" : ""; ?>>
                    <label for="filter_rating_4">
                        <div class="filter_rating_number">4</div>
                        <div class="filter_rating_star"></div>
                        <div>and above</div>
                    </label>
                </div>
                <div>
                    <input type="radio" id="filter_rating_3" name="rating" value="3" <?= $rating === "3" ? "checked" : ""; ?>>
                    <label for="filter_rating_3">
                        <div class="filter_rating_number">3</div>
                        <div class="filter_rating_star"></div>
                        <div>and above</div>
                    </label>
                </div>
                <div>
                    <input type="radio" id="filter_rating_2" name="rating" value="2" <?= $rating === "2" ? "checked" : ""; ?>>
                    <label for="filter_rating_2">
                        <div class="filter_rating_number">2</div>
                        <div class="filter_rating_star"></div>
                        <div>and above</div>
                    </label>
                </div>
                <div>
                    <input type="radio" id="filter_rating_1" name="rating" value="1" <?= $rating === "1" ? "checked" : ""; ?>>
                    <label for="filter_rating_1">
                        <div class="filter_rating_number">1</div>
                        <div class="filter_rating_star"></div>
                        <div>and above</div>
                    </label>
                </div>
                <div>
                    <input type="radio" id="filter_rating_0" name="rating" value="" <?= $rating === "" ? "checked" : ""; ?>>
                    <label for="filter_rating_0">
                        <div>Includes rated and unrated</div>
                    </label>
                </div>
            </div>
        </div>

        <div class="divider-horizontal"></div>

        <div class="filter-container">
            <button class="button-full">Apply</button>
        </div>
    </div>
    <div id="search-form-right-container">
        <div id="search-form-top-container">
            <div id="search-result">
                <span id="search-result-icon"></span>
                <span>Search result for '<?= htmlspecialchars($search_result_for); ?>'</span>
            </div>
            <div id="sort-result-container">
                <span>Sort by:</span>
                <div id="sort-result-select" class="custom-select">
                    <label class="selected-text"></label>
                    <div class="options">
                        <label class="option">
                            <input checked type="radio" name="sort" value="" <?= $sort === "" ? "checked" : ""; ?>>
                            <span>Best Match</span>
                        </label>
                        <label class="option">
                            <input type="radio" name="sort" value="popular" <?= $sort === "popular" ? "checked" : ""; ?>>
                            <span>Popular</span>
                        </label>
                        <label class="option">
                            <input type="radio" name="sort" value="latest" <?= $sort === "latest" ? "checked" : ""; ?>>
                            <span>Latest</span>
                        </label>
                        <label class="option">
                            <input type="radio" name="sort" value="price_low" <?= $sort === "price_low" ? "checked" : ""; ?>>
                            <span>Price - Low to High</span>
                        </label>
                        <label class="option">
                            <input type="radio" name="sort" value="price_high" <?= $sort === "price_high" ? "checked" : ""; ?>>
                            <span>Price - High to Low</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div id="search-form-bottom-container" class="items-container">
            <div id="no-search">No search result found</div>
            <?php foreach ($pager->result as $product): ?>
                <a class="product-container" href="/product-detail?id=<?= $product->id; ?>">
                    <img class="product-image" src="<?= isset($product->photo) ? "/uploads/product/" . preg_split("/\r\n|\n|\r/", $product->photo)[0] : "/src/img/icon/product_no_image.png"; ?>" alt="<?= htmlspecialchars($product->name); ?>">
                    <div class="product-info">
                        <div class="name"><?= htmlspecialchars($product->name); ?></div>
                        <div class="price"><?= toRMFormat($product->price); ?></div>
                        <div class="detail">
                            <div class="rating-image"></div>
                            <div class="rating-text"><?= $product->avg_rating == 0 ? "?" : toRatingFormat($product->avg_rating); ?></div>
                            <div class="sold"><?= $product->total_sold; ?></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div id="search-form-page-container" class="paging-container">
            <?php $pager->html($_GET); ?>
        </div>
    </div>
</form>

<?php
require_once __DIR__ . "/../_foot.php";
?>