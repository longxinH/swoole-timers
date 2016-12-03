<div class="col-sm-6 pull-right">
    <div class="dataTables_paginate paging_bootstrap">
        <ul class="pagination">
            <li class="prev <?php echo $list['pager']['current_page'] == $list['pager']['first_page'] ? 'disabled' : '' ?>">
                <?php
                    parse_str($_SERVER['QUERY_STRING'], $query);
                    if (isset($query['page'])) {
                        unset($query['page']);
                    }
                    $prevpage = http_build_query(array_merge($query, array('page' => $list['pager']['prev_page'])));
                ?>
                <?php
                    if ($list['pager']['current_page'] != $list['pager']['first_page']) {
                ?>
                    <a href="?<?php echo $prevpage;?>">
                        <i class="fa fa-angle-double-left"></i>
                    </a>
                <?php
                    } else {
                ?>
                    <a href="javascript:void(0)">
                        <i class="fa fa-angle-double-left"></i>
                    </a>
                <?php
                    }
                ?>
            </li>

            <?php
                foreach ($list['pager']['all_pages'] as $key => $val) {
                    parse_str($_SERVER['QUERY_STRING'], $query);
                    if (isset($query['page'])) {
                        unset($query['page']);
                    }
                    $allpage = http_build_query(array_merge($query, array('page' => $val)));
            ?>
                <li <?php echo $list['pager']['current_page'] == $val ? 'class="active"' : '' ?>><a <?php echo $list['pager']['current_page'] == $val ? 'href="javascript:void(0);"' : 'href="?' . $allpage . '"' ?>><?php echo $val ?></a></li>
            <?php
                }
                    parse_str($_SERVER['QUERY_STRING'], $query);
                    if(isset($query['page'])){
                        unset($query['page']);
                    }
                    $lastpage = http_build_query(array_merge($query, array('page' => $list['pager']['next_page'])));
            ?>
            <li class="next <?php echo $list['pager']['current_page'] == $list['pager']['last_page'] ? 'disabled' : '' ?>">
                <?php
                    if ($list['pager']['current_page'] != $list['pager']['last_page']) {
                ?>
                    <a href="?<?php echo $lastpage ?>">
                        <i class="fa fa-angle-double-right"></i>
                    </a>
                <?php
                    } else {
                ?>
                    <a href="javascript:void(0)">
                        <i class="fa fa-angle-double-right"></i>
                    </a>
                <?php
                    }
                ?>
            </li>
        </ul>
    </div>
</div>