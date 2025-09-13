<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_category");

$return_value["errors"] = [
    "id" => null,
    "name" => null,
    "photo" => null
];

$id = obtain_post("id");
$name = obtain_post("name");
$photo = obtain_file("photo");
$scale = obtain_post("scale");
$imgX = obtain_post("imgX");
$imgY = obtain_post("imgY");
$change_picture = obtain_post("change_picture");

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
    temp("toast_message", "Invalid ID");
} else if (!is_exist($id, "category", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID not found";
    temp("toast_message", "Category does not exist");
} else {
    if (!isset($name)) {
        $return_value["success"] = false;
        $return_value["errors"]["name"] = "Name is required";
    } else if (strlen($name) < 3) {
        $return_value["success"] = false;
        $return_value["errors"]["name"] = "Name cannot be shorter than 2 characters";
    } else if (strlen($name) > 30) {
        $return_value["success"] = false;
        $return_value["errors"]["name"] = "Name cannot be longer than 30 characters";
    }

    if ($change_picture && $photo != null) {
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
}

if ($return_value["success"]) {
    $stm = $db->prepare("SELECT * from category WHERE id = ?");
    $stm->execute([$id]);
    $category = $stm->fetchObject();

    if ($change_picture && $photo != null) {
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

            if (file_exists(__DIR__ . "/../../uploads/category/" . $category->photo)) {
                unlink(__DIR__ . "/../../uploads/category/" . $category->photo);
            }
            $stm = $db->prepare("UPDATE category SET photo = ? WHERE id = ?");
            $stm->execute([$unique_name, $id]);
            $return_value["photo"] = $unique_name;
        } catch (Exception $e) {
            $return_value["success"] = false;
            $return_value["errors"]["photo"] = $e->getMessage();
        }
    }

    if ($return_value["success"]) {
        $stm = $db->prepare("UPDATE category SET name = ? WHERE id = ?");
        $stm->execute([$name, $id]);
    }
}

echo json_encode($return_value);
