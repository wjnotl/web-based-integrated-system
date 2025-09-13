<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_product");

$id = obtain_post("id");
$name = obtain_post("name");
$description = obtain_post("description");
$category = obtain_post("category");
$price = obtain_post("price");
$keyword = obtain_post("keyword", "");
$keyword_change = obtain_post("keyword_change");
$photos = obtain_files("photos");
$change_picture = obtain_post("change_picture");
$scale = obtain_post("scale");
$imgX = obtain_post("imgX");
$imgY = obtain_post("imgY");
$remove_photos = obtain_post("remove_photos");

$return_value["errors"] = [
    "id" => null,
    "name" => null,
    "description" => null,
    "category" => null,
    "price" => null,
    "photo" => null,
];

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
    temp("toast_message", "ID is required");
} else if (!is_exist($id, "product", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "Invalid ID";
    temp("toast_message", "Product does not exist");
} else {
    if (!isset($name)) {
        $return_value["success"] = false;
        $return_value["errors"]["name"] = "Name is required";
    } else if (strlen($name) > 100) {
        $return_value["success"] = false;
        $return_value["errors"]["name"] = "Name cannot be longer than 100 characters";
    }

    if (isset($description) && strlen($description) > 2000) {
        $return_value["success"] = false;
        $return_value["errors"]["description"] = "Description cannot be longer than 2000 characters";
    }

    if (isset($category)) {
        if (!is_exist($category, "category", "id")) {
            $return_value["success"] = false;
            $return_value["errors"]["category"] = "Invalid category";
        }
    }

    if (!isset($price)) {
        $return_value["success"] = false;
        $return_value["errors"]["price"] = "Price is required";
    } else if (!isRMFormat($price)) {
        $return_value["success"] = false;
        $return_value["errors"]["price"] = "Price must be in RM format";
    } else if ((float)$price < 0) {
        $return_value["success"] = false;
        $return_value["errors"]["price"] = "Price cannot be negative";
    }

    if (isset($keyword_change)) {
        $keywords = explode(",", $keyword);
        $keywords = array_map("trim", $keywords);
        $keywords = array_filter($keywords);
    }

    if (
        isset($change_picture) && isset($photos) && is_array($photos) && !empty(array_filter($photos)) &&
        isset($scale) && is_array($scale) && !empty(array_filter($scale)) &&
        isset($imgX) && is_array($imgX) &&
        isset($imgY) && is_array($imgY) &&
        count($photos) === count($scale) && count($photos) === count($imgX) && count($photos) === count($imgY)
    ) {
        foreach ($photos as $index => $photo) {
            if ($photo->size > 4 * 1024 * 1024) {
                $return_value["success"] = false;
                $return_value["errors"]["photo"] = "Photo cannot be larger than 4MB";
                break;
            } else if (!in_array(mime_content_type($photo->tmp_name), ['image/bmp', 'image/x-ms-bmp', 'image/jpeg', 'image/png', 'image/webp'])) {
                $return_value["success"] = false;
                $return_value["errors"]["photo"] = "Only raster images (BMP, PNG, JPG, WEBP) are allowed";
                break;
            } else if (!isset($scale[$index]) || (float)($scale[$index]) > 2 || (float)($scale[$index]) < 1 || !isset($imgX[$index]) || !isset($imgY[$index])) {
                $return_value["success"] = false;
                $return_value["errors"]["photo"] = "Invalid image data! Please try again";
                break;
            }
        }
    }

    if ($return_value["success"]) {
        $stm = $db->prepare("SELECT * FROM product WHERE id = ?");
        $stm->execute([$id]);
        $product = $stm->fetchObject();

        $current_photos = [];
        foreach (preg_split("/\r\n|\n|\r/", $product->photo) as $image) {
            $current_photos[] = $image;
        }

        if (
            isset($change_picture) && isset($photos) && is_array($photos) && !empty(array_filter($photos)) &&
            isset($scale) && is_array($scale) &&
            isset($imgX) && is_array($imgX) &&
            isset($imgY) && is_array($imgY) &&
            count($photos) === count($scale) && count($photos) === count($imgX) && count($photos) === count($imgY)
        ) {
            $new_photos = [];
            foreach ($photos as $index => $photo) {
                $unique_name;
                do {
                    $unique_name = randomString(30) . ".jpg";
                } while (file_exists(__DIR__ . "/../../uploads/product/" . $unique_name));

                require_once __DIR__ . "/../../lib/SimpleImage.php";
                $simpleImage = new SimpleImage();
                try {
                    $simpleImage
                        ->fromFile($photo->tmp_name)
                        ->specialThumbnail(450, 450, (float)$imgX[$index], (float)$imgY[$index], (float)$scale[$index])
                        ->toFile(__DIR__ . "/../../uploads/product/$unique_name", "image/jpeg");

                    $current_photos[] = $unique_name;
                    $new_photos[] = $unique_name;
                } catch (Exception $e) {
                    $return_value["success"] = false;
                    $return_value["errors"]["photo"] = $e->getMessage();
                }

                if (!$return_value["success"]) {
                    foreach ($new_photos as $photo) {
                        if (file_exists(__DIR__ . "/../../uploads/product/" . $photo)) {
                            unlink(__DIR__ . "/../../uploads/product/" . $photo);
                        }
                    }
                    break;
                }
            }
        }

        if ($return_value["success"]) {
            if (isset($remove_photos) && is_array($remove_photos) && !empty(array_filter($remove_photos))) {
                foreach ($remove_photos as $photo) {
                    if (file_exists(__DIR__ . "/../../uploads/product/" . $photo)) {
                        unlink(__DIR__ . "/../../uploads/product/" . $photo);
                    }

                    $current_photos = array_filter($current_photos, function ($item) use ($photo) {
                        return $item !== $photo;
                    });
                }
            }

            $stm = $db->prepare("UPDATE product SET name = ?, description = ?, category_id = ?, price = ?, photo = ? WHERE id = ?");
            $stm->execute([$name, $description, $category, $price, empty(array_filter($current_photos)) ? null : implode("\r\n", $current_photos), $id]);

            if (isset($keyword_change)) {
                $stm = $db->prepare("DELETE FROM product_keyword WHERE product_id = ?");
                $stm->execute([$id]);
            }

            if (isset($keyword)) {
                foreach ($keywords as $keyword) {
                    $stm = $db->prepare("INSERT INTO product_keyword (product_id, keyword) VALUES (?, ?)");
                    $stm->execute([$id, $keyword]);
                }
            }
        }
    }
}

$return_value["remove_photos"] = $remove_photos;

echo json_encode($return_value);
