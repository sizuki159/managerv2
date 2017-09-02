<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\Mysql\AppMysqlCollection;

    define('LOADED', 1);
    require_once('global.php');

    $title  = lng('mysql.create_database.title_page');
    $themes = [ env('resource.filename.theme.mysql') ];
    $appAlert->setID(ALERT_MYSQL_CREATE_DATABASE);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $forms = [
        'name'       => null,
        'collection' => null
    ];

    if (isset($_POST['create'])) {
        $forms['name']       = addslashes($_POST['name']);
        $forms['collection'] = addslashes($_POST['collection']);

        if (empty($forms['name'])) {
            $appAlert->danger(lng('mysql.create_database.alert.not_input_database_name'));
        } else if ($appMysqlConnect->isDatabaseNameExists($forms['name'], null, true)) {
            $appAlert->danger(lng('mysql.create_database.alert.database_name_is_exists'));
        } else if ($forms['collection'] == AppMysqlCollection::COLLECTION_NONE && $appMysqlConnect->query('CREATE DATABASE `' . $forms['name'] . '`') == false) {
            $appAlert->danger(lng('mysql.create_database.alert.create_database_failed_error', 'error', $appMysqlConnect->error()));
        } else if ($forms['collection'] != AppMysqlCollection::COLLECTION_NONE) {
            if (AppMysqlCollection::isValidate($forms['collection'], $charset, $collate) == false) {
                $appAlert->danger(lng('mysql.create_database.alert.collection_not_validate'));
            } else if ($appMysqlConnect->query('CREATE DATABASE `' . $forms['name'] . '` CHARACTER SET ' . $charset . ' COLLATE ' . $collate) == false) {
                $appAlert->danger(lng('mysql.create_database.alert.create_database_failed_error', 'error', $appMysqlConnect->error()));
            } else {
                $idAlert = ALERT_MYSQL_LIST_DATABASE;
                $urlGoto = 'list_database.php';

                if ($appConfig->get('auto_redirect.create_database')) {
                    $idAlert = ALERT_MYSQL_LIST_TABLE;
                    $urlGoto = 'list_table.php?' . PARAMETER_DATABASE_URL . '=' . AppDirectory::rawEncode($forms['name']);
                }

                $appAlert->success(lng('mysql.create_database.alert.create_database_success', 'name', $forms['name']), $idAlert, $urlGoto);
            }
        } else {
            $appAlert->danger(lng('mysql.create_database.alert.create_database_failed'));
        }

        $forms['name']       = stripslashes($forms['name']);
        $forms['collection'] = stripslashes($forms['collection']);
    }
?>

    <?php $appAlert->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('mysql.create_database.title_page'); ?></span>
        </div>
        <form action="create_database.php" method="post">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="input">
                    <span><?php echo lng('mysql.create_database.form.input.database_name'); ?></span>
                    <input type="text" name="name" value="<?php echo $forms['name']; ?>" class="none" placeholder="<?php echo lng('mysql.create_database.form.placeholder.input_database_name'); ?>"/>
                </li>
                <li class="select">
                    <span><?php echo lng('mysql.create_database.form.input.collection'); ?></span>
                    <div class="select">
                        <select name="collection">
                            <?php AppMysqlCollection::display(lng('mysql.create_database.form.input.collection_none'), $forms['collection']); ?>
                        </select>
                    </div>
                </li>
                <li class="button">
                    <button type="submit" name="create" id="button-save-on-javascript">
                        <span><?php echo lng('mysql.create_database.form.button.create'); ?></span>
                    </button>
                    <a href="list_database.php">
                        <span><?php echo lng('mysql.create_database.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="menu-action">
        <li>
            <a href="disconnect.php">
                <span class="icomoon icon-cord"></span>
                <span><?php echo lng('mysql.home.menu_action.disconnect'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php');