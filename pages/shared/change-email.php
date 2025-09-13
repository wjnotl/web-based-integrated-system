<?php
require_once __DIR__ . "/../_base.php";

$token = obtain_get("token");

$db->query("DELETE from change_email WHERE expire < NOW() - INTERVAL 1 DAY");

if (!isset($token)) {
    temp("toast_message", "Invalid token. Please check your email and try again");
    redirect("/account");
} else {
    $stm = $db->prepare("SELECT * FROM change_email WHERE token = ?");
    $stm->execute([$token]);
    if ($stm->rowCount() === 1) {
        $change_email_obj = $stm->fetchObject();
        if (new DateTime($change_email_obj->expire) < new DateTime()) {
            $stm = $db->prepare("DELETE FROM change_email WHERE token = ?");
            $stm->execute([$token]);
            temp("toast_message", "This token has expired. Please request a new one");
            redirect("/account");
        }
    } else {
        temp("toast_message", "Invalid token. Please check your email or request a new one");
        redirect("/account");
    }
}

$title = "Change Email - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container container-center">
    <div class="title">Change Email</div>
    <form action="" method="post" data-token=<?= $token ?>>
        <div class="form-group">
            <label for="email">Email</label>
            <div class="form-input">
                <input type="text" id="email" name="email" />
            </div>
            <div class="form-error" data-error="email"></div>
        </div>

        <div class="form-group">
            <label for="confirm-email">Confirm Email</label>
            <div class="form-input">
                <input type="text" id="confirm-email" name="confirm_email" />
            </div>
            <div class="form-error" data-error="confirm_email"></div>
        </div>

        <div class="form-group">
            <button class="button-full" type="submit">Change Email</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>