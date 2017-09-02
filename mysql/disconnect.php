<?php

    define('LOADED', 1);
    require_once('global.php');

    if ($appMysqlConfig->set('mysql_is_connect', false)) {
        if ($appMysqlConfig->write())
            $appAlert->success(lng('mysql.home.alert.disconnect_success'), ALERT_MYSQL_HOME, 'index.php');
    }

    $appAlert->success(lng('mysql.home.alert.disconnect_failed'), ALERT_MYSQL_HOME, 'list_database.php');

?>