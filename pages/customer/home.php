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
                        0) AS total_sold,
                        COALESCE(
                            SUM(CASE WHEN (o.status = 'Delivered' AND o.creation_time >= NOW() - INTERVAL 7 DAY) THEN oi.quantity 
                            ELSE 0 END),
                        0) AS recent_total_sold
                    FROM product p
                    JOIN product_variant pv ON p.id = pv.product_id
                    LEFT JOIN review r ON p.id = r.product_id
                    LEFT JOIN order_item oi ON pv.id = oi.product_variant_id
                    LEFT JOIN orders o ON oi.order_id = o.id
                    GROUP BY p.id, p.name, p.price, p.photo
                    ORDER BY recent_total_sold DESC, total_sold DESC
                    LIMIT 6");
$stm->execute();
$trending_products = $stm->fetchAll();

$categories = $db->query("SELECT * FROM category");

$stm = $db->prepare("SELECT 
                        p.id, 
                        p.name, 
                        p.price, 
                        p.photo, 
                        COALESCE(AVG(r.rating), 0) AS avg_rating,
                        COALESCE(
                            SUM(CASE 
                            WHEN o.status <> 'Delivered' THEN 0
                            ELSE oi.quantity END),
                        0) AS total_sold
                    FROM product p
                    JOIN product_variant pv ON p.id = pv.product_id
                    LEFT JOIN review r ON p.id = r.product_id
                    LEFT JOIN order_item oi ON pv.id = oi.product_variant_id
                    LEFT JOIN orders o ON oi.order_id = o.id
                    GROUP BY p.id, p.name, p.price, p.photo
                    ORDER BY RAND()
                    LIMIT 24");
$stm->execute();
$random_products = $stm->fetchAll();

$title = "Home - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container home-container">
    <div class="title">Trending</div>
    <div class="items-container">
        <?php foreach ($trending_products as $product): ?>
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
</div>

<div class="container home-container">
    <div class="title">Category</div>
    <div class="items-container">
        <?php foreach ($categories as $category): ?>
            <a class="category-container" href="/search?category[]=<?= htmlspecialchars($category->name); ?>">
                <img class="category-image" src="/uploads/category/<?= $category->photo; ?>" alt="<?= htmlspecialchars($category->name); ?>">
                <div class="category-info">
                    <div class="category-name"><?= htmlspecialchars($category->name); ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="container home-container">
    <div class="title">Daily Discover</div>
    <div class="items-container">
        <?php foreach ($random_products as $product): ?>
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
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>