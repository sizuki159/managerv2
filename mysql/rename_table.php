<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\App\Mysql\AppMysqlCollection;
    use Librarys\Database\DatabaseConnect;

    define('LOADED',               1);
    define('DATABASE_CHECK_MYSQL', 1);
    define('TABLE_CHECK_MYSQL',    1);

    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');

    $title  = lng('mysql.rename_table.title_page');
    $themes = [ env('resource.filename.theme.mysql') ];
    $appAlert->setID(ALERT_MYSQL_RENAME_TABLE);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));
    $appParameter->add(PARAMETER_TABLE_URL,    AppDirectory::rawEncode($appMysqlConnect->getTableCurrent()));

    $forms = [
        'name'             => $appMysqlConnect->getTableCurrent(),
        'collection'       => AppMysqlCollection::getDefault(),
        'collection_table' => AppMysqlCollection::getDefault()
    ];

    $fetchAssoc = $appMysqlConnect->fetchAssoc(
        'SELECT `TABLE_COLLATION` ' .
        'FROM `' . DatabaseConnect::DATABASE_INFORMATION . '`.`TABLES` ' .
        'WHERE `TABLE_SCHEMA` IN ("' . addslashes($appMysqlConnect->getName()) . '") ' .
        'AND `TABLE_NAME`="' . addslashes($appMysqlConnect->getTableCurrent()) . '"'
    );

    if (is_array($fetchAssoc)) {
        $forms['collection']       = AppMysqlCollection::convertCollationToCollection($fetchAssoc['TABLE_COLLATION']);
        $forms['collection_table'] = $forms['collection'];
    }

    if (isset($_POST['rename'])) {
        $forms['name']       = addslashes($_POST['name']);
        $forms['collection'] = addslashes($_POST['collection']);

        if (empty($forms['name'])) {
            $appAlert->danger(lng('mysql.rename_table.alert.not_input_table_name'));
        } else if ($appMysqlConnect->isTableNameExists($forms['name'], $appMysqlConnect->getTableCurrent(), true)) {
            $appAlert->danger(lng('mysql.rename_table.alert.table_name_is_exists'));
        } else if ($forms['collection'] != AppMysqlCollection::COLLECTION_NONE && AppMysqlCollection::isValidate($forms['collection'], $charset, $collate) == false) {
            $appAlert->danger(lng('mysql.rename_table.alert.collection_not_validate'));
        } else {
            if ($forms['name'] == $appMysqlConnect->getTableCurrent()) {
                if ($forms['collection'] == $forms['collection_table']) {
                    $appAlert->danger(lng('mysql.rename_table.alert.has_not_changed'));
                } else if ($forms['collection'] == AppMysqlCollection::COLLECTION_NONE) {
                    $appAlert->warning(lng('mysql.rename_table.alert.not_set_collection_to_none'));
                } else {
                    $mysqlQueryAlterTable = 'ALTER TABLE `' . addslashes($appMysqlConnect->getTableCurrent()) . '` ' .
                                            'CONVERT TO CHARACTER SET ' . $charset . ' ' .
                                            'COLLATE                  ' . $collate;

                    if ($appMysqlConnect->query($mysqlQueryAlterTable) == false)
                        $appAlert->danger(lng('mysql.rename_table.alert.change_collection_failed', 'error', $appMysqlConnect->error()));
                    else
                        $appAlert->success(lng('mysql.rename_table.alert.change_collection_success', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_COLUMN, 'list_column.php' . $appParameter->toString());
                }
            } else {
                $mysqlQueryRenameTable = 'RENAME TABLE `' . addslashes($appMysqlConnect->getTableCurrent()) . '` ' .
                                         'TO   `' . $forms['name'] . '` ';

                $mysqlQueryAlterChangeCollection = 'ALTER TABLE `' . addslashes($appMysqlConnect->getTableCurrent()) . '` ' .
                                                   'CONVERT TO CHARACTER SET ' . $charset . ' ' .
                                                   'COLLATE                  ' . $collate;

                if ($appMysqlConnect->query($mysqlQueryAlterChangeCollection) == false || $appMysqlConnect->query($mysqlQueryRenameTable) == false) {
                    $appAlert->danger(lng('mysql.rename_table.alert.rename_table_failed', 'name', $appMysqlConnect->getTableCurrent(), 'error', $appMysqlConnect->error()));
                } else {
                    $appParameter->set(PARAMETER_TABLE_URL, AppDirectory::rawEncode($forms['name']));
                    $appParameter->toString(true);

                    $appAlert->success(lng('mysql.rename_table.alert.rename_table_success', 'name', $appMysqlConnect->getTableCurrent()), ALERT_MYSQL_LIST_COLUMN, 'list_column.php' . $appParameter->toString());
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
            <span><?php echo lng('mysql.rename_table.title_page'); ?></span>
        </div>
        <form action="rename_table.php<?php echo $appParameter->toString(); ?>" method="post">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="input">
                    <span><?php echo lng('mysql.rename_table.form.input.table_name'); ?></span>
                    <input type="text" name="name" value="<?php echo $forms['name']; ?>" class="none" placeholder="<?php echo lng('mysql.create_table.form.placeholder.input_table_name'); ?>"/>
                </li>
                <li class="select">
                    <span><?php echo lng('mysql.rename_table.form.input.collection'); ?></span>
                    <div class="select">
                        <select name="collection">
                            <?php AppMysqlCollection::display(lng('mysql.rename_table.form.input.collection_none'), $forms['collection']); ?>
                        </select>
                    </div>
                </li>
                <li class="button">
                    <button type="submit" name="rename" id="button-save-on-javascript">
                        <span><?php echo lng('mysql.rename_table.form.button.rename'); ?></span>
                    </button>
                    <a href="list_column.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('mysql.rename_table.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

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