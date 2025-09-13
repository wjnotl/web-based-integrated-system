<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_category");

$return_value["errors"] = [
    "name" => null,
    "photo" => null
];

$name = obtain_post("name");
$photo = obtain_file("photo");
$scale = obtain_post("scale");
$imgX = obtain_post("imgX");
$imgY = obtain_post("imgY");

if (!isset($name)) {
    $return_value["success"] = false;
    $return_value["errors"]["name"] = "Name is required";
} else if (strlen($name) < 3) {
    $return_value["success"] = false;
    $return_value["errors"]["name"] = "Name cannot be shorter than 2 characters";
} else if (strlen($name) > 30) {
    $return_value["success"] = false;
    $return_value["errors"]["name"] = "Name cannot be longer than 30 characters";
} else if (is_exist($name, "category", "name")) {
    $return_value["success"] = false;
    $return_value["errors"]["name"] = "Name already exists";
}

if ($photo == null) {
    $return_value["success"] = false;
    $return_value["errors"]["photo"] = "Photo is required";
} else if ($photo->size > 2 * 1024 * 1024) {
    $return_value["success"] = false;
    $return_value["errors"]["photo"] = "Photo cannot be larger than 2MB";
} else if (!in_array(mime_content_type($photo->tmp_name), ['image/bmp', 'image/x-ms-bmp', 'image/jpeg', 'image/png', 'image/webp'])) {
    $return_value["success"] = false;
    $return_value["errors"]["photo"] = "Only raster images (BMP, PNG, JPG, WEBP) are allowed";
} else if (!isset($scale) || (float)($scale) > 2 || (float)($scale) < 1 || !isset($imgX) || !isset($imgY)) {
    $return_value["success"] = false;
    $return_value["errors"]["photo"] = "Invalid image data! Please try again";
}

if ($return_value["success"]) {
    $unique_name;
    do {
        $unique_name = randomString(30) . ".jpg";
    } while (!is_unique($unique_name, "category", "photo"));

    require_once __DIR__ . "/../../lib/SimpleImage.php";
    $simpleImage = new SimpleImage();
    try {
        $simpleImage
            ->fromFile($photo->tmp_name)
            ->specialThumbnail(250, 250, (float)$imgX, (float)$imgY, (float)$scale)
            ->toFile(__DIR__ . "/../../uploads/category/$unique_name", "image/jpeg");

        $stm = $db->prepare("INSERT INTO category (name, photo) VALUES (?, ?)");
        $stm->execute([$name, $unique_name]);
        temp("toast_message", "Category added successfully");
    } catch (Exception $e) {
        $return_value["success"] = false;
        $return_value["errors"]["photo"] = $e->getMessage();
    }
}

echo json_encode($return_value);
