<?php
require_once __DIR__ . "/../_base.php";

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
                        LIMIT 15
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

$title = "Notice - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div id="no-notice" class="container">No notice found</div>

<?php foreach ($notices as $notice) : ?>
    <div class="container notice-container" data-notice-id="<?= $notice['id']; ?>">
        <div class="title"><?= htmlspecialchars($notice['title']); ?></div>
        <div class="date"><?= timeAgo($notice['creation_time']); ?></div>
        <?php if (isset($notice['content'])) : ?>
            <div class="content"><?= htmlspecialchars($notice['content']); ?></div>
        <?php endif; ?>
        <?php if (isset($notice['link'])) : ?>
            <div class="link">
                <a class="link-button" href="<?= htmlspecialchars($notice['link']); ?>" target="_blank"><?= htmlspecialchars($notice['link_text']); ?></a>
            </div>
        <?php endif; ?>
        <?php if (isset($notice['photo'])) : ?>
            <img class="image" src="/uploads/notice/<?= $notice['photo']; ?>" alt="<?= htmlspecialchars($notice['title']); ?>">
        <?php endif; ?>

        <?php if (!empty($notice['poll_options'])) :  ?>
            <form class="poll-container">
                <?php foreach ($notice['poll_options'] as $index => $option) : ?>
                    <label class="poll-option" data-vote-count="<?= $notice['poll_vote_counts'][$index]; ?>" data-voted="<?= $option['voted']; ?>">
                        <input class="poll-input" type="radio" name="option" value="<?= $option['id']; ?>" <?= $option['voted'] ? 'checked' : ''; ?>>
                        <div class="poll-input-button"></div>
                        <div class="poll-input-box">
                            <div class="poll-text-container">
                                <div class="poll-text"><?= htmlspecialchars($option['text']); ?></div>
                                <div class="poll-result-value"><?= $account_verified ? ($notice['poll_vote_percentages'][$index] . "%") : ""; ?></div>
                            </div>
                            <div class="poll-result-percentage" style="width: <?= $notice['poll_vote_percentages'][$index]; ?>%;"></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php
require_once __DIR__ . "/../_foot.php";
?>