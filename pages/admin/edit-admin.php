<?php
require_once __DIR__ . "/../_base.php";

$id = obtain_get("id");

$stm = $db->prepare("SELECT a.*, t.name AS account_type_name FROM account a JOIN account_type t ON a.account_type_id = t.id WHERE a.id = ? AND t.name <> 'Customer'");
$stm->execute([$id]);

if ($stm->rowCount() != 1) {
    temp("toast_message", "Admin does not exist");
    redirect("/manage-admin");
}

$admin = $stm->fetchObject();
switch ($admin->gender) {
    case 'm':
        $gender = "Male";
        break;
    case 'f':
        $gender = "Female";
        break;
    case '-':
        $gender = "Other";
        break;
    default:
        $gender = "Unknown";
        break;
};

$admin_types = $db->query("SELECT * FROM account_type WHERE name <> 'Customer'")->fetchAll();

$title = "Edit Admin - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form class="container detail-container" data-id="<?= $admin->id ?>">
    <div class="title">Edit Admin</div>
    <div class="content three-column-content">
        <div>ID</div>
        <div>:</div>
        <div><?= $admin->id ?></div>
        <div>Name</div>
        <div>:</div>
        <div><?= htmlspecialchars($admin->name) ?></div>
        <div>Gender</div>
        <div>:</div>
        <div><?= $gender ?></div>

        <div><label for="email">Email</label></div>
        <div><label for="email">:</label></div>
        <?php if ($admin->email === $default_admin_email) : ?>
            <div><?= $admin->email ?></div>
        <?php else : ?>
            <div class="form-group">
                <div class="form-input">
                    <input id="email" type="text" name="email" value="<?= $admin->email ?>">
                </div>
                <div class="form-error" data-error="email"></div>
            </div>
        <?php endif; ?>

        <div>Admin Type</div>
        <div>:</div>
        <?php if ($admin->email === $default_admin_email) : ?>
            <div><?= $admin->account_type_name ?></div>
        <?php else : ?>
            <div class="form-group">
                <div class="custom-select">
                    <label class="selected-text">Select Admin Type</label>
                    <div class="options">
                        <?php foreach ($admin_types as $admin_type) : ?>
                            <label class="option">
                                <input type="radio" name="admin_type" value="<?= $admin_type->id ?>" <?= $admin->account_type_id == $admin_type->id ? "checked" : ""; ?>>
                                <span><?= $admin_type->name ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-error" data-error="admin_type"></div>
            </div>
        <?php endif; ?>

        <div>Profile Picture</div>
        <div>:</div>
        <div class="form-group">
            <img width="130" height="130" src="<?= $admin->photo ? ("/uploads/account/" . $admin?->photo) : "/src/img/icon/pfp.png"; ?>" alt="Profile" class="profile-pic">
        </div>
    </div>
    <?php if ($admin->email !== $default_admin_email) : ?>
        <div class="button-group">
            <button type="submit" disabled>Save Changes</button>
        </div>
    <?php endif; ?>
    <div class="button-group">
        <button id="reset-password-button" type="button" class="button-blue">Reset Password</button>
    </div>
    <div class="button-group">
        <button id="logout-from-all-devices-button" type="button" class="button-red">Log Out from All Devices</button>
    </div>
</form>

<?php
require_once __DIR__ . "/../_foot.php";
?>