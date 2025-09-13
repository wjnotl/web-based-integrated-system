<?php
require_once __DIR__ . "/../_base.php";

$title = "Add Notice - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form class="container detail-container" enctype="multipart/form-data">
    <div class="title">Add Notice</div>
    <div class="content three-column-content">
        <div><label for="title">Title</label></div>
        <div><label for="title">:</label></div>
        <div class="form-group">
            <div class="form-input">
                <input type="text" name="title" id="title">
            </div>
            <div class="form-error" data-error="title"></div>
        </div>
        <div><label for="content">Content</label></div>
        <div><label for="content">:</label></div>
        <div class="form-group">
            <textarea id="content" rows="10" name="content" maxlength="2000"></textarea>
            <div class="form-error" data-error="content"></div>
        </div>
        <div><label for="link">Link</label></div>
        <div><label for="link">:</label></div>
        <div class="form-group">
            <div class="form-input">
                <input type="text" name="link" id="link">
            </div>
            <div class="form-error" data-error="link"></div>
        </div>
        <div><label for="link_text">Link Text</label></div>
        <div><label for="link_text">:</label></div>
        <div class="form-group">
            <div class="form-input">
                <input type="text" name="link_text" id="link_text">
            </div>
            <div class="form-error" data-error="link_text"></div>
        </div>
        <div>Photo</div>
        <div>:</div>
        <div class="form-group">
            <input type="file" id="picture-file" name="photo" accept="image/bmp, image/x-ms-bmp, image/jpeg, image/png, image/webp" />
            <div id="picture-wrapper">
                <div id="picture-preview-container">
                    <img id="picture-preview" src="" alt="Notice Picture" class="vertical-fit">
                </div>
            </div>
            <div class="button-group">
                <button type="button" id="upload-picture-button">Upload</button>
                <button type="button" id="remove-picture-button" class="button-red" disabled>Remove</button>
            </div>
            <div class="form-error" data-error="photo"></div>
        </div>
        <div>Poll</div>
        <div>:</div>
        <div class="form-group">
            <div class="poll-container">
            </div>
            <div class="input-group">
                <div class="form-input">
                    <input type="text" id="add-option-text" placeholder="Option Text...">
                </div>
                <button type="button" id="add-option-button" class="button-blue">Add Option</button>
            </div>
            <div class="form-error" data-error="poll"></div>
        </div>
    </div>
    <div class="button-group">
        <button type="submit">Add Notice</button>
    </div>
</form>

<div id="upload-overlay" class="overlay upload-overlay">
    <div class="content">
        <div class="overlay-header">
            <div class="title">Upload Notice Picture</div>
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