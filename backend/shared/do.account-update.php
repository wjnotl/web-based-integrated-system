<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_verified_account();

$return_value["errors"] = [
    "name" => null,
    "gender" => null,
    "photo" => null
];


$name = obtain_post("name");
$gender = obtain_post("gender");
$photo = obtain_file("photo");
$scale = obtain_post("scale");
$imgX = obtain_post("imgX");
$imgY = obtain_post("imgY");
$remove_picture = obtain_post("remove_picture");
$change_picture = obtain_post("change_picture");

if (!isset($name)) {
    $return_value["success"] = false;
    $return_value["errors"]["name"] = "Name is required";
} else if (strlen($name) < 2) {
    $return_value["success"] = false;
    $return_value["errors"]["name"] = "Name cannot be shorter than 2 characters";
} else if (strlen($name) > 50) {
    $return_value["success"] = false;
    $return_value["errors"]["name"] = "Name cannot be longer than 50 characters";
}

if (!isset($gender)) {
    $return_value["success"] = false;
    $return_value["errors"]["gender"] = "Select a gender";
} else if (!in_array($gender, ["m", "f", "-"])) {
    $return_value["success"] = false;
    $return_value["errors"]["gender"] = "Invalid gender";
}

if (!isset($remove_picture) && $change_picture && $photo != null) {
    if ($photo->size > 2 * 1024 * 1024) {
        $return_value["success"] = false;
        $return_value["errors"]["photo"] = "Photo cannot be larger than 2MB";
    } else if (!in_array(mime_content_type($photo->tmp_name), ['image/bmp', 'image/x-ms-bmp', 'image/jpeg', 'image/png', 'image/webp'])) {
        $return_value["success"] = false;
        $return_value["errors"]["photo"] = "Only raster images (BMP, PNG, JPG, WEBP) are allowed";
    } else if (!isset($scale) || (float)($scale) > 2 || (float)($scale) < 1 || !isset($imgX) || !isset($imgY)) {
        $return_value["success"] = false;
        $return_value["errors"]["photo"] = "Invalid image data! Please try again";
    }
}

if ($return_value["success"]) {
    if ($remove_picture) {
        if (isset($account_obj->photo) && file_exists(__DIR__ . "/../../uploads/account/" . $account_obj->photo)) {
            unlink(__DIR__ . "/../../uploads/account/" . $account_obj->photo);
        }

        $stm = $db->prepare("UPDATE account SET photo = NULL WHERE id = ?");
        $stm->execute([$account_obj->id]);
        $return_value["remove_photo"] = true;
    } else if ($change_picture && $photo != null) {
        $unique_name;
        do {
            $unique_name = randomString(30) . ".jpg";
        } while (!is_unique($unique_name, "account", "photo"));

        require_once __DIR__ . "/../../lib/SimpleImage.php";
        $simpleImage = new SimpleImage();
        try {
            $simpleImage
                ->fromFile($photo->tmp_name)
                ->specialThumbnail(250, 250, (float)$imgX, (float)$imgY, (float)$scale)
                ->toFile(__DIR__ . "/../../uploads/account/$unique_name", "image/jpeg");

            if (isset($account_obj->photo) && file_exists(__DIR__ . "/../../uploads/account/" . $account_obj->photo)) {
                unlink(__DIR__ . "/../../uploads/account/" . $account_obj->photo);
            }
            $stm = $db->prepare("UPDATE account SET photo = ? WHERE id = ?");
            $stm->execute([$unique_name, $account_obj->id]);
            $return_value["photo"] = $unique_name;
        } catch (Exception $e) {
            $return_value["success"] = false;
            $return_value["errors"]["photo"] = $e->getMessage();
        }
    }

    if ($return_value["success"]) {
        $stm = $db->prepare("UPDATE account SET name = ?, gender = ? WHERE id = ?");
        $stm->execute([$name, $gender, $account_obj->id]);
    }
}

echo json_encode($return_value);
