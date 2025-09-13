<?php
require_once __DIR__ . "/../_base.php";

$token = obtain_get("token");
if (!isset($token)) {
    temp("toast_message", "Invalid token. Please check your email and try again");
    redirect("/account");
} else {
    $stm = $db->prepare("SELECT da.expire, t.name AS account_type_name
    FROM delete_account da 
    JOIN account a ON da.account_id = a.id 
    JOIN account_type t ON a.account_type_id = t.id 
    WHERE da.token = ?");
    $stm->execute([$token]);
    if ($stm->rowCount() === 1) {
        $delete_account_obj = $stm->fetchObject();
        if (new DateTime($delete_account_obj->expire) < new DateTime()) {
            $stm = $db->prepare("DELETE FROM delete_account WHERE token = ?");
            $stm->execute([$token]);

            temp("toast_message", "This token has expired. Please request a new one");
            redirect("/account");
        }
    } else {
        temp("toast_message", "Invalid token. Please check your email or request a new one");
        redirect("/account");
    }
}

$title = "Delete Account - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container container-center">
    <div class="title">Confirm Delete Account</div>
    <form action="" method="post" data-token=<?= $token ?>>
        <div class="form-group">
            <?php if ($account_type->name === "Customer"): ?>
                <div>By confirming, your account will be scheduled for deletion in <strong>7 days</strong>.</div>
            <?php else: ?>
                <div>By confirming, your account will be deleted.</div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <div>This action is permanent and cannot be undone.</div>
        </div>
        <?php if ($account_type->name === "Customer"): ?>
            <div class="form-group">
                <div>If you change your mind, simply <a href="/login">login</a> again within 7 days to cancel the deletion.</div>
            </div>
        <?php endif; ?>
        <button class="button-full button-red" type="submit">Confirm Delete Account</button>
    </form>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>