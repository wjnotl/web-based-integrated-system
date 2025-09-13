<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_product");

$name = obtain_post("name");
$description = obtain_post("description");
$category = obtain_post("category");
$price = obtain_post("price");
$keyword = obtain_post("keyword");
$photos = obtain_files("photos");
$scale = obtain_post("scale");
$imgX = obtain_post("imgX");
$imgY = obtain_post("imgY");
$variants_colour = obtain_post("variants_colour");
$variants_size = obtain_post("variants_size");
$variants_stock = obtain_post("variants_stock");

$return_value["errors"] = [
    "name" => null,
    "description" => null,
    "category" => null,
    "price" => null,
    "photo" => null,
    "variant" => null
];

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

if (isset($keyword)) {
    $keywords = explode(",", $keyword);
    $keywords = array_map("trim", $keywords);
    $keywords = array_filter($keywords);
}

if (
    isset($photos) && is_array($photos) && !empty(array_filter($photos)) &&
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

if (
    isset($variants_colour) && is_array($variants_colour) &&
    isset($variants_size) && is_array($variants_size) &&
    isset($variants_stock) && is_array($variants_stock) &&
    count($variants_colour) === count($variants_size) && count($variants_colour) === count($variants_stock)
) {
    $variants = [];
    foreach ($variants_colour as $index => $colour) {
        $size = $variants_size[$index];
        $stock = $variants_stock[$index];

        if (!isset($colour)) {
            $return_value["success"] = false;
            $return_value["errors"]["variant"] = "Colour is required";
            break;
        } else if (strlen($colour) > 50) {
            $return_value["success"] = false;
            $return_value["errors"]["variant"] = "Colour cannot be longer than 50 characters";
            break;
        }

        if (!isset($size)) {
            $return_value["success"] = false;
            $return_value["errors"]["variant"] = "Size is required";
            break;
        } else if (strlen($size) > 30) {
            $return_value["success"] = false;
            $return_value["errors"]["variant"] = "Size cannot be longer than 30 characters";
            break;
        }

        if (!isset($stock)) {
            $return_value["success"] = false;
            $return_value["errors"]["variant"] = "Stock is required";
            break;
        } else if (filter_var($stock, FILTER_VALIDATE_INT) === false) {
            $return_value["success"] = false;
            $return_value["errors"]["variant"] = "Stock must be an integer";
            break;
        } else if ((int)$stock < 0) {
            $return_value["success"] = false;
            $return_value["errors"]["variant"] = "Stock cannot be negative";
            break;
        }

        $exists = false;
        foreach ($variants as $v) {
            if ($v["colour"] === $colour && $v["size"] === $size) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $return_value["success"] = false;
            $return_value["errors"]["variant"] = "Variant already exists";
            break;
        }

        $variants[] = [
            "colour" => $colour,
            "size" => $size,
            "stock" => $stock
        ];
    }
}

if ($return_value["success"]) {
    $new_photos = [];

    if (
        isset($photos) && is_array($photos) && !empty(array_filter($photos)) &&
        isset($scale) && is_array($scale) &&
        isset($imgX) && is_array($imgX) &&
        isset($imgY) && is_array($imgY) &&
        count($photos) === count($scale) && count($photos) === count($imgX) && count($photos) === count($imgY)
    ) {

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
        $stm = $db->prepare("INSERT INTO product (name, description, category_id, price, photo) VALUES (?, ?, ?, ?, ?)");
        $stm->execute([$name, $description, $category, $price, empty(array_filter($new_photos)) ? null : implode("\r\n", $new_photos)]);
        $product_id = $db->lastInsertId();

        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $stm = $db->prepare("INSERT INTO product_keyword (product_id, keyword) VALUES (?, ?)");
                $stm->execute([$product_id, $keyword]);
            }
        }

        if (isset($variants)) {
            foreach ($variants as $variant) {
                $stm = $db->prepare("INSERT INTO product_variant (product_id, colour, size, stock) VALUES (?, ?, ?, ?)");
                $stm->execute([$product_id, $variant["colour"], $variant["size"], $variant["stock"]]);
            }
        }

        temp("toast_message", "Product added successfully");
    }
}

echo json_encode($return_value);
