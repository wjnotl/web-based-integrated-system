<?php
require_once __DIR__ . "/../_base.php";

$categories = $db->query("SELECT name, id FROM category ORDER BY name");

$title = "Add Product - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form class="container detail-container" enctype="multipart/form-data">
    <div class="title">Add Product</div>
    <div class="content three-column-content">
        <div>Name</div>
        <div>:</div>
        <div class="form-group">
            <div class="form-input">
                <input type="text" name="name">
            </div>
            <div class="form-error" data-error="name"></div>
        </div>
        <div><label for="description">Description</label></div>
        <div><label for="description">:</label></div>
        <div class="form-group">
            <textarea id="description" rows="10" name="description" maxlength="2000"></textarea>
            <div class="form-error" data-error="description"></div>
        </div>
        <div>Category</div>
        <div>:</div>
        <div class="form-group">
            <div class="custom-select">
                <label class="selected-text">Select Category</label>
                <div class="options">
                    <label class="option">
                        <input type="radio" name="category" value="">    
                        <span>-</span>
                    </label>
                    <?php foreach ($categories as $category): ?>
                        <label class="option">
                            <input type="radio" name="category" value="<?= $category->id; ?>">
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
                <input type="text" name="price" placeholder="0.00" data-rm-format="true">
            </div>
            <div class="form-error" data-error="price"></div>
        </div>
        <div>Picture</div>
        <div>:</div>
        <div class="form-group">
            <input type="file" id="picture-file" name="photos[]" accept="image/bmp, image/x-ms-bmp, image/jpeg, image/png, image/webp" multiple />
            <div id="picture-galary"></div>
            <div class="button-group">
                <button type="button" id="upload-picture-button">Upload</button>
            </div>
            <div class="form-error" data-error="photo"></div>
        </div>
        <div>Keywords</div>
        <div>:</div>
        <div class="form-group">
            <div class="form-input">
                <input type="text" name="keyword">
            </div>
        </div>
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
                <tbody></tbody>
            </table>
            <div class="button-group">
                <button id="add-variant-button" class="button-blue" type="button">Add Variant</button>
            </div>
        </div>
    </div>
    <div class="button-group">
        <button type="submit">Add Product</button>
    </div>

</form>

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