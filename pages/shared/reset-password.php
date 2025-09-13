<?php
require_once __DIR__ . "/../_base.php";

$token = obtain_get("token");

$db->query("DELETE from reset_password WHERE expire < NOW() - INTERVAL 1 DAY");

if (!isset($token)) {
    temp("toast_message", "Invalid token. Please check your email and try again");
    redirect("/forgot-password");
} else {
    $stm = $db->prepare("SELECT * FROM reset_password WHERE token = ?");
    $stm->execute([$token]);
    if ($stm->rowCount() === 1) {
        $reset_password_obj = $stm->fetchObject();
        if (new DateTime($reset_password_obj->expire) < new DateTime()) {
            $stm = $db->prepare("DELETE FROM reset_password WHERE token = ?");
            $stm->execute([$token]);
            temp("toast_message", "This reset password link has expired. Please request a new one");
            redirect("/forgot-password");
        }
    } else {
        temp("toast_message", "Invalid token. Please check your email or request a new one");
        redirect("/forgot-password");
    }
}

$title = "Reset Password - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container container-center">
    <div class="title">Reset Password</div>
    <form action="" method="post" data-token=<?= $token ?>>
        <div class="form-group">
            <label for="password">Password</label>
            <div class="form-input">
                <input type="password" id="password" name="password" />
                <input type="checkbox" class="password-show-button">
            </div>
            <div class="form-error" data-error="password"></div>
        </div>

        <div class="form-group">
            <label for="confirm-password">Confirm Password</label>
            <div class="form-input">
                <input type="password" id="confirm-password" name="confirm_password" />
                <input type="checkbox" class="password-show-button">
            </div>
            <div class="form-error" data-error="confirm_password"></div>
        </div>

        <div class="form-group">
            <button class="button-full" type="submit">Reset Password</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>