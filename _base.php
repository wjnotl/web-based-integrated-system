<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

$db = new PDO("mysql:dbname=superme_malaysia", "root", "", [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
]);

$stripe_public_key = "pk_test_51R4k0DGYoQCWbL0nXPpI7NBzQBBpGnjGaLQnzwoO7MZNPUwxW08HPMJnGswFlPLj6o80wx0mXTBow00nCU5O7Db400IGYGgdTA";
$stripe_secret_key = "sk_test_51R4k0DGYoQCWbL0nz9EHMnfc7jgFw67sbDk6EC1UfPJPiMpAjEjMtZ1F28FfNdzvnJyachux7ZhxqFsboRQfpx1S00HHsmm0FW";

$default_admin_email = "malaysiasuperme@gmail.com";
$default_sender_email = "malaysiasuperme@gmail.com";

$stm = $db->query("SELECT a.* FROM account a JOIN account_type at ON a.account_type_id = at.id
WHERE a.pending_delete_expire IS NOT NULL 
AND a.pending_delete_expire < NOW()
AND at.name = 'Customer'
");
foreach ($stm->fetchAll() as $account_to_delete) {
    if (isset($account_to_delete->photo) && file_exists(__DIR__ . "/../../uploads/account/" . $account_to_delete->photo)) {
        unlink(__DIR__ . "/../../uploads/account/" . $account_to_delete->photo);
    }

    $db->prepare("DELETE FROM delete_account WHERE account_id = ?")->execute([$account_to_delete->id]);
    $db->prepare("DELETE FROM reset_password WHERE account_id = ?")->execute([$account_to_delete->id]);
    $db->prepare("DELETE FROM change_email WHERE account_id = ?")->execute([$account_to_delete->id]);
    $db->prepare("DELETE FROM session WHERE account_id = ?")->execute([$account_to_delete->id]);
    $db->prepare("DELETE FROM transaction WHERE account_id = ?")->execute([$account_to_delete->id]);
    $db->prepare("DELETE FROM poll_vote WHERE account_id = ?")->execute([$account_to_delete->id]);
    $db->prepare("DELETE FROM review WHERE account_id = ?")->execute([$account_to_delete->id]);
    $db->prepare("DELETE FROM like_review WHERE account_id = ?")->execute([$account_to_delete->id]);
    $db->prepare("DELETE FROM favourite WHERE account_id = ?")->execute([$account_to_delete->id]);
    $db->prepare("DELETE FROM cart WHERE account_id = ?")->execute([$account_to_delete->id]);
    $db->prepare("UPDATE orders SET account_id = NULL WHERE account_id = ?")->execute([$account_to_delete->id]);
    $db->prepare("UPDATE voucher SET account_id = NULL, account_email = ? WHERE account_id = ?")->execute([$account_to_delete->email, $account_to_delete->id]);
    $db->prepare("DELETE FROM account WHERE id = ?")->execute([$account_to_delete->id]);

    accountDeleted($account_to_delete->email, $account_to_delete->name);
}

$account_verified = false;
$session_id = null;
if (isset($_COOKIE['session_id']) && isset($_COOKIE['session_token'])) {
    $session_id = $_COOKIE['session_id'];
    $session_token = $_COOKIE['session_token'];

    $stm = $db->prepare('SELECT * FROM session WHERE id = ? AND token IS NOT NULL AND token = ? AND is_verified = 1 AND expire > NOW()');
    $stm->execute([$session_id, $session_token]);
    if ($stm->rowCount() === 1) {
        $session_obj = $stm->fetchObject();

        $stm = $db->prepare('SELECT * FROM account WHERE id = ?');
        $stm->execute([$session_obj->account_id]);
        if ($stm->rowCount() === 1) {
            $account_obj = $stm->fetchObject();
            if (!$account_obj->is_banned) {
                $stm = $db->prepare('SELECT * FROM account_type WHERE id = ?');
                $stm->execute([$account_obj->account_type_id]);
                if ($stm->rowCount() === 1) {
                    $account_type = $stm->fetchObject();
                    $account_verified = true;
                }
            }
        }
    }
}

function get_address()
{
    $ip_address = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    if ($ip_address == "127.0.0.1" || $ip_address == "::1") {
        return "Local Host";
    } else {
        $response = file_get_contents("http://www.geoplugin.net/json.gp?ip=$ip_address");
        $address_data = json_decode($response, true);
        return $address_data["geoplugin_city"] . ", " . $address_data["geoplugin_regionName"] . ", " . $address_data["geoplugin_countryName"];
    }
}

function get_device_info()
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    $info = [
        "browser" => "Unknown Browser",
        "os" => "Unknown OS",
        "device" => "Computer"
    ];

    // Detect browser
    if (strpos($userAgent, "Chrome") !== false && strpos($userAgent, "Edg") === false && strpos($userAgent, "OPR") === false) {
        $info["browser"] = "Google Chrome";
    } elseif (strpos($userAgent, "Safari") !== false && strpos($userAgent, "Chrome") === false) {
        $info["browser"] = "Safari";
    } elseif (strpos($userAgent, "Firefox") !== false) {
        $info["browser"] = "Mozilla Firefox";
    } elseif (strpos($userAgent, "Edg") !== false) {
        $info["browser"] = "Microsoft Edge";
    } elseif (strpos($userAgent, "OPR") !== false || strpos($userAgent, "Opera") !== false) {
        $info["browser"] = "Opera";
    } elseif (strpos($userAgent, "MSIE") !== false || strpos($userAgent, "Trident") !== false) {
        $info["browser"] = "Internet Explorer";
    }

    // Detect device
    if (preg_match('/Mobi|Android/i', $userAgent)) {
        $info["device"] = "Mobile Phone";
    } elseif (preg_match('/Tablet|iPad/i', $userAgent)) {
        $info["device"] = "Tablet";
    }

    // Detect OS
    if (strpos($userAgent, "iPhone") !== false) {
        $info["os"] = "iOS";
    } elseif (strpos($userAgent, "iPad") !== false) {
        $info["os"] = "iOS";
    } elseif (strpos($userAgent, "Macintosh") !== false) {
        $info["os"] = "MacOS";
    } elseif (strpos($userAgent, "Windows") !== false) {
        $info["os"] = "Windows";
    } elseif (strpos($userAgent, "Android") !== false) {
        $info["os"] = "Android";
    } elseif (strpos($userAgent, "Linux") !== false) {
        $info["os"] = "Linux";
    }

    return $info;
}

function obtain_get($key, $value = null)
{
    if (!isset($_GET[$key]) || $_GET[$key] === "") {
        return $value;
    }

    $value = $_GET[$key];
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function obtain_post($key, $value = null)
{
    if (!isset($_POST[$key]) || $_POST[$key] === "") {
        return $value;
    }

    $value = $_POST[$key];
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function obtain_file($key)
{
    $f = $_FILES[$key] ?? null;

    if ($f && $f['error'] == 0) {
        return (object)$f;
    }

    return null;
}

function obtain_files($key)
{
    $result = [];

    if (!isset($_FILES[$key])) {
        return null;
    }

    $files = $_FILES[$key];

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] == 0) {
            $result[] = (object)[
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];
        }
    }

    return $result;
}

function is_unique($value, $table, $field)
{
    global $db;
    $stm = $db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

function is_exist($value, $table, $field)
{
    global $db;
    $stm = $db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}

function randomString($length)
{
    return substr(bin2hex(random_bytes($length)), 0, $length);
}

function temp($key, $value = null)
{
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    } else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

function isRMFormat($str)
{
    return preg_match('/^\d+(\.\d{1,2})?$/', $str);
}

function toRMFormat($str)
{
    return number_format((float)$str, 2, '.', '');
}

function toRMFormatReport($str)
{
    return "RM " . number_format((float)$str, 2, '.', ',');
}

function toPercentageReport($str)
{
    $str = (float)$str;
    if ($str < 0) {
        return "(" . number_format(abs($str), 2, '.', ',') . "%)";
    }

    return number_format($str, 2, '.', ',') . "%";
}

function isDateFormat($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function toDateFormat($dateStr)
{
    $timestamp = strtotime($dateStr);
    return strtoupper(date("d M Y", $timestamp));
}

function timeAgo($datetime)
{
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    }
    if ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    }
    if ($diff->d >= 7) {
        return floor($diff->d / 7) . ' week' . (floor($diff->d / 7) > 1 ? 's' : '') . ' ago';
    }
    if ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    }
    if ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    }
    if ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    }
    if ($diff->s > 0) {
        return $diff->s . ' second' . ($diff->s > 1 ? 's' : '') . ' ago';
    }

    return 'just now';
}

function toRatingFormat($rating_float)
{
    return number_format($rating_float, 1, '.', '');
}

function calculatePollPercentage($voteCounts)
{
    $totalVotes = array_sum($voteCounts);
    if ($totalVotes === 0) return $voteCounts;

    $percentages = [];
    $sum = 0;

    foreach ($voteCounts as $votes) {
        $percent = round(($votes / $totalVotes) * 100);
        $percentages[] = $percent;
        $sum += $percent;
    }

    if ($sum !== 100) {
        $maxIndex = array_keys($percentages, max($percentages))[0];
        $percentages[$maxIndex] += (100 - $sum);
    }

    return $percentages;
}

function order_expire()
{
    global $db;

    $db->beginTransaction();
    $stm = $db->query("SELECT * 
                FROM orders
                WHERE expired_at IS NOT NULL 
                AND expired_at < NOW() 
                FOR UPDATE");

    $orders = $stm->fetchAll();

    foreach ($orders as $order) {
        // update voucher
        if (isset($order->voucher_id)) {
            $stm = $db->prepare("UPDATE voucher SET is_used = 0 WHERE id = ?");
            $stm->execute([$order->voucher_id]);
        }

        // update stock
        $stm = $db->prepare("UPDATE product_variant pv
                                JOIN order_item oi ON pv.id = oi.product_variant_id
                                SET pv.stock = pv.stock + oi.quantity
                                WHERE oi.order_id = ?");
        $stm->execute([$order->id]);

        // update status
        $stm = $db->prepare("UPDATE orders SET status = 'Canceled', expired_at = NULL WHERE id = ?");
        $stm->execute([$order->id]);
    }

    $db->commit();
}

function get_mail()
{
    require_once __DIR__ . "/lib/PHPMailer.php";
    require_once __DIR__ . "/lib/SMTP.php";

    global $default_sender_email;

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Port = 587;

    $m->Host = 'smtp.gmail.com';
    $m->Username = $default_sender_email;
    $m->Password = 'zagy swde kxbo qfnq';

    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, "Superme Malaysia");

    /*
    // For debugging
    $m->SMTPDebug = 2;
    $m->Debugoutput = 'html';
    */

    return $m;
}

function accountDeleted($email, $name) {
    $m = get_mail();
    $m->addAddress($email, $name);
    $m->addEmbeddedImage(__DIR__ . "/src/img/icon/superme.png", "superme_pfp");
    $m->isHTML(true);

    $m->Subject = "Account Deleted - Superme Malaysia";
    $m->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; background-color: #ffffff;'>
            <div style='text-align: center;'>
                <img src='cid:superme_pfp' alt='Superme Logo' style='width: 80px;'>
                <h2 style='color: #333;'>Account Deleted</h2>
                <p style='font-size: 16px; color: #555;'>
                    Hello, this is to inform you that your account has been deleted.
                    Hope you enjoyed your stay with us.
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