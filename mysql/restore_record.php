<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\File\FileInfo;
    use Librarys\Database\DatabaseBackupRestore;

    define('LOADED',               1);
    define('DATABASE_CHECK_MYSQL', 1);

    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');

    $title   = lng('mysql.restore_record.title_page');
    $themes  = [ env('resource.filename.theme.mysql') ];
    $appAlert->setID(ALERT_MYSQL_RESTORE_RECORD);
    require_once(ROOT . 'incfiles' . SP . 'header.php');
    requireDefine('mysql_restore_manager');

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));

    $recordName = null;

    if (isset($_GET[MYSQL_RESTORE_RECORD_PARAMETER_FILE_URL]) && empty($_GET[MYSQL_RESTORE_RECORD_PARAMETER_FILE_URL]) == false)
        $recordName = AppDirectory::rawDecode($_GET[MYSQL_RESTORE_RECORD_PARAMETER_FILE_URL]);

    $databaseBackupRestore = new DatabaseBackupRestore($appMysqlConnect);
    $pathDatabaseBackup    = $databaseBackupRestore->getPathDirectoryDatabaseBackup();
    $pathFileRecordBackup  = $databaseBackupRestore->getPathFileDatabaseBackup($recordName);

    if (FileInfo::isTypeFile($pathFileRecordBackup) == false)
        $appAlert->danger(lng('mysql.restore_record.alert.record_file_not_exists', 'name', $recordName), ALERT_MYSQL_RESTORE_MANAGER, 'restore_manager.php' . $appParameter->toString());

    if (isset($_POST['restore'])) {
        $databaseBackupRestore->setRestoreFilename($recordName);

        if ($databaseBackupRestore->restore() == false)
            $appAlert->danger(lng('mysql.restore_record.alert.restore_record_failed', 'name', $recordName), 'error', $appMysqlConnect->error());
        else
            $appAlert->success(lng('mysql.restore_record.alert.restore_record_success', 'name', $recordName), ALERT_MYSQL_LIST_TABLE, 'list_table.php' . $appParameter->toString());
    }
?>

    <?php echo $appAlert->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('mysql.restore_record.title_page'); ?>: <?php echo $appMysqlConnect->getName(); ?></span>
        </div>
        <form action="restore_record.php<?php echo $appParameter->toString(); ?>&<?php echo MYSQL_RESTORE_RECORD_PARAMETER_FILE_URL; ?>=<?php echo AppDirectory::rawEncode($recordName); ?>" method="post" id="form-list-database-backup">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="accept">
                    <span><?php echo lng('mysql.restore_record.form.input.accept_message', 'name', $recordName); ?></span>
                </li>

                <li class="button">
                    <button type="submit" name="restore">
                        <span><?php echo lng('mysql.restore_record.form.button.restore'); ?></span>
                    </button>
                    <a href="restore_manager.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('mysql.restore_manager.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

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
            <a href="list_table.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-table"></span>
                <span><?php echo lng('mysql.home.menu_action.list_table'); ?></span>
            </a>
        </li>
        <li>
            <a href="list_database.php">
                <span class="icomoon icon-mysql"></span>
                <span><?php echo lng('mysql.home.menu_action.list_database'); ?></span>
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