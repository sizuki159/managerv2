<?php

    use Librarys\App\AppUser;
    use Librarys\App\Config\AppUserConfig;

    define('LOADED', 1);
    require_once('global.php');

    $title = lng('user.setting.title_page');
    $appAlert->setID(ALERT_USER_SETTING);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $forms = [
        'username'        => $appUser->get('username'),
        'email'           => $appUser->get('email'),
        'password_old'    => null,
        'password_new'    => null,
        'password_verify' => null
    ];

    if (isset($_POST['change'])) {
        $forms['username']        = addslashes($_POST['username']);
        $forms['email']           = addslashes($_POST['email']);
        $forms['password_old']    = addslashes($_POST['password_old']);
        $forms['password_new']    = addslashes($_POST['password_new']);
        $forms['password_verify'] = addslashes($_POST['password_verify']);

        $usernameConfig = $appUser->get('username');
        $emailConfig    = $appUser->get('email');
        $passwordConfig = $appUser->get('password');

        if (empty($forms['username'])) {
            $appAlert->danger(lng('user.setting.alert.not_input_username'));
        } else if (empty($forms['email'])) {
            $appAlert->danger(lng('user.setting.alert.not_input_email'));
        } else if (empty($forms['password_old'])) {
            $appAlert->danger(lng('user.setting.alert.not_input_password_old'));
        } else if (empty($forms['password_new']) == false && empty($forms['password_verify'])) {
            $appAlert->danger(lng('user.setting.alert.not_input_password_verify'));
        } else if (AppUser::isValidateUsername($forms['username']) == false) {
            $appAlert->danger(lng('user.setting.alert.username_not_validate', 'validate', AppUser::USERNAME_VALIDATE));
        } else if (isValidateEmail($forms['email']) == false) {
            $appAlert->danger(lng('user.setting.alert.email_not_validate'));
        } else if (empty($passwordConfig) == false && $appUser->checkPassword($passwordConfig, $forms['password_old']) === false) {
            $appAlert->danger(lng('user.setting.alert.password_old_wrong'));
        } else if (empty($forms['password_new']) == false && strcmp($forms['password_new'], $forms['password_verify']) !== 0) {
            $appAlert->danger(lng('user.setting.alert.password_new_not_equal_password_verify'));
        } else if (
            strcasecmp($usernameConfig, $forms['username']) === 0 &&
            strcasecmp($emailConfig,    $forms['email'])    === 0 &&

            (
                empty($forms['password_new']) ||
                AppUser::checkPassword($passwordConfig, $forms['password_new'])
            )
        ) {
            $appAlert->danger(lng('user.setting.alert.nothing_change'));
        } else {
            $time = time();

            if (
                $appUser->setConfig(AppUserConfig::ARRAY_KEY_USERNAME,  $forms['username']) == false ||
                $appUser->setConfig(AppUserConfig::ARRAY_KEY_EMAIL,     $forms['email'])    == false ||
                $appUser->setConfig(AppUserConfig::ARRAY_KEY_MODIFY_AT, time())             == false
            ) {
                $appAlert->danger(lng('user.setting.alert.change_config_info_failed'));
            } else if (
                empty($forms['password_new']) == false &&
                $appUser->setConfig(AppUserConfig::ARRAY_KEY_PASSWORD, AppUser::createPassword($forms['password_new'])) == false
            ) {
                $appAlert->danger(lng('user.setting.alert.change_config_password_failed'));
            } else if ($appUser->writeConfig(true) == false) {
                $appAlert->danger(lng('user.setting.alert.save_change_config_failed'));
            } else {
                $appAlert->success(lng('user.setting.alert.save_change_config_success'), ALERT_USER_LOGIN, 'login.php');
            }
        }
    }
?>

    <?php $appAlert->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('user.setting.title_page'); ?></span>
        </div>
        <form action="setting.php" method="post">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="input">
                    <span><?php echo lng('user.setting.form.input.username'); ?></span>
                    <input type="text" name="username" value="<?php echo $forms['username']; ?>" placeholder="<?php echo lng('user.setting.form.placeholder.input_username'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('user.setting.form.input.email'); ?></span>
                    <input type="email" name="email" value="<?php echo $forms['email']; ?>" placeholder="<?php echo lng('user.setting.form.placeholder.input_email'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('user.setting.form.input.password_old'); ?></span>
                    <input type="password" name="password_old" value="<?php echo $forms['password_old']; ?>" placeholder="<?php echo lng('user.setting.form.placeholder.input_password_old'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('user.setting.form.input.password_new'); ?></span>
                    <input type="password" name="password_new" value="<?php echo $forms['password_new']; ?>" placeholder="<?php echo lng('user.setting.form.placeholder.input_password_new'); ?>"/>
                </li>
                <li class="input">
                    <span><?php echo lng('user.setting.form.input.password_verify'); ?></span>
                    <input type="password" name="password_verify" value="<?php echo $forms['password_verify']; ?>" placeholder="<?php echo lng('user.setting.form.placeholder.input_password_verify'); ?>"/>
                </li>
                <li class="button">
                    <button type="submit" name="change">
                        <span><?php echo lng('user.setting.form.button.change'); ?></span>
                    </button>
                    <a href="<?php echo env('app.http.host'); ?>">
                        <span><?php echo lng('user.setting.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="alert">
        <li class="info"><?php echo lng('user.setting.alert.tips'); ?></li>
    </ul>

    <ul class="menu-action">
        <li>
            <a href="<?php echo env('app.http.host'); ?>/system/setting.php">
                <span class="icomoon icon-config"></span>
                <span><?php echo lng('system.setting.menu_action.setting_system'); ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo env('app.http.host'); ?>/system/setting_theme.php">
                <span class="icomoon icon-theme"></span>
                <span><?php echo lng('system.setting.menu_action.setting_theme'); ?></span>
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