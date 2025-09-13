<?php
require_once __DIR__ . "/_base.php";
?>

<footer>
    <div id="footer-container">
        <div id="footer-copyright">Copyright © <?= date("Y"); ?> Superme Malaysia Sdn Bhd [201253407298 (980818-X)]. All Rights Reserved</div>
        <div id="footer-address">Lot 2, Jalan Mohd Salley, Persiaran Sukan, Seksyen 13, 40100 Shah Alam, Selangor Darul Ehsan</div>
        <div id="footer-links">
            <a href="/about-us" target="_blank">About Us</a>
            <a href="/privacy-policy" target="_blank">Privacy Policy</a>
            <a href="/contact-us" target="_blank">Contact Us</a>
        </div>
        <div id="footer-social-media">
            <a style="background-image: url(/src/img/icon/facebook.svg); " href="https://www.facebook.com/superme/" target="_blank"></a>
            <a style="background-image: url(/src/img/icon/instagram.svg); " href="https://www.instagram.com/superme/" target="_blank"></a>
            <a style="background-image: url(/src/img/icon/youtube.svg); " href="https://www.youtube.com/@superme" target="_blank"></a>
        </div>
    </div>
</footer>

<div class="toast-container" data-toast-message="<?= htmlspecialchars(temp("toast_message")); ?>"></div>

<div id="confirmation-popup" class="overlay">
    <div class="content">
        <div class="title"></div>
        <div class="button-group-inline">
            <button id="confirmation-popup-confirm"></button>
            <button class="button-red" id="confirmation-popup-cancel"></button>
        </div>
    </div>
</div>