<?php

    namespace Librarys\App;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Boot;
    use Librarys\File\FileInfo;
    use Librarys\App\AppUser;
    use Librarys\App\AppUpdate;
    use Librarys\App\AppUpgrade;
    use Librarys\App\Config\AppAboutConfig;

    final class AppAlert
    {

        private $boot;
        private $id;
        private $langMsg;

        const SESSION_NAME_PREFIX = 'ALERT_';

        const DANGER  = 'danger';
        const SUCCESS = 'success';
        const WARNING = 'warning';
        const INFO    = 'info';
        const NONE    = 'none';

        public function __construct(Boot $boot)
        {
            $this->boot = $boot;

            if (FileInfo::isTypeFile(env('resource.define.alert')))
                require_once(env('resource.define.alert'));
        }

        public function danger($message, $id = null, $urlGoto = null)
        {
            $this->add($message, self::DANGER, $id, $urlGoto);
        }

        public function success($message, $id = null, $urlGoto = null)
        {
            $this->add($message, self::SUCCESS, $id, $urlGoto);
        }

        public function warning($message, $id = null, $urlGoto = null)
        {
            $this->add($message, self::WARNING, $id, $urlGoto);
        }

        public function info($message, $id = null, $urlGoto = null)
        {
            $this->add($message, self::INFO, $id, $urlGoto);
        }

        public function add($message, $type = self::DANGER, $id = null, $urlGoto = null)
        {
        	$this->boot->sessionInitializing();

            if ($id == null) {
                if ($this->id == null)
                    $this->id = time();

                $id = $this->id;
            }

            if ($message == null && $this->langMsg != null)
                $message = $this->langMsg;

            $_SESSION[self::SESSION_NAME_PREFIX . $id][] = [
                'message' => $message,
                'type'    => $type
            ];

            if ($urlGoto !== null)
                gotoURL($urlGoto);
        }

        public function display()
        {
            global $appUser, $appConfig;

            if ($appUser->checkUserIsUsePasswordDefault())
                $this->warning(lng('home.alert.password_user_is_equal_default', 'time', AppUser::TIME_SHOW_WARNING_PASSWORD_DEFAULT));

            if ($appUser->isPositionAdminstrator() && defined('DISABLE_ALERT_HAS_UPDATE') == false && $this->hasAlertDisplay() == false && $appConfig->get('check_update.enable', false) == true) {
                $appAboutConfig = new AppAboutConfig($this->boot);
                $timeCurrent    = time();
                $timeShow       = 300;
                $timeCheck      = $appConfig->get('check_update.time', 86400);
                $checkLast      = $appAboutConfig->get(AppAboutConfig::ARRAY_KEY_CHECK_AT, $timeCurrent);

                if ($timeCurrent - $checkLast >= $timeCheck) {
                    $appUpdate = new AppUpdate($this->boot, $appAboutConfig);

                    if ($appUpdate->checkUpdate() === true) {
                        $updateStatus = $appUpdate->getUpdateStatus();

                        if ($updateStatus === AppUpdate::RESULT_VERSION_IS_OLD)
                            $this->success(lng('app.check_update.alert.version_is_old', 'version_current', $appAboutConfig->get(AppAboutConfig::ARRAY_KEY_VERSION), 'version_update', $appUpdate->getVersionUpdate()));
                        else if ($updateStatus === AppUpdate::RESULT_HAS_ADDITIONAL)
                            $this->success(lng('app.check_update.alert.has_additional', 'version_current', $appAboutConfig->get(AppAboutConfig::ARRAY_KEY_VERSION)));
                        else
                            $this->info(lng('app.check_update.alert.version_is_latest', 'version_current', $appAboutConfig->get(AppAboutConfig::ARRAY_KEY_VERSION)));
                    }
                } else {
                    $appUpgrade = new AppUpgrade($this->boot, $appAboutConfig);

                    if ($appUpgrade->checkHasUpgradeLocal()) {
                        if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_UPGRADE)
                            $this->success(lng('app.check_update.alert.version_is_old', 'version_current', $appAboutConfig->get(AppAboutConfig::ARRAY_KEY_VERSION), 'version_update', $appUpgrade->getVersionUpgrade()));
                        else if ($appUpgrade->getTypeBinInstall() === AppUpgrade::TYPE_BIN_INSTALL_ADDITIONAL)
                            $this->success(lng('app.check_update.alert.has_additional', 'version_current', $appAboutConfig->get(AppAboutConfig::ARRAY_KEY_VERSION)));
                    }
                }
            }

            if ($this->id != null && isset($_SESSION[self::SESSION_NAME_PREFIX . $this->id]) && count($_SESSION[self::SESSION_NAME_PREFIX . $this->id]) > 0) {
                $array  = $_SESSION[self::SESSION_NAME_PREFIX . $this->id];
                $buffer = '<ul class="alert">';

                foreach ($array AS $index => $alert) {
                    if (is_object($alert['message']))
                        $alert['message'] = 'Object';
                    else if (is_array($alert['message']))
                        $alert['message'] = 'Array';

                    $buffer .= '<li class="' . $alert['type'] . '">';
                    $buffer .= '<span>' . $alert['message'] . '</span>';
                    $buffer .= '</li>';
                }

                $buffer .= '</ul>';

                echo($buffer);
                unset($_SESSION[self::SESSION_NAME_PREFIX . $this->id]);
            }
        }

        public function setID($id)
        {
            $this->id = $id;
        }

        public function setLangMsg($key)
        {
            $args = func_get_args();
            $nums = func_num_args();

            if ($nums <= 1)
                $args = [];
            else
                $args = array_splice($args, 1, $nums);

            $this->langMsg = lng($key, $args);
        }

        public function removeLangMsg()
        {
            $this->langMsg = null;
        }

        public function getLangMsg()
        {
            return $this->langMsg;
        }

        public function clean($id = null)
        {
            if ($id == null)
                $id = $this->id;

            if ($id == null)
                return;

            unset($_SESSION[self::SESSION_NAME_PREFIX . $id]);
        }

        public function hasAlertDisplay($id = null)
        {
            if ($id == null)
                $id = $this->id;

            if ($id == null)
                return false;

            return isset($_SESSION[self::SESSION_NAME_PREFIX . $id]) && count($_SESSION[self::SESSION_NAME_PREFIX . $id]) > 0;
        }

        public function gotoURL($url)
        {
            gotoURL($url);
        }

    }

?>