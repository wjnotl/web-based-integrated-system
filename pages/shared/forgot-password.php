<?php
require_once __DIR__ . "/../_base.php";

$title = "Forgot Password - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container container-center">
    <div class="title">Forgot Password</div>
    <form action="" method="post">
        <div class="form-group">
            <label for="email">Email</label>
            <div class="form-input">
                <input type="text" id="email" name="email" />
            </div>
            <div class="form-error" data-error="email"></div>
        </div>

        <div class="form-group">
            <button class="button-full" type="submit">Send Reset Link</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>