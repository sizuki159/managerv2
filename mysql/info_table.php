<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\File\FileInfo;
    use Librarys\Database\DatabaseConnect;

    define('LOADED',               1);
    define('DATABASE_CHECK_MYSQL', 1);
    define('TABLE_CHECK_MYSQL',    1);

    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');

    $title  = lng('mysql.info_table.title_page');
    $themes = [ env('resource.filename.theme.mysql') ];
    $appAlert->setID(ALERT_MYSQL_INFO_TABLE);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));
    $appParameter->add(PARAMETER_TABLE_URL,    AppDirectory::rawEncode($appMysqlConnect->getTableCurrent()));

    $fetchAssoc = $appMysqlConnect->fetchAssoc(
        'SELECT * ' .
        'FROM `' . DatabaseConnect::DATABASE_INFORMATION . '`.`TABLES` ' .
        'WHERE `TABLE_SCHEMA` IN ("' . addslashes($appMysqlConnect->getName()) . '") ' .
        'AND `TABLE_NAME`="' . addslashes($appMysqlConnect->getTableCurrent()) . '"'
    );

    $keys = [
        'TABLE_ROWS' => [
            'default' => 0,
            'func'    => 'intval'
        ],

        'AVG_ROW_LENGTH' => [
            'default' => 0,
            'func'    => 'intval'
        ],

        'DATA_LENGTH' => [
             'default' => 0,
             'func'    => 'intval'
         ],

        'INDEX_LENGTH' => [
            'default' => 0,
            'func'    => 'intval'
        ],

        'MAX_DATA_LENGTH' => [
            'default' => 0,
            'func'    => 'intval'
        ],

        'DATA_FREE' => [
            'default' => 0,
            'func'    => 'intval'
        ],

        'ROW_FORMAT' => null
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

    $urlBack = null;
?>

    <?php echo $appAlert->display(); ?>

    <ul class="mysql-info">
        <li class="title">
            <span><?php echo lng('mysql.info_table.title_page'); ?></span>
        </li>
        <li class="title_list">
            <span><?php echo lng('mysql.info_table.title_statistics'); ?></span>
        </li>
        <li class="label">
            <ul>
                <li><span><?php echo lng('mysql.info_table.label_name'); ?></span></li>
                <li><span><?php echo lng('mysql.info_table.label_engine'); ?></span></li>
                <li><span><?php echo lng('mysql.info_table.label_collation'); ?></span></li>
                <li><span><?php echo lng('mysql.info_table.label_row_format'); ?></span></li>
                <?php if ($fetchAssoc['TABLE_ROWS'] > 0) { ?>
                    <li><span><?php echo lng('mysql.info_table.label_rows'); ?></span></li>
                    <li><span><?php echo lng('mysql.info_table.label_rows_length'); ?></span></li>
                    <li><span><?php echo lng('mysql.info_table.label_rows_size'); ?></span></li>
                <?php } ?>
                <?php if ($fetchAssoc['AUTO_INCREMENT'] != null) { ?>
                    <li><span><?php echo lng('mysql.info_table.label_auto_increment'); ?></span></li>
                <?php } ?>
                <?php if ($fetchAssoc['CREATE_TIME'] != null) { ?>
                    <li><span><?php echo lng('mysql.info_table.label_create_time'); ?></span></li>
                <?php } ?>
                <?php if ($fetchAssoc['UPDATE_TIME'] != null) { ?>
                    <li><span><?php echo lng('mysql.info_table.label_update_time'); ?></span></li>
                <?php } ?>
                <?php if ($fetchAssoc['CHECK_TIME'] != null) { ?>
                    <li><span><?php echo lng('mysql.info_table.label_check_time'); ?></span></li>
                <?php } ?>
            </ul>
        </li>
        <li class="value">
            <ul>
                <li><span><?php echo $fetchAssoc['TABLE_NAME']; ?></span></li>
                <li><span><?php echo $fetchAssoc['ENGINE']; ?></span></li>
                <li><span><?php echo $fetchAssoc['TABLE_COLLATION']; ?></span></li>
                <li><span><?php echo $fetchAssoc['ROW_FORMAT']; ?></span></li>
                <?php if ($fetchAssoc['TABLE_ROWS'] > 0) { ?>
                    <li><span><?php echo $fetchAssoc['TABLE_ROWS']; ?></span></li>
                    <li><span><?php echo FileInfo::sizeToString($fetchAssoc['AVG_ROW_LENGTH']); ?></span></li>
                    <li><span><?php echo FileInfo::sizeToString(intval(($fetchAssoc['DATA_LENGTH'] + $fetchAssoc['INDEX_LENGTH']) / $fetchAssoc['TABLE_ROWS'])); ?></span></li>
                <?php } ?>
                <?php if ($fetchAssoc['AUTO_INCREMENT'] != null) { ?>
                    <li><span><?php echo $fetchAssoc['AUTO_INCREMENT']; ?></span></li>
                <?php } ?>

                <?php if ($fetchAssoc['CREATE_TIME'] != null) { ?>
                    <li><span><?php echo $fetchAssoc['CREATE_TIME']; ?></span></li>
                <?php } ?>

                <?php if ($fetchAssoc['UPDATE_TIME'] != null) { ?>
                    <li><span><?php echo $fetchAssoc['UPDATE_TIME']; ?></span></li>
                <?php } ?>

                <?php if ($fetchAssoc['CHECK_TIME'] != null) { ?>
                    <li><span><?php echo $fetchAssoc['CHECK_TIME']; ?></span></li>
                <?php } ?>
            </ul>
        </li>
        <li class="divider"></li>
        <li class="title_list">
            <span><?php echo lng('mysql.info_table.title_space_usage'); ?></span>
        </li>
        <li class="label">
            <ul>
                <li><span><?php echo lng('mysql.info_table.label_data_length'); ?></span></li>
                <li><span><?php echo lng('mysql.info_table.label_index_length'); ?></span></li>
                <li><span><?php echo lng('mysql.info_table.label_total_length'); ?>s</span></li>
            </ul>
        </li>
        <li class="value">
            <ul>
                <li><span><?php echo FileInfo::sizeToString($fetchAssoc['DATA_LENGTH']); ?></span></li>
                <li><span><?php echo FileInfo::sizeToString($fetchAssoc['INDEX_LENGTH']); ?></span></li>
                <li><span><?php echo FileInfo::sizeToString($fetchAssoc['DATA_LENGTH'] + $fetchAssoc['INDEX_LENGTH']); ?></span></li>
            </ul>
        </li>
        <?php if (empty($fetchAssoc['TABLE_COMMENT']) == false && $fetchAssoc['TABLE_COMMENT'] != null) { ?>
            <li class="comment">
                <div class="title">
                    <span><?php echo lng('mysql.info_table.label_comment'); ?></span>
                </div>
                <div class="comment">
                    <span><?php echo $fetchAssoc['TABLE_COMMENT']; ?></span>
                </div>
            </li>
        <?php } ?>
    </ul>

    <ul class="menu-action">
        <li>
            <a href="create_column.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-plus"></span>
                <span><?php echo lng('mysql.list_table.menu_action.create_column'); ?></span>
            </a>
        </li>
        <li>
            <a href="create_data.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-plus"></span>
                <span><?php echo lng('mysql.list_table.menu_action.create_data'); ?></span>
            </a>
        </li>
        <li>
            <a href="truncate_data.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-trash"></span>
                <span><?php echo lng('mysql.list_table.menu_action.truncate_data'); ?></span>
            </a>
        </li>
        <li>
            <a href="rename_table.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-trash"></span>
                <span><?php echo lng('mysql.list_table.menu_action.rename_table'); ?></span>
            </a>
        </li>
        <li>
            <a href="delete_table.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-trash"></span>
                <span><?php echo lng('mysql.list_table.menu_action.delete_table'); ?></span>
            </a>
        </li>
        <li>
            <a href="list_data.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-storage"></span>
                <span><?php echo lng('mysql.list_table.menu_action.list_data'); ?></span>
            </a>
        </li>
        <li>
            <a href="list_column.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-column"></span>
                <span><?php echo lng('mysql.list_table.menu_action.list_column'); ?></span>
            </a>
        </li>
        <li>
            <a href="list_table.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-table"></span>
                <span><?php echo lng('mysql.list_table.menu_action.list_table'); ?></span>
            </a>
        </li>

        <?php if ($appMysqlConnect->isDatabaseNameCustom()) { ?>
            <li>
                <a href="list_database.php">
                    <span class="icomoon icon-mysql"></span>
                    <span><?php echo lng('mysql.list_table.menu_action.list_database'); ?></span>
                </a>
            </li>
        <?php } ?>
    </ul>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php'); ?>