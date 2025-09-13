<?php
require_once __DIR__ . "/../_base.php";

$title = "Sign Up - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container container-center">
    <div class="title">Welcome to Superme Malaysia</div>
    <form action="" method="post">
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
                <input type="radio" name="gender" id="male" value="m"/>
                <label for="male">Male</label>
                <input type="radio" name="gender" id="female" value="f"/>
                <label for="female">Female</label>
                <input type="radio" name="gender" id="other" value="-"/>
                <label for="other">Other</label>
            </div>
            <div class="form-error" data-error="gender"></div>
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
            <label for="confirm_password">Confirm Password</label>
            <div class="form-input">
                <input type="password" id="confirm_password" name="confirm_password" />
                <input type="checkbox" class="password-show-button">
            </div>
            <div class="form-error" data-error="confirm_password"></div>
        </div>

        <div class="form-group">
            <button type="submit">Sign Up</button>
        </div>

        <div class="form-group">
            Have an account?
            <a href="/login">Login</a>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>