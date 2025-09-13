<?php
require_once __DIR__ . '/../_base.php';

if (!isset($allow_access)) {
    redirect('/');
}

if ($navigate_access !== 2 && $request !== "session-verification") {
    temp("next", $_SERVER['REQUEST_URI']);
}

if ($navigate_access === 1) {
    if ($account_verified === false) {
        redirect("/login");
    }
} else if ($navigate_access === 2) {
    if ($account_verified === true) {
        if ($role === "customer") {
            redirect(temp("next") ?? "/");
        } else {
            redirect("/");
        }
    }
} else if ($navigate_access === 3) {
    if ($account_verified === false) {
        redirect("/login");
    }
}

function redirect($url = null)
{
    $url = $url ?? '/';
    header("Location: $url");
    exit;
}

function get_device_classname($device_type)
{
    switch ($device_type) {
        case 'Computer':
            return 'computer';
        case 'Mobile Phone':
            return 'phone';
        case 'Tablet':
            return 'tablet';
        default:
            return 'computer';
    }
}