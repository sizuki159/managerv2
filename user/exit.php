<?php

    define('LOADED',              1);
    define('DISABLE_CHECK_LOGIN', 1);
    define('ROOT',                '..' . DIRECTORY_SEPARATOR);

    require_once(ROOT . 'incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    if ($appUser->isLogin()) {
    	if ($appUser->exitSession())
   		   $appAlert->success(lng('user.login.alert.exit_session_success'), ALERT_USER_LOGIN, 'login.php');
        else
       	    $appAlert->danger(lng('user.login.alert.exit_session_failed'), ALERT_INDEX, env('app.http.host'));
    } else {
        $appAlert->danger(lng('user.login.alert.not_login'), ALERT_USER_LOGIN, 'login.php');
    }

?>