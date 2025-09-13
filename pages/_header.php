<?php
require_once __DIR__ . "/_base.php";

$notify = true;
if ($request === "notice") {
	$notify = false;
	setcookie("last_notice_visit", date("Y-m-d H:i:s"), 0, "/");
	if ($account_verified) {
		$stm = $db->prepare("UPDATE account SET last_notice_visit = NOW() WHERE id = ?");
		$stm->execute([$account_obj->id]);
	}
} else {
	$stm = $db->prepare("SELECT creation_time FROM notice ORDER BY creation_time DESC LIMIT 1");
	$stm->execute();
	if ($stm->rowCount() === 1) {
		$last_notice_time = new DateTime($stm->fetchColumn());

		if (isset($_COOKIE["last_notice_visit"])) {
			$notify = new DateTime($_COOKIE["last_notice_visit"]) < $last_notice_time;
		}

		if ($account_verified && $notify && isset($account_obj->last_notice_visit) && new DateTime($account_obj->last_notice_visit) > $last_notice_time) {
			$notify = false;
		}
	} else {
		$notify = false;
	}
}
?>

<header>
	<div id="header-container">
		<a id="header-home-button" href="/"></a>
		<form id="header-search-form" action="/search" method="GET">
			<input type="text" name="keyword" placeholder="Search for products ..." value="<?= htmlspecialchars($keyword ?? ""); ?>">
			<button type="submit"></button>
		</form>
		<a href="/cart" class="header-button" id="header-cart-button"></a>
		<a href="/notice" class="header-button" id="header-notice-button">
			<div id="header-notice-badge" <?= $notify ? "class='show'" : "" ?>>!</div>
		</a>
		<div class="header-button" id="header-account-button">
			<img id="header-account-image" src="<?= $account_verified && $account_obj->photo ? ("/uploads/account/" . $account_obj?->photo) : "/src/img/icon/pfp.png"; ?>" alt="Profile">
			<div id="header-account-dropdown">
				<?php if ($account_verified && $account_type->name === "Customer"): ?>
					<a href="/account">Account</a>
					<a href="/device">Device</a>
					<a href="/order-history">Order History</a>
					<a href="/wallet">Wallet</a>
					<a href="/voucher">Voucher</a>
					<a href="/favourite">Favourite</a>
					<div id="logout-button">Logout</div>
				<?php else: ?>
					<a href="/login">Login</a>
					<a href="/signup">Sign Up</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</header>