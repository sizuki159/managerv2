<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\App\AppFileDownload;
    use Librarys\File\FileInfo;
    use Librarys\Zip\PclZip;
    use Librarys\Database\DatabaseBackupRestore;

    define('LOADED',               1);
    define('DATABASE_CHECK_MYSQL', 1);

    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');

    $title   = lng('mysql.restore_manager.title_page');
    $themes  = [ env('resource.filename.theme.mysql') ];
    $scripts = [ env('resource.filename.javascript.checkbox_checkall') ];
    $appAlert->setID(ALERT_MYSQL_RESTORE_MANAGER);
    requireDefine('mysql_restore_manager');

    $listRecords  = array();
    $countRecords = 0;

    if (isset($_POST['records']) && is_array($_POST['records'])) {
        $listRecords  = $_POST['records'];
        $countRecords = count($listRecords);
    }

    $listRecords = AppDirectory::rawDecodes($listRecords);
    $nameAction  = null;
    $isAction    = true;

    if (isset($_POST['action']) && empty($_POST['action']) == false)
        $nameAction = addslashes(trim($_POST['action']));

    if ($nameAction == MYSQL_RESTORE_MANAGER_ACTION_DELETE_MULTI)
        $title = lng('mysql.restore_manager.title.delete');
    else if ($nameAction == MYSQL_RESTORE_MANAGER_ACTION_DOWNLOAD_MULTI)
        $title = lng('mysql.restore_manager.title.download');
    else
        $isAction = false;

    if ($isAction && $countRecords <= 0)
        $appAlert->danger(lng('mysql.restore_manager.alert.not_record_select'));

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));

    $databaseBackupRestore = new DatabaseBackupRestore($appMysqlConnect);
    $pathDatabaseBackup    = $databaseBackupRestore->getPathDirectoryDatabaseBackup();
    $handle                = FileInfo::globDirectory($pathDatabaseBackup . SP . '*.' . DatabaseBackupRestore::MIME);
    $countHandle           = count($handle);
    $listBackups           = array();
    $countList             = 0;

    if ($handle !== false && $countHandle > 0) {
        foreach ($handle AS $entryPath)
            $listBackups[] = basename($entryPath);

        $countList = count($listBackups);
    }

    if (isset($_POST['delete_button'])) {
        $isFailed     = false;
        $countSuccess = $countRecords;

        foreach ($listRecords AS $recordFilename) {
            $recordPath = FileInfo::filterPaths($databaseBackupRestore->getPathDirectoryDatabaseBackup() . SP . $recordFilename);

            if (FileInfo::unlink($recordPath) == false) {
                $isFailed = true;
                $countSuccess--;
                $appAlert->danger(lng('mysql.restore_manager.alert.delete.delete_record_failed', 'name', $recordFilename));
            }
        }

        if ($isFailed == false)
            $appAlert->success(lng('mysql.restore_manager.alert.delete.delete_success'), null, 'restore_manager.php' . $appParameter->toString());
        else if ($countRecords > 1 && $countSuccess > 0)
            $appAlert->success(lng('mysql.restore_manager.delete_success'));
    } else if ($nameAction == MYSQL_RESTORE_MANAGER_ACTION_DOWNLOAD_MULTI || isset($_POST['download_button'])) {
        $pathDirectoryTmp = env('app.path.tmp');

        if (FileInfo::mkdir($pathDirectoryTmp, true) == false)
            $appAlert->danger(lng('mysql.restore_manager.alert.download.download_failed'));

        $pathFile     = FileInfo::filterPaths($pathDirectoryTmp . SP . md5(time()));
        $pclZip       = new PclZip($pathFile);
        $isFailed     = false;
        $countSuccess = $countRecords;

        foreach ($listRecords AS $recordFilename) {
            $recordPath = FileInfo::filterPaths($databaseBackupRestore->getPathDirectoryDatabaseBackup() . SP . $recordFilename);

            if ($pclZip->add($recordPath, PCLZIP_OPT_REMOVE_PATH, $databaseBackupRestore->getPathDirectoryDatabaseBackup()) == false) {
                $isFailed = true;
                $countSuccess--;
                $appAlert->danger(lng('mysql.restore_manager.alert.download.download_record_failed', 'name', $recordFilename, 'error', $pclZip->errorInfo(true)));
            }
        }

        if ($isFailed == false) {
            $filenameDownload = null;

            if ($countRecords === 1)
                $filenameDownload = str_replace('.' . DatabaseBackupRestore::MIME, null, $listRecords[0]) . '_compress.zip';
            else
                $filenameDownload = 'mysql_' . $appMysqlConnect->getName() . '_' . date('d-m-Y_H-i', time()) . '_compress.zip';

            $appFileDownload  = new AppFileDownload();

            if (
                $appFileDownload->setFileOnPath($pathFile, $filenameDownload) == false ||
                $appFileDownload->reponseHeader() == false ||
                $appFileDownload->download() == false
            ) {
                $appAlert->danger(lng('mysql.restore_manager.alert.download.download_failed'));
            }
        }
    }

    require_once(ROOT . 'incfiles' . SP . 'header.php');
?>

    <?php echo $appAlert->display(); ?>

    <form action="restore_manager.php<?php echo $appParameter->toString(); ?>" method="post" id="form-list-database-backup">
        <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

        <?php if ($isAction) { ?>
            <input type="hidden" name="action" value="<?php echo $nameAction; ?>"/>
        <?php } ?>

        <div class="form-action">
            <div class="title">
                <span><?php echo $title; ?>: <?php echo $appMysqlConnect->getName(); ?></span>
            </div>

            <ul class="list-database no-box-shadow">

                <?php if ($countList <= 0) { ?>
                    <li class="empty">
                        <span class="icomoon icon-backup"></span>
                        <span><?php echo lng('mysql.restore_manager.empty_backup_record'); ?></span>
                    </li>
                <?php } else { ?>
                    <?php $countChecked = 0; ?>

                    <?php for ($i = 0; $i < $countList; ++$i) { ?>
                        <?php $entryFilename = $listBackups[$i]; ?>
                        <?php $entryFilepath = FileInfo::filterPaths($pathDatabaseBackup . SP . $entryFilename); ?>

                        <li class="type-backup-record<?php if ($i + 1 === $countList && ($countList % 2) !== 0) { ?> entry-odd<?php } ?><?php if ($countList === 1) { ?> entry-only-one<?php } ?>">
                            <div class="icon">
                                <?php $id = 'backup-' . $entryFilename; ?>
                                <?php $isChecked = in_array($entryFilename, $listRecords); ?>

                                <input
                                    type="checkbox"
                                    name="records[]"
                                    id="<?php echo $id; ?>"
                                    value="<?php echo $entryFilename; ?>"
                                    <?php if ($isChecked) { ?>checked="checked"<?php } ?>
                                    <?php if ($appConfig->get('enable_disable.count_checkbox_mysql_javascript')) { ?> onclick="javascript:CheckboxCheckAll.onCheckItem('<?php echo $id; ?>')"<?php } ?>/>

                                <label for="<?php echo $id; ?>" class="not-content"></label>
                                <span class="icomoon icon-backup"></span>

                                <?php if ($isChecked) { ?>
                                    <?php $countChecked++; ?>
                                <?php } ?>
                            </div>
                            <a href="restore_record.php<?php echo $appParameter->toString(); ?>&<?php echo MYSQL_RESTORE_RECORD_PARAMETER_FILE_URL; ?>=<?php echo AppDirectory::rawEncode($entryFilename); ?>" class="name">
                                <span><?php echo $entryFilename; ?></span>
                            </a>
                            <div class="info">
                                <span><?php echo FileInfo::fileSize($entryFilepath, true); ?></span>
                            </div>
                        </li>
                    <?php } ?>

                    <li class="checkbox-all">
                        <input
                            type="checkbox"
                            name="checked_all_entry"
                            id="checked-all-entry"
                            <?php if ($countChecked === $countList) { ?>checked="checked"<?php } ?>
                            onclick="javascript:CheckboxCheckAll.onCheckAll();"/>

                        <label for="checked-all-entry">
                            <span><?php echo lng('mysql.restore_manager.form.input.checkbox_all_entry'); ?></span>
                            <?php if ($appConfig->get('enable_disable.count_checkbox_mysql_javascript')) { ?>
                                <span id="checkall-count"></span>
                                 <script type="text/javascript">
                                    OnLoad.add(function() {
                                        CheckboxCheckAll.onInitForm('form-list-database-backup', 'checked-all-entry', 'checkall-count');
                                    });
                                </script>
                           <?php } ?>
                        </label>
                    </li>
                <?php } ?>

            </ul>

            <ul class="form-element">
                <?php if ($nameAction == MYSQL_RESTORE_MANAGER_ACTION_DELETE_MULTI) { ?>
                    <li class="accept">
                        <span><?php echo lng('mysql.restore_manager.form.input.delete.accept_message'); ?></span>
                    </li>

                    <li class="button">
                        <button type="submit" name="delete_button">
                            <span><?php echo lng('mysql.restore_manager.form.button.delete'); ?></span>
                        </button>
                        <a href="restore_manager.php<?php echo $appParameter->toString(); ?>">
                            <span><?php echo lng('mysql.restore_manager.form.button.cancel'); ?></span>
                        </a>
                    </li>
                <?php } else if ($nameAction == MYSQL_RESTORE_MANAGER_ACTION_DOWNLOAD_MULTI) { ?>
                    <li class="button">
                        <button type="submit" name="download_button">
                            <span><?php echo lng('mysql.restore_manager.form.button.download'); ?></span>
                        </button>
                        <a href="restore_manager.php<?php echo $appParameter->toString(); ?>">
                            <span><?php echo lng('mysql.restore_manager.form.button.cancel'); ?></span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <?php if ($countList > 0 && $isAction == false) { ?>
            <ul class="action-multi">
                <li>
                    <button type="submit" name="action" value="<?php echo MYSQL_RESTORE_MANAGER_ACTION_DELETE_MULTI; ?>">
                        <span class="icomoon icon-trash"></span>
                        <span class="label"><?php echo lng('mysql.restore_manager.action_multi.delete'); ?></span>
                    </button>
                </li>
                <li>
                    <button type="submit" name="action" value="<?php echo MYSQL_RESTORE_MANAGER_ACTION_DOWNLOAD_MULTI; ?>">
                        <span class="icomoon icon-download"></span>
                        <span class="label"><?php echo lng('mysql.restore_manager.action_multi.download'); ?></span>
                    </button>
                </li>
            </ul>
        <?php } ?>

    </form>

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