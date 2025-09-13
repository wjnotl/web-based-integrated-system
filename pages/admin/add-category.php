<?php
require_once __DIR__ . "/../_base.php";

$title = "Add Category - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form class="container detail-container" enctype="multipart/form-data">
    <div class="title">Add Category</div>
    <div class="content three-column-content">
        <div>Name</div>
        <div>:</div>
        <div class="form-group">
            <div class="form-input">
                <input type="text" name="name">
            </div>
            <div class="form-error" data-error="name"></div>
        </div>
        <div>Profile Picture</div>
        <div>:</div>
        <div class="form-group">
            <input type="file" id="picture-file" name="photo" accept="image/bmp, image/x-ms-bmp, image/jpeg, image/png, image/webp" />
            <div id="picture-wrapper">
                <div id="picture-preview-container">
                    <img id="picture-preview" src="" alt="Category Picture">
                </div>
            </div>
            <div class="button-group">
                <button type="button" id="upload-picture-button">Upload</button>
            </div>
            <div class="form-error" data-error="photo"></div>
        </div>
    </div>
    <div class="button-group">
        <button type="submit">Add Category</button>
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