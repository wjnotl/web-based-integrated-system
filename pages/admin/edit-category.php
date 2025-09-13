<?php
require_once __DIR__ . "/../_base.php";

$id = obtain_get("id");

$stm = $db->prepare("SELECT * FROM category WHERE id = ?");
$stm->execute([$id]);

if ($stm->rowCount() !== 1) {
    temp("toast_message", "Category does not exist");
    redirect("/manage-category");
}

$category = $stm->fetchObject();

$title = "Edit Category - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form class="container detail-container" enctype="multipart/form-data" data-id="<?= $category->id ?>">
    <div class="title">Edit Category</div>
    <div class="content three-column-content">
        <div>ID</div>
        <div>:</div>
        <div><?= $category->id ?></div>
        <div>Name</div>
        <div>:</div>
        <div class="form-group">
            <div class="form-input">
                <input type="text" name="name" value="<?= htmlspecialchars($category->name) ?>">
            </div>
            <div class="form-error" data-error="name"></div>
        </div>
        <div>Picture</div>
        <div>:</div>
        <div class="form-group">
            <input type="file" id="picture-file" name="photo" accept="image/bmp, image/x-ms-bmp, image/jpeg, image/png, image/webp" />
            <input type="checkbox" id="change-picture-checkbox" name="change_picture" />
            <div id="picture-wrapper">
                <div id="picture-preview-container">
                    <img id="picture-preview" src="/uploads/category/<?= $category->photo ?>" alt="Category Picture" class="vertical-fit">
                </div>
            </div>
            <div class="button-group">
                <button type="button" id="upload-picture-button">Upload</button>
                <button type="button" id="reset-picture-button" class="button-blue" disabled>Reset</button>
            </div>
            <div class="form-error" data-error="photo"></div>
        </div>
    </div>
    <div class="button-group">
        <button type="submit" disabled>Save Changes</button>
    </div>
</form>

<div id="upload-overlay" class="overlay upload-overlay">
    <div class="content">
        <div class="overlay-header">
            <div class="title">Upload Category Picture</div>
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

<?php
require_once __DIR__ . "/../_foot.php";
?>