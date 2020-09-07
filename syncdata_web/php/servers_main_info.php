<?php
function get_status_avg_con($avg_con){
    if ($avg_con > 0 && $avg_con < 5) return "norm";
    elseif ($avg_con >= 5 && $avg_con < 10) return "warn";
    else return "error";
}
function get_status_avg_upld($avg_upld){
    if ($avg_upld > 0 && $avg_upld < 5) return "norm";
    elseif ($avg_upld >= 5 && $avg_upld < 20) return "warn";
    else return "error";
}
function get_status_number_error($number_err){
    if ($number_err == 0) return "norm";
    elseif ($number_err >= 1 && $number_err < 10) return "warn";
    else return "error";
}


if (!empty($_servers_main_info)) {
    foreach ($_servers_main_info as $server) {
        $avg_con = $server["avg_time_connection"];
        $status_avg_con = get_status_avg_con($avg_con);

        $avg_upld = $server["avg_time_upload"];
        $status_avg_upld = get_status_avg_upld($avg_upld);

        $number_err = $server["number_error"];
        $status_number_err = get_status_number_error($number_err);

        $status_date = "error";
        $server_id = str_replace(".", "", $server["host"])
    ?>
        <div class="db" id="db-<?php echo $server_id ?>">
            <div class="db-main-info card">
                <div class="db-name text-main">
                    <?php echo $server["host"] ?>
                </div>
                <div class="rows-stat">
                    <div class="db-time-conn row-stat">
                        <div class="flex">
                            <div class="circle <?php echo $status_avg_con ?>" id="circle-avg-con-<?php echo $server_id ?>"></div>
                            <div>Срд время соединения</div>
                        </div>
                        <div id="val-avg-con-<?php echo $server_id ?>" class="text-<?php echo $status_avg_con ?>"><?php echo $avg_con ?></div>
                    </div>
                    <div class="db-time-upld row-stat">
                        <div class="flex">
                            <div class="circle <?php echo $status_avg_upld ?>" id="circle-avg-upld-<?php echo $server_id ?>"></div>
                            <div>Срд время загрузки</div>
                        </div>
                        <div id="val-avg-upld-<?php echo $server_id ?>" class="text-<?php echo $status_avg_upld ?>"><?php echo $avg_upld ?></div>
                    </div>
                    <div class="db-last-conn row-stat">
                        <div class="flex">
                            <div class="circle <?php echo $status_date ?>" id="circle-date-<?php echo $server_id ?>"></div>
                            <div>Последнее соединение</div>
                        </div>
                        <div id="val-date-<?php echo $server_id ?>" class="delta-time text-<?php echo $status_date ?>"><?php echo $server["last_connection"] ?></div>
                    </div>
                    <div class="db-number-err row-stat">
                        <div class="flex">
                            <div class="circle <?php echo $status_number_err ?>" id="circle-num-err-<?php echo $server_id ?>"></div>
                            <div>Количество ошибок</div>
                        </div>
                        <div id="val-num-err-<?php echo $server_id ?>" class="text-<?php echo $status_number_err ?>"><?php echo $number_err ?></div>
                    </div>
                </div>
            </div>
            <div id="chart-<?php echo $server_id ?>" class="db-extended-info card">
                <!--<div class="action-dropdown">
                    <span class="fa-ellipsis-v"></span>
                    <div class="dropdown-menu show show-menu">
                        <div class="dropdown-item">Hour</div>
                        <div class="dropdown-item">Day</div>
                        <div class="dropdown-item">Week</div>

                    </div>
                </div>-->
            </div>
        </div>
    <?php }
} ?>
