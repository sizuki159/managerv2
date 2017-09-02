<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\Mysql\AppMysqlCollection;
    use Librarys\Database\DatabaseConnect;

    define('LOADED',               1);
    define('DATABASE_CHECK_MYSQL', 1);

    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_TABLE, 'list_table.php');

    $title  = lng('mysql.rename_database.title_page');
    $themes = [ env('resource.filename.theme.mysql') ];
    $appAlert->setID(ALERT_MYSQL_RENAME_DATABASE);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $fetchAssoc = $appMysqlConnect->fetchAssoc(
        'SELECT `' . DatabaseConnect::TABLE_SCHEMATA_INFORMATION . '`.* ' .
        'FROM   `' . DatabaseConnect::DATABASE_INFORMATION . '`.`SCHEMATA` `schemata` ' .
        'WHERE  `schemata`.`SCHEMA_NAME`="' . addslashes($appMysqlConnect->getName()) . '"'
    );

    $forms = [
        'name'          => $appMysqlConnect->getName(),
        'collection'    => AppMysqlCollection::getDefault(),
        'collection_db' => AppMysqlCollection::getDefault()
    ];

    if (is_array($fetchAssoc)) {
        $forms['collection']    = $fetchAssoc['DEFAULT_CHARACTER_SET_NAME'] . AppMysqlCollection::COLLECTION_SPLIT . $fetchAssoc['DEFAULT_COLLATION_NAME'];
        $forms['collection_db'] = $forms['collection'];
    }

    if (isset($_POST['rename'])) {
        $forms['name']       = addslashes($_POST['name']);
        $forms['collection'] = addslashes($_POST['collection']);

        if (empty($forms['name'])) {
            $appAlert->danger(lng('mysql.rename_database.alert.not_input_database_name'));
        } else if ($appMysqlConnect->isDatabaseNameExists($forms['name'], $appMysqlConnect->getName(), true)) {
            $appAlert->danger(lng('mysql.rename_database.alert.database_name_is_exists'));
        } else if ($forms['collection'] != AppMysqlCollection::COLLECTION_NONE && AppMysqlCollection::isValidate($forms['collection'], $charset, $collate) == false) {
            $appAlert->danger(lng('mysql.rename_database.alert.collection_not_validate'));
        } else {
            if ($forms['name'] == $appMysqlConnect->getName()) {
                if ($forms['collection'] == $forms['collection_db']) {
                    $appAlert->danger(lng('mysql.rename_database.alert.has_not_changed'));
                } else if ($forms['collection'] == AppMysqlCollection::COLLECTION_NONE) {
                    $appAlert->warning(lng('mysql.rename_database.alert.not_set_collection_to_none'));
                } else {
                    $mysqlQUeryAlterDatabase = 'ALTER DATABASE `' . addslashes($appMysqlConnect->getName()) . '` ' .
                                               'CHARACTER SET '   . addslashes($charset) . ' ' .
                                               'COLLATE       '   . addslashes($collate);

                    if ($appMysqlConnect->query($mysqlQUeryAlterDatabase) == false)
                        $appAlert->danger(lng('mysql.rename_database.alert.change_collection_failed', 'error', $appMysqlConnect->error()));
                    else
                        $appAlert->success(lng('mysql.rename_database.alert.change_collection_success', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');
                }
            } else {
                $mysqlStrCreateDatabase = 'CREATE DATABASE `' . $forms['name'] . '`';

                if ($forms['collection'] != AppMysqlCollection::COLLECTION_NONE) {
                    $mysqlStrCreateDatabase .= ' CHARACTER SET ' . addslashes($charset) .
                                               ' COLLATE '       . addslashes($collate);
                }

                if ($appMysqlConnect->query($mysqlStrCreateDatabase) == false) {
                    $appAlert->danger(lng('mysql.rename_database.alert.rename_database_failed', 'name', $appMysqlConnect->getName(), 'error', $appMysqlConnect->error()));
                } else {
                    $mysqlQueryTables = $appMysqlConnect->query('SHOW TABLE STATUS');
                    $isWarningError   = false;
                    $strWarningError  = null;

                    if ($appMysqlConnect->isResource($mysqlQueryTables)) {
                        while ($mysqlAssocTables = $appMysqlConnect->fetchAssoc($mysqlQueryTables)) {
                            $mysqlAssocTables['Name'] = addslashes($mysqlAssocTables['Name']);

                            $mysqlQueryRenameTable  = 'RENAME TABLE `' . addslashes($appMysqlConnect->getName()) . '`.`' . $mysqlAssocTables['Name'] . '` ' .
                                                                'TO `' . addslashes($forms['name'])              . '`.`' . $mysqlAssocTables['Name'];

                            if ($appMysqlConnect->query($mysqlQueryRenameTable) == false) {
                                $isWarningError  = true;
                                $strWarningError = $appMysqlConnect->error();
                            }
                        }
                    }

                    if ($isWarningError == false && $appMysqlConnect->query('DROP DATABASE `' . addslashes($appMysqlConnect->getName()) . '`') == false) {
                        $appAlert->danger(lng('mysql.rename_database.alert.rename_database_failed', 'name', $appMysqlConnect->getName(), 'error', $appMysqlConnect->error()));
                    } else {
                        if ($isWarningError)
                            $appAlert->warning(lng('mysql.rename_database.alert.rename_is_warning_error', 'error', $strWarningError), ALERT_MYSQL_LIST_DATABASE);

                        $appAlert->success(lng('mysql.rename_database.alert.rename_database_success', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');
                    }

                }
            }
        }

        $forms['name']       = stripslashes($forms['name']);
        $forms['collection'] = stripslashes($forms['collection']);
    }
?>

    <?php echo $appAlert->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('mysql.rename_database.title_page'); ?></span>
        </div>
        <form action="rename_database.php?<?php echo PARAMETER_DATABASE_URL; ?>=<?php echo AppDirectory::rawEncode($appMysqlConnect->getName()); ?>" method="post">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="input">
                    <span><?php echo lng('mysql.rename_database.form.input.database_name'); ?></span>
                    <input type="text" name="name" value="<?php echo $forms['name']; ?>" class="none" placeholder="<?php echo lng('mysql.create_database.form.placeholder.input_database_name'); ?>"/>
                </li>
                <li class="select">
                    <span><?php echo lng('mysql.rename_database.form.input.collection'); ?></span>
                    <div class="select">
                        <select name="collection">
                            <?php AppMysqlCollection::display(lng('mysql.rename_database.form.input.collection_none'), $forms['collection']); ?>
                        </select>
                    </div>
                </li>
                <li class="button">
                    <button type="submit" name="rename" id="button-save-on-javascript">
                        <span><?php echo lng('mysql.rename_database.form.button.rename'); ?></span>
                    </button>
                    <a href="list_database.php">
                        <span><?php echo lng('mysql.rename_database.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="menu-action">
        <li>
            <a href="info_database.php?<?php echo PARAMETER_DATABASE_URL; ?>=<?php echo AppDirectory::rawEncode($appMysqlConnect->getName()); ?>">
                <span class="icomoon icon-about"></span>
                <span><?php echo lng('mysql.list_database.menu_action.info_database'); ?></span>
            </a>
        </li>
        <li>
            <a href="delete_database.php?<?php echo PARAMETER_DATABASE_URL; ?>=<?php echo AppDirectory::rawEncode($appMysqlConnect->getName()); ?>">
                <span class="icomoon icon-trash"></span>
                <span><?php echo lng('mysql.list_database.menu_action.delete_database'); ?></span>
            </a>
        </li>
        <li>
            <a href="create_database.php">
                <span class="icomoon icon-plus"></span>
                <span><?php echo lng('mysql.list_database.menu_action.create_database'); ?></span>
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