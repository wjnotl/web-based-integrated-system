<?php

require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

only_admin("manage_notice");

$return_value["errors"] = [
    "id" => null,
    "content" => null,
    "photo" => null,
    "poll" => null
];

$id = obtain_post("id");
$content = obtain_post("content");
$link = obtain_post("link");
$link_text = obtain_post("link_text");
$photo = obtain_file("photo");
$scale = obtain_post("scale");
$imgX = obtain_post("imgX");
$imgY = obtain_post("imgY");
$remove_picture = obtain_post("remove_picture");
$change_picture = obtain_post("change_picture");
$add_option = obtain_post("add_option");
$remove_option = obtain_post("remove_option");

if (!isset($id)) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID is required";
    temp("toast_message", "Invalid ID");
} else if (!is_exist($id, "notice", "id")) {
    $return_value["success"] = false;
    $return_value["errors"]["id"] = "ID not found";
    temp("toast_message", "Notice does not exist");
} else {
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

    if ($change_picture && $photo != null) {
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

    if (isset($remove_option) && is_array($remove_option) && !empty(array_filter($remove_option))) {
        foreach ($remove_option as $option_id) {
            if (!is_exist($option_id, "poll_option", "id")) {
                $return_value["success"] = false;
                $return_value["errors"]["poll"] = "No option with ID found when removing";
                break;
            }
        }
    }

    if ($return_value["success"]) {
        $stm = $db->prepare("SELECT * from notice WHERE id = ?");
        $stm->execute([$id]);
        $notice = $stm->fetchObject();

        if ($remove_picture) {
            if (isset($notice->photo) && file_exists(__DIR__ . "/../../uploads/account/" . $notice->photo)) {
                unlink(__DIR__ . "/../../uploads/account/" . $notice->photo);
            }

            $stm = $db->prepare("UPDATE notice SET photo = NULL WHERE id = ?");
            $stm->execute([$id]);
            $return_value["remove_photo"] = true;
        } else if ($change_picture && $photo != null) {
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
                $stm = $db->prepare("UPDATE notice SET photo = ? WHERE id = ?");
                $stm->execute([$unique_name, $id]);
                $return_value["photo"] = $unique_name;
            } catch (Exception $e) {
                $return_value["success"] = false;
                $return_value["errors"]["photo"] = $e->getMessage();
            }
        }

        if ($return_value["success"]) {
            $stm = $db->prepare("UPDATE notice SET content = ?, link = ?, link_text = ? WHERE id = ?");
            $stm->execute([$content, $link, $link_text, $id]);

            if (isset($add_option) && is_array($add_option) && !empty(array_filter($add_option))) {
                foreach ($add_option as $option_text) {
                    $stm = $db->prepare("INSERT INTO poll_option (notice_id, text) VALUES (?, ?)");
                    $stm->execute([$id, $option_text]);
                }
            }

            if (isset($remove_option) && is_array($remove_option) && !empty(array_filter($remove_option))) {
                foreach ($remove_option as $option_id) {
                    $db->prepare("DELETE FROM poll_vote WHERE poll_option_id = ?")->execute([$option_id]);
                    $db->prepare("DELETE FROM poll_option WHERE id = ?")->execute([$option_id]);
                }
            }
        }
    }
}

echo json_encode($return_value);
