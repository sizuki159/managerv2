<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppParameter;
    use Librarys\File\FileInfo;

    define('LOADED',               1);
    define('DATABASE_CHECK_MYSQL', 1);
    define('TABLE_CHECK_MYSQL',    1);

    require_once('global.php');

    if ($appMysqlConfig->get('mysql_name') != null)
        $appAlert->danger(lng('mysql.list_database.alert.mysql_is_not_connect_root', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');

    $appParameter = new AppParameter();
    $appParameter->add(PARAMETER_DATABASE_URL, AppDirectory::rawEncode($appMysqlConnect->getName()));
    $appParameter->add(PARAMETER_TABLE_URL,    AppDirectory::rawEncode($appMysqlConnect->getTableCurrent()));

    $title  = lng('mysql.truncate_data.title_page');
    $themes = [ env('resource.filename.theme.mysql') ];
    $appAlert->setID(ALERT_MYSQL_TRUNCATE_DATA);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    if (isset($_POST['truncate'])) {
        if ($appMysqlConnect->query('TRUNCATE TABLE `' . addslashes($appMysqlConnect->getTableCurrent())) == false)
            $appAlert->danger(lng('mysql.truncate_data.alert.truncate_data_failed', 'name', $appMysqlConnect->getTableCurrent(), 'error', $appMysqlConnect->error()));
        else
            $appAlert->success(lng('mysql.truncate_data.alert.truncate_data_success', 'name', $appMysqlConnect->getTableCurrent()), ALERT_MYSQL_LIST_COLUMN, 'list_column.php' . $appParameter->toString());
    }
?>

    <?php $appAlert->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('mysql.truncate_data.title_page'); ?>: <?php echo $appMysqlConnect->getName(); ?></span>
        </div>
        <form action="truncate_data.php<?php echo $appParameter->toString(); ?>" method="post">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="accept">
                    <span><?php echo lng('mysql.truncate_data.form.accept_truncate_data', 'name', $appMysqlConnect->getTableCurrent()); ?></span>
                </li>
                <li class="button">
                    <button type="submit" name="truncate" id="button-save-on-javascript">
                        <span><?php echo lng('mysql.truncate_data.form.button.truncate'); ?></span>
                    </button>
                    <a href="list_database.php">
                        <span><?php echo lng('mysql.truncate_data.form.button.cancel'); ?></span>
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
                <span class="icomoon icon-edit"></span>
                <span><?php echo lng('mysql.list_table.menu_action.create_data'); ?></span>
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
            <a href="list-data.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-storage"></span>
                <span><?php echo lng('mysql.list_table.menu_action.list_data'); ?></span>
            </a>
        </li>
        <li>
            <a href="list-column.php<?php echo $appParameter->toString(); ?>">
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