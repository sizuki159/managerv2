<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\File\FileInfo;

    use Librarys\App\Mysql\AppMysqlDataType;
    use Librarys\App\Mysql\AppMysqlQueryCreate;

    define('LOADED',               1);
    define('DATABASE_CHECK_MYSQL', 1);
    define('TABLE_CHECK_MYSQL',    1);

    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));
    $appParameter->add(PARAMETER_TABLE_URL,    AppDirectory::rawEncode($appMysqlConnect->getTableCurrent()));

    $title  = lng('mysql.create_data.title_page');
    $themes = [ env('resource.filename.theme.mysql') ];
    $appAlert->setID(ALERT_MYSQL_CREATE_DATA);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $mysqlStrColumns     = 'SHOW COLUMNS FROM `' . addslashes($appMysqlConnect->getTableCurrent()) . '`';
    $mysqlQueryColumns   = $appMysqlConnect->query($mysqlStrColumns);
    $mysqlNumRowsColumns = $appMysqlConnect->numRows($mysqlQueryColumns);

    if ($mysqlNumRowsColumns <= 0)
        $appAlert->warning(lng('mysql.create_data.alert.columns_in_table_is_zero', 'name', $appMysqlConnect->getTableCurrent()), ALERT_MYSQL_CREATE_COLUMN, 'create_column.php' . $appParameter->toString());

    $listFields = array();
    $listDatas  = array();

    while ($mysqlAssocColumns = $appMysqlConnect->fetchAssoc($mysqlQueryColumns)) {
        $listFields[$mysqlAssocColumns['Field']] = $mysqlAssocColumns;
        $listDatas[$mysqlAssocColumns['Field']]  = null;
    }

    if (isset($_POST['continue']) || isset($_POST['create'])) {
        $mysqlQuery = new AppMysqlQueryCreate(AppMysqlQueryCreate::COMMAND_INSERT_INTO, $appMysqlConnect->getTableCurrent());

        foreach ($listDatas AS $fieldKey => &$fieldValue) {
            $fieldValue = addslashes($_POST[$fieldKey]);
            $mysqlQuery->addData($fieldKey, $fieldValue);
        }

        $mysqlQuery = $mysqlQuery->query();

        if ($mysqlQuery == false) {
            $appAlert->danger(lng('mysql.create_data.alert.create_data_failed', 'error', $appMysqlConnect->error()));
        } else {
            $idAlert = null;
            $urlGoto = null;

            if (isset($_POST['create'])) {
                $idAlert = ALERT_MYSQL_LIST_DATA;
                $urlGoto = 'list_data.php' . $appParameter->toString();
            }

            $appAlert->success(lng('mysql.create_data.alert.create_data_success'), $idAlert, $urlGoto);

        }

        foreach ($listDatas AS $fieldKey => &$fieldValue) {
            if ($mysqlQuery == false)
                $fieldValue = stripslashes($fieldValue);
            else
                $fieldValue = null;
        }
    }
?>

    <?php $appAlert->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('mysql.create_data.title_page'); ?></span>
        </div>
        <form action="create_data.php<?php echo $appParameter->toString(); ?>" method="post">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <?php foreach ($listFields AS $fieldKey => $fieldAssoc) { ?>
                    <?php if (preg_match('/^([a-zA-Z0-9\-_]+)(\(+|\s+|\\b)/', $fieldAssoc['Type'], $matches) && AppMysqlDataType::isHasLength($matches[1]) == false) { ?>
                        <li class="textarea">
                            <span><?php echo lng('mysql.create_data.form.input.column_name', 'name', $fieldKey, 'type', $fieldAssoc['Type']); ?></span>
                            <textarea name="<?php echo $fieldKey; ?>" placeholder="<?php echo lng('mysql.create_data.form.placeholder.input_column_data', 'name', $fieldKey); ?>" rows="5"><?php echo htmlspecialchars($listDatas[$fieldKey]); ?></textarea>
                        </li>
                    <?php } else { ?>
                        <li class="input">
                            <span><?php echo lng('mysql.create_data.form.input.column_name', 'name', $fieldKey, 'type', $fieldAssoc['Type']); ?></span>
                            <input type="<?php if (AppMysqlDataType::isNumeric($matches[1])) { ?>number<?php } else { ?>text<?php } ?>" name="<?php echo $fieldKey; ?>" value="<?php echo htmlspecialchars($listDatas[$fieldKey]); ?>" placeholder="<?php echo lng('mysql.create_data.form.placeholder.input_column_data', 'name', $fieldKey); ?>"/>
                        </li>
                    <?php } ?>
                <?php } ?>

                <li class="button">
                    <button type="submit" name="continue" id="button-save-on-javascript">
                        <span><?php echo lng('mysql.create_data.form.button.create_and_continue'); ?></span>
                    </button>
                    <button type="submit" name="create">
                        <span><?php echo lng('mysql.create_data.form.button.create'); ?></span>
                    </button>
                    <a href="list_data.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('mysql.create_data.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="alert">
        <li class="info"><?php echo lng('mysql.create_data.alert.tips'); ?></li>
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

        <?php $appParameter->remove(PARAMETER_TABLE_URL); ?>
        <li>
            <a href="list_table.php<?php echo $appParameter->toString(true); ?>">
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