<?php

    use Librarys\App\Config\AppAboutConfig;
    use Librarys\App\AppUpdate;
    use Librarys\App\AppUpgrade;
    use Librarys\File\FileCurl;

    define('LOADED',                   1);
    define('DISABLE_ALERT_HAS_UPDATE', 1);
    define('PARAMETER_CHECK_URL',      'check');

    require_once('global.php');

    if ($appUser->isPositionAdminstrator() == false)
        $appAlert->danger(lng('user.default.alert.not_permission_access_feature'), ALERT_INDEX, env('app.http.host'));

    $title      = lng('app.check_update.title_page');
    $themes     = [ env('resource.filename.theme.about') ];
    $config     = new AppAboutConfig($boot);
    $appUpdate  = new AppUpdate($boot, $config);
    $appUpgrade = new AppUpgrade($boot, $config);
    $servers    = $appUpdate->getServers();
    $appAlert->setID(ALERT_APP_CHECK_UPDATE);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $hasUpgrade = $appUpgrade->checkHasUpgradeLocal();

    if (isset($_GET[PARAMETER_CHECK_URL])) {
        if (count($servers) <= 0) {
            $appAlert->danger(lng('app.check_update.alert.not_server_check'));
        } else if ($appUpdate->checkUpdate() === false) {
            $serverErrors = $appUpdate->getServerErrors();

            if (is_array($serverErrors)) {
                foreach ($serverErrors AS $server => $errors) {
                    $errorInt       = $errors[AppUpdate::ARRAY_KEY_ERROR_INT];
                    $errorUrl       = $errors[AppUpdate::ARRAY_KEY_URL];
                    $errorCheck     = $errors[AppUpdate::ARRAY_KEY_ERROR_CHECK];
                    $errorServer    = $errors[AppUpdate::ARRAY_KEY_ERROR_SERVER];
                    $errorWriteInfo = $errors[AppUpdate::ARRAY_KEY_ERROR_WRITE_INFO];

                    if ($errorInt === FileCurl::ERROR_URL_NOT_FOUND)
                        $appAlert->danger(lng('app.check_update.alert.address_not_found', 'url', $errorUrl));
                    else if ($errorInt === FileCurl::ERROR_FILE_NOT_FOUND)
                        $appAlert->danger(lng('app.check_update.alert.file_not_found', 'url', $errorUrl));
                    else if ($errorInt === FileCurl::ERROR_AUTO_REDIRECT)
                        $appAlert->danger(lng('app.check_update.alert.auto_redirect_url_failed', 'url', $errorUrl));
                    else if ($errorInt === FileCurl::ERROR_CONNECT_FAILED)
                        $appAlert->danger(lng('app.check_update.alert.connect_url_failed', 'url', $errorUrl));
                    else if ($errorCheck === AppUpdate::ERROR_CHECK_JSON_DATA)
                        $appAlert->danger(lng('app.check_update.alert.error_json_data', 'url', $errorUrl));
                    else if ($errorCheck === AppUpdate::ERROR_CHECK_JSON_DATA_NOT_VALIDATE)
                        $appAlert->danger(lng('app.check_update.alert.error_json_data_not_validate', 'url', $errorUrl));
                    else if ($errorServer === AppUpdate::ERROR_SERVER_NOT_FOUND_LIST_VERSION_IN_SERVER)
                        $appAlert->danger(lng('app.check_update.alert.error_not_found_list_version_in_server', 'url', $errorUrl));
                    else if ($errorServer === AppUpdate::ERROR_SERVER_NOT_FOUND_PARAMETER_VERSION_GUEST)
                        $appAlert->danger(lng('app.check_update.alert.error_not_found_parameter_guest', 'url', $errorUrl));
                    else if ($errorServer === AppUpdate::ERROR_SERVER_NOT_FOUND_PARAMETER_VERSION_BUILD)
                        $appAlert->danger(lng('app.check_update.alert.error_not_found_parameter_build', 'url', $errorUrl));
                    else if ($errorServer === AppUpdate::ERROR_SERVER_VERSION_GUEST_NOT_VALIDATE)
                        $appAlert->danger(lng('app.check_update.alert.error_version_guest_not_validate', 'url', $errorUrl));
                    else if ($errorServer === AppUpdate::ERROR_SERVER_VERSION_SERVER_NOT_VALIDATE)
                        $appAlert->danger(lng('app.check_update.alert.error_version_server_not_validate', 'url', $errorUrl));
                    else if ($errorServer === AppUpdate::ERROR_SERVER_NOT_FOUND_VERSION_CURRENT_IN_SERVER)
                        $appAlert->danger(lng('app.check_update.alert.error_not_found_version_current_in_server', 'url', $errorUrl));
                    else if ($errorWriteInfo === AppUpdate::ERROR_WRITE_INFO_FAILED)
                        $appAlert->danger(lng('app.check_update.alert.error_write_info_failed', 'url', $errorUrl));
                    else if ($errorWriteInfo === AppUpdate::ERROR_MKDIR_SAVE_DATA_UPGRADE)
                        $appAlert->danger(lng('app.check_update.alert.error_mkdir_save_data_upgrade', 'url', $errorUrl));
                    else if ($errorWriteInfo === AppUpdate::ERROR_DECODE_COMPRESS_DATA)
                        $appAlert->danger(lng('app.check_update.alert.error_decode_compress_data', 'url', $errorUrl));
                    else if ($errorWriteInfo === AppUpdate::ERROR_DECODE_COMPRESS_ADDITIONAL_UPDATE)
                        $appAlert->danger(lng('app.check_update.alert.error_decode_compress_additional_update', 'url', $errorUrl));
                    else if ($errorWriteInfo === AppUpdate::ERROR_DECODE_COMPRESS_UPDATE_SCRIPT)
                        $appAlert->danger(lng('app.check_update.alert.error_decode_compress_update_script', 'url', $errorUrl));
                    else if ($errorWriteInfo === AppUpdate::ERROR_WRITE_DATA_UPGRADE)
                        $appAlert->danger(lng('app.check_update.alert.error_write_data_upgrade', 'url', $errorUrl));
                    else if ($errorWriteInfo === AppUpdate::ERROR_WRITE_ADDITIONAL_UPDATE)
                        $appAlert->danger(lng('app.check_update.alert.error_write_additional_update', 'url', $errorUrl));
                    else if ($errorWriteInfo === AppUpdate::ERROR_MD5_BIN_CHECK)
                        $appAlert->danger(lng('app.check_update.alert.error_md5_bin_check', 'url', $errorUrl));
                    else if ($errorWriteInfo === AppUpdate::ERROR_MD5_ADDITIONAL_UPDATE_CHECK)
                        $appAlert->danger(lng('app.check_update.alert.error_md5_additional_update_check', 'url', $errorUrl));
                    else if ($errorWriteInfo === AppUpdate::ERROR_WRITE_UPDATE_SCRIPT)
                        $appAlert->danger(lng('app.check_update.alert.error_write_update_script', 'url', $errorUrl));
                    else
                        $appAlert->danger(lng('app.check_update.alert.error_unknown', 'url', $errorUrl));
                }
            }
        } else {
            $updateStatus = $appUpdate->getUpdateStatus();

            if ($updateStatus === AppUpdate::RESULT_VERSION_IS_OLD)
                $appAlert->success(lng('app.check_update.alert.version_is_old', 'version_current', $config->get(AppAboutConfig::ARRAY_KEY_VERSION), 'version_update', $appUpdate->getVersionUpdate()), ALERT_APP_UPGRADE_APP, 'upgrade_app.php');
            else if ($updateStatus === AppUpdate::RESULT_HAS_ADDITIONAL)
                $appAlert->success(lng('app.check_update.alert.has_additional', 'version_current', $config->get(AppAboutConfig::ARRAY_KEY_VERSION)), ALERT_APP_UPGRADE_APP, 'upgrade_app.php');
            else
                $appAlert->info(lng('app.check_update.alert.version_is_latest', 'version_current', $config->get(AppAboutConfig::ARRAY_KEY_VERSION)));
        }

        gotoURL('check_update.php');
    } else if ($hasUpgrade && $appAlert->hasAlertDisplay() == false) {
        if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_UPGRADE)
            $appAlert->success(lng('app.check_update.alert.version_is_old', 'version_current', $config->get(AppAboutConfig::ARRAY_KEY_VERSION), 'version_update', $appUpgrade->getVersionUpgrade()));
        else if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_ADDITIONAL)
            $appAlert->success(lng('app.check_update.alert.has_additional', 'version_current', $config->get(AppAboutConfig::ARRAY_KEY_VERSION)));
    }
?>

    <?php $appAlert->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('app.check_update.title_page'); ?></span>
        </div>

        <ul class="about-list">
            <li class="label">
                <ul>
                    <li><span><?php echo lng('app.check_update.info.label.last_check_update'); ?></span></li>
                    <li><span><?php echo lng('app.check_update.info.label.last_upgrade'); ?></span></li>
                    <li><span><?php echo lng('app.check_update.info.label.last_build'); ?></span></li>
                    <li><span><?php echo lng('app.check_update.info.label.version_current'); ?></span></li>

                    <?php if (is_array($servers)) { ?>
                        <?php for ($i = 0; $i < count($servers); ++$i) { ?>
                            <li><span><?php echo lng('app.check_update.info.label.server_check', 'index', $i); ?></span></li>
                        <?php } ?>
                    <?php } ?>
                </ul>
            </li>

            <li class="value">
                <ul>
                    <?php if ($config->get(AppAboutConfig::ARRAY_KEY_CHECK_AT) <= 0) { ?>
                        <li><span><?php echo lng('app.check_update.info.value.not_last_check_update'); ?></span></li>
                    <?php } else { ?>
                        <li><span><?php echo date('d.m.Y - H:i:s', intval($config->get(AppAboutConfig::ARRAY_KEY_CHECK_AT))); ?></span></li>
                    <?php } ?>

                    <?php if ($config->get(AppAboutConfig::ARRAY_KEY_UPGRADE_AT) <= 0) { ?>
                        <li><span><?php echo lng('app.check_update.info.value.not_last_upgrade'); ?></span></li>
                    <?php } else { ?>
                        <li><span><?php echo date('d.m.Y - H:i:s', intval($config->get(AppAboutConfig::ARRAY_KEY_UPGRADE_AT))); ?></span></li>
                    <?php } ?>

                    <li><span><?php echo date('d.m.Y - H:i:s', intval($config->get(AppAboutConfig::ARRAY_KEY_BUILD_AT))); ?></span></li>

                    <li><span><?php echo $config->get(AppAboutConfig::ARRAY_KEY_VERSION); ?> <?php if ($config->get(AppAboutConfig::ARRAY_KEY_IS_BETA)) echo 'beta'; ?></span></li>

                    <?php if (is_array($servers)) { ?>
                        <?php foreach ($servers AS $server) { ?>
                            <li><span><?php echo $server; ?></span></li>
                        <?php } ?>
                    <?php } ?>
                </ul>
            </li>
        </ul>

        <div class="about-button-check button-action-box center">
            <a href="check_update.php?<?php echo PARAMETER_CHECK_URL; ?>">
                <span><?php echo lng('app.check_update.form.button.check'); ?></span>
            </a>
        </div>
    </div>

    <ul class="alert">
        <li class="info"><span><?php echo lng('app.check_update.alert.tips'); ?></span></li>
    </ul>

    <ul class="menu-action">
        <li>
            <a href="about.php">
                <span class="icomoon icon-about"></span>
                <span><?php echo lng('app.about.menu_action.about'); ?></span>
            </a>
        </li>

        <?php if ($hasUpgrade) { ?>
            <li>
                <a href="upgrade_app.php">
                    <span class="icomoon icon-update"></span>
                    <span><?php echo lng('app.about.menu_action.upgrade_app'); ?></span>
                </a>
            </li>
        <?php } ?>

        <li>
            <a href="validate_app.php">
                <span class="icomoon icon-check"></span>
                <span><?php echo lng('app.about.menu_action.validate_app'); ?></span>
            </a>
        </li>
        <li>
            <a href="help.php">
                <span class="icomoon icon-help"></span>
                <span><?php echo lng('app.about.menu_action.help'); ?></span>
            </a>
        </li>
        <li>
            <a href="feedback.php">
                <span class="icomoon icon-feedback"></span>
                <span><?php echo lng('app.about.menu_action.feedback'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once(ROOT . 'incfiles' . SP . 'footer.php'); ?>