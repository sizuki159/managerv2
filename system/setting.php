<?php

    define('LOADED',  1);
    define('SETTING', 1);

    require_once('global.php');

    $title = lng('system.setting.title_page');
    $appAlert->setID(ALERT_SYSTEM_SETTING);
    require_once(ROOT . 'incfiles' . SP . 'header.php');

    $forms = [
        'http_referer' => null,

        'paging' => [
            'file_home_list'      => $appConfig->get('paging.file_home_list'),
            'file_view_zip'       => $appConfig->get('paging.file_view_zip'),
            'file_edit_text'      => $appConfig->get('paging.file_edit_text'),
            'file_edit_text_line' => $appConfig->get('paging.file_edit_text_line'),

            'mysql_list_data' => $appConfig->get('paging.mysql_list_data')
        ],

        'login' => [
            'enable_forgot_password' => $appConfig->get('login.enable_forgot_password')
        ],

        'enable_disable' => [
            'button_save_on_javascript'       => $appConfig->get('enable_disable.button_save_on_javascript'),
            'auto_focus_input_last'           => $appConfig->get('enable_disable.auto_focus_input_last'),
            'count_checkbox_file_javascript'  => $appConfig->get('enable_disable.count_checkbox_file_javascript'),
            'count_checkbox_mysql_javascript' => $appConfig->get('enable_disable.count_checkbox_mysql_javascript')
        ],

        'auto_redirect' => [
            'file_rename' => $appConfig->get('auto_redirect.file_rename'),
            'file_chmod'  => $appConfig->get('auto_redirect.file_chmod'),

            'create_directory' => $appConfig->get('auto_redirect.create_directory'),
            'create_file'      => $appConfig->get('auto_redirect.create_file'),
            'create_database'  => $appConfig->get('auto_redirect.create_database'),
            'rename_database'  => $appConfig->get('auto_redirect.rename_database')
        ],
    ];

    if (isset($_SERVER['HTTP_REFERER']))
        $forms['http_referer'] = trim($_SERVER['HTTP_REFERER']);

    if (isset($_POST['save'])) {
        $forms['http_referer'] = trim($_POST['http_referer']);
        $isFailed              = false;

        foreach ($forms['paging'] AS $key => &$value) {
            $envKey  = 'paging.' . $key;
            $formKey = 'paging_' . $key;

            if (isset($_POST[$formKey]))
                $value = intval(addslashes($_POST[$formKey]));
            else
                $value = 0;

            if ($appConfig->set($envKey, $value) == false) {
                $isFailed = true;
                $appAlert->danger(lng('system.setting.alert.save_setting_failed'));

                break;
            }
        }

        if ($isFailed == false) {
            if (isset($_POST['login_enable_forgot_password']))
                $forms['login']['enable_forgot_password'] = boolval(addslashes($_POST['login_enable_forgot_password']));
            else
                $forms['login']['enable_forgot_password'] = false;

            if ($appConfig->set('login.enable_forgot_password', $forms['login']['enable_forgot_password']) == false) {
                $isFailed = true;
                $appAlert->danger(lng('system.setting.alert.save_setting_failed'));
            }
        }

        if ($isFailed == false) {
            foreach ($forms['enable_disable'] AS $key => &$value) {
                $envKey  = 'enable_disable.' . $key;
                $formKey = 'enable_disable_' . $key;

                if (isset($_POST[$formKey]))
                    $value = boolval(addslashes($_POST[$formKey]));
                else
                    $value = false;

                if ($appConfig->set($envKey, $value) == false) {
                    $isFailed = true;
                    $appAlert->danger(lng('system.setting.alert.save_setting_failed'));

                    break;
                }
            }
        }

        if ($isFailed == false) {
            foreach ($forms['auto_redirect'] AS $key => &$value) {
                $envKey  = 'auto_redirect.' . $key;
                $formKey = 'auto_redirect_' . $key;

                if (isset($_POST[$formKey]))
                    $value = boolval(addslashes($_POST[$formKey]));
                else
                    $value = false;

                if ($appConfig->set($envKey, $value) == false) {
                    $isFailed = true;
                    $appAlert->danger(lng('system.setting.alert.save_setting_failed'));

                    break;
                }
            }
        }

        if ($isFailed == false) {
        	if ($appConfig->write())
                $appAlert->success(lng('system.setting.alert.save_setting_success'), null);
    	    else
                $appAlert->danger(lng('system.setting.alert.save_setting_failed'));
        }
    }

    if ($forms['http_referer'] == null || empty($forms['http_referer']))
        $forms['http_referer'] = env('app.http.host');
    else if (strpos($forms['http_referer'], $_SERVER['PHP_SELF']) !== false)
        $forms['http_referer'] = env('app.http.host');
?>

    <?php $appAlert->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('system.setting.title_page'); ?></span>
        </div>
        <form action="setting.php" method="post">
	        <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>
            <input type="hidden" name="http_referer" value="<?php echo $forms['http_referer']; ?>"/>

            <ul class="form-element">
                <?php if ($appConfig->isEnvEnabled('paging.file_home_list')) { ?>
                    <li class="input">
                        <span><?php echo lng('system.setting.form.input.paging_file_home_list'); ?></span>
                        <input type="number" name="paging_file_home_list" value="<?php echo $forms['paging']['file_home_list']; ?>" placeholder="<?php echo lng('system.setting.form.placeholder.input_paging_file_home_list'); ?>"/>
                    </li>
                <?php } ?>
                <?php if ($appConfig->isEnvEnabled('paging.file_view_zip')) { ?>
                    <li class="input">
                        <span><?php echo lng('system.setting.form.input.paging_file_view_zip'); ?></span>
                        <input type="number" name="paging_file_view_zip" value="<?php echo $forms['paging']['file_view_zip']; ?>" placeholder="<?php echo lng('system.setting.form.placeholder.input_paging_file_view_zip'); ?>"/>
                    </li>
                <?php } ?>
                <?php if ($appConfig->isEnvEnabled('paging.file_edit_text')) { ?>
                    <li class="input">
                        <span><?php echo lng('system.setting.form.input.paging_file_edit_text'); ?></span>
                        <input type="number" name="paging_file_edit_text" value="<?php echo $forms['paging']['file_edit_text']; ?>" placeholder="<?php echo lng('system.setting.form.placeholder.input_paging_file_edit_text'); ?>"/>
                    </li>
                <?php } ?>
                <?php if ($appConfig->isEnvEnabled('paging_file_edit_text_line')) { ?>
                    <li class="input">
                        <span><?php echo lng('system.setting.form.input.paging_file_edit_text_line'); ?></span>
                        <input type="number" name="paging_file_edit_text_line" value="<?php echo $forms['paging']['file_edit_text_line']; ?>" placeholder="<?php echo lng('system.setting.form.placeholder.input_paging_file_edit_text_line'); ?>"/>
                    </li>
                <?php } ?>
                <?php if ($appConfig->isEnvEnabled('paging_mysql_list_data')) { ?>
                    <li class="input">
                        <span><?php echo lng('system.setting.form.input.paging_mysql_list_data'); ?></span>
                        <input type="number" name="paging_mysql_list_data" value="<?php echo $forms['paging']['mysql_list_data']; ?>" placeholder="<?php echo lng('system.setting.form.placeholder.input_paging_mysql_list_data'); ?>"/>
                    </li>
                <?php } ?>
                <li class="checkbox">
                    <span><?php echo lng('system.setting.form.input.enable_disable_label'); ?></span>
                    <ul>
                        <?php if ($appConfig->isEnvEnabled('login.enable_forgot_password')) { ?>
                            <li>
                                <input type="checkbox" id="login-enable-forgot-password" name="login_enable_forgot_password" value="1"<?php if ($forms['login']['enable_forgot_password'] == true) { ?> checked="checked"<?php } ?>/>
                                <label for="login-enable-forgot-password">
                                    <span><?php echo lng('system.setting.form.input.enable_forgot_password'); ?></span>
                                </label>
                            </li>
                        <?php } ?>
                        <?php if ($appConfig->isEnvEnabled('enable_disable.button_save_on_javascript')) { ?>
                            <li>
                                <input type="checkbox" id="enable-disable-button-save-on-javascript" name="enable_disable_button_save_on_javascript" value="1"<?php if ($forms['enable_disable']['button_save_on_javascript'] == true) { ?> checked="checked"<?php } ?>/>
                                <label for="enable-disable-button-save-on-javascript">
                                    <span><?php echo lng('system.setting.form.input.enable_disable_button_save_on_javascript'); ?></span>
                                </label>
                            </li>
                        <?php } ?>
                        <?php if ($appConfig->isEnvEnabled('enable_disable.auto_focus_input_last')) { ?>
                            <li>
                                <input type="checkbox" id="enable-disable-auto-focus-input-last" name="enable_disable_auto_focus_input_last" value="1"<?php if ($forms['enable_disable']['auto_focus_input_last'] == true) { ?> checked="checked"<?php } ?>/>
                                <label for="enable-disable-auto-focus-input-last">
                                    <span><?php echo lng('system.setting.form.input.enable_disable_auto_focus_input_last'); ?></span>
                                </label>
                            </li>
                        <?php } ?>
                        <?php if ($appConfig->isEnvEnabled('enable_disable.count_checkbox_file_javascript')) { ?>
                            <li>
                                <input type="checkbox" id="enable-disable-count-checkbox-file-javascript" name="enable_disable_count_checkbox_file_javascript" value="1"<?php if ($forms['enable_disable']['count_checkbox_file_javascript'] == true) { ?> checked="checked"<?php } ?>/>
                                <label for="enable-disable-count-checkbox-file-javascript">
                                    <span><?php echo lng('system.setting.form.input.enable_disable_count_checkbox_file_javascript'); ?></span>
                                </label>
                            </li>
                        <?php } ?>
                        <?php if ($appConfig->isEnvEnabled('enable_disable.count_checkbox_mysql_javascript')) { ?>
                            <li>
                                <input type="checkbox" id="enable-disable-count-checkbox-mysql-javascript" name="enable_disable_count_checkbox_mysql_javascript" value="1"<?php if ($forms['enable_disable']['count_checkbox_mysql_javascript'] == true) { ?> checked="checked"<?php } ?>/>
                                <label for="enable-disable-count-checkbox-mysql-javascript">
                                    <span><?php echo lng('system.setting.form.input.enable_disable_count_checkbox_mysql_javascript'); ?></span>
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
                <li class="checkbox">
                    <span><?php echo lng('system.setting.form.input.auto_redirect_label'); ?></span>
                	<ul>
                        <?php if ($appConfig->isEnvEnabled('auto_redirect.file_rename')) { ?>
                    		<li>
    		                	<input type="checkbox" id="auto-redirect-file-rename" name="auto_redirect_file_rename" value="1"<?php if($forms['auto_redirect']['file_rename'] == true) { ?> checked="checked"<?php } ?>/>
    		                	<label for="auto-redirect-file-rename">
    		                		<span><?php echo lng('system.setting.form.input.enable_auto_redirect_file_rename'); ?></span>
    		                	</label>
                    		</li>
                        <?php } ?>
                        <?php if ($appConfig->isEnvEnabled('auto_redirect.file_chmod')) { ?>
                    		<li>
    		                	<input type="checkbox" id="auto-redirect-file-chmod" name="auto_redirect_file_chmod" value="1"<?php if($forms['auto_redirect']['file_chmod'] == true) { ?> checked="checked"<?php } ?>/>
            		        	<label for="auto-redirect-file-chmod">
                    				<span><?php echo lng('system.setting.form.input.enable_auto_redirect_file_chmod'); ?></span>
                    			</label>
                    		</li>
                        <?php } ?>
                        <?php if ($appConfig->isEnvEnabled('auto_redirect.create_directory')) { ?>
                    		<li>
    		                	<input type="checkbox" id="auto-redirect-create-directory" name="auto_redirect_create_directory" value="1"<?php if($forms['auto_redirect']['create_directory'] == true) { ?> checked="checked"<?php } ?>/>
            		        	<label for="auto-redirect-create-directory">
                    				<span><?php echo lng('system.setting.form.input.enable_auto_redirect_create_directory'); ?></span>
                    			</label>
                    		</li>
                        <?php } ?>
                        <?php if ($appConfig->isEnvEnabled('auto_redirect.create_file')) { ?>
                    		<li>
    		                	<input type="checkbox" id="auto-redirect-create-file" name="auto_redirect_create_file" value="1"<?php if($forms['auto_redirect']['create_file'] == true) { ?> checked="checked"<?php } ?>/>
            		        	<label for="auto-redirect-create-file">
                    				<span><?php echo lng('system.setting.form.input.enable_auto_redirect_create_file'); ?></span>
                    			</label>
                    		</li>
                        <?php } ?>
                        <?php if ($appConfig->isEnvEnabled('auto_redirect.create_database')) { ?>
                    		<li>
    		                	<input type="checkbox" id="auto-redirect-create-database" name="auto_redirect_create_database" value="1"<?php if($forms['auto_redirect']['create_database'] == true) { ?> checked="checked"<?php } ?>/>
            		        	<label for="auto-redirect-create-database">
                    				<span><?php echo lng('system.setting.form.input.enable_auto_redirect_create_database'); ?></span>
                    			</label>
                    		</li>
                        <?php } ?>
                        <?php if ($appConfig->isEnvEnabled('auto_redirect.rename_database')) { ?>
                            <li>
                                <input type="checkbox" id="auto-redirect-rename-database" name="auto_redirect_rename_database" value="1"<?php if($forms['auto_redirect']['rename_database'] == true) { ?> checked="checked"<?php } ?>/>
                                <label for="auto-redirect-rename-database">
                                    <span><?php echo lng('system.setting.form.input.enable_auto_redirect_rename_database'); ?></span>
                                </label>
                            </li>
                        <?php } ?>
                	</ul>
                </li>
                <li class="button">
                    <button type="submit" name="save" id="button-save-on-javascript">
                        <span><?php echo lng('system.setting.form.button.save'); ?></span>
                    </button>
                    <a href="<?php echo $forms['http_referer']; ?>">
                        <span><?php echo lng('system.setting.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="alert">
    	<li class="info">
    		<span><?php echo lng('system.setting.alert.tips'); ?></span>
    	</li>
    </ul>

    <ul class="menu-action">
        <li>
            <a href="<?php echo env('app.http.host'); ?>/system/setting_theme.php">
                <span class="icomoon icon-theme"></span>
                <span><?php echo lng('system.setting.menu_action.setting_theme'); ?></span>
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