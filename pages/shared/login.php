<?php
require_once __DIR__ . "/../_base.php";

$title = "Login - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container container-center">
    <div class="title">Welcome to Superme Malaysia</div>
    <form action="" method="post">
        <div class="form-group">
            <label for="email">Email</label>
            <div class="form-input">
                <input type="text" id="email" name="email" />
            </div>
            <div class="form-error" data-error="email"></div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="form-input">
                <input type="password" id="password" name="password" />
                <input type="checkbox" class="password-show-button">
            </div>
            <div class="form-error" data-error="password"></div>
        </div>

        <div class="form-group">
            <button type="submit">Login</button>
            <a class="float-right" href="/forgot-password">Forgot Password?</a>
        </div>

        <div class="form-group">
            New User?
            <a href="/signup">Sign Up</a>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>