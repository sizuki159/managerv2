<?php

    use Librarys\App\AppAssets;

    if (!defined('LOADED'))
        exit(0);

    requireDefine('asset');

    if (isset($themes) == false || is_array($themes) == false)
        $themes = array();

    if (isset($scripts) == false || is_array($scripts) == false)
        $scripts = array();

    array_unshift($themes, env('resource.filename.theme.icomoon'));
    array_unshift($themes, env('resource.filename.theme.app'));

    if ($appConfig->get('enable_disable.auto_focus_input_last') == true)
        array_unshift($scripts, env('resource.filename.javascript.auto_focus_input_last'));

    if ($appConfig->get('enable_disable.button_save_on_javascript') == true)
        array_unshift($scripts, env('resource.filename.javascript.button_save_on_javascript'));

    array_unshift($scripts, env('resource.filename.javascript.onload'));
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $title; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow, noodp, nodir"/>
        <meta http-equiv="Cache-Control" content="private, max-age=0, no-cache, no-store, must-revalidate"/>
        <meta http-equiv="Pragma" content="no-cache"/>
        <meta http-equiv="Expires" content="Thu, 01 Jan 1970 00:00:00 GMT">

        <?php foreach ($themes AS $entry) { ?>
            <link rel="stylesheet" type="text/css" href="<?php echo AppAssets::makeURLResourceTheme($appConfig->get('theme.directory'), $entry); ?>" media="all,handheld" />
        <?php } ?>
        <?php unset($themes); ?>

        <link rel="icon" type="image/png" href="<?php echo AppAssets::makeURLResourceIcon($appConfig->get('theme.directory'), env('resource.filename.icon.favicon_png')); ?>"/>
        <link rel="icon" type="image/x-icon" href="<?php echo AppAssets::makeURLResourceIcon($appConfig->get('theme.directory'), env('resource.filename.icon.favicon_ico')); ?>"/>
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo AppAssets::makeURLResourceIcon($appConfig->get('theme.directory'), env('resource.filename.icon.favicon_ico')); ?>"/>

        <?php foreach ($scripts AS $entry) { ?>
            <script type="text/javascript" src="<?php echo AppAssets::makeURLResourceJavascript($entry); ?>"></script>
        <?php } ?>
        <?php unset($scripts); ?>

    </head>
    <body>
        <div id="container">
            <div id="header">
                <div id="logo">
                    <a href="<?php echo env('app.http.host'); ?>">
                        <span id="logo" class="icomoon icon-home"></span>
                    </a>
                </div>
                <ul id="action">
                    <?php if ($appUser->isLogin()) { ?>
                        <li>
                            <a href="#">
                                <span class="icomoon icon-search"></span>
                            </a>
                        </li>

                        <?php $url    = env('app.http.host') . '/mysql'; ?>
                        <?php $isShow = true; ?>

                        <?php if ($appMysqlConfig->get('mysql_is_connect', false)) { ?>
                           <?php if ($appMysqlConfig->get('mysql_name', null) == null) { ?>
                                <?php $url   .= '/list_database.php'; ?>
                                <?php $isShow = defined('MYSQL_LIST_DATABASE') == false; ?>
                            <?php } else { ?>
                                <?php $url   .= '/list_table.php'; ?>
                                <?php $isShow = defined('MYSQL_LIST_TABLE') == false; ?>
                            <?php } ?>
                        <?php } else if (defined('MYSQL_REQUIRE')) { ?>
                            <?php $isShow = false; ?>
                        <?php } ?>

                        <?php if ($isShow) { ?>
                            <li>
                                <a href="<?php echo $url; ?>">
                                    <span class="icomoon icon-mysql"></span>
                                </a>

                            </li>
                        <?php } ?>
                        <?php unset($url); ?>
                        <?php unset($isShow); ?>

                        <?php if (defined('SETTING') == false) { ?>
                            <li>
                                <a href="<?php echo env('app.http.host'); ?>/system/setting.php">
                                    <span class="icomoon icon-config"></span>
                                </a>
                            </li>
                        <?php } ?>
                        <li class="about">
                            <a href="<?php echo env('app.http.host'); ?>/app/about.php">
                                <span class="icomoon icon-about"></span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo env('app.http.host'); ?>/user/exit.php">
                                <span class="icomoon icon-exit"></span>
                            </a>
                        </li>
                    <?php } else { ?>
                        <li>
                            <a href="<?php echo env('app.http.host'); ?>/app/about.php">
                                <span class="icomoon icon-about"></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div id="content">
