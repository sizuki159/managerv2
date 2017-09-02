<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\File\FileInfo;

    use Librarys\App\Mysql\AppMysqlDataType;
    use Librarys\App\Mysql\AppMysqlCollection;
    use Librarys\App\Mysql\AppMysqlAttribute;
    use Librarys\App\Mysql\AppMysqlEngineStorage;
    use Librarys\App\Mysql\AppMysqlFieldKey;

    use Librarys\Database\DatabaseBackupRestore;

    define('LOADED',               1);
    define('MYSQL_LIST_TABLE',     1);
    define('DATABASE_CHECK_MYSQL', 1);

    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');

    $title  = lng('mysql.create_table.title_page');
    $themes = [ env('resource.filename.theme.mysql') ];
    $appAlert->setID(ALERT_MYSQL_CREATE_TABLE);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));

    $forms = [
        'table_name'     => null,
        'column_name'    => null,
        'default_value'  => null,
        'length_value'   => null,
        'data_type'      => null,
        'collection'     => null,
        'attribute'      => null,
        'engine_storage' => null,
        'field_key'      => null,
        'is_null'        => false,
        'auto_increment' => false
    ];

    if (isset($_POST['create'])) {
        $forms['table_name']     = addslashes($_POST['table_name']);
        $forms['column_name']    = addslashes($_POST['column_name']);
        $forms['default_value']  = addslashes($_POST['default_value']);
        $forms['length_value']   = intval(addslashes($_POST['length_value']));
        $forms['data_type']      = addslashes($_POST['data_type']);
        $forms['collection']     = addslashes($_POST['collection']);
        $forms['attribute']      = addslashes($_POST['attribute']);
        $forms['engine_storage'] = addslashes($_POST['engine_storage']);
        $forms['field_key']      = addslashes($_POST['field_key']);

        if (isset($_POST['is_null']))
            $forms['is_null'] = boolval(addslashes($_POST['is_null']));

        if (isset($_POST['auto_increment']))
            $forms['auto_increment'] = boolval(addslashes($_POST['auto_increment']));

        if (empty($forms['table_name'])) {
            $appAlert->danger(lng('mysql.create_table.alert.not_input_table_name'));
        } else if (empty($forms['column_name'])) {
            $appAlert->danger(lng('mysql.create_table.alert.not_input_column_name'));
        } else if ($forms['collection'] != AppMysqlCollection::COLLECTION_NONE && AppMysqlCollection::isValidate($forms['collection'], $charset, $collate) == false) {
            $appAlert->danger(lng('mysql.create_table.alert.collection_not_validate'));
        } else if (empty($forms['length_value']) == false && $appMysqlConnect->isLengthDataValidate($forms['length_value']) == false) {
            $appAlert->danger(lng('mysql.create_table.alert.length_data_not_validate'));
        } else if ($appMysqlConnect->isTableNameExists($forms['table_name'])) {
            $appAlert->danger(lng('mysql.create_table.alert.table_name_is_exists'));
        } else {
            $dataTypeBuffer      = $forms['data_type'];
            $collectionBuffer    = null;
            $attributeBuffer     = null;
            $nullBuffer          = 'NULL';
            $defaultBuffer       = null;
            $autoIncrementBuffer = null;
            $fieldKeyBuffer      = null;

            if (empty($forms['length_value']) == false)
                $dataTypeBuffer .= '(' . $forms['length_value'] . ')';

            if ($forms['collection'] != AppMysqlCollection::COLLECTION_NONE)
                $collectionBuffer = 'CHARACTER SET ' . $charset . ' COLLATE ' . $collate;

            if ($forms['attribute'] != AppMysqlAttribute::ATTRIBUTES_NONE)
                $attributeBuffer = $forms['attribute'];

            if ($forms['is_null'] == false)
                $nullBuffer = 'NOT NULL';

            if (empty($forms['default_value']) == false)
                $defaultBuffer = 'DEFAULT "' . $forms['default_value'] . '"';

            if ($forms['auto_increment'])
                $autoIncrementBuffer = 'AUTO_INCREMENT';

            if ($forms['field_key'] != AppMysqlFieldKey::FIELD_KEY_NONE)
                $fieldKeyBuffer = ', ' . $forms['field_key'] . '(`' . $forms['column_name'] . '`)';

            $mysqlStr  = 'CREATE TABLE `' . $forms['table_name'] . '` ';
            $mysqlStr .= '(`' . $forms['column_name'] . '` ';
            $mysqlStr .= $dataTypeBuffer;

            if ($attributeBuffer != null)
                $mysqlStr .= ' ' . $attributeBuffer;

            $mysqlStr .= ' ' . $nullBuffer;

            if ($defaultBuffer != null)
                $mysqlStr .= ' ' . $defaultBuffer;

            if ($autoIncrementBuffer != null)
                $mysqlStr .= ' ' . $autoIncrementBuffer;

            if ($fieldKeyBuffer != null)
                $mysqlStr .= ' ' . $fieldKeyBuffer;

            $mysqlStr .= ') ENGINE=' . $forms['engine_storage'];

            if ($collectionBuffer != null)
                $mysqlStr .= ' ' . $collectionBuffer;

            if ($autoIncrementBuffer != null)
                $mysqlStr .= ' ' . $autoIncrementBuffer . '=1';

            if ($appMysqlConnect->query($mysqlStr) == false)
                $appAlert->danger(lng('mysql.create_table.alert.create_table_failed', 'error', $appMysqlConnect->error()));
            else
                $appAlert->success(lng('mysql.create_table.alert.create_table_success', 'name', $forms['table_name']), ALERT_MYSQL_LIST_TABLE, 'list_table.php' . $appParameter->toString());

            $forms['table_name']     = stripslashes($forms['table_name']);
            $forms['column_name']    = stripslashes($forms['column_name']);
            $forms['default_value']  = htmlspecialchars(stripslashes($forms['default_value']));
            $forms['length_value']   = stripslashes($forms['length_value']);
            $forms['data_type']      = stripslashes($forms['data_type']);
            $forms['collection']     = stripslashes($forms['collection']);
            $forms['attribute']      = stripslashes($forms['attribute']);
            $forms['engine_storage'] = stripslashes($forms['engine_storage']);
            $forms['field_key']      = stripslashes($forms['field_key']);
        }

    }

    $databaseBackupRestore = new DatabaseBackupRestore($appMysqlConnect);
?>

    <?php echo $appAlert->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('mysql.create_table.title_page'); ?></span>
        </div>
        <form action="create_table.php<?php echo $appParameter->toString(); ?>" method="post">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="input">
                    <span><?php echo lng('mysql.create_table.form.input.table_name'); ?></span>
                    <input type="text" name="table_name" value="<?php echo $forms['table_name']; ?>" class="none" placeholder="<?php echo lng('mysql.create_table.form.placeholder.input_table_name'); ?>"/>
                </li>
                <li class="indentation"></li>
                <li class="input">
                    <span><?php echo lng('mysql.create_table.form.input.column_name'); ?></span>
                    <input type="text" name="column_name" value="<?php echo $forms['column_name']; ?>" class="none" placeholder="<?php echo lng('mysql.create_table.form.placeholder.input_column_name'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('mysql.create_table.form.input.default_value'); ?></span>
                    <input type="text" name="default_value" value="<?php echo $forms['default_value']; ?>" class="none" placeholder="<?php echo lng('mysql.create_table.form.placeholder.input_default_value'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('mysql.create_table.form.input.length_value'); ?></span>
                    <input type="number" name="length_value" value="<?php echo $forms['length_value']; ?>" class="none" placeholder="<?php echo lng('mysql.create_table.form.placeholder.input_length_value'); ?>"/>
                </li>
                <li class="select">
                    <span><?php echo lng('mysql.create_table.form.input.data_type'); ?></span>
                    <div class="select">
                        <select name="data_type">
                            <?php AppMysqlDataType::display(lng('mysql.create_table.form.input.data_type_none'), $forms['data_type']); ?>
                        </select>
                    </div>
                </li>
                <li class="select">
                    <span><?php echo lng('mysql.create_table.form.input.collection'); ?></span>
                    <div class="select">
                        <select name="collection">
                            <?php AppMysqlCollection::display(lng('mysql.create_table.form.input.collection_none'), $forms['collection']); ?>
                        </select>
                    </div>
                </li>
                <li class="select">
                    <span><?php echo lng('mysql.create_table.form.input.attribute'); ?></span>
                    <div class="select">
                        <select name="attribute">
                            <?php AppMysqlAttribute::display(lng('mysql.create_table.form.input.attribute_none'), $forms['attribute']); ?>
                        </select>
                    </div>
                </li>
                <li class="select">
                    <span><?php echo lng('mysql.create_table.form.input.engine_storage'); ?></span>
                    <div class="select">
                        <select name="engine_storage">
                            <?php AppMysqlEngineStorage::display(lng('mysql.create_table.form.input.engine_storage_none'), $forms['engine_storage']); ?>
                        </select>
                    </div>
                </li>
                <li class="checkbox">
                    <span class="title"><?php echo lng('mysql.create_table.form.input.checkbox_more'); ?></span>
                    <ul>
                        <li>
                            <input type="checkbox" name="is_null" id="is_null" value="1"<?php if ($forms['is_null']) { ?> checked="checked"<?php } ?>/>
                            <label for="is_null">
                                <span><?php echo lng('mysql.create_table.form.input.is_null'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="checkbox" name="auto_increment" id="auto_increment" value="1"<?php if ($forms['auto_increment']) { ?> checked="checked"<?php } ?>/>
                            <label for="auto_increment">
                                <span><?php echo lng('mysql.create_table.form.input.auto_increment'); ?></span>
                            </label>
                        </li>
                    </ul>
                </li>
                <li class="radio">
                    <span class="title"><?php echo lng('mysql.create_table.form.input.field_key'); ?></span>
                    <ul>
                        <?php AppMysqlFieldKey::display('field_key', lng('mysql.create_table.form.input.field_key_none'), $forms['field_key']); ?>
                    </ul>
                </li>
                <li class="button">
                    <button type="submit" name="create" id="button-save-on-javascript">
                        <span><?php echo lng('mysql.create_table.form.button.create'); ?></span>
                    </button>
                    <a href="list_table.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('mysql.create_table.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="menu-action">
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