<?php

class SimplePager
{
    public $limit;      // Page size
    public $page;       // Current page
    public $item_count; // Total item count
    public $page_count; // Total page count
    public $result;     // Result set (array of records)
    public $count;      // Item count on the current page
    public $params;     // Get params

    public function __construct($query, $params, $count_query, $count_params, $limit, $page)
    {
        global $db;
        
        // Set [limit] and [page]
        $this->limit = (int)max($limit, 1);
        $this->page = (int)max($page, 1);
        
        // Set [item count]
        $stm = $db->prepare($count_query);
        $stm->execute($count_params);
        $this->item_count = $stm->fetchColumn();
        
        
        // Set [page count]
        $this->page_count = (int)ceil($this->item_count / $this->limit);
        
        // Calculate offset
        $offset = ($this->page - 1) * $this->limit;

        // Set [result]
        $stm = $db->prepare($query . " LIMIT $offset, $this->limit");
        $stm->execute($params);
        $this->result = $stm->fetchAll();

        // Set [count]
        $this->count = count($this->result);
    }

    public function html($get_params = [])
    {
        if (!$this->result) return;

        $this->params = $get_params;

        $prev = max($this->page - 1, 1);
        $next = min($this->page + 1, $this->page_count);

        $show_range_before_after = 2;
        $show_start = max(1, $this->page - $show_range_before_after);
        $show_end = min($this->page_count, $this->page + $show_range_before_after);

        // ensure always show 5 pages if possible
        if ($show_end - $show_start < 4) {
            if ($show_start === 1) {
                $show_end = min($this->page_count, 5);
            } else if ($show_end === $this->page_count) {
                $show_start = max(1, $this->page_count - 4);
            }
        }

        echo "<a href='" . $this->rebuildSearchParams(1) . "' class='paging-symbol first" . ($this->page === 1 ? " disabled" : "") . "'></a>";
        echo "<a href='" . $this->rebuildSearchParams($prev) .  "' class='paging-symbol prev" . ($this->page === 1 ? " disabled" : "") . "'></a>";

        for ($p = $show_start; $p <= $show_end; $p++) {
            echo "<a href='" . $this->rebuildSearchParams($p) .  "'" . ($p === $this->page ? " class='selected'" : "") . ">$p</a>";
        }

        echo "<a href='" . $this->rebuildSearchParams($next) . "' class='paging-symbol next" . ($this->page === $this->page_count ? " disabled" : "") . "'></a>";
        echo "<a href='" . $this->rebuildSearchParams($this->page_count) . "' class='paging-symbol last" . ($this->page === $this->page_count ? " disabled" : "") . "'></a>";
    }

    public function rebuildSearchParams($page_number = 1)
    {
        $params = $this->params;
        unset($params['page']);
        if ($page_number !== 1) {
            $params['page'] = $page_number;
        }

        return "?" . http_build_query($params);
    }
}
