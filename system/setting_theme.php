<?php

    use Librarys\App\AppUser;

    define('LOADED', 1);
    require_once('global.php');

    $title = lng('system.setting_theme.title_page');
    $appAlert->setID(ALERT_SYSTEM_SETTING_THEME);
    require_once(ROOT . 'incfiles' . SP . 'header.php');
?>

    <ul class="alert">
        <li class="info">
            <span><?php echo lng('home.alert.features_is_construct'); ?></span>
        </li>
    </ul>

    <ul class="menu-action">
        <li>
            <a href="<?php echo env('app.http.host'); ?>/system/setting.php">
                <span class="icomoon icon-config"></span>
                <span><?php echo lng('system.setting.menu_action.setting_system'); ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo env('app.http.host'); ?>/user/setting.php">
                <span class="icomoon icon-config"></span>
                <span><?php echo lng('system.setting.menu_action.setting_profile'); ?></span>
            </a>
        </li>
        <li class="hidden">
            <a href="<?php echo env('app.http.host'); ?>/user/manager.php">
                <span class="icomoon icon-user"></span>
                <span><?php echo lng('system.setting.menu_action.manager_user'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php'); ?>