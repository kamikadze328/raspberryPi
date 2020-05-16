<?php
function get_status_avg_con($avg_con){
    if ($avg_con > 0 && $avg_con < 5) return "norm";
    elseif ($avg_con >= 5 && $avg_con < 10) return "warn";
    else return "error";
}
function get_status_avg_upld($avg_upld){
    if ($avg_upld > 0 && $avg_upld < 2) return "norm";
    elseif ($avg_upld >= 2 && $avg_upld < 4) return "warn";
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

        $status_date = "error"
    ?>
        <div class="db">
            <div class="db-main-info card">
                <div class="db-name text-main">
                    <?php echo $server["host"] ?>
                </div>
                <div class="rows-stat">
                    <div class="db-time-conn row-stat">
                        <div class="flex">
                            <div class="circle <?php echo $status_avg_con ?>"></div>
                            <div>Avg time connection</div>
                        </div>
                        <div class="text-<?php echo $status_avg_con ?>"><?php echo $avg_con ?></div>
                    </div>
                    <div class="db-time-upld row-stat">
                        <div class="flex">
                            <div class="circle <?php echo $status_avg_upld ?>"></div>
                            <div>Avg time upload</div>
                        </div>
                        <div class="text-<?php echo $status_avg_upld ?>"><?php echo $avg_upld ?></div>
                    </div>
                    <div class="db-last-conn row-stat">
                        <div class="flex">
                            <div class="circle <?php echo $status_date ?>"></div>
                            <div>Last connection</div>
                        </div>
                        <div class="text-<?php echo $status_date ?>"><?php echo $server["last_connection"] ?></div>
                    </div>
                    <div class="db-number-err row-stat">
                        <div class="flex">
                            <div class="circle <?php echo $status_number_err ?>"></div>
                            <div>Number error</div>
                        </div>
                        <div class="text-<?php echo $status_number_err ?>"><?php echo $number_err ?></div>
                    </div>
                </div>
            </div>
            <div id="chart-<?php echo str_replace(".", "", $server["host"]) ?>" class="db-extended-info card">
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
