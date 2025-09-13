<?php
require_once __DIR__ . "/../_base.php";

$admin_types = $db->query("SELECT * FROM account_type WHERE name <> 'Customer'")->fetchAll();

$title = "Add Admin - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container container-center">
    <div class="title">Add Admin</div>
    <form action="" method="post">
        <div class="form-group">
            <label>Admin Type</label>
            <div class="custom-select">
                <label class="selected-text">Select Admin Type</label>
                <div class="options">
                    <?php foreach ($admin_types as $admin_type) : ?>
                        <label class="option">
                            <input type="radio" name="admin_type" value="<?= $admin_type->id; ?>">
                            <span><?= $admin_type->name; ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-error" data-error="admin_type"></div>
        </div>

        <div class="form-group">
            <label for="name">Name</label>
            <div class="form-input">
                <input type="text" id="name" name="name" />
            </div>
            <div class="form-error" data-error="name"></div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <div class="form-input">
                <input type="text" id="email" name="email" />
            </div>
            <div class="form-error" data-error="email"></div>
        </div>

        <div class="form-group">
            <label>Gender</label>
            <div class="input-group">
                <input type="radio" name="gender" id="male" value="m" />
                <label for="male">Male</label>
                <input type="radio" name="gender" id="female" value="f" />
                <label for="female">Female</label>
                <input type="radio" name="gender" id="other" value="-" />
                <label for="other">Other</label>
            </div>
            <div class="form-error" data-error="gender"></div>
        </div>

        <div class="form-group">
            <button type="submit">Add Admin</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>