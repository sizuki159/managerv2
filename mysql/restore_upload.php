<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\File\FileInfo;
    use Librarys\Database\DatabaseBackupRestore;

    define('LOADED',               1);
    define('DATABASE_CHECK_MYSQL', 1);

    define('FUNC_STORE_FILE',        1);
    define('FUNC_RESTORE',           2);
    define('FUNC_STORE_AND_RESTORE', 3);

    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');

    $title   = lng('mysql.restore_upload.title_page');
    $themes  = [ env('resource.filename.theme.mysql') ];
    $scripts = [ env('resource.filename.javascript.custom_input_file') ];
    $appAlert->setID(ALERT_MYSQL_RESTORE_UPLOAD);
    require_once(ROOT . 'incfiles' . SP . 'header.php');
    requireDefine('mysql_restore_manager');

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));

    $databaseBackupRestore = new DatabaseBackupRestore($appMysqlConnect);
    $pathDatabaseBackup    = $databaseBackupRestore->getPathDirectoryDatabaseBackup();

    $forms = [
        'file'          => null,
        'name_store'    => null,
        'func_upload'   => FUNC_RESTORE
    ];

    if (isset($_POST['upload'])) {
        $forms['file']        = $_FILES['file_record'];
        $forms['name_store']  = addslashes($_POST['name_store']);
        $forms['func_upload'] = intval(addslashes($_POST['func_upload']));

        if (empty($forms['file']['name'])) {
            $appAlert->danger(lng('mysql.restore_upload.alert.empty_file_upload'));
        } else if ($forms['func_upload'] !== FUNC_STORE_FILE && $forms['func_upload'] !== FUNC_RESTORE && $forms['func_upload'] !== FUNC_STORE_AND_RESTORE) {
            $appAlert->danger(lng('mysql.restore_upload.alert.func_upload_not_validate'));
        } else if ($forms['file']['error'] === UPLOAD_ERR_INI_SIZE) {
            $appAlert->danger(lng('mysql.restore_upload.alert.file_upload_error_size', 'name', $forms['file']['name']));
        } else if (DatabaseBackupRestore::isFilenameValidate($forms['file']['name']) == false) {
            $appAlert->danger(lng('mysql.restore_upload.alert.file_upload_not_validate', 'name', $forms['file']['name']));
        } else if ($forms['func_upload'] !== FUNC_RESTORE && empty($forms['name_store']) == false && DatabaseBackupRestore::isFilenameValidate($forms['name_store']) == false) {
            $appAlert->danger(lng('mysql.restore_upload.alert.file_name_store_not_validate', 'name', $forms['name_store']));
        } else {
            $nameStore      = basename($forms['file']['name']);
            $pathStore      = $forms['file']['tmp_name'];
            $isStoreSuccess = true;

            if ($forms['func_upload'] !== FUNC_RESTORE) {
                $nameStore = $forms['file']['name'];

                if ($forms['name_store'] != null && empty($forms['name_store']) == false)
                    $nameStore = $forms['name_store'];

                $fileRename = null;
                $pathRename = null;
                $nameStore  = FileInfo::fileNameFix($nameStore);
                $pathStore  = FileInfo::filterPaths($databaseBackupRestore->getPathFileDatabaseBackup($nameStore));

                if (FileInfo::fileExists($pathStore)) {
                    for ($i = 0; $i < 50; ++$i) {
                        $fileRename = rand(10000, 99999) . '_' . $nameStore;
                        $pathRename = FileInfo::filterPaths($databaseBackupRestore->getPathFileDatabaseBackup($fileRename));

                        if (FileInfo::fileExists($pathRename) == false) {
                            break;
                        } else {
                            $fileRename = null;
                            $pathRename = null;
                        }
                    }

                    $nameStore = $fileRename;
                    $pathStore = $pathRename;
                }

                if (FileInfo::copyFile($forms['file']['tmp_name'], $pathStore) == false) {
                    $appAlert->danger(lng('mysql.restore_upload.alert.store_file_failed', 'name', $forms['file']['name']));
                    $isStoreSuccess = false;
                } else if ($forms['func_upload'] === FUNC_STORE_FILE) {
                    $appAlert->success(lng('mysql.restore_upload.alert.store_file_success', 'name', $forms['file']['name']), ALERT_MYSQL_RESTORE_MANAGER, 'restore_manager.php' . $appParameter->toString());
                }
            }

            if ($forms['func_upload'] !== FUNC_STORE_FILE && $isStoreSuccess) {
                if ($databaseBackupRestore->restore($pathStore) == false)
                    $appAlert->danger(lng('mysql.restore_upload.alert.restore_record_failed', 'name', $nameStore), 'error', $appMysqlConnect->error());
                else if ($forms['func_upload'] === FUNC_RESTORE)
                    $appAlert->success(lng('mysql.restore_upload.alert.restore_record_success', 'name', $nameStore, 'size', FileInfo::sizeToString($forms['file']['size'])), ALERT_MYSQL_LIST_TABLE, 'list_table.php' . $appParameter->toString());
                else if ($forms['func_upload'] === FUNC_STORE_AND_RESTORE)
                    $appAlert->success(lng('mysql.restore_upload.alert.store_and_restore_record_success', 'name', $nameStore, 'size', FileInfo::fileSize($pathStore, true)), ALERT_MYSQL_LIST_TABLE, 'list_table.php' . $appParameter->toString());
            }
        }

        $forms['name_store'] = stripslashes($forms['name_store']);
    }
?>

    <?php echo $appAlert->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('mysql.restore_upload.title_page'); ?>: <?php echo $appMysqlConnect->getName(); ?></span>
        </div>
        <form action="restore_upload.php<?php echo $appParameter->toString(); ?>" method="post" id="form-list-database-backup" enctype="multipart/form-data">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="input-file" id="input-file">
                    <input type="file" name="file_record" id="file_record"/>
                    <label for="file_record">
                        <span lng="<?php echo lng('mysql.restore_upload.form.input.choose_file'); ?>"><?php echo lng('mysql.restore_upload.form.input.choose_file'); ?></span>
                    </label>
                </li>
                <li class="input">
                    <span><?php echo lng('mysql.restore_upload.form.input.name_store'); ?></span>
                    <input type="text" name="name_store" value="<?php echo htmlspecialchars($forms['name_store']); ?>" placeholder="<?php echo lng('mysql.restore_upload.form.placeholder.input_name_store'); ?>"/>
                </li>
                <li class="radio-choose">
                    <span><?php echo lng('mysql.restore_upload.form.input.label_more_options'); ?></span>
                    <ul class="radio-choose-tab">
                        <li>
                            <input type="radio" name="func_upload" id="func-upload-store-file" value="<?php echo FUNC_STORE_FILE; ?>"<?php if ($forms['func_upload'] === FUNC_STORE_FILE) { ?> checked="checked"<?php } ?>/>
                            <label for="func-upload-store-file">
                                <span><?php echo lng('mysql.restore_upload.form.input.radio_func_upload_store_file'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="radio" name="func_upload" id="func-upload-restore" value="<?php echo FUNC_RESTORE; ?>"<?php if ($forms['func_upload'] === FUNC_RESTORE) { ?> checked="checked"<?php } ?>/>
                            <label for="func-upload-restore">
                                <span><?php echo lng('mysql.restore_upload.form.input.radio_func_upload_restore'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="radio" name="func_upload" id="func-upload-store-and-restore" value="<?php echo FUNC_STORE_AND_RESTORE; ?>"<?php if ($forms['func_upload'] === FUNC_STORE_AND_RESTORE) { ?> checked="checked"<?php } ?>/>
                            <label for="func-upload-store-and-restore">
                                <span><?php echo lng('mysql.restore_upload.form.input.radio_func_upload_store_and_restore'); ?></span>
                            </label>
                        </li>
                    </ul>
                </li>
                <li class="button">
                    <button type="submit" name="upload">
                        <span><?php echo lng('mysql.restore_upload.form.button.upload'); ?></span>
                    </button>
                    <a href="restore_manager.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('mysql.restore_upload.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="alert">
        <li class="info">
            <span><?php echo lng('mysql.restore_upload.alert.tips'); ?></span>
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