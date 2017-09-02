<?php

    use Librarys\App\AppDirectory;

    define('LOADED',              1);
    define('MYSQL_LIST_DATABASE', 1);

    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_TABLE, 'list_table.php');

    $title  = lng('mysql.list_database.title_page');
    $themes = [ env('resource.filename.theme.mysql') ];
    $appAlert->setID(ALERT_MYSQL_LIST_DATABASE);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $mysqlStr   = 'SHOW DATABASES';
    $mysqlQuery = $appMysqlConnect->query($mysqlStr);
    $mysqlNums  = $appMysqlConnect->numRows($mysqlQuery);

?>

    <?php echo $appAlert->display(); ?>

    <div class="mysql-query-string">
        <span><?php echo $appMysqlConnect->getMysqlQueryExecStringCurrent(); ?></span>
    </div>

    <ul class="list-database">
        <?php if ($mysqlNums <= 0) { ?>
            <li class="empty">
                <span class="icomoon icon-mysql"></span>
                <span><?php echo lng('mysql.list_database.empty_list_database'); ?></span>
            </li>
        <?php } else { ?>
            <?php $mysqlAssoc = null; ?>
            <?php $indexAssoc = 0; ?>

            <?php while (($mysqlAssoc = $appMysqlConnect->fetchAssoc($mysqlQuery))) { ?>
                <?php $indexAssoc++; ?>

                <li class="type-database<?php if ($indexAssoc === $mysqlNums && ($mysqlNums % 2) !== 0) { ?> entry-odd<?php } ?><?php if ($mysqlNums === 1) { ?> entry-only-one<?php } ?>">
                    <div class="icon">
                        <a href="info_database.php?<?php echo PARAMETER_DATABASE_URL; ?>=<?php echo AppDirectory::rawEncode($mysqlAssoc['Database']); ?>">
                            <span class="icomoon icon-mysql"></span>
                        </a>
                    </div>
                    <a href="list_table.php?<?php echo PARAMETER_DATABASE_URL; ?>=<?php echo AppDirectory::rawEncode($mysqlAssoc['Database']); ?>" class="name">
                        <span><?php echo $mysqlAssoc['Database']; ?></span>
                    </a>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>

    <ul class="alert">
        <li class="info">
            <span><?php echo lng('mysql.list_database.alert.tips', 'type', $appMysqlConnect->getExtension()->getExtensionType()); ?></span>
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
            <a href="disconnect.php">
                <span class="icomoon icon-cord"></span>
                <span><?php echo lng('mysql.home.menu_action.disconnect'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php'); ?>