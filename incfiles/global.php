<?php

    $memoryUsageBegin = @memory_get_usage();

    if (defined('LOADED') == false)
        exit;

    if (defined('SP') == false)
        define('SP', DIRECTORY_SEPARATOR);

    if (defined('ROOT') == false)
        define('ROOT', '.');

    define('PARAMETER_CHECK_CHANGE_CONFIG_URL', 'check_change_config');

    $directory = realpath(ROOT);

    require_once(
        $directory . SP .
        'librarys' . SP .
        'Boot.php'
    );

    $boot = new Librarys\Boot(
        require_once(
            $directory . SP .
            'assets'   . SP .
            'config'   . SP .
            'app.php'
        ),

        defined('ENABLE_CUSTOM_HEADER')
    );

    if (isValidateIP(($ip = takeIP())) == false)
        die(lng('default.global.ip_not_validate', 'ip', $ip));
    else
        unset($ip);

    use Librarys\App\AppUser;
    use Librarys\App\AppAlert;
    use Librarys\App\AppClean;
    use Librarys\App\AppChecker;
    use Librarys\App\AppDirectory;
    use Librarys\App\Config\AppConfig;
    use Librarys\App\Mysql\AppMysqlConfig;

    $appChecker      = new AppChecker    ($boot);
    $appConfig       = new AppConfig     ($boot);
    $appUser         = new AppUser       ($boot);
    $appAlert        = new AppAlert      ($boot);
    $appDirectory    = new AppDirectory  ($boot);
    $appMysqlConfig  = new AppMysqlConfig($boot);

    unset($directory);

    if ($appChecker->execute()->isAccept() == false) {
        if ($appChecker->isInstallDirectory() == false)
            die(lng('default.global.app_is_install_root'));
        else if ($appChecker->isDirectoryPermissionExecute() == false)
            die(lng('default.global.host_is_not_premission'));
        else if ($appChecker->isConfigValidate() == false)
            die(lng('default.global.config_app_not_found'));
        else
            die(lng('default.global.unknown_error_app'));
    }

    // Get config system
    $appConfig->execute();
    $appUser->execute();

    // Get config user
    $appConfig->execute($appUser);
    $appConfig->requireEnvProtected(env('resource.config.manager_dis'));

    if ($boot->getCFSRToken()->validatePost() !== true)
        die(lng('default.global.cfsr_not_validate'));

    $appDirectory->execute();
    $appMysqlConfig->execute($appUser);

    $httpHostApp    = env('app.http.host');
    $httpHostConfig = $appConfig->get('http_host');

    if (strcmp($httpHostApp, $httpHostConfig) !== 0) {
        if ($appConfig->setSystem('http_host', $httpHostApp) == false || $appConfig->write(true) == false)
            die(lng('default.global.change_config_failed'));

        if (isset($_GET[PARAMETER_CHECK_CHANGE_CONFIG_URL]))
            die(lng('default.global.change_config_failed'));

        if (AppClean::scanAutoClean(true))
            gotoURL($httpHostApp . '?' . PARAMETER_CHECK_CHANGE_CONFIG_URL);
    } else {
        AppClean::scanAutoClean();

        if (isset($_GET[PARAMETER_CHECK_CHANGE_CONFIG_URL]))
            $appAlert->success(lng('default.global.change_config_success'), $appUser->isLogin() ? ALERT_INDEX : ALERT_USER_LOGIN, $httpHostApp);
    }

    if ($appUser->getConfig()->hasEntryConfigArraySystem() == false) {
        $idAlert = ALERT_USER_LOGIN;
        $urlGoto = $httpHostApp . '/user/login.php';

        if ($appUser->createFirstUser())
            $appAlert->success(lng('default.global.create_first_user_success', 'username', AppUser::USERNAME_CREATE_FIRST, 'password', AppUser::PASSWORD_CREATE_FIRST), $idAlert, $urlGoto);
        else
            $appAlert->danger(lng('default.global.create_first_user_failed'), $idAlert, $urlGoto);
    }

    if (defined('DISABLE_CHECK_LOGIN') == false) {
        if ($appUser->isLogin() == false)
            $appAlert->danger(lng('user.login.alert.not_login'), ALERT_USER_LOGIN, env('app.http.host') . '/user/login.php');
        else if ($appUser->isUserBand())
            $appAlert->danger(lng('user.login.alert.user_is_band'), ALERT_USER_LOGIN, env('app.http.host') . '/user/login.php');
    }

    unset($httpHostApp);
    unset($httpHostConfig);

?>
