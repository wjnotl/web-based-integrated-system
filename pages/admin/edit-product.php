<?php
require_once __DIR__ . "/../_base.php";

$id = obtain_get("id");

$stm = $db->prepare("SELECT * FROM product WHERE id = ?");
$stm->execute([$id]);

if ($stm->rowCount() !== 1) {
    temp("toast_message", "Product does not exist");
    redirect("/manage-product");
}

$product = $stm->fetchObject();
$product_photos = [];
if (isset($product->photo)) {
    foreach (preg_split("/\r\n|\n|\r/", $product->photo) as $image) {
        $product_photos[] = $image;
    }
}

$stm = $db->prepare("SELECT keyword FROM product_keyword WHERE product_id = ?");
$stm->execute([$id]);
$keywords = $stm->fetchAll(PDO::FETCH_COLUMN);

$stm = $db->prepare("SELECT * FROM product_variant WHERE product_id = ? ORDER BY colour");
$stm->execute([$id]);
$variants = $stm->fetchAll();

$categories = $db->query("SELECT id, name FROM category ORDER BY name");

$title = "Edit Product - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container detail-container">
    <div class="title">Edit Product</div>
    <form class="content three-column-content" enctype="multipart/form-data" data-id="<?= $product->id ?>">
        <div>ID</div>
        <div>:</div>
        <div><?= $product->id ?></div>
        <div>Name</div>
        <div>:</div>
        <div class="form-group">
            <div class="form-input">
                <input type="text" name="name" value="<?= htmlspecialchars($product->name) ?>">
            </div>
            <div class="form-error" data-error="name"></div>
        </div>
        <div><label for="description">Description</label></div>
        <div><label for="description">:</label></div>
        <div class="form-group">
            <textarea id="description" rows="10" name="description" maxlength="2000"><?= htmlspecialchars($product->description) ?></textarea>
            <div class="form-error" data-error="description"></div>
        </div>
        <div>Category</div>
        <div>:</div>
        <div class="form-group">
            <div class="custom-select">
                <label class="selected-text">Select Category</label>
                <div class="options">
                    <label class="option">
                        <input type="radio" name="category" value="" <?= !isset($product->category_id) ? "checked" : ""; ?>>    
                        <span>-</span>
                    </label>
                    <?php foreach ($categories as $category): ?>
                        <label class="option">
                            <input type="radio" name="category" value="<?= $category->id; ?>" <?= $category->id === $product->category_id ? "checked" : ""; ?>>
                            <span><?= htmlspecialchars($category->name); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-error" data-error="category"></div>
        </div>
        <div><label for="price">Price (RM)</label></div>
        <div><label for="price">:</label></div>
        <div class="form-group">
            <div class="form-input">
                <input type="text" name="price" value="<?= toRMFormat($product->price); ?>" placeholder="0.00" data-rm-format="true">
            </div>
            <div class="form-error" data-error="price"></div>
        </div>
        <div>Picture</div>
        <div>:</div>
        <div class="form-group">
            <input type="file" id="picture-file" name="photos[]" accept="image/bmp, image/x-ms-bmp, image/jpeg, image/png, image/webp" multiple />
            <input type="checkbox" id="change-picture-checkbox" name="change_picture" />
            <div id="picture-galary">
                <?php foreach ($product_photos as $index => $photo): ?>
                    <div class="picture-wrapper" data-upload="">
                        <div class="picture-preview-container">
                            <img class="picture-preview vertical-fit" src="/uploads/product/<?= $photo ?>" alt="Product Picture">
                        </div>
                        <div class="delete-preview">Delete</div>
                    </div>
                <?php endforeach ?>
            </div>
            <div class="button-group">
                <button type="button" id="upload-picture-button">Upload</button>
                <button type="button" id="reset-picture-button" class="button-blue" disabled>Reset</button>
            </div>
            <div class="form-error" data-error="photo"></div>
        </div>
        <div>Keywords</div>
        <div>:</div>
        <div class="form-group">
            <div class="form-input">
                <input type="text" name="keyword" value="<?= htmlspecialchars(implode(", ", $keywords)); ?>">
            </div>
        </div>

        <div class="button-group">
            <button type="submit" disabled>Save Changes</button>
        </div>
    </form>
    <div id="variant-content" class="content three-column-content">
        <div>Variants</div>
        <div>:</div>
        <div class="form-group">
            <table>
                <thead>
                    <tr>
                        <th>Colour</th>
                        <th>Size</th>
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($variants as $variant) : ?>
                        <tr data-id="<?= $variant->id; ?>">
                            <td><?= htmlspecialchars($variant->colour) ?></td>
                            <td><?= htmlspecialchars($variant->size) ?></td>
                            <td>
                                <div class="form-group">
                                    <div class="form-input">
                                        <input type="number" name="variant_stock" value="<?= $variant->stock; ?>">
                                    </div>
                                    <div class="form-error" data-error="variant_stock_<?= $variant->id; ?>"></div>
                                </div>
                            </td>
                            <td>
                                <div class="button-group-inline">
                                    <button class="button-red delete-variant-button">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="button-group">
                <button id="add-variant-button" class="button-blue" type="button">Add Variant</button>
            </div>
        </div>
    </div>

</div>

<div id="upload-overlay" class="overlay upload-overlay">
    <div class="content">
        <div class="overlay-header">
            <div class="title">Upload Product Picture</div>
            <div class="overlay-close"></div>
        </div>
        <div class="upload-drop-zone" class="show">
            <div class="text">Drag and drop your image here or click to select</div>
        </div>
        <div class="upload-preview-zone">
            <div class="upload-preview-container" class="show">
                <img class="upload-preview-image" alt="Preview" draggable="false" />
            </div>
            <input type="range" id="zoom-slider" min="1" max="2" step="0.01" />
            <button id="confirm-upload">Confirm</button>
        </div>
    </div>
</div>

<div id="variant-overlay" class="overlay">
    <div class="content">
        <div class="overlay-header">
            <div class="title">Add Variant</div>
            <div class="overlay-close"></div>
        </div>
        <form>
            <div class="form-group">
                <label for="variant-colour">Colour</label>
                <div class="form-input">
                    <input type="text" id="variant-colour" name="new_variant_colour">
                </div>
                <div class="form-error" data-error="new_variant_colour"></div>
            </div>
            <div class="form-group">
                <label for="variant-size">Size</label>
                <div class="form-input">
                    <input type="text" id="variant-size" name="new_variant_size">
                </div>
                <div class="form-error" data-error="new_variant_size"></div>
            </div>
            <div class="form-group">
                <label for="stock">Stock</label>
                <div class="form-input">
                    <input type="number" id="stock" name="new_variant_stock">
                </div>
                <div class="form-error" data-error="new_variant_stock"></div>
            </div>
            <button type="submit">Add Variant</button>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>