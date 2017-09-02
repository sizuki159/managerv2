<?php

    if (defined('LOADED') == false)
        exit;

    define('MYSQL_REQUIRE',                   1);
    define('PARAMETER_DATABASE_URL',          'database');
    define('PARAMETER_TABLE_URL',             'table');
    define('PARAMETER_COLUMN_URL',            'column');
    define('PARAMETER_IS_REFERER_LIST_TABLE', 'is_referer_list_table');
    define('ROOT',                            '..' . DIRECTORY_SEPARATOR);

    require_once(
        realpath(ROOT) .
        DIRECTORY_SEPARATOR . 'incfiles' .
        DIRECTORY_SEPARATOR . 'global.php'
    );

    use Librarys\App\AppDirectory;
    use Librarys\App\Mysql\AppMysqlConnect;

    $appMysqlConnect = new AppMysqlConnect($boot);
    $appMysqlConnect->setDatabaseExtensionDefault('Librarys\Database\Extension\DatabaseExtensionMysql');
    $appMysqlConnect->setDatabaseExtensionRuntime('Librarys\Database\Extension\DatabaseExtensionMysqli');
    $appMysqlConnect->executeInitializing();

    if ($appMysqlConfig->get('mysql_is_connect', false) == true) {
        $appMysqlConnect->setHost    ($appMysqlConfig->get('mysql_host'));
        $appMysqlConnect->setUsername($appMysqlConfig->get('mysql_username'));
        $appMysqlConnect->setPassword($appMysqlConfig->get('mysql_password'));

        if (isset($_GET[PARAMETER_DATABASE_URL]) && empty($_GET[PARAMETER_DATABASE_URL]) == false) {
            if ($appMysqlConfig->get('mysql_name') == null) {
                $appMysqlConnect->setName(trim(addslashes(AppDirectory::rawDecode($_GET[PARAMETER_DATABASE_URL]))));
                $appMysqlConnect->setDatabaseNameCustom(true);
            } else {
                $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConfig->get('mysql_name')), ALERT_MYSQL_LIST_TABLE, 'list_table.php');
            }
        } else if (defined('DATABASE_CHECK_MYSQL') && empty($_GET[PARAMETER_DATABASE_URL]) && $appMysqlConfig->get('mysql_name') == null) {
            $appAlert->danger(lng('mysql.home.alert.mysql_connect_database_name_failed', 'name', $appMysqlConfig->get('mysql_name')), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');
        }

        if ($appMysqlConfig->get('mysql_name') != null) {
            $appMysqlConnect->setName($appMysqlConfig->get('mysql_name'));
            $appMysqlConnect->setDatabaseNameCustom(false);
        }

        $appMysqlConnect->setPort    ($appMysqlConfig->get('mysql_port'));
        $appMysqlConnect->setEncoding($appMysqlConfig->get('mysql_encoding'));

        $idAlertMysql     = ALERT_MYSQL_LIST_DATABASE;
        $urlRedirectMysql = 'list_database.php';

        if ($appMysqlConfig->get('mysql_name') != null || $appMysqlConnect->isDatabaseNameCustom()) {
            $idAlertMysql     = ALERT_MYSQL_LIST_TABLE;
            $urlRedirectMysql = 'list_table.php';
        }

        if ($appMysqlConnect->openConnect(false) == false) {
            if ($appMysqlConnect->isDatabaseNameCustom())
                $appAlert->danger(lng('mysql.home.alert.mysql_connect_database_name_failed', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, env('app.http.host') . '/mysql/list_database.php');
            else if (defined('MYSQL_HOME') && strtolower($_SERVER['REQUEST_METHOD']) == 'get')
                $appAlert->danger(lng('mysql.home.alert.mysql_connect_failed', 'error', $appMysqlConnect->errorConnect()), ALERT_MYSQL_HOME);
            else if (defined('MYSQL_HOME') == false)
                $appAlert->danger(lng('mysql.home.alert.mysql_connect_failed', 'error', $appMysqlConnect->errorConnect()), ALERT_MYSQL_HOME, env('app.http.host') . '/mysql');
        } else if (defined('MYSQL_HOME')) {
            $appAlert->info(lng('mysql.home.alert.mysql_is_already_connect'), $idAlertMysql, $urlRedirectMysql);
        }

        if (isset($_GET[PARAMETER_TABLE_URL]) && empty($_GET[PARAMETER_TABLE_URL]) == false) {
            $appMysqlConnect->setTableCurrent(AppDirectory::rawDecode($_GET[PARAMETER_TABLE_URL]));

            if ($appMysqlConnect->checkTableCurrent() == false)
                $appAlert->danger(lng('mysql.home.alert.mysql_table_name_not_exists', 'table', $appMysqlConnect->getTableCurrent(), 'database', $appMysqlConnect->getName()), $idAlertMysql, $urlRedirectMysql);
        } else if (defined('TABLE_CHECK_MYSQL') && empty($_GET[PARAMETER_TABLE_URL])) {
            $appAlert->danger(lng('mysql.home.alert.mysql_table_not_exists', 'database', $appMysqlConnect->getName()), $idAlertMysql, $urlRedirectMysql);
        }

        if (isset($_GET[PARAMETER_COLUMN_URL]) && empty($_GET[PARAMETER_COLUMN_URL]) == false) {
            $appMysqlConnect->setColumnCurrent(AppDirectory::rawDecode($_GET[PARAMETER_COLUMN_URL]));
            $appMysqlConnect->checkColumnCurrent();
        }

        unset($idAlertMysql);
        unset($urlRedirectMysql);
    }
?>