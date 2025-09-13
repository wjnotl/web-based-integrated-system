<?php
require_once __DIR__ . "/../_base.php";

header("Access-Control-Allow-Methods: POST");

$page = max(1, (int)obtain_post("page", "1"));
$limit = 15;

$stm = $db->prepare("SELECT COUNT(*) FROM notice n");
$stm->execute();
$notice_count = $stm->fetchColumn();

$page_count = (int)ceil($notice_count / $limit);
$offset = ($page - 1) * $limit;

$stm = $db->prepare("SELECT 
                        n.id,
                        n.title,
                        n.content,
                        n.photo,
                        n.link,
                        n.link_text,
                        n.creation_time,
                        po.id AS option_id,
                        po.text AS option_text,
                        MAX(CASE 
                                WHEN (pv.account_id = ?) THEN 1 
                                ELSE 0 
                            END) AS voted,
                        COUNT(pv.poll_option_id) AS vote_count
                    FROM (
                        SELECT * 
                        FROM notice 
                        ORDER BY creation_time DESC 
                        LIMIT $offset, $limit
                    ) n
                    LEFT JOIN poll_option po ON po.notice_id = n.id
                    LEFT JOIN poll_vote pv ON pv.poll_option_id = po.id
                    GROUP BY n.id, po.id
                    ORDER BY n.creation_time DESC");
$stm->execute([$account_verified ? $account_obj->id : null]);
$rows = $stm->fetchAll();

$notices = [];
foreach ($rows as $row) {
    $id = $row->id;
    if (!isset($notices[$id])) {
        $notices[$id] = [
            'id' => $id,
            'title' => $row->title,
            'content' => $row->content,
            'photo' => $row->photo,
            'link' => $row->link,
            'link_text' => $row->link_text,
            'creation_time' => $row->creation_time,
            'poll_options' => [],
            'poll_vote_counts' => [],
        ];
    }

    if (!is_null($row->option_id)) {
        $notices[$id]['poll_options'][] = [
            'id' => $row->option_id,
            'text' => $row->option_text,
            'voted' => $row->voted,
        ];
        $notices[$id]['poll_vote_counts'][] = $account_verified ? $row->vote_count : 0;
    }
}

foreach ($notices as $id => $notice) {
    $notices[$id]['poll_vote_percentages'] = calculatePollPercentage($notices[$id]['poll_vote_counts']);
}

$result_notices = [];
foreach ($notices as $notice) {
    $result_notices[] = $notice;
}

$return_value["last"] = $page >= $page_count;
$return_value["result"] = $result_notices;

echo json_encode($return_value);
