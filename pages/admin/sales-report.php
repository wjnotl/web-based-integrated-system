<?php
require_once __DIR__ . "/../_base.php";

$sales_report = [
    "annually" => [
        "Revenue" => [],
        "Revenue Growth" => [],
        "Total Order" => [],
        "Average Order Value" => []
    ],
    "quarterly" => [
        "Revenue" => [],
        "Revenue Growth" => [],
        "Total Order" => [],
        "Average Order Value" => []
    ]
];

$sales_by_category = [
    "annually" => [],
    "quarterly" => []
];

$stm = $db->query("SELECT id, name FROM category ORDER BY name");
$categories = $stm->fetchAll();
foreach ($categories as $category) {
    $sales_by_category["annually"][$category->name] = [];
    $sales_by_category["quarterly"][$category->name] = [];
}

$sales_by_region = [
    "annually" => [],
    "quarterly" => []
];

$regions = ["Johor", "Kedah", "Kelantan", "Kuala Lumpur", "Labuan", "Melaka", "Negeri Sembilan", "Pahang", "Perak", "Perlis", "Pulau Pinang", "Putrajaya", "Sabah", "Sarawak", "Selangor", "Terengganu"];
foreach ($regions as $region) {
    $sales_by_region["annually"][$region] = [];
    $sales_by_region["quarterly"][$region] = [];
}

$current_year = date('Y');
$current_month = date('m');
$current_quarter = ceil($current_month / 3); // 1 to 4

$years = [];

for ($i = 0; $i < 6; $i++) {
    $year = $current_year - $i;
    $years[] = $year;

    $start_date = $year . '-01-01';
    $end_date = $year . '-12-31';

    $stm = $db->prepare("
        SELECT 
            COALESCE(SUM(total_price), 0) AS revenue,
            COUNT(*) AS total_order
        FROM orders
        WHERE status = 'Delivered'
        AND DATE(creation_time) BETWEEN ? AND ?
    ");
    $stm->execute([$start_date, $end_date]);
    $data = $stm->fetchObject();

    $sales_report["annually"]["Revenue"][] = $data->revenue;
    $sales_report["annually"]["Total Order"][] = $data->total_order;
    if ($data->total_order > 0) {
        $sales_report["annually"]["Average Order Value"][] = $data->revenue / $data->total_order;
    } else {
        $sales_report["annually"]["Average Order Value"][] = 0;
    }

    // category sales
    $stm = $db->prepare("
            SELECT COALESCE(SUM(oi.quantity * oi.price), 0)
            FROM order_item oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'Delivered'
            AND DATE(o.creation_time) BETWEEN ? AND ?
            AND oi.category_id IS NOT NULL
            AND oi.category_id = ?
    ");
    foreach ($categories as $category) {
        $stm->execute([$start_date, $end_date, $category->id]);
        $sales_by_category["annually"][$category->name][] = $stm->fetchColumn();
    }

    // region sales
    $stm = $db->prepare("
            SELECT COALESCE(SUM(oi.quantity * oi.price), 0)
            FROM order_item oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'Delivered'
            AND DATE(o.creation_time) BETWEEN ? AND ?
            AND o.state = ?
    ");
    foreach ($regions as $region) {
        $stm->execute([$start_date, $end_date, $region]);
        $sales_by_region["annually"][$region][] = $stm->fetchColumn();
    }
}

// add one more year to calculate growth
$year--;
$start_date = $year . '-01-01';
$end_date = $year . '-12-31';
$stm = $db->prepare("
        SELECT COALESCE(SUM(total_price), 0)
        FROM orders
        WHERE status = 'Delivered'
        AND DATE(creation_time) BETWEEN ? AND ?
");
$stm->execute([$start_date, $end_date]);
$sales_report["annually"]["Revenue"][] = $stm->fetchColumn();

// calculate annually growth
$revenues = $sales_report["annually"]["Revenue"];
for ($i = 0; $i < count($revenues); $i++) {
    if (!isset($revenues[$i + 1]) || $revenues[$i + 1] == 0) {
        $sales_report["annually"]["Revenue Growth"][] = "N/A";
    } else {
        $growth = (($revenues[$i] / $revenues[$i + 1]) - 1) * 100;
        $sales_report["annually"]["Revenue Growth"][] = toPercentageReport($growth);
    }
}

$quarters = [];
for ($i = 0; $i < 6; $i++) {
    $year = $current_year;
    $quarter = $current_quarter - $i;

    while ($quarter <= 0) {
        $quarter += 4;
        $year--;
    }

    $quarters[] = $year . ' Q' . $quarter;

    if ($quarter == 1) {
        $start_date = $year . '-01-01';
        $end_date = $year . '-03-31';
    } else if ($quarter == 2) {
        $start_date = $year . '-04-01';
        $end_date = $year . '-06-30';
    } else if ($quarter == 3) {
        $start_date = $year . '-07-01';
        $end_date = $year . '-09-30';
    } else if ($quarter == 4) {
        $start_date = $year . '-10-01';
        $end_date = $year . '-12-31';
    }

    $stm = $db->prepare("
        SELECT 
            COALESCE(SUM(total_price), 0) AS revenue,
            COUNT(*) AS total_order
        FROM orders
        WHERE status = 'Delivered'
        AND DATE(creation_time) BETWEEN ? AND ?
    ");
    $stm->execute([$start_date, $end_date]);
    $data = $stm->fetchObject();

    $sales_report["quarterly"]["Revenue"][] = $data->revenue;
    $sales_report["quarterly"]["Total Order"][] = $data->total_order;
    if ($data->total_order > 0) {
        $sales_report["quarterly"]["Average Order Value"][] = $data->revenue / $data->total_order;
    } else {
        $sales_report["quarterly"]["Average Order Value"][] = 0;
    }

    // category sales
    $stm = $db->prepare("
            SELECT COALESCE(SUM(oi.quantity * oi.price), 0)
            FROM order_item oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'Delivered'
            AND DATE(o.creation_time) BETWEEN ? AND ?
            AND oi.category_id IS NOT NULL
            AND oi.category_id = ?
    ");
    foreach ($categories as $category) {
        $stm->execute([$start_date, $end_date, $category->id]);
        $sales_by_category["quarterly"][$category->name][] = $stm->fetchColumn();
    }

    // region sales
    $stm = $db->prepare("
            SELECT COALESCE(SUM(oi.quantity * oi.price), 0)
            FROM order_item oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'Delivered'
            AND DATE(o.creation_time) BETWEEN ? AND ?
            AND o.state = ?
    ");
    foreach ($regions as $region) {
        $stm->execute([$start_date, $end_date, $region]);
        $sales_by_region["quarterly"][$region][] = $stm->fetchColumn();
    }
}

// add one more quarter to calculate growth
$quarter--;
while ($quarter <= 0) {
    $quarter += 4;
    $year--;
}
if ($quarter == 1) {
    $start_date = $year . '-01-01';
    $end_date = $year . '-03-31';
} else if ($quarter == 2) {
    $start_date = $year . '-04-01';
    $end_date = $year . '-06-30';
} else if ($quarter == 3) {
    $start_date = $year . '-07-01';
    $end_date = $year . '-09-30';
} else if ($quarter == 4) {
    $start_date = $year . '-10-01';
    $end_date = $year . '-12-31';
}
$stm = $db->prepare("
        SELECT COALESCE(SUM(total_price), 0)
        FROM orders
        WHERE status = 'Delivered'
        AND DATE(creation_time) BETWEEN ? AND ?
");
$stm->execute([$start_date, $end_date]);
$sales_report["quarterly"]["Revenue"][] = $stm->fetchColumn();

// calculate quarterly growth
$revenues = $sales_report["quarterly"]["Revenue"];
for ($i = 0; $i < count($revenues); $i++) {
    if (!isset($revenues[$i + 1]) || $revenues[$i + 1] == 0) {
        $sales_report["quarterly"]["Revenue Growth"][] = "N/A";
    } else {
        $growth = (($revenues[$i] / $revenues[$i + 1]) - 1) * 100;
        $sales_report["quarterly"]["Revenue Growth"][] = toPercentageReport($growth);
    }
}

$title = "Sales Report - Superme Malaysia";
require_once __DIR__ . "/../_head.php";
?>

<div class="container sales-container">
    <div class="title">Sales Report</div>
    <div class="custom-radio-container center-items center-text specified-width">
        <label>
            <input type="radio" name="sales_report" value="annually" checked>
            <div>
                <div class="custom-radio-text">Annually</div>
            </div>
        </label>
        <label>
            <input type="radio" name="sales_report" value="quarterly">
            <div>
                <div class="custom-radio-text">Quarterly</div>
            </div>
        </label>
    </div>
    <table class="annually-table">
        <thead>
            <tr>
                <th></th>
                <?php foreach ($years as $year): ?>
                    <th><?= $year ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales_report["annually"] as $key => $value): ?>
                <tr>
                    <th><?= $key ?></th>
                    <?php for ($i = 0; $i < 6; $i++): ?>
                        <?php if ($key === "Revenue" || $key === "Average Order Value"): ?>
                            <td><?= toRMFormatReport($value[$i]); ?></td>
                        <?php else: ?>
                            <td><?= $value[$i]; ?></td>
                        <?php endif; ?>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <table class="quarterly-table">
        <thead>
            <tr>
                <th></th>
                <?php foreach ($quarters as $quarter): ?>
                    <th><?= $quarter ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales_report["quarterly"] as $key => $value): ?>
                <tr>
                    <th><?= $key ?></th>
                    <?php for ($i = 0; $i < 6; $i++): ?>
                        <?php if ($key === "Revenue" || $key === "Average Order Value"): ?>
                            <td><?= toRMFormatReport($value[$i]); ?></td>
                        <?php else: ?>
                            <td><?= $value[$i]; ?></td>
                        <?php endif; ?>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="container sales-container">
    <div class="title">Sales By Category</div>
    <div class="custom-radio-container center-items center-text specified-width">
        <label>
            <input type="radio" name="sales_by_category" value="annually" checked>
            <div>
                <div class="custom-radio-text">Annually</div>
            </div>
        </label>
        <label>
            <input type="radio" name="sales_by_category" value="quarterly">
            <div>
                <div class="custom-radio-text">Quarterly</div>
            </div>
        </label>
    </div>
    <table class="annually-table">
        <thead>
            <tr>
                <th></th>
                <?php foreach ($years as $year): ?>
                    <th><?= $year ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales_by_category["annually"] as $category => $value): ?>
                <tr>
                    <th><?= $category ?></th>
                    <?php foreach ($value as $revenue): ?>
                        <td><?= toRMFormatReport($revenue); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <table class="quarterly-table">
        <thead>
            <tr>
                <th></th>
                <?php foreach ($quarters as $quarter): ?>
                    <th><?= $quarter ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales_by_category["quarterly"] as $category => $value): ?>
                <tr>
                    <th><?= $category ?></th>
                    <?php foreach ($value as $revenue): ?>
                        <td><?= toRMFormatReport($revenue); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="container sales-container">
    <div class="title">Sales By Region</div>
    <div class="custom-radio-container center-items center-text specified-width">
        <label>
            <input type="radio" name="sales_by_region" value="annually" checked>
            <div>
                <div class="custom-radio-text">Annually</div>
            </div>
        </label>
        <label>
            <input type="radio" name="sales_by_region" value="quarterly">
            <div>
                <div class="custom-radio-text">Quarterly</div>
            </div>
        </label>
    </div>
    <table class="annually-table">
        <thead>
            <tr>
                <th></th>
                <?php foreach ($years as $year): ?>
                    <th><?= $year ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales_by_region["annually"] as $region => $value): ?>
                <tr>
                    <th><?= $region ?></th>
                    <?php foreach ($value as $revenue): ?>
                        <td><?= toRMFormatReport($revenue); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <table class="quarterly-table">
        <thead>
            <tr>
                <th></th>
                <?php foreach ($quarters as $quarter): ?>
                    <th><?= $quarter ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales_by_region["quarterly"] as $region => $value): ?>
                <tr>
                    <th><?= $region ?></th>
                    <?php foreach ($value as $revenue): ?>
                        <td><?= toRMFormatReport($revenue); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . "/../_foot.php";
?>