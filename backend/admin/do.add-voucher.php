<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_voucher");

$return_value["errors"] = [
    "name" => null,
    "value" => null,
    "claim_limit" => null,
    "valid_days" => null,
    "expiry_date" => null
];

$name = obtain_post("name");
$value = obtain_post("value");
$claim_limit = obtain_post("claim_limit");
$valid_days = obtain_post("valid_days");
$expiry_date = obtain_post("expiry_date");
$for_signup = obtain_post("for_signup");

if (!isset($name)) {
    $return_value["success"] = false;
    $return_value["errors"]["name"] = "Name is required";
} else if (strlen($name) > 100) {
    $return_value["success"] = false;
    $return_value["errors"]["name"] = "Name cannot be longer than 100 characters";
}

if (!isset($value)) {
    $return_value["success"] = false;
    $return_value["errors"]["value"] = "Value is required";
} else if (!in_array($value, ["10", "20", "50", "100"])) {
    $return_value["success"] = false;
    $return_value["errors"]["value"] = "Invalid value";
}

if (isset($claim_limit)) {
    if (filter_var($claim_limit, FILTER_VALIDATE_INT) === false) {
        $return_value["success"] = false;
        $return_value["errors"]["claim_limit"] = "Claim limit must be an integer";
    } else if ((int)$claim_limit < 1) {
        $return_value["success"] = false;
        $return_value["errors"]["claim_limit"] = "Claim limit must be greater than 0";
    }
}

if (isset($valid_days)) {
    if (filter_var($valid_days, FILTER_VALIDATE_INT) === false) {
        $return_value["success"] = false;
        $return_value["errors"]["valid_days"] = "Valid days must be an integer";
    } else if ((int)$valid_days < 1) {
        $return_value["success"] = false;
        $return_value["errors"]["valid_days"] = "Valid days must be greater than 0";
    }
}

if (isset($expiry_date) && !isDateFormat($expiry_date)) {
    $return_value["success"] = false;
    $return_value["errors"]["expiry_date"] = "Invalid expiry date";
}

if (!isset($expiry_date) && !isset($valid_days)) {
    $return_value["success"] = false;
    $return_value["errors"]["valid_days"] = "Expiry date or valid days is required";
    $return_value["errors"]["expiry_date"] = "Expiry date or valid days is required";
}

$token = null;
if (!isset($for_signup)) {
    do {
        $token = randomString(150);
    } while (!is_unique($token, "voucher_template", "token"));
}

if ($return_value["success"]) {
    $stm = $db->prepare("INSERT INTO voucher_template (name, value, claim_limit, valid_days, expiry_date, for_signup, token, total_claimed) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
    $stm->execute([$name, $value, $claim_limit, $valid_days, $expiry_date, isset($for_signup) ? 1 : 0, $token]);
    temp("toast_message", "Voucher added successfully");
}

echo json_encode($return_value);
