<?php

    use Librarys\App\AppDirectory;
    use Librarys\File\FileInfo;
    use Librarys\Database\DatabaseConnect;

    define('LOADED', 1);
    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_TABLE, 'list_table.php');

    $title  = lng('mysql.info_database.title_page');
    $themes = [ env('resource.filename.theme.mysql') ];
    $appAlert->setID(ALERT_MYSQL_INFO_DATABASE);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    // Clone in phpmyadmin
    $fetchAssoc = $appMysqlConnect->fetchAssoc(
        'SELECT `' . DatabaseConnect::TABLE_SCHEMATA_INFORMATION . '`.*, ' .
                'COUNT(`tables`.`TABLE_SCHEMA`)  AS `SCHEMA_TABLES`, ' .
                'SUM(`tables`.`TABLE_ROWS`)      AS `SCHEMA_TABLE_ROWS`, ' .
                'SUM(`tables`.`DATA_LENGTH`)     AS `SCHEMA_DATA_LENGTH`, ' .
                'SUM(`tables`.`MAX_DATA_LENGTH`) AS `SCHEMA_MAX_DATA_LENGTH`, ' .
                'SUM(`tables`.`INDEX_LENGTH`)    AS `SCHEMA_INDEX_LENGTH`, ' .
                'SUM(`tables`.`DATA_LENGTH` + `tables`.`INDEX_LENGTH`) ' .
                                                'AS `SCHEMA_LENGTH`, ' .
                'SUM(`tables`.`DATA_FREE`)       AS `SCHEMA_DATA_FREE` ' .

        'FROM      `' . DatabaseConnect::DATABASE_INFORMATION . '`.`SCHEMATA` `schemata` ' .
        'LEFT JOIN `' . DatabaseConnect::DATABASE_INFORMATION . '`.`TABLES` `tables` ' .
        'ON        `tables`.`TABLE_SCHEMA` = `schemata`.`SCHEMA_NAME` ' .
        'WHERE     `schemata`.`SCHEMA_NAME`="' . addslashes($appMysqlConnect->getName()) . '"'
    );

    $fetchAssoc['SCHEMA_TABLES']       = intval($fetchAssoc['SCHEMA_TABLES']);
    $fetchAssoc['SCHEMA_TABLE_ROWS']   = intval($fetchAssoc['SCHEMA_TABLE_ROWS']);
    $fetchAssoc['SCHEMA_DATA_LENGTH']  = intval($fetchAssoc['SCHEMA_DATA_LENGTH']);
    $fetchAssoc['SCHEMA_INDEX_LENGTH'] = intval($fetchAssoc['SCHEMA_INDEX_LENGTH']);
    $fetchAssoc['SCHEMA_LENGTH']       = intval($fetchAssoc['SCHEMA_LENGTH']);

    $keys = [
        'SCHEMA_TABLES' => [
            'default' => 0,
            'func'    => 'intval'
        ],

        'SCHEMA_TABLE_ROWS' => [
            'default' => 0,
            'func'    => 'intval'
        ],

        'SCHEMA_DATA_LENGTH' => [
            'default' => 0,
            'func'    => 'intval'
        ],

        'SCHEMA_INDEX_LENGTH' => [
            'default' => 0,
            'func'    => 'intval'
        ],

        'SCHEMA_LENGTH' => [
            'default' => 0,
            'func'    => 'intval'
        ]
    ];

    foreach ($keys AS $key => $options) {
        if (isset($fetchAssoc[$key]) == false) {
            if ($options == null)
                $fetchAssoc[$key] = null;
            else
                $fetchAssoc[$key] = $options['default'];
        }

        if ($options != null)
            $fetchAssoc[$key] = $options['func']($fetchAssoc[$key]);
    }

    $urlBack = 'list_database.php';

    if (isset($_GET[PARAMETER_IS_REFERER_LIST_TABLE]) && empty($_GET[PARAMETER_IS_REFERER_LIST_TABLE]) == false) {
        if (intval($_GET[PARAMETER_IS_REFERER_LIST_TABLE]) === 1) {
            $urlBack  = 'list_table.php?';
            $urlBack .= PARAMETER_DATABASE_URL . '=' . AppDirectory::rawEncode($appMysqlConnect->getName());
        }
    }
?>

    <?php echo $appAlert->display(); ?>

    <ul class="mysql-info">
        <li class="title">
            <span><?php echo lng('mysql.info_database.title_page'); ?></span>
        </li>
        <li class="label">
            <ul>
                <li><span><?php echo lng('mysql.info_database.label_name'); ?></span></li>
                <li><span><?php echo lng('mysql.info_database.label_collation'); ?></span></li>
                <li><span><?php echo lng('mysql.info_database.label_data'); ?></span></li>
                <li><span><?php echo lng('mysql.info_database.label_tables'); ?></span></li>
                <li><span><?php echo lng('mysql.info_database.label_rows'); ?></span></li>
                <li><span><?php echo lng('mysql.info_database.label_indexes'); ?></span></li>
                <li><span><?php echo lng('mysql.info_database.label_total'); ?></span></li>
            </ul>
        </li>
        <li class="value">
            <ul>
                <li><span><?php echo $appMysqlConnect->getName(); ?></span></li>
                <li><span><?php echo $fetchAssoc['DEFAULT_COLLATION_NAME']; ?></span></li>
                <li><span><?php echo FileInfo::sizeToString($fetchAssoc['SCHEMA_DATA_LENGTH']); ?></span></li>
                <li><span><?php echo $fetchAssoc['SCHEMA_TABLES']; ?></span></li>
                <li><span><?php echo $fetchAssoc['SCHEMA_TABLE_ROWS']; ?></span></li>
                <li><span><?php echo FileInfo::sizeToString($fetchAssoc['SCHEMA_INDEX_LENGTH']); ?></span></li>
                <li><span><?php echo FileInfo::sizeToString($fetchAssoc['SCHEMA_LENGTH']); ?></span></li>
            </ul>
        </li>
    </ul>

    <ul class="menu-action">
        <li>
            <a href="create_database.php">
                <span class="icomoon icon-plus"></span>
                <span><?php echo lng('mysql.list_database.menu_action.create_database'); ?></span>
            </a>
        </li>
        <li>
            <a href="rename_database.php?<?php echo PARAMETER_DATABASE_URL; ?>=<?php echo AppDirectory::rawEncode($appMysqlConnect->getName()); ?>">
                <span class="icomoon icon-edit"></span>
                <span><?php echo lng('mysql.list_database.menu_action.rename_database'); ?></span>
            </a>
        </li>
        <li>
            <a href="delete_database.php?<?php echo PARAMETER_DATABASE_URL; ?>=<?php echo AppDirectory::rawEncode($appMysqlConnect->getName()); ?>">
                <span class="icomoon icon-trash"></span>
                <span><?php echo lng('mysql.list_database.menu_action.delete_database'); ?></span>
            </a>
        </li>
        <li>
            <a href="list_database.php">
                <span class="icomoon icon-mysql"></span>
                <span><?php echo lng('mysql.list_table.menu_action.list_database'); ?></span>
            </a>
        </li>
        <li>
            <a href="disconnect.php">
                <span class="icomoon icon-cord"></span>
                <span><?php echo lng('mysql.home.menu_action.disconnect'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php'); ?>