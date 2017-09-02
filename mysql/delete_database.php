<?php

    use Librarys\App\AppDirectory;

    define('LOADED', 1);
    require_once('global.php');

    $title  = lng('mysql.delete_database.title_page');
    $themes = [ env('resource.filename.theme.mysql') ];
    $appAlert->setID(ALERT_MYSQL_DELETE_DATABASE);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    if (isset($_POST['delete'])) {
        if ($appMysqlConnect->query('DROP DATABASE `' . addslashes($appMysqlConnect->getName())) == false)
            $appAlert->danger(lng('mysql.delete_database.alert.delete_database_failed', 'name', $appMysqlConnect->getName(), 'error', $appMysqlConnect->error()));
        else
            $appAlert->success(lng('mysql.delete_database.alert.delete_database_success', 'name', $appMysqlConnect->getName()), ALERT_MYSQL_LIST_DATABASE, 'list_database.php');
    }
?>

    <?php $appAlert->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('mysql.delete_database.title_page'); ?>: <?php echo $appMysqlConnect->getName(); ?></span>
        </div>
        <form action="delete_database.php?<?php echo PARAMETER_DATABASE_URL; ?>=<?php echo AppDirectory::rawEncode($appMysqlConnect->getName()); ?>" method="post">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="accept">
                    <span><?php echo lng('mysql.delete_database.form.accept_delete_database', 'name', $appMysqlConnect->getName()); ?></span>
                </li>
                <li class="button">
                    <button type="submit" name="delete" id="button-save-on-javascript">
                        <span><?php echo lng('mysql.delete_database.form.button.delete'); ?></span>
                    </button>
                    <a href="list_database.php">
                        <span><?php echo lng('mysql.delete_database.form.button.cancel'); ?></span>
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
            <a href="rename_database.php?<?php echo PARAMETER_DATABASE_URL; ?>=<?php echo AppDirectory::rawEncode($appMysqlConnect->getName()); ?>">
                <span class="icomoon icon-edit"></span>
                <span><?php echo lng('mysql.list_database.menu_action.rename_database'); ?></span>
            </a>
        </li>
        <li>
            <a href="create_database.php">
                <span class="icomoon icon-plus"></span>
                <span><?php echo lng('mysql.list_database.menu_action.create_database'); ?></span>
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