<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\App\AppPaging;
    use Librarys\File\FileInfo;

    define('LOADED',               1);
    define('MYSQL_LIST_DATA',      1);
    define('DATABASE_CHECK_MYSQL', 1);
    define('TABLE_CHECK_MYSQL',    1);

    define('PARAMETER_PAGE_DATA_URL',  'page_data');
    define('PARAMETER_ORDER_DATA_URL', 'order_data');

    define('ORDER_DATA_DESC', 'desc');
    define('ORDER_DATA_ASC',  'asc');

    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));
    $appParameter->add(PARAMETER_TABLE_URL,    AppDirectory::rawEncode($appMysqlConnect->getTableCurrent()));

    $mysqlBy      = addslashes($appMysqlConnect->getColumnKey($appMysqlConnect->getTableCurrent()));
    $mysqlStr     = null;
    $mysqlEmptyBy = empty($mysqlBy);
    $mysqlTable   = addslashes($appMysqlConnect->getTableCurrent());
    $mysqlNums  = 0;

    if ($mysqlEmptyBy == false)
        $mysqlNums = $appMysqlConnect->numRows('SELECT * FROM `' . $mysqlTable . '`');

    if ($mysqlNums <= 0 && isset($_SERVER['HTTP_REFERER']) && stripos( $_SERVER['HTTP_REFERER'], 'list_table.php') !== false)
        gotoURL('list_column.php' . $appParameter->toString());
    else if ($mysqlNums <= 0)
        $appAlert->warning(lng('mysql.list_data.alert.data_is_empty_not_view'), ALERT_MYSQL_LIST_COLUMN, 'list_column.php' . $appParameter->toString());

    $title  = lng('mysql.list_data.title_page');
    $themes = [ env('resource.filename.theme.mysql') ];
    $appAlert->setID(ALERT_MYSQL_LIST_DATA);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $orders = [
        'key'   => ORDER_DATA_DESC,
        'query' => strtoupper(ORDER_DATA_DESC)
    ];

    $pagings = [
        'current'     => 0,
        'total'       => 0,
        'begin_query' => 0,
        'end_query'   => $mysqlNums,
        'row_on_page' => $mysqlNums,
        'max'         => $appConfig->get('paging.mysql_list_data', 0)
    ];

    if (isset($_GET[PARAMETER_ORDER_DATA_URL]) && empty($_GET[PARAMETER_ORDER_DATA_URL]) == false) {
        $orders['key'] = trim(addslashes($_GET[PARAMETER_ORDER_DATA_URL]));

        if ($orders['key'] != ORDER_DATA_DESC && $orders['key'] != ORDER_DATA_ASC)
            $orders['key'] = ORDER_DATA_DESC;

        $orders['query'] = strtoupper($orders['key']);
    }

    if (isset($_GET[PARAMETER_PAGE_DATA_URL]) && empty($_GET[PARAMETER_PAGE_DATA_URL]) == false)
        $pagings['current'] = intval(addslashes($_GET[PARAMETER_PAGE_DATA_URL]));

    if ($pagings['current'] <= 0)
        $pagings['current'] = 1;

    if ($pagings['max'] > 0) {
        $pagings['begin_query'] = ($pagings['current']     * $pagings['max']) - $pagings['max'];
        $pagings['end_query']   = ($pagings['begin_query'] + $pagings['max']);
        $pagings['row_on_page'] = ($pagings['end_query'] - $pagings['begin_query']);
        $pagings['end_query']   = $pagings['max'];
        $pagings['total']       = ceil($mysqlNums / $pagings['max']);
    }

    $urlBeginPaging = 'list_data.php?' . PARAMETER_DATABASE_URL   . '=' . AppDirectory::rawEncode($appMysqlConnect->getName()) .
                                   '&' . PARAMETER_TABLE_URL      . '=' . AppDirectory::rawEncode($appMysqlConnect->getTableCurrent()) .
                                   '&' . PARAMETER_ORDER_DATA_URL . '=' . AppDirectory::rawEncode($orders['key']);

    $pagePaging = new AppPaging(
        $urlBeginPaging,
        $urlBeginPaging . '&' . PARAMETER_PAGE_DATA_URL . '='
    );

    if ($pagings['max'] > 0 && $mysqlEmptyBy == false) {
        $mysqlStr = 'SELECT * ' .
                    'FROM `' .     $mysqlTable . '` ' .
                    'ORDER BY `' . $mysqlBy . '` ' . $orders['query'] . ' ' .
                    'LIMIT ' . $pagings['begin_query'] . ', ' . $pagings['end_query'];
    } else if ($mysqlEmptyBy == false) {
        $mysqlStr = 'SELECT * ' .
                    'FROM `' . $mysqlTable . '` ' .
                    'ORDER BY `' . $mysqlBy . '` ' . $orders['query'];
    }

    $mysqlQuery = $appMysqlConnect->query($mysqlStr);
?>

    <?php echo $appAlert->display(); ?>

    <div class="mysql-query-string">
        <span><?php echo $appMysqlConnect->getMysqlQueryExecStringCurrent(); ?></span>
    </div>

    <ul class="list-database">
        <li class="back">
            <a href="info_table.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-table"></span>
            </a>
            <a href="list_table.php?<?php echo PARAMETER_DATABASE_URL; ?>=<?php echo AppDirectory::rawEncode($appMysqlConnect->getName()); ?>&<?php echo PARAMETER_TABLE_URL; ?>=<?php echo AppDirectory::rawEncode($appMysqlConnect->getTableCurrent()); ?>">
                <span><?php echo $appMysqlConnect->getTableCurrent(); ?></span>
            </a>
        </li>
        <?php if ($mysqlNums <= 0) { ?>
            <li class="empty">
                <span class="icomoon icon-column"></span>
                <span><?php echo lng('mysql.list_data.alert.empty_list_data'); ?></span>
            </li>
        <?php } else { ?>
            <li class="order">
                <?php if ($orders['key'] == ORDER_DATA_ASC) { ?>
                    <span class="current"><?php echo strtoupper(ORDER_DATA_ASC); ?></span>
                    <span class="text">|</span>
                    <a href="list_data.php<?php echo $appParameter->toString(); ?>&<?php echo PARAMETER_ORDER_DATA_URL; ?>=<?php echo ORDER_DATA_DESC; ?>">
                        <span class="choose"><?php echo strtoupper(ORDER_DATA_DESC); ?></span>
                    </a>
                <?php } else { ?>
                    <a href="list_data.php<?php echo $appParameter->toString(); ?>&<?php echo PARAMETER_ORDER_DATA_URL; ?>=<?php echo ORDER_DATA_ASC; ?>">
                        <span class="choose"><?php echo strtoupper(ORDER_DATA_ASC); ?></span>
                    </a>
                    <span class="text">|</span>
                    <span class="current"><?php echo strtoupper(ORDER_DATA_DESC); ?></span>
                <?php } ?>
            </li>

            <?php $indexAssoc = 0; ?>

            <?php while ($mysqlAssoc = $appMysqlConnect->fetchAssoc($mysqlQuery)) { ?>
                <?php $indexAssoc++; ?>

                <li class="type-column<?php if ($indexAssoc === $pagings['row_on_page'] && ($pagings['row_on_page'] % 2) !== 0) { ?> entry-odd<?php } ?><?php if ($pagings['row_on_page'] === 1) { ?> entry-only-one<?php } ?>">
                    <a href="#">
                        <span class="icomoon icon-column"></span>
                    </a>
                    <a href="#">
                        <span><?php echo $mysqlAssoc[$mysqlBy]; ?></span>
                    </a>
                </li>
            <?php } ?>

            <?php if ($pagings['max'] > 0 && $pagings['total'] > 1) { ?>
                <li class="paging">
                    <?php echo $pagePaging->display($pagings['current'], $pagings['total']); ?>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>

    <ul class="menu-action">
        <li>
            <a href="info_table.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-about"></span>
                <span><?php echo lng('mysql.list_table.menu_action.info_table'); ?></span>
            </a>
        </li>
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
                <span class="icomoon icon-edit"></span>
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