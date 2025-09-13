<?php
require_once __DIR__ . "/../_base.php";

$stm = $db->prepare("SELECT 
                        p.id, 
                        p.name, 
                        p.price, 
                        p.photo, 
                        COALESCE(AVG(r.rating), 0) AS avg_rating,
                        COALESCE(
                            SUM(CASE WHEN o.status <> 'Delivered' THEN 0
                            ELSE oi.quantity END),
                        0) AS total_sold
                    FROM favourite f
                    JOIN product p ON f.product_id = p.id
                    JOIN product_variant pv ON p.id = pv.product_id
                    LEFT JOIN review r ON p.id = r.product_id
                    LEFT JOIN order_item oi ON pv.id = oi.product_variant_id
                    LEFT JOIN orders o ON oi.order_id = o.id
                    WHERE f.account_id = ?
                    GROUP BY p.id, p.name, p.price, p.photo
                    ORDER BY f.creation_time DESC;");
$stm->execute([$account_obj->id]);
$favourites = $stm->fetchAll();

$title = "My Favourite - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div id="favourite-container" class="container">
    <div class="title">My Favourites</div>
    <div id="no-favourite">No favourites yet</div>
    <div class="items-container">
        <?php foreach ($favourites as $favourite): ?>
            <a class="product-container" href="/product-detail?id=<?= $favourite->id; ?>" data-product-id="<?= $favourite->id; ?>">
                <img class="product-image" src="<?= isset($favourite->photo) ? "/uploads/product/" . preg_split("/\r\n|\n|\r/", $favourite->photo)[0] : "/src/img/icon/product_no_image.png"; ?>" alt="<?= htmlspecialchars($favourite->name); ?>">
                <div class="product-info">
                    <div class="name"><?= htmlspecialchars($favourite->name); ?></div>
                    <div class="price"><?= toRMFormat($favourite->price); ?></div>
                    <div class="detail">
                        <div class="rating-image"></div>
                        <div class="rating-text"><?= $favourite->avg_rating == 0 ? "?" : toRatingFormat($favourite->avg_rating); ?></div>
                        <div class="sold"><?= $favourite->total_sold; ?></div>
                    </div>
                    <div class="buttons-container">
                        <div class="remove-button"></div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>