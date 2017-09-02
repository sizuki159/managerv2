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

    $themes  = [ env('resource.filename.theme.mysql') ];
    $scripts = [ env('resource.filename.javascript.checkbox_checkall') ];
    $appAlert->setID(ALERT_MYSQL_ACTION_TABLE);
    requireDefine('mysql_action_table');

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));

    $listTables  = array();
    $countTables = 0;

    if (isset($_POST['tables']) && is_array($_POST['tables'])) {
        $listTables  = $_POST['tables'];
        $countTables = count($listTables);
    }

    if ($countTables <= 0)
        $appAlert->danger(lng('mysql.action_table.alert.not_table_select'), ALERT_MYSQL_LIST_TABLE, 'list_table.php' . $appParameter->toString());

    $listEntrys = AppDirectory::rawDecodes($listTables);
    $nameAction = null;

    if (isset($_POST['action']) && empty($_POST['action']) == false)
        $nameAction = addslashes(trim($_POST['action']));

    if ($nameAction == MYSQL_ACTION_TABLE_DELETE_MULTI)
        $title = 'delete';
    else if ($nameAction == MYSQL_ACTION_TABLE_TRUNCATE_MULTI)
        $title = 'truncate';
    else if ($nameAction == MYSQL_ACTION_TABLE_BACKUP_MULTI)
        $title = 'backup';
    else
        $appAlert->danger(lng('mysql.action_table.alert.action_not_validate'), ALERT_MYSQL_LIST_TABLE, 'list_table.php' . $appParameter->toString());

    $title = lng('mysql.action_table.title.' . $title);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $queryTables = $appMysqlConnect->query('SHOW TABLE STATUS');
    $numsTables  = 0;

    if ($appMysqlConnect->isResource($queryTables)) {
        $numsTables = $appMysqlConnect->numRows($queryTables);

        if ($numsTables <= 0)
            $appAlert->danger(lng('mysql.action_table.alert.no_table'), ALERT_MYSQL_LIST_TABLE, 'list_table.php' . $appParameter->toString());
    }

    $forms = [
        'backup' => [
            'filename' => 'mysql_' . $appMysqlConnect->getName() . '_' . date('d-m-Y_H-i', time()) . '.' . DatabaseBackupRestore::MIME
        ]
    ];

    $databaseBackupRestore  = new DatabaseBackupRestore($appMysqlConnect);

    if (isset($_POST['delete_button'])) {
        $isFailed     = false;
        $countSuccess = $countTables;

        foreach ($listTables AS $tableName) {
            if ($appMysqlConnect->query('DROP TABLE `' . addslashes($tableName)) == false) {
                $isFailed = true;
                $countSuccess--;
                $appAlert->danger(lng('mysql.action_table.alert.DELETE.delete_table_failed', 'name', $tableName, 'error', $appMysqlConnect->error()));
            }
        }

        if ($isFailed == false)
            $appAlert->success(lng('mysql.action_table.alert.delete.delete_success'), ALERT_MYSQL_LIST_TABLE, 'list_table.php' . $appParameter->toString());
        else if ($countTables > 1 && $countSuccess > 0)
            $appAlert->success(lng('mysql.action_table.alert.delete.delete_success'));
    } else if (isset($_POST['truncate_button'])) {
        $isFailed     = false;
        $countSuccess = $countTables;

        foreach ($listTables AS $tableName) {
            if ($appMysqlConnect->query('TRUNCATE TABLE `' . addslashes($tableName)) == false) {
                $isFailed = true;
                $countSuccess--;
                $appAlert->danger(lng('mysql.action_table.alert.truncate.truncate_table_failed', 'name', $tableName, 'error', $appMysqlConnect->error()));
            }
        }

        if ($isFailed == false)
            $appAlert->success(lng('mysql.action_table.alert.truncate.truncate_success'), ALERT_MYSQL_LIST_TABLE, 'list_table.php' . $appParameter->toString());
        else if ($countTables > 1 && $countSuccess > 0)
            $appAlert->success(lng('mysql.action_table.alert.truncate.truncate_success'));
    } else if (isset($_POST['backup_button'])) {
        $isFailed                    = false;
        $countSuccess                = $countTables;
        $forms['backup']['filename'] = addslashes($_POST['filename']);
        $databaseBackupRestore->setBackupFilename($forms['backup']['filename']);

        if (empty($forms['backup']['filename'])) {
            $appAlert->danger(lng('mysql.action_table.alert.backup.not_input_filename'));
        } else if (FileInfo::isNameValidate($forms['backup']['filename']) == false) {
            $appAlert->danger(lng('mysql.action_table.alert.backup.filename_not_validate', 'validate', FileInfo::FILENAME_VALIDATE));
        } else if (FileInfo::fileExists($databaseBackupRestore->getPathFileDatabaseBackup())) {
            $appAlert->danger(lng('mysql.action_table.alert.backup.filename_is_exists', 'name', $forms['backup']['filename']));
        } else if ($databaseBackupRestore->backupInfomation() == false) {
            $isFailed = true;
            $appAlert->danger(lng('mysql.action_table.alert.backup.backup_infomation_failed', 'name', $appMysqlConnect->getName(), 'error', $appMysqlConnect->error()));
        } else {
            foreach ($listTables AS $tableName) {
                if ($databaseBackupRestore->backupTable(addslashes($tableName)) == false) {
                    $isFailed = true;
                    $countSuccess--;
                    $appAlert->danger(lng('mysql.action_table.alert.backup.backup_table_failed', 'name', $tableName, 'error', $appMysqlConnect->error()));
                }
            }

            if ($isFailed == false)
                $appAlert->success(lng('mysql.action_table.alert.backup.backup_success', 'name', $forms['backup']['filename'], 'size', FileInfo::fileSize($databaseBackupRestore->getPathFileDatabaseBackup($forms['backup']['filename']), true)), ALERT_MYSQL_LIST_TABLE, 'list_table.php' . $appParameter->toString());
            else if ($countTables > 1 && $countSuccess > 0)
                $appAlert->success(lng('mysql.action_table.alert.backup.backup_success', 'name', $forms['backup']['filename']));
        }

        $forms['backup']['filename'] = stripslashes($forms['backup']['filename']);
    }
?>

    <?php $appAlert->display(); ?>

    <form action="action_table.php<?php echo $appParameter->toString(); ?>" method="post" id="form-list-database">
        <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>
        <input type="hidden" name="action" value="<?php echo $nameAction; ?>"/>

        <div class="form-action">
            <div class="title">
                <span><?php echo $title; ?></span>
            </div>

            <ul class="list-database no-box-shadow">
                <?php $indexAssoc = 0; ?>

                <?php while ($assocTable = $appMysqlConnect->fetchAssoc($queryTables)) { ?>
                    <?php $urlParameterTable  = '?' . PARAMETER_DATABASE_URL . '=' . AppDirectory::rawEncode($appMysqlConnect->getName()); ?>
                    <?php $urlParameterTable .= '&' . PARAMETER_TABLE_URL    . '=' . AppDirectory::rawEncode($assocTable['Name']); ?>
                    <?php $indexAssoc++; ?>

                    <li class="type-table<?php if ($indexAssoc === $numsTables && ($numsTables % 2) !== 0)  { ?> entry-odd<?php } ?><?php if ($numsTables === 1) { ?> entry-only-one<?php } ?>">
                        <div class="icon">
                            <?php $id = 'table-' . $assocTable['Name']; ?>

                            <input
                                type="checkbox"
                                name="tables[]"
                                id="<?php echo $id; ?>"
                                value="<?php echo $assocTable['Name']; ?>"
                                <?php if (in_array($assocTable['Name'], $listTables)) { ?>checked="checked"<?php } ?>
                                <?php if ($appConfig->get('enable_disable.count_checkbox_mysql_javascript')) { ?> onclick="javascript:CheckboxCheckAll.onCheckItem('<?php echo $id; ?>')"<?php } ?>/>
                            <label for="<?php echo $id; ?>" class="not-content"></label>
                            <a href="info_table.php<?php echo $urlParameterTable; ?>">
                                <span class="icomoon icon-table"></span>
                            </a>
                        </div>
                        <a href="list_data.php<?php echo $urlParameterTable; ?>" class="name">
                            <span><?php echo $assocTable['Name']; ?></span>
                        </a>
                    </li>
                <?php } ?>

                <li class="checkbox-all">
                    <input type="checkbox" name="checked_all_entry" id="checked-all-entry" onclick="javascript:CheckboxCheckAll.onCheckAll();" checked="checked"/>
                    <label for="checked-all-entry">
                        <span><?php echo lng('mysql.action_table.form.input.checkbox_all_entry'); ?></span>
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
            </ul>

            <ul class="form-element">

                <?php if ($nameAction == MYSQL_ACTION_TABLE_DELETE_MULTI) { ?>
                    <li class="accept">
                        <span><?php echo lng('mysql.action_table.form.input.delete.accept_message'); ?></span>
                    </li>

                    <li class="button">
                        <button type="submit" name="delete_button" id="button-save-on-javascript">
                            <span><?php echo lng('mysql.action_table.form.button.delete'); ?></span>
                        </button>
                        <a href="list_table.php<?php echo $appParameter->toString(); ?>">
                            <span><?php echo lng('mysql.action_table.form.button.cancel'); ?></span>
                        </a>
                    </li>
                <?php } else if ($nameAction == MYSQL_ACTION_TABLE_TRUNCATE_MULTI) { ?>
                    <li class="accept">
                        <span><?php echo lng('mysql.action_table.form.input.truncate.accept_message'); ?></span>
                    </li>

                    <li class="button">
                        <button type="submit" name="truncate_button" id="button-save-on-javascript">
                            <span><?php echo lng('mysql.action_table.form.button.truncate'); ?></span>
                        </button>
                        <a href="list_table.php<?php echo $appParameter->toString(); ?>">
                            <span><?php echo lng('mysql.action_table.form.button.cancel'); ?></span>
                        </a>
                    </li>
                <?php } else if ($nameAction == MYSQL_ACTION_TABLE_BACKUP_MULTI) { ?>
                    <li class="input">
                        <span><?php echo lng('mysql.action_table.form.input.backup.filename'); ?></span>
                        <input type="text" name="filename" value="<?php echo htmlspecialchars($forms['backup']['filename']); ?>" placeholder="<?php echo lng('mysql.action_table.form.placeholder.backup.input_filename'); ?>"/>
                    </li>

                    <li class="button">
                        <button type="submit" name="backup_button" id="button-save-on-javascript">
                            <span><?php echo lng('mysql.action_table.form.button.backup'); ?></span>
                        </button>
                        <a href="list_table.php<?php echo $appParameter->toString(); ?>">
                            <span><?php echo lng('mysql.action_table.form.button.cancel'); ?></span>
                        </a>
                    </li>
                <?php } ?>

            </ul>
        </div>

        <ul class="action-multi">
            <?php if ($nameAction != MYSQL_ACTION_TABLE_DELETE_MULTI) { ?>
                <li>
                    <button type="submit" name="action" value="<?php echo MYSQL_ACTION_TABLE_DELETE_MULTI; ?>">
                        <span class="icomoon icon-trash"></span>
                        <span class="label"><?php echo lng('mysql.list_table.action_multi.delete'); ?></span>
                    </button>
                </li>
            <?php } ?>
            <?php if ($nameAction != MYSQL_ACTION_TABLE_TRUNCATE_MULTI) { ?>
                <li>
                    <button type="submit" name="action" value="<?php echo MYSQL_ACTION_TABLE_TRUNCATE_MULTI; ?>">
                        <span class="icomoon icon-trash"></span>
                        <span class="label"><?php echo lng('mysql.list_table.action_multi.truncate'); ?></span>
                    </button>
                </li>
            <?php } ?>
            <?php if ($nameAction != MYSQL_ACTION_TABLE_BACKUP_MULTI) { ?>
                <li>
                    <button type="submit" name="action" value="<?php echo MYSQL_ACTION_TABLE_BACKUP_MULTI; ?>">
                        <span class="icomoon icon-backup"></span>
                        <span class="label"><?php echo lng('mysql.list_table.action_multi.backup'); ?></span>
                    </button>
                </li>
            <?php } ?>
            </ul>
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