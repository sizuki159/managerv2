<?php

    define('LOADED',                  1);
    define('DISABLE_CHECK_LOGIN',     1);
    define('SESSION_NAME_LOCK_COUNT', 'login_lock_count');
    define('SESSION_NAME_LOCK_TIME',  'login_lock_time');

    require_once('global.php');

    if ($appUser->isLogin() && $appUser->isUserBand(null, true) == false)
        $appAlert->info(lng('user.login.alert.login_already'), ALERT_INDEX, env('app.http.host'));

    $title = lng('user.login.title_page');
    $themes = [ env('resource.filename.theme.login') ];
    $appAlert->setID(ALERT_USER_LOGIN);
    require_once('..' . SP . 'incfiles' . SP . 'header.php');

    $username = null;
    $password = null;

    $isEnabledLockCount  = $appConfig->get('login.enable_lock_count_failed');
    $maxLockCountFailed  = $appConfig->get('login.max_lock_count');
    $timeLockCountFailed = $appConfig->get('login.time_lock');
    $currentTimeNow      = intval($_SERVER['REQUEST_TIME']);
    $isLockCountStatus   = false;
    $currentCountLock    = 0;
    $currentTimeLock     = 0;

    if ($isEnabledLockCount) {
        if (isset($_SESSION[SESSION_NAME_LOCK_COUNT]))
            $currentCountLock = intval(addslashes($_SESSION[SESSION_NAME_LOCK_COUNT]));

        if (isset($_SESSION[SESSION_NAME_LOCK_TIME]))
            $currentTimeLock = intval(addslashes($_SESSION[SESSION_NAME_LOCK_TIME]));

        if ($currentTimeNow - $currentTimeLock > $timeLockCountFailed) {
            $currentCountLock  = 0;
            $currentTimeLock   = $currentTimeNow;
            $isLockCountStatus = false;

            if ($currentCountLock >= $maxLockCountFailed && $currentTimeLock > 0 && isset($_SESSION[SESSION_NAME_LOCK_COUNT]) && isset($_SESSION[SESSION_NAME_LOCK_TIME]))
                $appAlert->success(lng('user.login.alert.unlock_count'));

            unset($_SESSION[SESSION_NAME_LOCK_COUNT]);
            unset($_SESSION[SESSION_NAME_LOCK_TIME]);
        } else {
            $isLockCountStatus = $currentCountLock >= $maxLockCountFailed;
        }
    } else {
        if (isset($_SESSION[SESSION_NAME_LOCK_COUNT]))
            unset($_SESSION[SESSION_NAME_LOCK_COUNT]);

        if (isset($_SESSION[SESSION_NAME_LOCK_TIME]))
            unset($_SESSION[SESSION_NAME_LOCK_TIME]);
    }

    if ($isEnabledLockCount && $isLockCountStatus) {
        $timeLockCalc = ($currentTimeLock + $timeLockCountFailed) - $currentTimeNow;

        if ($timeLockCalc < 60) {
            $timeLockCalc = $timeLockCalc . 's';
        } else if ($timeLockCalc >= 60 && $timeLockCalc < 3600) {
            $timeLockCalcMinute = floor($timeLockCalc / 60);
            $timeLockCalcSecond = $timeLockCalc - ($timeLockCalcMinute * 60);
            $timeLockCalc       = $timeLockCalcMinute . ':' . $timeLockCalcSecond . 's';
        } else {
            $timeLockCalcHour   = floor($timeLockCalc / 3600);
            $timeLockCalcMinute = floor(($timeLockCalc - ($timeLockCalcHour * 3600)) / 60);
            $timeLockCalcSecond = $timeLockCalc - (($timeLockCalcHour * 3600) + ($timeLockCalcMinute * 60));
            $timeLockCalc       = $timeLockCalcHour . ':' . $timeLockCalcMinute . ':' . $timeLockCalcSecond . 's';
        }

        if (isset($_SERVER['REQUEST_METHOD']) && strcasecmp($_SERVER['REQUEST_METHOD'], 'post') === 0)
            gotoURL('login.php');

        $appAlert->danger(lng('user.login.alert.lock_count_failed', 'count', $currentCountLock, 'time', $timeLockCalc));
    } else if (isset($_POST['submit'])) {
        $user     = null;
        $username = addslashes($_POST['username']);
        $password = addslashes($_POST['password']);

        if (empty($username) || empty($password)) {
            $appAlert->danger(lng('user.login.alert.not_input_username_or_password'));
        } else if (($idUser = $appUser->isUser($username, $password)) === false) {
            $appAlert->danger(lng('user.login.alert.username_or_password_wrong'));
        } else if ($idUser === null || empty($idUser)) {
            $appAlert->danger(lng('user.login.alert.user_not_exists'));
        } else if ($appUser->isUserBand($idUser, false)) {
            $appAlert->danger(lng('user.login.alert.user_is_band'));
        } else if ($appUser->createSessionUser($idUser) == false) {
            $appAlert->danger(lng('user.login.alert.login_failed'));
        } else {
            if (isset($_SESSION[SESSION_NAME_LOCK_COUNT]))
                unset($_SESSION[SESSION_NAME_LOCK_COUNT]);

            if (isset($_SESSION[SESSION_NAME_LOCK_TIME]))
                unset($_SESSION[SESSION_NAME_LOCK_TIME]);

            $appAlert->success(lng('user.login.alert.login_success'), ALERT_INDEX, env('app.http.host'));
        }

        $_SESSION[SESSION_NAME_LOCK_COUNT] = intval(++$currentCountLock);
        $_SESSION[SESSION_NAME_LOCK_TIME]  = intval(time());
    }

    $appAlert->display();

?>

    <?php if ($isLockCountStatus == false) { ?>
        <div id="login">
            <form action="login.php" method="post" id="login-form">
                <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>
                <input type="text" name="username" value="<?php echo stripslashes($username); ?>" placeholder="<?php echo lng('user.login.form.input_username_placeholder'); ?>" autofocus="autofocus"<?php if ($isLockCountStatus) { ?> disabled="disabled"<?php } ?>/>
                <input type="password" name="password" value="<?php echo stripslashes($password); ?>" placeholder="<?php echo lng('user.login.form.input_password_placeholder'); ?>"<?php if ($isLockCountStatus) { ?> disabled="disabled"<?php } ?>/>
                <div id="login-form-action">
                    <?php if ($appConfig->get('user.login.enable_forgot_password')) { ?>
                        <a href="forgot_password.php" id="forgot-password">
                            <span><?php echo lng('user.login.form.forgot_password'); ?></span>
                        </a>
                    <?php } ?>
                    <button type="submit" name="submit"<?php if ($isLockCountStatus) { ?> disabled="disabled"<?php } ?>>
                        <span><?php echo lng('user.login.form.button_login'); ?></span>
                    </button>
                </div>
            </form>
        </div>
    <?php } ?>

<?php require_once('..' .SP . 'incfiles' . SP . 'footer.php'); ?>