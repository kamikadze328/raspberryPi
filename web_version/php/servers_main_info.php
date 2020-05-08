<?php foreach ($_servers_main_info as $server) {
    $avg_con = $server["avg_time_connection"];
    if ($avg_con < 5) $status_avg_con = "norm";
    elseif ($avg_con >= 5 && $avg_con < 10) $status_avg_con = "warn";
    elseif ($avg_con >= 10) $status_avg_con = "error";

    $avg_upld = $server["avg_time_upload"];
    if ($avg_upld < 2) $status_avg_upld = "norm";
    elseif ($avg_upld >= 2 && $avg_upld < 4) $status_avg_upld = "warn";
    elseif ($avg_upld >= 4) $status_avg_upld = "error";

    $number_err = $server["number_error"];
    if ($number_err < 1) $status_number_err = "norm";
    elseif ($number_err >= 1 && $number_err < 10) $status_number_err = "warn";
    elseif ($number_err >= 10) $status_number_err = "error";

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
                        <div class="circle warn"></div>
                        <div>Last connection</div>
                    </div>
                    <div class="text-warn"><?php echo $server["last_connection"] ?></div>
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
        <div class="db-extented-info card">

        </div>
    </div>
<?php } ?>