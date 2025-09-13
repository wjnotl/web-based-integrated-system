<?php
require_once __DIR__ . "/../_base.php";

$id = obtain_get("id");
if (!isset($id)) {
    redirect("/");
}

$stm = $db->prepare("SELECT  
                        p.name, 
                        p.price,
                        p.description,
                        p.photo, 
                        COALESCE(
                            SUM(CASE 
                            WHEN o.status <> 'Delivered' THEN 0 
                            ELSE oi.quantity END),
                        0) AS total_sold
                    FROM product p
                    JOIN product_variant pv ON p.id = pv.product_id
                    LEFT JOIN order_item oi ON pv.id = oi.product_variant_id
                    LEFT JOIN orders o ON oi.order_id = o.id
                    WHERE p.id = ?
                    GROUP BY p.name, p.price, p.photo");
$stm->execute([$id]);
if ($stm->rowCount() !== 1) {
    redirect("/");
}
$product = $stm->fetchObject();

$product_photos = [];
if (isset($product->photo)) {
    foreach (preg_split("/\r\n|\n|\r/", $product->photo) as $image) {
        $product_photos[] = $image;
    }
}

$stm = $db->prepare("SELECT * FROM product_variant WHERE product_id = ?");
$stm->execute([$id]);
$variants = $stm->fetchAll();

$variant_colours = [];
$variant_sizes = [];

foreach ($variants as $variant) {
    if (!isset($variant_colours[$variant->colour])) {
        $variant_colours[$variant->colour] = [];
    }
    $variant_colours[$variant->colour][] = $variant->size;

    if (!isset($variant_sizes[$variant->size])) {
        $variant_sizes[$variant->size] = [];
    }
    $variant_sizes[$variant->size][] = $variant->colour;
}

$favourite = false;
if ($account_verified) {
    $stm = $db->prepare("SELECT COUNT(*) FROM favourite WHERE product_id = ? AND account_id = ?");
    $stm->execute([$id, $account_obj->id]);
    if ($stm->fetchColumn() === 1) {
        $favourite = true;
    }
}


$stm = $db->prepare("SELECT 
                        COUNT(*) AS total_reviews,
                        COALESCE(AVG(rating), 0) AS avg_rating
                    FROM review
                    WHERE product_id = ?");
$stm->execute([$id]);
$bundled_reviews = $stm->fetchObject();

$bought_before = false;
$own_review_exist = false;
if ($account_verified) {
    $stm = $db->prepare("SELECT content, rating FROM review WHERE account_id = ? AND product_id = ?");
    $stm->execute([$account_obj->id, $id]);
    if ($stm->rowCount() === 1) {
        $own_review_exist = true;
        $own_review = $stm->fetchObject();
    }

    $stm = $db->prepare("SELECT COUNT(*) FROM order_item oi JOIN orders o ON oi.order_id = o.id 
    WHERE o.account_id = ? AND o.status = 'Delivered' AND oi.product_id = ?");
    $stm->execute([$account_obj->id, $id]);
    $bought_before = $stm->fetchColumn() > 0;

    $query = "SELECT
            a.name,
            a.photo,
            r.account_id,
            r.content,
            r.rating,
            r.creation_time,
            COUNT(DISTINCT lr.account_id) AS likes,
            MAX(
                CASE
                    WHEN lr.account_id = ? THEN 1
                    ELSE 0
                END
            ) AS liked_by_me,
            CASE
                WHEN r.account_id = ? THEN 1
                ELSE 2
            END AS my_review
        FROM review r
        JOIN account a ON r.account_id = a.id
        LEFT JOIN like_review lr ON (r.account_id = lr.reviewer_id AND r.product_id = lr.product_id)
        WHERE r.product_id = ?
        GROUP BY r.account_id, r.content, r.rating, r.creation_time, a.name, a.photo
        ORDER BY my_review, likes DESC, r.creation_time DESC
        LIMIT 15";

    $params = [$account_obj->id, $account_obj->id, $id];
} else {
    $query = "SELECT
            a.name,
            a.photo,
            r.account_id,
            r.content,
            r.rating,
            r.creation_time,
            COUNT(DISTINCT lr.account_id) AS likes,
            MAX(0) AS liked_by_me
        FROM review r
        JOIN account a ON r.account_id = a.id
        LEFT JOIN like_review lr ON (r.account_id = lr.reviewer_id AND r.product_id = lr.product_id)
        WHERE r.product_id = ?
        GROUP BY r.account_id, r.content, r.rating, r.creation_time, a.name, a.photo
        ORDER BY likes DESC, r.creation_time DESC
        LIMIT 15";

    $params = [$id];
}

$stm = $db->prepare($query);
$stm->execute($params);
$reviews = $stm->fetchAll();

$title = $product->name . " - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<style id="rating-star-value-style">
    :root {
        --rating-star-value: <?= $bundled_reviews->avg_rating; ?>;
    }
</style>

<div id="product-info-container" class="container product-detail-container" data-product-id="<?= htmlspecialchars($id); ?>">
    <div id="product-picture-container">
    
        <img id="product-picture" src="<?= isset($product->photo) ? "/uploads/product/" . $product_photos[0] : "/src/img/icon/product_no_image.png"; ?>" alt="<?= htmlspecialchars($product->name); ?>">
        <div id="product-gallery-container">
            <?php for ($i = 0; $i < count($product_photos); $i++): ?>
                <img <?php if ($i === 0) echo "class='selected'"; ?> src="/uploads/product/<?= $product_photos[$i]; ?>" alt="<?= htmlspecialchars($product->name); ?>">
            <?php endfor; ?>
        </div>
    </div>
    <form>
        <div id="product-name"><?= htmlspecialchars($product->name); ?></div>
        <div id="product-brief-review-container">
            <?php if ($bundled_reviews->avg_rating != 0): ?>
                <div class="rating-value"><?= toRatingFormat($bundled_reviews->avg_rating); ?></div>
                <div class="rating-stars-container">
                    <div class="empty-star">
                        <div class="star-setter">
                            <div class="star"></div>
                        </div>
                    </div>
                    <div class="empty-star">
                        <div class="star-setter">
                            <div class="star"></div>
                        </div>
                    </div>
                    <div class="empty-star">
                        <div class="star-setter">
                            <div class="star"></div>
                        </div>
                    </div>
                    <div class="empty-star">
                        <div class="star-setter">
                            <div class="star"></div>
                        </div>
                    </div>
                    <div class="empty-star">
                        <div class="star-setter">
                            <div class="star"></div>
                        </div>
                    </div>
                </div>
                <div class="divider-vertical"></div>
            <?php endif; ?>
            <div id="product-rating-count"><?= $bundled_reviews->total_reviews; ?></div>
            <div class="divider-vertical"></div>
            <div id="product-sold-count"><?= $product->total_sold; ?></div>
        </div>
        <div id="product-price"><?= toRMFormat($product->price); ?></div>
        <div id="input-grid-container">
            <div>Colour</div>
            <div class="custom-radio-container center-text">
                <?php foreach ($variant_colours as $colour => $sizes): ?>
                    <label>
                        <input type="radio" name="product_colour" value="<?= htmlspecialchars($colour); ?>" data-sizes="<?= htmlspecialchars(implode(",", $sizes)); ?>">
                        <div>
                            <div class="custom-radio-text"><?= htmlspecialchars($colour); ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <div>Size</div>
            <div class="custom-radio-container center-text">
                <?php foreach ($variant_sizes as $size => $colours): ?>
                    <label>
                        <input type="radio" name="product_size" value="<?= htmlspecialchars($size); ?>" data-colours="<?= htmlspecialchars(implode(",", $colours)); ?>">
                        <div>
                            <div class="custom-radio-text"><?= htmlspecialchars($size); ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="form-group">
                <label for="product-quantity">Quantity</label>
            </div>
            <div class="form-group">
                <div class="input-group">
                    <div class="form-input">
                        <input type="number" id="product-quantity" name="product_quantity" />
                    </div>
                    <div id="product-stock-count" data-stock=""></div>
                </div>

            </div>
        </div>
        <div class="form-group">
            <div class="form-error" data-error="product_quantity"></div>
        </div>
        <div id="product-info-buttons-container">
            <button id="add-to-cart-button" disabled type="submit">Add To Cart</button>
            <div id="add-to-favourite-button" class="<?= $favourite ? "favourite" : ""; ?>"></div>
        </div>
    </form>
</div>

<div class="container product-detail-container">
    <div class="title">Description</div>
    <div id="product-description"><?= htmlspecialchars($product->description); ?></div>
</div>

<div id="product-review-container" class="container product-detail-container" <?= $account_verified ? ("data-account-id='" . $account_obj->id . "'") : ""; ?>>
    <div class="title">Reviews</div>

    <div id="review-head-container">
        <div class="rating-value"><?= $bundled_reviews->avg_rating == 0 ? "?" : toRatingFormat($bundled_reviews->avg_rating); ?></div>
        <div class="rating-stars-container">
            <div class="empty-star">
                <div class="star-setter">
                    <div class="star"></div>
                </div>
            </div>
            <div class="empty-star">
                <div class="star-setter">
                    <div class="star"></div>
                </div>
            </div>
            <div class="empty-star">
                <div class="star-setter">
                    <div class="star"></div>
                </div>
            </div>
            <div class="empty-star">
                <div class="star-setter">
                    <div class="star"></div>
                </div>
            </div>
            <div class="empty-star">
                <div class="star-setter">
                    <div class="star"></div>
                </div>
            </div>
        </div>
        <div id="filter-review-container" class="custom-radio-container center-text specified-width">
            <label>
                <input type="radio" name="filter_review" value="all" checked>
                <div>
                    <div class="custom-radio-text">All</div>
                </div>
            </label>
            <label>
                <input type="radio" name="filter_review" value="5">
                <div>
                    <div class="custom-radio-text">5 Star</div>
                </div>
            </label>
            <label>
                <input type="radio" name="filter_review" value="4">
                <div>
                    <div class="custom-radio-text">4 Star</div>
                </div>
            </label>
            <label>
                <input type="radio" name="filter_review" value="3">
                <div>
                    <div class="custom-radio-text">3 Star</div>
                </div>
            </label>
            <label>
                <input type="radio" name="filter_review" value="2">
                <div>
                    <div class="custom-radio-text">2 Star</div>
                </div>
            </label>
            <label>
                <input type="radio" name="filter_review" value="1">
                <div>
                    <div class="custom-radio-text">1 Star</div>
                </div>
            </label>
        </div>
    </div>

    <div id="own-review-container">
        <img class="pfp-image" src="<?= $account_verified && $account_obj->photo ? ("/uploads/account/" . $account_obj?->photo) : "/src/img/icon/pfp.png"; ?>" alt="Profile">
        <div id="own-review-input-container">
            <div class="form-group">
                <div class="input-group">
                    <div data-rating="<?= $own_review_exist ? htmlspecialchars($own_review->rating) : 0; ?>" class="rating-stars-container">
                        <div class="empty-star">
                            <div class="star-setter">
                                <div class="star"></div>
                            </div>
                        </div>
                        <div class="empty-star">
                            <div class="star-setter">
                                <div class="star"></div>
                            </div>
                        </div>
                        <div class="empty-star">
                            <div class="star-setter">
                                <div class="star"></div>
                            </div>
                        </div>
                        <div class="empty-star">
                            <div class="star-setter">
                                <div class="star"></div>
                            </div>
                        </div>
                        <div class="empty-star">
                            <div class="star-setter">
                                <div class="star"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-error" data-error="own_rating"></div>
                </div>
            </div>
            <div class="form-group">
                <div class="input-group">
                    <div class="form-input">
                        <input id="own-review-input" type="text" maxlength="200" placeholder="<?= $account_verified ? ($bought_before ? "Add comment" : "You can only review after receiving the product") : "Please login to add comment";  ?>" value="<?= $own_review_exist ? htmlspecialchars($own_review->content) : ""; ?>" <?= (!$account_verified || $own_review_exist || !$bought_before) ? "readonly" : ""; ?>>
                    </div>
                    <div id="send-review-button" <?= (!$account_verified || $own_review_exist || !$bought_before) ? "" : "class='show'"; ?>></div>
                    <div id="delete-review-button" <?= $own_review_exist ? "class='show'" : ""; ?>></div>
                </div>
                <div class="form-error" data-error="own_comment"></div>
            </div>
        </div>
    </div>

    <div id="no-review">No reviews found</div>

    <?php foreach ($reviews as $review): ?>
        <div class="review-container" data-account-id="<?= $review->account_id; ?>" data-likes="<?= $review->likes; ?>" data-liked-by-me="<?= $review->liked_by_me; ?>">
            <img class="pfp-image" src="<?= $review->photo ? "/uploads/account/" . $review->photo : "/src/img/icon/pfp.png"; ?>" alt="Profile">
            <div class="user-info">
                <span class="name"><?= htmlspecialchars($review->name); ?></span>
                <div data-rating="<?= $review->rating; ?>" class="rating-stars-container">
                    <div class="empty-star">
                        <div class="star-setter">
                            <div class="star"></div>
                        </div>
                    </div>
                    <div class="empty-star">
                        <div class="star-setter">
                            <div class="star"></div>
                        </div>
                    </div>
                    <div class="empty-star">
                        <div class="star-setter">
                            <div class="star"></div>
                        </div>
                    </div>
                    <div class="empty-star">
                        <div class="star-setter">
                            <div class="star"></div>
                        </div>
                    </div>
                    <div class="empty-star">
                        <div class="star-setter">
                            <div class="star"></div>
                        </div>
                    </div>
                </div>
                <span class="log-entry"><?= timeAgo($review->creation_time); ?></span>
                <p class="comment"><?= htmlspecialchars($review->content); ?></p>
                <div class="helpful">
                    <div class="thumb-up <?= $review->liked_by_me ? "selected" : ""; ?>"></div>
                    <div class="thumb-up-count"><?= $review->likes; ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>