<?php
require_once __DIR__ . "/../_base.php";

header("Content-Type: application/json"); // Set response type to JSON
header("Access-Control-Allow-Origin: *"); // Allow cross-origin

$return_value = [
    "success" => true,
    "login" => !$account_verified
];

function only_verified_account()
{
    global $account_verified;
    global $return_value;
    if (!$account_verified) {
        $return_value["success"] = false;
        echo json_encode($return_value);
        exit;
    }
}

function only_customer()
{
    global $account_verified;
    global $account_type;
    global $return_value;
    if (!$account_verified || $account_type->name !== "Customer") {
        $return_value["success"] = false;
        echo json_encode($return_value);
        exit;
    }
}

function only_admin($manage_value)
{
    global $account_verified;
    global $account_type;
    global $return_value;
    if (!$account_verified || !property_exists($account_type, $manage_value) || $account_type->$manage_value === 0) {
        $return_value["success"] = false;
        echo json_encode($return_value);
        exit;
    }
}

function get_session($account_id)
{
    $address = get_address();
    $device_info = get_device_info();

    global $db;
    $stm = $db->prepare("SELECT * FROM session 
                        WHERE account_id = ? AND is_verified = 1 AND address = ? AND device_os = ? AND device_type = ? AND browser = ? AND expire > NOW()
                        ");
    $stm->execute([$account_id, $address, $device_info["os"], $device_info["device"], $device_info["browser"]]);
    return $stm->fetchObject();
}

function create_session($account_id)
{
    $address = get_address();
    $device_info = get_device_info();

    global $db;
    $stm = $db->prepare("SELECT * FROM session WHERE account_id = ?");
    $stm->execute([$account_id]);
    $session_obj = $stm->fetchAll();
    foreach ($session_obj as $session) {
        if ($session->address === $address && $session->device_os === $device_info["os"] && $session->device_type === $device_info["device"] && $session->browser === $device_info["browser"]) {
            $stm = $db->prepare("DELETE FROM session WHERE id = ?");
            $stm->execute([$session->id]);
        }
    }

    $session_id = null;
    do {
        $session_id = randomString(30);
    } while (!is_unique($session_id, "session", "id"));

    $session_token = null;
    do {
        $session_token = randomString(150);
    } while (!is_unique($session_token, "session", "token"));

    $otp = randomOTP(6);
    $stm = $db->prepare("INSERT INTO session (id, token, otp, is_verified, expire, address, device_os, device_type, browser, last_login_time, account_id)
                            VALUES (?, ?, ?, 0, ADDTIME(NOW(), '00:05:00'), ?, ?, ?, ?, NOW(), ?)");
    $stm->execute([$session_id, $session_token, $otp, get_address(), $device_info["os"], $device_info["device"], $device_info["browser"], $account_id]);

    return [
        "session_id" => $session_id,
        "session_token" => $session_token,
        "otp" => $otp
    ];
}

function randomOTP($length = 6)
{
    $min = pow(10, $length - 1);  // 100000
    $max = pow(10, $length) - 1;  // 999999
    return (string)random_int($min, $max);
}

function generate_token_from_card($card_number, $card_cvc, $card_expiry)
{
    /*
    curl https://api.stripe.com/v1/tokens \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -H "Authorization: Bearer public_key" \
    -d "card[number]"=4242424242424242 \
    -d "card[exp_month]"=5 \
    -d "card[exp_year]"=2026 \
    -d "card[cvc]"=314
    */
    global $stripe_public_key;

    $card_expiry = explode("/", $card_expiry);

    $card_details = [
        "card[number]" => $card_number,
        "card[exp_month]" => $card_expiry[0],
        "card[exp_year]" => $card_expiry[1],
        "card[cvc]" => $card_cvc,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/tokens');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        "Authorization: Bearer $stripe_public_key",
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($card_details));
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    $return_value = [
        "errors" => [
            "card_number" => null,
            "card_cvc" => null,
            "card_expiry" => null
        ],
        "success" => true,
        "card_token" => null,
        "card_brand" => null,
        "card_last4" => null
    ];

    if (isset($result["error"])) {
        $return_value["success"] = false;
        if (isset($result["error"]["code"])) {
            switch ($result["error"]["code"]) {
                case "invalid_expiry_year":
                    $return_value["errors"]["card_expiry"] = "Invalid expiry year";
                    break;
                case "invalid_expiry_month":
                    $return_value["errors"]["card_expiry"] = "Invalid expiry month";
                    break;
                case "incorrect_number":
                    $return_value["errors"]["card_number"] = "Card number is invalid";
                    break;
                case "invalid_cvc":
                    $return_value["errors"]["card_cvc"] = "Card CVC is invalid";
                    break;
                case "processing_error":
                    $return_value["errors"]["card_number"] = "Processing error, please try again";
                    break;
                case "card_declined":
                    $return_value["errors"]["card_number"] = "Card declined, please try again";
                    break;
                default:
                    $return_value["errors"]["card_number"] = $result["error"]["message"];
                    break;
            }
        } else {
            $return_value["errors"]["card_number"] = "Unknown error, please try again";
        }
    } else if (!isset($result["id"]) || !isset($result["card"]) || !isset($result["card"]["brand"]) || !isset($result["card"]["last4"])) {
        $return_value["success"] = false;
        $return_value["errors"]["card_number"] = "Unknown error, please try again";
    } else {
        $return_value["card_token"] = $result["id"];
        $return_value["card_brand"] = $result["card"]["brand"];
        $return_value["card_last4"] = $result["card"]["last4"];
    }

    return $return_value;
}

function charge_card($amount, $card_token, $description)
{
    /*
    curl https://api.stripe.com/v1/charges \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -H "Authorization: Bearer secret_key" \
    -d "amount"=5000 \
    -d "currency"="myr" \
    -d "source"="token" \
    -d "description"="order_id"
    */

    global $stripe_secret_key;

    $payment_details = [
        "amount" => $amount * 100,
        "currency" => "myr",
        "source" => $card_token,
        "description" => $description
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/charges');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        "Authorization: Bearer $stripe_secret_key",
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payment_details));
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    $return_value = [
        "errors" => [
            "card_number" => null,
            "card_cvc" => null,
            "card_expiry" => null
        ],
        "success" => true,
        "charged" => false
    ];

    if (isset($result["error"])) {
        $return_value["success"] = false;
        if (isset($result["error"]["code"]) && isset($result["error"]["message"])) {
            switch ($result["error"]["code"]) {
                case "card_declined":
                case "incorrect_number":
                case "processing_error":
                    $return_value["errors"]["card_number"] = $result["error"]["message"];
                    break;
                case "expired_card":
                    $return_value["errors"]["card_expiry"] = $result["error"]["message"];
                    break;
                case "incorrect_cvc":
                    $return_value["errors"]["card_cvc"] = $result["error"]["message"];
                    break;
                default:
                    $return_value["errors"]["card_number"] = $result["error"]["message"];
                    break;
            }
        } else {
            $return_value["errors"]["card_number"] = "Unknown error, please try again";
        }
    } else if (!isset($result["id"])) {
        $return_value["success"] = false;
        $return_value["errors"]["card_number"] = "Unknown error, please try again";
    } else {
        $return_value["charged"] = true;
    }

    return $return_value;
}

function sessionVerification($email, $name, $session_id, $otp, $signup = false)
{

    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    $m = get_mail();
    $m->addAddress($email, $name);
    $m->addEmbeddedImage(__DIR__ . "/../src/img/icon/superme.png", "superme_pfp");
    $m->isHTML(true);

    $m->Subject = "Verify Your Session - Superme Malaysia";
    $m->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; background-color: #ffffff;'>
            <div style='text-align: center;'>
                <img src='cid:superme_pfp' alt='Superme Logo' style='width: 80px;'>
                <h2 style='color: #333;'>" . ($signup ? "Sign-up Verification" : "Login Verification") . "</h2>
                <p style='font-size: 16px; color: #555;'>
                    Hello, we detected a new " . ($signup ? "sign-up" : "login") . " attempt to your <strong>Superme Malaysia</strong> account.
                    Please verify this session to continue.
                </p>
        
                <a href='$protocol" . $_SERVER['HTTP_HOST'] .  "/session-verification?id=$session_id&otp=$otp' style='display: inline-block; margin: 20px 0; padding: 12px 25px; background-color: #74c637; color: white; text-decoration: none; border-radius: 6px; font-size: 16px;'>
                    Verify Session
                </a>
        
                <p style='font-size: 15px; color: #555; margin-top: 20px;'>
                    Or use this code:
                </p>
                <div style='font-size: 28px; font-weight: bold; color: #74c637; margin: 10px 0;'>$otp</div>
        
                <p style='font-size: 13px; color: #999;'>
                    This code is valid for 5 minutes. If you didn't attempt to " . ($signup ? "sign-up" : "login") . ", please ignore this message.
                </p>
        
                <hr style='margin: 30px 0;'>
                <p style='font-size: 12px; color: #aaa;'>
                    &copy; " . date('Y') . " Superme Malaysia. All rights reserved.
                </p>
            </div>
        </div>
    ";
    $m->send();
}

function resetPassword($email, $name, $token)
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    $m = get_mail();
    $m->addAddress($email, $name);
    $m->addEmbeddedImage(__DIR__ . "/../src/img/icon/superme.png", "superme_pfp");
    $m->isHTML(true);

    $m->Subject = "Reset Password - Superme Malaysia";
    $m->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; background-color: #ffffff;'>
            <div style='text-align: center;'>
                <img src='cid:superme_pfp' alt='Superme Logo' style='width: 80px;'>
                <h2 style='color: #333;'>Reset Password</h2>
                <p style='font-size: 16px; color: #555;'>
                    Hello, we detected a password reset request for your <strong>Superme Malaysia</strong> account.
                    Please click the button below to continue.
                </p>
        
                <a href='$protocol" . $_SERVER['HTTP_HOST'] .  "/reset-password?token=$token' style='display: inline-block; margin: 20px 0; padding: 12px 25px; background-color: #74c637; color: white; text-decoration: none; border-radius: 6px; font-size: 16px;'>
                    Reset Password
                </a>
        
                <p style='font-size: 13px; color: #999;'>
                    This link is valid for 5 minutes. If you didn't request this reset, you can safely ignore this message.
                </p>
        
                <hr style='margin: 30px 0;'>
                <p style='font-size: 12px; color: #aaa;'>
                    &copy; " . date('Y') . " Superme Malaysia. All rights reserved.
                </p>
            </div>
        </div>
    ";
    $m->send();
}

function changeEmail($email, $name, $token)
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    $m = get_mail();
    $m->addAddress($email, $name);
    $m->addEmbeddedImage(__DIR__ . "/../src/img/icon/superme.png", "superme_pfp");
    $m->isHTML(true);

    $m->Subject = "Change Email - Superme Malaysia";
    $m->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; background-color: #ffffff;'>
            <div style='text-align: center;'>
                <img src='cid:superme_pfp' alt='Superme Logo' style='width: 80px;'>
                <h2 style='color: #333;'>Change Email</h2>
                <p style='font-size: 16px; color: #555;'>
                    Hello, we detected a change email request for your <strong>Superme Malaysia</strong> account.
                    Please click the button below to continue.
                </p>
        
                <a href='$protocol" . $_SERVER['HTTP_HOST'] .  "/change-email?token=$token' style='display: inline-block; margin: 20px 0; padding: 12px 25px; background-color: #74c637; color: white; text-decoration: none; border-radius: 6px; font-size: 16px;'>
                    Change Email
                </a>
        
                <p style='font-size: 13px; color: #999;'>
                    This link is valid for 5 minutes. If you didn't request this change, you can safely ignore this message.
                </p>
        
                <hr style='margin: 30px 0;'>
                <p style='font-size: 12px; color: #aaa;'>
                    &copy; " . date('Y') . " Superme Malaysia. All rights reserved.
                </p>
            </div>
        </div>
    ";
    $m->send();
}

function requestDeleteAccount($email, $name, $token)
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    $m = get_mail();
    $m->addAddress($email, $name);
    $m->addEmbeddedImage(__DIR__ . "/../src/img/icon/superme.png", "superme_pfp");
    $m->isHTML(true);

    $m->Subject = "Request Delete Account - Superme Malaysia";
    $m->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; background-color: #ffffff;'>
            <div style='text-align: center;'>
                <img src='cid:superme_pfp' alt='Superme Logo' style='width: 80px;'>
                <h2 style='color: #333;'>Request Delete Account</h2>
                <p style='font-size: 16px; color: #555;'>
                    Hello, we detected a request to delete your <strong>Superme Malaysia</strong> account.
                    Please click the button below to continue.
                </p>
        
                <a href='$protocol" . $_SERVER['HTTP_HOST'] .  "/delete-account?token=$token' style='display: inline-block; margin: 20px 0; padding: 12px 25px; background-color: #74c637; color: white; text-decoration: none; border-radius: 6px; font-size: 16px;'>
                    Delete Account
                </a>
        
                <p style='font-size: 13px; color: #999;'>
                    This link is valid for 5 minutes. If you didn't request this delete, you can safely ignore this message.
                </p>
        
                <hr style='margin: 30px 0;'>
                <p style='font-size: 12px; color: #aaa;'>
                    &copy; " . date('Y') . " Superme Malaysia. All rights reserved.
                </p>
            </div>
        </div>
    ";
    $m->send();
}

function accountDeletion($email, $name)
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    $m = get_mail();
    $m->addAddress($email, $name);
    $m->addEmbeddedImage(__DIR__ . "/../src/img/icon/superme.png", "superme_pfp");
    $m->isHTML(true);

    $m->Subject = "Account Deletion - Superme Malaysia";
    $m->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; background-color: #ffffff;'>
            <div style='text-align: center;'>
                <img src='cid:superme_pfp' alt='Superme Logo' style='width: 80px;'>
                <h2 style='color: #333;'>Account Deletion</h2>
                <p style='font-size: 16px; color: #555;'>
                    Hello, this is to inform you that your account will be deleted after 7 days.
                    If you wish to keep your account, please login again.
                </p>

                <a href='$protocol" . $_SERVER['HTTP_HOST'] .  "/login' style='display: inline-block; margin: 20px 0; padding: 12px 25px; background-color: #74c637; color: white; text-decoration: none; border-radius: 6px; font-size: 16px;'>
                    Login
                </a>
        
                <hr style='margin: 30px 0;'>
                <p style='font-size: 12px; color: #aaa;'>
                    &copy; " . date('Y') . " Superme Malaysia. All rights reserved.
                </p>
            </div>
        </div>
    ";
    $m->send();
}

function passwordChanged($email, $name)
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    $m = get_mail();
    $m->addAddress($email, $name);
    $m->addEmbeddedImage(__DIR__ . "/../src/img/icon/superme.png", "superme_pfp");
    $m->isHTML(true);

    $m->Subject = "Password Changed - Superme Malaysia";
    $m->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; background-color: #ffffff;'>
            <div style='text-align: center;'>
                <img src='cid:superme_pfp' alt='Superme Logo' style='width: 80px;'>
                <h2 style='color: #333;'>Password Changed</h2>
                <p style='font-size: 16px; color: #555;'>
                    Hello, this is to inform you that your password has been changed. If you did not change your password, please change your password immediately by clicking the button below.
                </p>

                <a href='$protocol" . $_SERVER['HTTP_HOST'] .  "/forgot-password' style='display: inline-block; margin: 20px 0; padding: 12px 25px; background-color: #74c637; color: white; text-decoration: none; border-radius: 6px; font-size: 16px;'>
                    Change Password
                </a>
        
                <hr style='margin: 30px 0;'>
                <p style='font-size: 12px; color: #aaa;'>
                    &copy; " . date('Y') . " Superme Malaysia. All rights reserved.
                </p>
            </div>
        </div>
    ";
    $m->send();
}

function emailChanged($email, $name, $new_email)
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    $m = get_mail();
    $m->addAddress($email, $name);
    $m->addEmbeddedImage(__DIR__ . "/../src/img/icon/superme.png", "superme_pfp");
    $m->isHTML(true);

    $m->Subject = "Email Changed - Superme Malaysia";
    $m->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; background-color: #ffffff;'>
            <div style='text-align: center;'>
                <img src='cid:superme_pfp' alt='Superme Logo' style='width: 80px;'>
                <h2 style='color: #333;'>Email Changed</h2>
                <p style='font-size: 16px; color: #555;'>
                    Hello, this is to inform you that your email has been changed from <strong>$email</strong> to </strong>$new_email</strong>. If you did not change your email, please contact us immediately by clicking the button below.
                </p>

                <a href='$protocol" . $_SERVER['HTTP_HOST'] .  "/contact-us' style='display: inline-block; margin: 20px 0; padding: 12px 25px; background-color: #74c637; color: white; text-decoration: none; border-radius: 6px; font-size: 16px;'>
                    Contact Us
                </a>
        
                <hr style='margin: 30px 0;'>
                <p style='font-size: 12px; color: #aaa;'>
                    &copy; " . date('Y') . " Superme Malaysia. All rights reserved.
                </p>
            </div>
        </div>
    ";
    $m->send();
}

function orderConfirmed($email, $name, $order_id)
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    $m = get_mail();
    $m->addAddress($email, $name);
    $m->addEmbeddedImage(__DIR__ . "/../src/img/icon/superme.png", "superme_pfp");
    $m->isHTML(true);

    $m->Subject = "Order Confirmed - Superme Malaysia";
    $m->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; background-color: #ffffff;'>
            <div style='text-align: center;'>
                <img src='cid:superme_pfp' alt='Superme Logo' style='width: 80px;'>
                <h2 style='color: #333;'>Order ID: $order_id</h2>
                <p style='font-size: 16px; color: #555;'>
                    Hello, this is to inform you that your order has been confirmed. You can check the order details by clicking the button below.
                </p>

                <a href='$protocol" . $_SERVER['HTTP_HOST'] .  "/order-detail?id=$order_id' style='display: inline-block; margin: 20px 0; padding: 12px 25px; background-color: #74c637; color: white; text-decoration: none; border-radius: 6px; font-size: 16px;'>
                    Order Details
                </a>
        
                <hr style='margin: 30px 0;'>
                <p style='font-size: 12px; color: #aaa;'>
                    &copy; " . date('Y') . " Superme Malaysia. All rights reserved.
                </p>
            </div>
        </div>
    ";
    $m->send();
}

function addAdmin($name, $email, $password)
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    $m = get_mail();
    $m->addAddress($email, $name);
    $m->addEmbeddedImage(__DIR__ . "/../src/img/icon/superme.png", "superme_pfp");
    $m->isHTML(true);

    $m->Subject = "Account Created - Superme Malaysia";
    $m->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; background-color: #ffffff;'>
            <div style='text-align: center;'>
                <img src='cid:superme_pfp' alt='Superme Logo' style='width: 80px;'>
                <h2 style='color: #333;'>Account Created</h2>
                <p style='font-size: 16px; color: #555;'>
                    Hello, this is to inform you that your account has been created. You can login by clicking the button below.
                </p>
                <p style='font-size: 16px; color: #555;'>
                    Email: $email
                </p>
                <p style='font-size: 16px; color: #555;'>
                    Password: $password
                </p>

                <a href='$protocol" . $_SERVER['HTTP_HOST'] .  "/login' style='display: inline-block; margin: 20px 0; padding: 12px 25px; background-color: #74c637; color: white; text-decoration: none; border-radius: 6px; font-size: 16px;'>
                    Login
                </a>
        
                <hr style='margin: 30px 0;'>
                <p style='font-size: 12px; color: #aaa;'>
                    &copy; " . date('Y') . " Superme Malaysia. All rights reserved.
                </p>
            </div>
        </div>
    ";
    $m->send();
}
