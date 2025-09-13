<?php
require_once __DIR__ . "/../_base.php";

$id = obtain_get("id");

$stm = $db->prepare("SELECT * FROM notice WHERE id = ?");
$stm->execute([$id]);

if ($stm->rowCount() != 1) {
    temp("toast_message", "Notice does not exist");
    redirect("/manage-notice");
}

$notice = $stm->fetchObject();

$stm = $db->prepare("SELECT po.*,
                        COUNT(pv.poll_option_id) AS vote_count
                    FROM poll_option po
                    LEFT JOIN poll_vote pv ON pv.poll_option_id = po.id
                    WHERE po.notice_id = ?
                    GROUP BY po.id");
$stm->execute([$id]);

if ($stm->rowCount() > 0) {
    $options = $stm->fetchAll();
    $option_vote_count = [];
    foreach ($options as $option) {
        $option_vote_count[] = $option->vote_count;
    }
    $options_vote_percentage = calculatePollPercentage($option_vote_count);
}

$title = "Edit Notice - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form class="container detail-container" data-id="<?= $notice->id ?>" enctype="multipart/form-data">
    <div class="title">Edit Notice</div>
    <div class="content three-column-content">
        <div>ID</div>
        <div>:</div>
        <div><?= $notice->id ?></div>
        <div>Title</div>
        <div>:</div>
        <div><?= htmlspecialchars($notice->title) ?></div>
        <div><label for="content">Content</label></div>
        <div><label for="content">:</label></div>
        <div class="form-group">
            <textarea id="content" rows="10" name="content" maxlength="2000"><?= htmlspecialchars($notice->content) ?></textarea>
            <div class="form-error" data-error="content"></div>
        </div>
        <div><label for="link">Link</label></div>
        <div><label for="link">:</label></div>
        <div class="form-group">
            <div class="form-input">
                <input type="text" name="link" id="link" value="<?= htmlspecialchars($notice->link) ?>" maxlength="300">
            </div>
            <div class="form-error" data-error="link"></div>
        </div>
        <div><label for="link_text">Link Text</label></div>
        <div><label for="link_text">:</label></div>
        <div class="form-group">
            <div class="form-input">
                <input type="text" name="link_text" id="link_text" value="<?= htmlspecialchars($notice->link_text) ?>" maxlength="50">
            </div>
            <div class="form-error" data-error="link_text"></div>
        </div>
        <div>Photo</div>
        <div>:</div>
        <div class="form-group">
            <input type="file" id="picture-file" name="photo" accept="image/bmp, image/x-ms-bmp, image/jpeg, image/png, image/webp" />
            <input type="checkbox" id="change-picture-checkbox" name="change_picture" />
            <input type="checkbox" name="remove_picture" id="remove-picture-checkbox" />
            <div id="picture-wrapper">
                <div id="picture-preview-container">
                    <img id="picture-preview" src="<?= isset($notice->photo) ? "/uploads/notice/" . $notice->photo : "" ?>" alt="Notice Picture" class="vertical-fit">
                </div>
            </div>
            <div class="button-group">
                <button type="button" id="upload-picture-button">Upload</button>
                <button type="button" id="remove-picture-button" class="button-red">Remove</button>
                <button type="button" id="reset-picture-button" class="button-blue" disabled>Reset</button>
            </div>
            <div class="form-error" data-error="photo"></div>
        </div>
        <div>Poll</div>
        <div>:</div>
        <div class="form-group">
            <div class="poll-container">
                <?php if (isset($options)) : ?>
                    <?php foreach ($options as $index => $option) : ?>
                        <div class="poll-option" data-vote-count="<?= $option_vote_count[$index]; ?>">
                            <div class="poll-input-box">
                                <div class="poll-text-container">
                                    <div class="poll-text"><?= htmlspecialchars($option->text); ?></div>
                                    <div class="poll-result-value"><?= $options_vote_percentage[$index]; ?>%</div>
                                </div>
                                <div class="poll-result-percentage" style="width: <?= $options_vote_percentage[$index] ?>%;"></div>
                            </div>
                            <button type="button" class="button-red remove-option-button" data-id="<?= $option->id; ?>">Remove</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
        <button type="submit" disabled>Save Changes</button>
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