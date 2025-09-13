<?php
require_once __DIR__ . "/../_base.php";

$id = obtain_get("id");

$stm = $db->prepare("SELECT a.* FROM account a JOIN account_type t ON a.account_type_id = t.id WHERE a.id = ? AND t.name = 'Customer'");
$stm->execute([$id]);

if ($stm->rowCount() != 1) {
    temp("toast_message", "Customer does not exist");
    redirect("/manage-customer");
}

$customer = $stm->fetchObject();
switch ($customer->gender) {
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

$title = "Edit Customer - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<form class="container detail-container" data-id="<?= $customer->id ?>">
    <div class="title">Edit Customer</div>
    <div class="content three-column-content">
        <div>ID</div>
        <div>:</div>
        <div><?= $customer->id ?></div>
        <div>Name</div>
        <div>:</div>
        <div><?= htmlspecialchars($customer->name) ?></div>
        <div>Gender</div>
        <div>:</div>
        <div><?= $gender ?></div>
        <div><label for="email">Email</label></div>
        <div><label for="email">:</label></div>
        <div class="form-group">
            <div class="form-input">
                <input id="email" type="text" name="email" value="<?= $customer->email ?>">
            </div>
            <div class="form-error" data-error="email"></div>
        </div>
        <div><label for="banned">Banned</label></div>
        <div><label for="banned">:</label></div>
        <div class="form-group">
            <div class="input-group">
                <input id="banned" type="checkbox" name="banned" <?= $customer->is_banned ? "checked" : ""; ?>>
            </div>
        </div>
        <div>Profile Picture</div>
        <div>:</div>
        <div class="form-group">
            <img width="130" height="130" src="<?= $customer->photo ? ("/uploads/account/" . $customer?->photo) : "/src/img/icon/pfp.png"; ?>" alt="Profile" class="profile-pic">
        </div>
    </div>
    <div class="button-group">
        <button type="submit" disabled>Save Changes</button>
    </div>
    <?php if (isset($customer->pending_delete_expire)) : ?>
        <div class="button-group">
            <button id="revoke-deletion-button" type="button" class="button-yellow">Revoke Deletion</button>
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