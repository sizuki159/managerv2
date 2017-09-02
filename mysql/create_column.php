<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\File\FileInfo;

    use Librarys\App\Mysql\AppMysqlDataType;
    use Librarys\App\Mysql\AppMysqlCollection;
    use Librarys\App\Mysql\AppMysqlAttribute;
    use Librarys\App\Mysql\AppMysqlFieldKey;
    use Librarys\App\Mysql\AppMysqlColumnPosition;

    define('LOADED',               1);
    define('DATABASE_CHECK_MYSQL', 1);
    define('TABLE_CHECK_MYSQL',    1);

    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));
    $appParameter->add(PARAMETER_TABLE_URL,    AppDirectory::rawEncode($appMysqlConnect->getTableCurrent()));

    $title  = lng('mysql.create_column.title_page');
    $themes = [ env('resource.filename.theme.mysql') ];
    $appAlert->setID(ALERT_MYSQL_CREATE_COLUMN);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $forms = [
        'column_name'    => null,
        'default_value'  => null,
        'length_value'   => null,
        'data_type'      => null,
        'collection'     => null,
        'attribute'      => null,
        'position'       => null,
        'field_key'      => null,
        'is_null'        => false,
        'auto_increment' => false,

        'has_field_key'  => AppMysqlFieldKey::isTableHasFieldKey($appMysqlConnect->getTableCurrent())
    ];

    if ($forms['has_field_key'])
        $forms['field_key'] = AppMysqlFieldKey::FIELD_KEY_NONE;

    if (isset($_POST['create']) || isset($_POST['continue'])) {
        $forms['column_name']    = addslashes($_POST['column_name']);
        $forms['default_value']  = addslashes($_POST['default_value']);
        $forms['length_value']   = intval(addslashes($_POST['length_value']));
        $forms['data_type']      = addslashes($_POST['data_type']);
        $forms['collection']     = addslashes($_POST['collection']);
        $forms['attribute']      = addslashes($_POST['attribute']);
        $forms['position']       = addslashes($_POST['position']);
        $forms['field_key']      = addslashes($_POST['field_key']);

        if (isset($_POST['is_null']))
            $forms['is_null'] = boolval(addslashes($_POST['is_null']));

        if (isset($_POST['auto_increment']))
            $forms['auto_increment'] = boolval(addslashes($_POST['auto_increment']));

        if (empty($forms['column_name'])) {
            $appAlert->danger(lng('mysql.create_column.alert.not_input_column_name'));
        } else if ($forms['collection'] != AppMysqlCollection::COLLECTION_NONE && AppMysqlCollection::isValidate($forms['collection'], $charset, $collate) == false) {
            $appAlert->danger(lng('mysql.create_column.alert.collection_not_validate'));
        } else if (empty($forms['length_value']) == false && $appMysqlConnect->isLengthDataValidate($forms['length_value']) == false) {
            $appAlert->danger(lng('mysql.create_column.alert.length_data_not_validate'));
        } else if (($positionResult = AppMysqlColumnPosition::isPositionValidate($forms['position'])) === false) {
            $appAlert->danger(lng('mysql.create_column.alert.position_not_validate'));
        } else if ($appMysqlConnect->isColumnNameExists($appMysqlConnect->getTableCurrent(), $forms['column_name'])) {
            $appAlert->danger(lng('mysql.create_column.alert.column_name_is_exists'));
        } else {
            $dataTypeBuffer      = $forms['data_type'];
            $collectionBuffer    = null;
            $attributeBuffer     = null;
            $nullBuffer          = 'NULL';
            $defaultBuffer       = null;
            $autoIncrementBuffer = null;
            $fieldKeyBuffer      = null;
            $positionBuffer      = null;

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

            if ($forms['position'] == AppMysqlColumnPosition::POSITION_AFTER_FIRST)
                $positionBuffer = 'FIRST';
            else if ($forms['position'] == AppMysqlColumnPosition::POSITION_AFTER_LAST)
                $positionBuffer = null;
            else if ($positionResult != null && empty($positionResult) == false)
                $positionBuffer = 'AFTER `' . addslashes($positionResult) . '`';

            $mysqlStr  = 'ALTER TABLE `' . addslashes($appMysqlConnect->getTableCurrent()) . '` ';
            $mysqlStr .= 'ADD `' . $forms['column_name'] . '` ';
            $mysqlStr .= $dataTypeBuffer;

            if ($attributeBuffer != null)
                $mysqlStr .= ' ' . $attributeBuffer;

            if ($collectionBuffer != null)
                $mysqlStr .= ' ' . $collectionBuffer;

            $mysqlStr .= ' ' . $nullBuffer;

            if ($defaultBuffer != null)
                $mysqlStr .= ' ' . $defaultBuffer;

            if ($autoIncrementBuffer != null)
                $mysqlStr .= ' ' . $autoIncrementBuffer;

            if ($fieldKeyBuffer != null)
                $mysqlStr .= ' ' . $fieldKeyBuffer;

            if ($positionBuffer != null)
                $mysqlStr .= ' ' . $positionBuffer;

            if ($appMysqlConnect->query($mysqlStr) == false) {
                $appAlert->danger(lng('mysql.create_column.alert.create_column_failed', 'error', $appMysqlConnect->error()));
            } else {
                $idAlert = null;
                $urlGoto = null;

                if (isset($_POST['create'])) {
                    $urlGoto = 'list_column.php' . $appParameter->toString();
                    $idAlert = ALERT_MYSQL_LIST_COLUMN;
                }

                $appAlert->success(lng('mysql.create_column.alert.create_column_success', 'name', $forms['column_name']), $idAlert, $urlGoto);
            }

            $forms['column_name']    = null;
            $forms['default_value']  = null;
            $forms['length_value']   = null;
            $forms['data_type']      = null;
            $forms['collection']     = stripslashes($forms['collection']);
            $forms['attribute']      = null;

            if ($forms['field_key'] == null && $forms['has_field_key'])
                $forms['field_key'] = AppMysqlFieldKey::FIELD_KEY_NONE;
        }

    }
?>

    <?php $appAlert->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('mysql.create_column.title_page'); ?></span>
        </div>
        <form action="create_column.php<?php echo $appParameter->toString(); ?>" method="post">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="input">
                    <span><?php echo lng('mysql.create_column.form.input.column_name'); ?></span>
                    <input type="text" name="column_name" value="<?php echo $forms['column_name']; ?>" class="none" placeholder="<?php echo lng('mysql.create_column.form.placeholder.input_column_name'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('mysql.create_column.form.input.default_value'); ?></span>
                    <input type="text" name="default_value" value="<?php echo $forms['default_value']; ?>" class="none" placeholder="<?php echo lng('mysql.create_column.form.placeholder.input_default_value'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('mysql.create_column.form.input.length_value'); ?></span>
                    <input type="number" name="length_value" value="<?php echo $forms['length_value']; ?>" class="none" placeholder="<?php echo lng('mysql.create_column.form.placeholder.input_length_value'); ?>"/>
                </li>
                <li class="select">
                    <span><?php echo lng('mysql.create_column.form.input.data_type'); ?></span>
                    <div class="select">
                        <select name="data_type">
                            <?php AppMysqlDataType::display(lng('mysql.create_column.form.input.data_type_none'), $forms['data_type']); ?>
                        </select>
                    </div>
                </li>
                <li class="select">
                    <span><?php echo lng('mysql.create_column.form.input.collection'); ?></span>
                    <div class="select">
                        <select name="collection">
                            <?php AppMysqlCollection::display(lng('mysql.create_column.form.input.collection_none'), $forms['collection']); ?>
                        </select>
                    </div>
                </li>
                <li class="select">
                    <span><?php echo lng('mysql.create_column.form.input.attribute'); ?></span>
                    <div class="select">
                        <select name="attribute">
                            <?php AppMysqlAttribute::display(lng('mysql.create_column.form.input.attribute_none'), $forms['attribute']); ?>
                        </select>
                    </div>
                </li>
                <li class="select">
                    <span><?php echo lng('mysql.create_column.form.input.position'); ?></span>
                    <div class="select">
                        <select name="position">
                            <?php
                                AppMysqlColumnPosition::display(
                                    $appMysqlConnect->getTableCurrent(),

                                    lng('mysql.create_column.form.input.position_label_column'),
                                    lng('mysql.create_column.form.input.position_label_after_first'),
                                    lng('mysql.create_column.form.input.position_label_after_last'),

                                    $forms['position']
                                );
                            ?>
                        </select>
                    </div>
                </li>
                <li class="checkbox">
                    <span class="title"><?php echo lng('mysql.create_column.form.input.checkbox_more'); ?></span>
                    <ul>
                        <li>
                            <input type="checkbox" name="is_null" id="is_null" value="1"<?php if ($forms['is_null']) { ?> checked="checked"<?php } ?>/>
                            <label for="is_null">
                                <span><?php echo lng('mysql.create_column.form.input.is_null'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="checkbox" name="auto_increment" id="auto_increment" value="1"<?php if ($forms['auto_increment']) { ?> checked="checked"<?php } ?>/>
                            <label for="auto_increment">
                                <span><?php echo lng('mysql.create_column.form.input.auto_increment'); ?></span>
                            </label>
                        </li>
                    </ul>
                </li>
                <li class="radio">
                    <span class="title"><?php echo lng('mysql.create_column.form.input.field_key'); ?></span>
                    <ul>
                        <?php AppMysqlFieldKey::display('field_key', lng('mysql.create_column.form.input.field_key_none'), $forms['field_key']); ?>
                    </ul>
                </li>
                <li class="button">
                    <button type="submit" name="continue" id="button-save-on-javascript">
                        <span><?php echo lng('mysql.create_column.form.button.create_and_continue'); ?></span>
                    </button>
                    <button type="submit" name="create">
                        <span><?php echo lng('mysql.create_column.form.button.create'); ?></span>
                    </button>
                    <a href="list_column.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('mysql.create_column.form.button.cancel'); ?></span>
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