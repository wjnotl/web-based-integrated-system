<?php
require_once __DIR__ . "/../_base.php";

$title = "My Account - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div id="account-container" class="container">
    <div class="title">My Account</div>
    <form id="account-form" enctype="multipart/form-data">
        <div class="profile-container">
            <div id="profile-info">
                <div class="form-group">
                    <label for="name">Name</label>
                    <div class="form-input">
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($account_obj->name); ?>" />
                    </div>
                    <div class="form-error" data-error="name"></div>
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <div class="input-group">
                        <input type="radio" name="gender" id="male" value="m" <?= $account_obj->gender === "m" ? "checked" : ""; ?> />
                        <label for="male">Male</label>
                        <input type="radio" name="gender" id="female" value="f" <?= $account_obj->gender === "f" ? "checked" : ""; ?> />
                        <label for="female">Female</label>
                        <input type="radio" name="gender" id="other" value="-" <?= $account_obj->gender === "-" ? "checked" : ""; ?> />
                        <label for="other">Other</label>
                    </div>
                    <div class="form-error" data-error="gender"></div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <div class="form-input">
                            <input type="password" id="email" value="<?= htmlspecialchars($account_obj->email); ?>" readonly />
                            <input type="checkbox" class="password-show-button">
                        </div>
                        <?php if ($account_obj->email !== $default_admin_email) : ?>
                            <div id="change-email-button"></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" id="account-submit-button" class="button-full" disabled>Save Changes</button>
                    <button type="button" id="change-password-button" class="button-full button-blue">Change Password</button>
                    <?php if ($account_obj->email !== $default_admin_email) : ?>
                        <button type="button" id="delete-account-button" class="button-full button-red">Delete Account</button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="divider-vertical"></div>

            <div id="profile-picture">
                <input type="file" id="profile-picture-file" name="photo" accept="image/bmp, image/x-ms-bmp, image/jpeg, image/png, image/webp" />
                <input type="checkbox" id="remove-picture-checkbox" name="remove_picture" />
                <input type="checkbox" id="change-picture-checkbox" name="change_picture" />
                <div id="profile-preview-container">
                    <img id="profile-preview" src="<?= $account_obj->photo ? "/uploads/account/" . $account_obj->photo : "/src/img/icon/pfp.png"; ?>" alt="Profile" class="vertical-fit" />
                </div>
                <div class="form-group">
                    <div class="form-error" data-error="photo"></div>
                </div>
                <div class="button-group-inline">
                    <button type="button" id="upload-profile-picture-button">Upload</button>
                    <button type="button" id="remove-profile-picture-button" class="button-red">Remove</button>
                    <button type="button" id="reset-profile-picture-button" class="button-blue" disabled>Reset</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="upload-overlay" class="overlay upload-overlay">
    <div class="content">
        <div class="overlay-header">
            <div class="title">Upload Profile Picture</div>
            <div class="overlay-close"></div>
        </div>
        <div class="upload-drop-zone" class="show">
            <div class="text">Drag and drop your image here or click to select</div>
        </div>
        <div class="upload-preview-zone">
            <div class="upload-preview-container" class="show">
                <img class="upload-preview-image" alt="Preview" draggable="false" />
                <div class="pfp-circle"></div>
            </div>
            <input type="range" id="zoom-slider" min="1" max="2" step="0.01" />
            <button id="confirm-upload">Confirm</button>
        </div>
    </div>
</div>

<div id="password-overlay" class="overlay">
    <div class="content">
        <div class="overlay-header">
            <div class="title">Change Password</div>
            <div class="overlay-close"></div>
        </div>
        <form>
            <div class="form-group">
                <label for="current-password">Current Password</label>
                <div class="form-input">
                    <input type="password" id="current-password" name="current_password" />
                    <input type="checkbox" class="password-show-button">
                </div>
                <div class="form-error" data-error="current_password"></div>
            </div>
            <div class="form-group">
                <label for="new-password">New Password</label>
                <div class="form-input">
                    <input type="password" id="new-password" name="new_password" />
                    <input type="checkbox" class="password-show-button">
                </div>
                <div class="form-error" data-error="new_password"></div>
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm New Password</label>
                <div class="form-input">
                    <input type="password" id="confirm-password" name="confirm_password" />
                    <input type="checkbox" class="password-show-button">
                </div>
                <div class="form-error" data-error="confirm_password"></div>
            </div>
            <button type="submit" class="button-full">Update Password</button>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>