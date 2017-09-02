<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\File\FileInfo;
    use Librarys\Database\DatabaseBackupRestore;

    define('LOADED',               1);
    define('MYSQL_LIST_TABLE',     1);
    define('DATABASE_CHECK_MYSQL', 1);

    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');

    $title   = lng('mysql.list_table.title_page');
    $themes  = [ env('resource.filename.theme.mysql') ];
    $scripts = [ env('resource.filename.javascript.checkbox_checkall') ];
    $appAlert->setID(ALERT_MYSQL_LIST_TABLE);
    require_once(ROOT . 'incfiles' . SP . 'header.php');
    requireDefine('mysql_action_table');

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));

    $mysqlStr   = 'SHOW TABLE STATUS';
    $mysqlQuery = $appMysqlConnect->query($mysqlStr);
    $mysqlNums  = $appMysqlConnect->numRows($mysqlQuery);

    $databaseBackupRestore = new DatabaseBackupRestore($appMysqlConnect);
    $databaseBackupRestore->autoScanClean();
?>

    <?php echo $appAlert->display(); ?>

    <div class="mysql-query-string">
        <span><?php echo $appMysqlConnect->getMysqlQueryExecStringCurrent(); ?></span>
    </div>

    <form action="action_table.php<?php echo $appParameter->toString(); ?>" method="post" id="form-list-database">
        <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

        <ul class="list-database">
            <?php if ($appMysqlConnect->isDatabaseNameCustom()) { ?>
                <li class="back">
                    <a href="info_database.php<?php echo $appParameter->toString(); ?>&<?php echo PARAMETER_IS_REFERER_LIST_TABLE; ?>=1">
                        <span class="icomoon icon-mysql"></span>
                    </a>
                    <a href="list_database.php">
                        <strong><?php echo $appMysqlConnect->getName(); ?></strong>
                    </a>
                </li>
            <?php } ?>

            <?php if ($mysqlNums <= 0) { ?>
                <li class="empty">
                    <span class="icomoon icon-mysql"></span>
                    <span><?php echo lng('mysql.list_table.alert.empty_list_table'); ?></span>
                </li>
            <?php } else { ?>
                <?php $mysqlAssoc = null; ?>
                <?php $indexAssoc = 0; ?>

                <?php while (($mysqlAssoc = $appMysqlConnect->fetchAssoc($mysqlQuery))) { ?>
                    <?php $urlParameterTable  = '?' . PARAMETER_DATABASE_URL . '=' . AppDirectory::rawEncode($appMysqlConnect->getName()); ?>
                    <?php $urlParameterTable .= '&' . PARAMETER_TABLE_URL    . '=' . AppDirectory::rawEncode($mysqlAssoc['Name']); ?>
                    <?php $indexAssoc++; ?>

                    <li class="type-table<?php if ($indexAssoc === $mysqlNums && ($mysqlNums % 2) !== 0)  { ?> entry-odd<?php } ?><?php if ($mysqlNums === 1) { ?> entry-only-one<?php } ?>">
                        <div class="icon">
                            <?php $id = 'table-' . $mysqlAssoc['Name']; ?>

                            <input
                                type="checkbox"
                                name="tables[]"
                                id="<?php echo $id; ?>"
                                value="<?php echo $mysqlAssoc['Name']; ?>"
                                <?php if ($appConfig->get('enable_disable.count_checkbox_mysql_javascript')) { ?> onclick="javascript:CheckboxCheckAll.onCheckItem('<?php echo $id; ?>')"<?php } ?>/>

                            <label for="<?php echo $id; ?>" class="not-content"></label>
                            <a href="info_table.php<?php echo $urlParameterTable; ?>">
                                <span class="icomoon icon-table"></span>
                            </a>
                        </div>
                        <a href="list_data.php<?php echo $urlParameterTable; ?>" class="name">
                            <span><?php echo $mysqlAssoc['Name']; ?></span>
                        </a>
                        <div class="info">
                            <span><?php echo FileInfo::sizeToString($mysqlAssoc['Index_length']); ?></span><span>,</span>
                            <?php if ($mysqlAssoc['Rows'] <= 0) { ?>
                                <?php $mysqlAssoc['Rows'] = $appMysqlConnect->numRows('SHOW COLUMNS FROM `' . addslashes($mysqlAssoc['Name']) . '`'); ?>
                            <?php } ?>

                            <span><?php echo $mysqlAssoc['Rows']; ?> <?php echo lng('mysql.list_table.column_count'); ?></span>
                        </div>
                    </li>
                <?php } ?>

                <li class="checkbox-all">
                    <input type="checkbox" name="checked_all_entry" id="checked-all-entry" onclick="javascript:CheckboxCheckAll.onCheckAll();"/>
                    <label for="checked-all-entry">
                        <span><?php echo lng('mysql.list_table.form.input.checkbox_all_entry'); ?></span>
                        <?php if ($appConfig->get('enable_disable.count_checkbox_mysql_javascript')) { ?>
                            <span id="checkall-count"></span>
                            <script type="text/javascript">
                                OnLoad.add(function() {
                                    CheckboxCheckAll.onInitForm('form-list-database', 'checked-all-entry', 'checkall-count');
                                });
                            </script>
                        <?php } ?>
                    </label>
                </li>
            <?php } ?>
        </ul>

        <?php if ($mysqlNums > 0) { ?>
            <ul class="action-multi">
                <li>
                    <button type="submit" name="action" value="<?php echo MYSQL_ACTION_TABLE_DELETE_MULTI; ?>">
                        <span class="icomoon icon-trash"></span>
                        <span class="label"><?php echo lng('mysql.list_table.action_multi.delete'); ?></span>
                    </button>
                </li>
                <li>
                    <button type="submit" name="action" value="<?php echo MYSQL_ACTION_TABLE_TRUNCATE_MULTI; ?>">
                        <span class="icomoon icon-storage"></span>
                        <span class="label"><?php echo lng('mysql.list_table.action_multi.truncate'); ?></span>
                    </button>
                </li>
                <li>
                    <button type="submit" name="action" value="<?php echo MYSQL_ACTION_TABLE_BACKUP_MULTI; ?>">
                        <span class="icomoon icon-backup"></span>
                        <span class="label"><?php echo lng('mysql.list_table.action_multi.backup'); ?></span>
                    </button>
                </li>
            </ul>
        <?php } ?>
    </form>

    <ul class="alert">
        <li class="info">
            <span><?php echo lng('mysql.list_table.alert.tips', 'type', $appMysqlConnect->getExtension()->getExtensionType()); ?></span>
        </li>
    </ul>

    <ul class="menu-action">
        <li>
            <a href="create_table.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-plus"></span>
                <span><?php echo lng('mysql.home.menu_action.create_table'); ?></span>
            </a>
        </li>
        <li>
            <a href="restore_upload.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-upload"></span>
                <span><?php echo lng('mysql.home.menu_action.restore_upload'); ?></span>
            </a>
        </li>
        <li>
            <a href="restore_manager.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-restore"></span>
                <span><?php echo lng('mysql.home.menu_action.restore_manager', 'count', $databaseBackupRestore->getRestoreDatabaseRecordCount()); ?></span>
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