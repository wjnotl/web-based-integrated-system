<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_notice");

$return_value["errors"] = [
    "title" => null,
    "content" => null,
    "photo" => null,
    "poll" => null,
    "link" => null,
    "link_text" => null
];

$title = obtain_post("title");
$content = obtain_post("content");
$link = obtain_post("link");
$link_text = obtain_post("link_text");
$photo = obtain_file("photo");
$scale = obtain_post("scale");
$imgX = obtain_post("imgX");
$imgY = obtain_post("imgY");
$add_option = obtain_post("add_option");

if (!isset($title)) {
    $return_value["success"] = false;
    $return_value["errors"]["title"] = "Title is required";
} else if (strlen($title) > 70) {
    $return_value["success"] = false;
    $return_value["errors"]["title"] = "Title cannot be longer than 70 characters";
}

if (isset($content) && strlen($content) > 2000) {
    $return_value["success"] = false;
    $return_value["errors"]["content"] = "Content cannot be longer than 2000 characters";
}

if (isset($link) || isset($link_text)) {
    if (isset($link) && !isset($link_text)) {
        $return_value["success"] = false;
        $return_value["errors"]["link_text"] = "Link text is required";
    } else if (!isset($link) && isset($link_text)) {
        $return_value["success"] = false;
        $return_value["errors"]["link"] = "Link is required";
    } else if (strlen($link) > 300) {
        $return_value["success"] = false;
        $return_value["errors"]["link"] = "Link cannot be longer than 300 characters";
    } else if (strlen($link_text) > 50) {
        $return_value["success"] = false;
        $return_value["errors"]["link_text"] = "Link text cannot be longer than 50 characters";
    }
}

if ($photo != null) {
    if ($photo->size > 4 * 1024 * 1024) {
        $return_value["success"] = false;
        $return_value["errors"]["photo"] = "Photo cannot be larger than 4MB";
    } else if (!in_array(mime_content_type($photo->tmp_name), ['image/bmp', 'image/x-ms-bmp', 'image/jpeg', 'image/png', 'image/webp'])) {
        $return_value["success"] = false;
        $return_value["errors"]["photo"] = "Only raster images (BMP, PNG, JPG, WEBP) are allowed";
    } else if (!isset($scale) || (float)($scale) > 2 || (float)($scale) < 1 || !isset($imgX) || !isset($imgY)) {
        $return_value["success"] = false;
        $return_value["errors"]["photo"] = "Invalid image data! Please try again";
    }
}

if (isset($add_option) && is_array($add_option) && !empty(array_filter($add_option))) {
    foreach ($add_option as $option_text) {
        if (!isset($option_text)) {
            $return_value["success"] = false;
            $return_value["errors"]["poll"] = "Create option text is required";
            break;
        } else if (strlen($option_text) > 100) {
            $return_value["success"] = false;
            $return_value["errors"]["poll"] = "Create option text cannot be longer than 100 characters";
            break;
        }
    }
}

if ($return_value["success"]) {
    if ($photo != null) {
        $unique_name;
        do {
            $unique_name = randomString(30) . ".jpg";
        } while (!is_unique($unique_name, "notice", "photo"));

        require_once __DIR__ . "/../../lib/SimpleImage.php";
        $simpleImage = new SimpleImage();
        try {
            $simpleImage
                ->fromFile($photo->tmp_name)
                ->specialThumbnail(500, 500, (float)$imgX, (float)$imgY, (float)$scale)
                ->toFile(__DIR__ . "/../../uploads/notice/$unique_name", "image/jpeg");

            if (isset($notice->photo) && file_exists(__DIR__ . "/../../uploads/notice/" . $notice->photo)) {
                unlink(__DIR__ . "/../../uploads/notice/" . $notice->photo);
            }

            $return_value["photo"] = $unique_name;
        } catch (Exception $e) {
            $return_value["success"] = false;
            $return_value["errors"]["photo"] = $e->getMessage();
        }
    }

    if ($return_value["success"]) {
        if (isset($return_value["photo"])) {
            $stm = $db->prepare("INSERT INTO notice (title, content, photo, link, link_text) VALUES (?, ?, ?, ?, ?)");
            $stm->execute([$title, $content, $unique_name, $link, $link_text]);
        } else {
            $stm = $db->prepare("INSERT INTO notice (title, content, link, link_text) VALUES (?, ?, ?, ?)");
            $stm->execute([$title, $content, $link, $link_text]);
        }
        $notice_id = $db->lastInsertId();

        if (isset($add_option) && is_array($add_option) && !empty(array_filter($add_option))) {
            foreach ($add_option as $option_text) {
                $stm = $db->prepare("INSERT INTO poll_option (notice_id, text) VALUES (?, ?)");
                $stm->execute([$notice_id, $option_text]);
            }
        }

        temp("toast_message", "Notice added successfully");
    }
}

$return_value["link"] = $link;
$return_value["link_text"] = $link_text;
echo json_encode($return_value);
