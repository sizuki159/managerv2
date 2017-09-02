<?php

    namespace Librarys\App;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Boot;
    use Librarys\File\FileInfo;
    use Librarys\File\FileCurl;
    use Librarys\App\AppParameter;
    use Librarys\App\Config\AppAboutConfig;
    use Librarys\App\Config\AppUpgradeConfig;

    final class AppUpdate
    {

        private $boot;
        private $aboutConfig;
        private $servers;
        private $serverErrors = array();
        private $path         = null;

        private $jsonArray;
        private $updateStatus;
        private $updateType;

        const PARAMETER_VERSION_GUEST_URL  = 'version_guest';
        const PARAMETER_VERSION_BUILD_URL  = 'version_build_guest';
        const PARAMETER_LANGUAGE_GUEST_URL = 'language_guest';

        const ARRAY_KEY_URL              = 'url';
        const ARRAY_KEY_ERROR_INT        = 'error_int';
        const ARRAY_KEY_HTTP_CODE        = 'http_code';
        const ARRAY_KEY_ERROR_CHECK      = 'error_check';
        const ARRAY_KEY_ERROR_SERVER     = 'error_server';
        const ARRAY_KEY_ERROR_WRITE_INFO = 'error_write_info';

        const ARRAY_DATA_KEY_SERVER_NAME          = 'server_name';
        const ARRAY_DATA_KEY_VERSION              = 'version';
        const ARRAY_DATA_KEY_IS_BETA              = 'is_beta';
        const ARRAY_DATA_KEY_CHANGELOG            = 'changelog';
        const ARRAY_DATA_KEY_README               = 'readme';
        const ARRAY_DATA_KEY_BUILD_LAST           = 'build_last';
        const ARRAY_DATA_KEY_DATA_UPGRADE         = 'data_upgrade';
        const ARRAY_DATA_KEY_ADDITIONAL_UPDATE    = 'additional_update';
        const ARRAY_DATA_KEY_UPDATE_SCRIPT        = 'update_script';
        const ARRAY_DATA_KEY_MD5_BIN_CHECK        = 'md5_bin_check';
        const ARRAY_DATA_KEY_MD5_ADDITIONAL_CHECK = 'md5_additional_check';
        const ARRAY_DATA_KEY_ENTRY_IGONE_REMOVE   = 'entry_igone_remove';
        const ARRAY_DATA_KEY_ERROR_INT            = 'error_int';

        const RESULT_NONE              = 0;
        const RESULT_VERSION_IS_LATEST = 1;
        const RESULT_VERSION_IS_OLD    = 2;
        const RESULT_HAS_ADDITIONAL    = 3;

        const ERROR_CHECK_NONE                   = 0;
        const ERROR_CHECK_JSON_DATA              = 1;
        const ERROR_CHECK_JSON_DATA_NOT_VALIDATE = 2;

        const ERROR_SERVER_NONE                                = 0;
        const ERROR_SERVER_NOT_FOUND_LIST_VERSION_IN_SERVER    = 1;
        const ERROR_SERVER_NOT_FOUND_PARAMETER_VERSION_GUEST   = 2;
        const ERROR_SERVER_NOT_FOUND_PARAMETER_VERSION_BUILD   = 3;
        const ERROR_SERVER_VERSION_GUEST_NOT_VALIDATE          = 4;
        const ERROR_SERVER_VERSION_SERVER_NOT_VALIDATE         = 5;
        const ERROR_SERVER_NOT_FOUND_VERSION_CURRENT_IN_SERVER = 6;

        const ERROR_WRITE_INFO_NONE                    = 0;
        const ERROR_WRITE_INFO_FAILED                  = 1;
        const ERROR_MKDIR_SAVE_DATA_UPGRADE            = 2;
        const ERROR_DECODE_COMPRESS_DATA               = 3;
        const ERROR_DECODE_COMPRESS_ADDITIONAL_UPDATE  = 4;
        const ERROR_DECODE_COMPRESS_UPDATE_SCRIPT      = 5;
        const ERROR_WRITE_DATA_UPGRADE                 = 6;
        const ERROR_WRITE_ADDITIONAL_UPDATE            = 7;
        const ERROR_WRITE_UPDATE_SCRIPT                = 8;
        const ERROR_MD5_BIN_CHECK                      = 9;
        const ERROR_MD5_ADDITIONAL_UPDATE_CHECK        = 10;
        const ERROR_WRITE_UNKNOWN                      = 11;

        const VERSION_BIN_FILENAME            = 'bin.zip';
        const VERSION_ADDITIONAL_FILENAME     = 'additional.zip';
        const VERSION_BIN_MD5_FILENAME        = 'bin.zip.md5';
        const VERSION_ADDITIONAL_MD5_FILENAME = 'additional.zip.md5';
        const VERSION_CHANGELOG_FILENAME      = 'changelog.md';
        const VERSION_README_FILENAME         = 'readme.md';
        const VERSION_UPDATE_SCRIPT_FILENAME  = 'update.script';

        const TYPE_BIN_UPGRDAE    = 1;
        const TYPE_BIN_ADDITIONAL = 2;

        public function __construct(Boot $boot, AppAboutConfig $about)
        {
            $this->boot        = $boot;
            $this->aboutConfig = $about;
            $this->servers     = env('app.server_app');

            if (Boot::isRunLocal())
                $this->path = 'app/ManagerServer/check_update.php';
            else
                $this->path = 'app/manager/check_update.php';
        }

        public function checkUpdate()
        {
            global $appConfig, $appUser;

            if ($appUser->isPositionAdminstrator() == false)
                return false;

            if (is_array($this->servers) == false)
                return false;

            self::cleanUpgrade();

            $languageCurrent = 'en';
            $countSuccess    = count($this->servers);
            $appParameter    = new AppParameter();

            if ($appConfig != null)
                $languageCurrent = $appConfig->get('language');

            $appParameter->add(self::PARAMETER_VERSION_GUEST_URL,  AppDirectory::rawEncode($this->aboutConfig->get(AppAboutConfig::ARRAY_KEY_VERSION)));
            $appParameter->add(self::PARAMETER_VERSION_BUILD_URL,  AppDirectory::rawEncode($this->aboutConfig->get(AppAboutConfig::ARRAY_KEY_BUILD_AT)));
            $appParameter->add(self::PARAMETER_LANGUAGE_GUEST_URL, AppDirectory::rawEncode($languageCurrent));

            foreach ($this->servers AS $server) {
                $curl           = new FileCurl($server . '/' . $this->path . $appParameter->toString());
                $errorCheck     = self::ERROR_CHECK_NONE;
                $errorServer    = self::ERROR_SERVER_NONE;
                $errorWriteInfo = self::ERROR_WRITE_INFO_NONE;

                if ($curl->curl() != false) {
                    $bufferLength = $curl->getBufferLength();
                    $bufferData   = $curl->getBuffer();
                    $jsonData     = jsonDecode($bufferData);
                    $jsonValidate = self::validateJsonData($jsonData);

                    if ($jsonData === false) {
                        $errorCheck = self::ERROR_CHECK_JSON_DATA;
                    } else if ($jsonValidate === false) {
                        $errorCheck = self::ERROR_CHECK_JSON_DATA_NOT_VALIDATE;
                    } else {
                        $this->jsonArray = $jsonData;

                        if ($this->hasJsonArrayKey(self::ARRAY_DATA_KEY_ERROR_INT) && $this->getJsonArrayValue(self::ARRAY_DATA_KEY_ERROR_INT) !== self::ERROR_SERVER_NONE)
                            $errorServer = intval($this->getJsonArrayValue(self::ARRAY_DATA_KEY_ERROR_INT));
                        else if ($this->checkDataUpdate() != false && $this->makeFileUpdateInfo($errorWriteInfo))
                            return true;
                    }
                }

                $countSuccess--;
                $this->serverErrors[$server] = [
                    self::ARRAY_KEY_URL              => $curl->getURL(),
                    self::ARRAY_KEY_ERROR_INT        => $curl->getErrorInt(),
                    self::ARRAY_KEY_HTTP_CODE        => $curl->getHttpCode(),
                    self::ARRAY_KEY_ERROR_CHECK      => $errorCheck,
                    self::ARRAY_KEY_ERROR_SERVER     => $errorServer,
                    self::ARRAY_KEY_ERROR_WRITE_INFO => $errorWriteInfo
                ];
            }

            if ($countSuccess <= 0)
                return false;

            return true;
        }

        private function checkDataUpdate()
        {
            if ($this->aboutConfig instanceof AppAboutConfig == false)
                return false;

            if (is_array($this->jsonArray) == false)
                return false;

            $this->updateStatus = self::RESULT_NONE;

            $buildAt        = intval($this->aboutConfig->get(AppAboutConfig::ARRAY_KEY_BUILD_AT));
            $versionCurrent = $this->aboutConfig->get(AppAboutConfig::ARRAY_KEY_VERSION);
            $versionUpdate  = $this->getVersionUpdate();
            $buildUpdate    = intval($this->getBuildUpdate());

            if (self::versionCurrentIsOld($versionCurrent, $versionUpdate))
                $this->updateStatus = self::RESULT_VERSION_IS_OLD;
            else if ($buildUpdate > $buildAt)
                $this->updateStatus = self::RESULT_HAS_ADDITIONAL;
            else
                $this->updateStatus = self::RESULT_VERSION_IS_LATEST;

            $this->aboutConfig->setSystem(AppAboutConfig::ARRAY_KEY_CHECK_AT, time());
            $this->aboutConfig->write();

            return true;
        }

        private function makeFileUpdateInfo(&$errorWriteInfo = null)
        {
            if ($this->updateStatus === self::RESULT_VERSION_IS_LATEST && $this->updateStatus !== self::RESULT_HAS_ADDITIONAL)
                return true;

            $appUpgradeConfig = new AppUpgradeConfig($this->boot);

            foreach ($this->jsonArray AS $key => $value) {
                if (
                        $key !== self::ARRAY_DATA_KEY_DATA_UPGRADE      &&
                        $key !== self::ARRAY_DATA_KEY_ADDITIONAL_UPDATE &&
                        $key !== self::ARRAY_DATA_KEY_CHANGELOG         &&
                        $key !== self::ARRAY_DATA_KEY_README            &&
                        $key !== self::ARRAY_DATA_KEY_UPDATE_SCRIPT     &&
                        $key !== self::ARRAY_DATA_KEY_ERROR_INT         &&

                        $appUpgradeConfig->setSystem($key, $value) == false
                    )
                {
                    $errorWriteInfo = self::ERROR_WRITE_INFO_FAILED;
                    return false;
                }
            }

            if ($appUpgradeConfig->write() == false) {
                $errorWriteInfo = self::ERROR_WRITE_INFO_FAILED;
                return false;
            }

            $pathDirectoryUpgrade = env('app.path.upgrade');

            if (FileInfo::fileExists($pathDirectoryUpgrade) == false && FileInfo::mkdir($pathDirectoryUpgrade, true) == false) {
                $errorWriteInfo = self::ERROR_MKDIR_SAVE_DATA_UPGRADE;
                return false;
            }

            $binFilePath          = self::getPathFileUpgrade(self::VERSION_BIN_FILENAME);
            $additionFilePath     = self::getPathFileUpgrade(self::VERSION_ADDITIONAL_FILENAME);
            $changelogFilePath    = self::getPathFileUpgrade(self::VERSION_CHANGELOG_FILENAME);
            $readmeFilePath       = self::getPathFileUpgrade(self::VERSION_README_FILENAME);
            $updateScriptFilePath = self::getPathFileUpgrade(self::VERSION_UPDATE_SCRIPT_FILENAME);

            $binFileBuffer          = self::decodeCompress($this->getJsonArrayValue(self::ARRAY_DATA_KEY_DATA_UPGRADE));
            $additionalFileBuffer   = self::decodeCompress($this->getJsonArrayValue(self::ARRAY_DATA_KEY_ADDITIONAL_UPDATE));
            $changelogFileBuffer    = self::decodeCompress($this->getJsonArrayValue(self::ARRAY_DATA_KEY_CHANGELOG));
            $readmeFileBuffer       = self::decodeCompress($this->getJsonArrayValue(self::ARRAY_DATA_KEY_README));
            $updateScriptFileBuffer = self::decodeCompress($this->getJsonArrayValue(self::ARRAY_DATA_KEY_UPDATE_SCRIPT));

            if ($this->updateStatus === self::RESULT_VERSION_IS_OLD) {
                if ($binFileBuffer === false) {
                    $errorWriteInfo = self::ERROR_DECODE_COMPRESS_DATA;
                    return false;
                } else if (FileInfo::fileWriteContents($binFilePath, $binFileBuffer) == false) {
                    $errorWriteInfo = self::ERROR_WRITE_DATA_UPGRADE;
                    return false;
                } else if (strcmp(@md5_file($binFilePath), $this->getJsonArrayValue(self::ARRAY_DATA_KEY_MD5_BIN_CHECK)) !== 0) {
                    $errorWriteInfo = self::ERROR_MD5_BIN_CHECK;
                    return false;
                }
            } else if ($this->updateStatus === self::RESULT_HAS_ADDITIONAL) {
                if ($additionalFileBuffer === false) {
                    $errorWriteInfo = self::ERROR_DECODE_COMPRESS_ADDITIONAL_UPDATE;
                    return false;
                } else if (FileInfo::fileWriteContents($additionFilePath, $additionalFileBuffer) == false) {
                    $errorWriteInfo = self::ERROR_WRITE_ADDITIONAL_UPDATE;
                    return false;
                } else if (strcmp(@md5_file($additionFilePath), $this->getJsonArrayValue(self::ARRAY_DATA_KEY_MD5_ADDITIONAL_CHECK)) !== 0) {
                    $errorWriteInfo = self::ERROR_MD5_ADDITIONAL_UPDATE_CHECK;
                    return false;
                }
            } else {
                $errorWriteInfo = self::ERROR_WRITE_UNKNOWN;
                return false;
            }

            if ($updateScriptFileBuffer === false) {
                $errorWriteInfo = self::ERROR_DECODE_COMPRESS_UPDATE_SCRIPT;
                return false;
            } else if (FileInfo::fileWriteContents($updateScriptFilePath, $updateScriptFileBuffer) == false) {
                $errorWriteInfo = self::ERROR_WRITE_UPDATE_SCRIPT;
                return false;
            }

            if ($changelogFileBuffer !== false)
                FileInfo::fileWriteContents($changelogFilePath, htmlspecialchars($changelogFileBuffer));

            if ($readmeFileBuffer !== false)
                FileInfo::fileWriteContents($readmeFilePath, htmlspecialchars($readmeFileBuffer));

            return true;
        }

        public static function decodeCompress($data)
        {
            if ($data == null || empty($data))
                return false;

            return @hex2bin($data);
        }

        public function getServers()
        {
            return $this->servers;
        }

        public function getServerErrors()
        {
            return $this->serverErrors;
        }

        public function getUpdateStatus()
        {
            return $this->updateStatus;
        }

        public function getJsonArrayValue($key)
        {
            if (is_array($this->jsonArray) == false)
                return null;

            if (isset($this->jsonArray[$key]))
                return $this->jsonArray[$key];

            return null;
        }

        public function hasJsonArrayKey($key)
        {
            if (is_array($this->jsonArray) == false)
                return false;

            if (isset($this->jsonArray[$key]))
                return true;

            return false;
        }

        public function getVersionUpdate()
        {
            return $this->getJsonArrayValue(self::ARRAY_DATA_KEY_VERSION);
        }

        public function getBuildUpdate()
        {
            return $this->getJsonArrayValue(self::ARRAY_DATA_KEY_BUILD_LAST);
        }

        public static function getPathFileUpgrade($filenameEntry, $pathDirectorysCustom = null)
        {
            if ($pathDirectorysCustom !== null)
                return FileInfo::filterPaths($pathDirectorysCustom . SP . $filenameEntry);

            return FileInfo::filterPaths(env('app.path.upgrade') . SP . $filenameEntry);
        }

        public static function validateJsonData($jsonArray)
        {
            if ($jsonArray == null || is_array($jsonArray) == false)
                return false;

            if (isset($jsonArray[self::ARRAY_DATA_KEY_ERROR_INT]))
                return true;

            $keyHas      = array();
            $keyValidate = [
                self::ARRAY_DATA_KEY_VERSION,
                self::ARRAY_DATA_KEY_CHANGELOG,
                self::ARRAY_DATA_KEY_README,
                self::ARRAY_DATA_KEY_BUILD_LAST,
                self::ARRAY_DATA_KEY_DATA_UPGRADE,
                self::ARRAY_DATA_KEY_ADDITIONAL_UPDATE,
                self::ARRAY_DATA_KEY_UPDATE_SCRIPT,
                self::ARRAY_DATA_KEY_MD5_BIN_CHECK,
                self::ARRAY_DATA_KEY_MD5_ADDITIONAL_CHECK,
                self::ARRAY_DATA_KEY_ENTRY_IGONE_REMOVE
            ];

            foreach ($jsonArray AS $key => $value) {
                if (in_array($key, $keyValidate)) {
                    $keyHas[] = $key;

                    if ($key === self::ARRAY_DATA_KEY_VERSION && self::validateVersionValue($value) == false)
                        return false;
                }
            }

            $countKeyHas      = count($keyHas);
            $countKeyValidate = count($keyValidate);

            if ($countKeyValidate === $countKeyHas)
                return true;

            return false;
        }

        public static function validateVersionValue($version, &$matches = null)
        {
            return preg_match('/^(\d)+\.(\d)+\.?(\d)?$/i', $version, $matches);
        }

        public static function versionCurrentIsOld($versionCurrent, $versionUpdate)
        {
            $versionCurrentMatches = null;
            $versionUpdateMacthes  =  null;

            if (is_array($versionCurrent) == false)
                self::validateVersionValue($versionCurrent, $versionCurrentMatches);
            else
                $versionCurrentMatches = $versionCurrent;

            if (is_array($versionUpdate) == false)
                self::validateVersionValue($versionUpdate, $versionUpdateMacthes);
            else
                $versionUpdateMacthes = $versionUpdate;

            if (is_array($versionCurrentMatches) == false || is_array($versionUpdateMacthes) == false)
                return false;

            if (isset($versionCurrentMatches[3]) == false)
                $versionCurrentMatches[3] = -1;

            if (isset($versionUpdateMacthes[3]) == false)
                $versionUpdateMacthes[3] = -1;

            for ($i = 1; $i <= 3; ++$i) {
                if (intval($versionUpdateMacthes[$i]) > intval($versionCurrentMatches[$i]))
                    return true;
            }

            return false;
        }

        public static function cleanUpgrade()
        {
            $files = [
                env('resource.config.upgrade'),

                self::getPathFileUpgrade(self::VERSION_BIN_FILENAME),
                self::getPathFileUpgrade(self::VERSION_ADDITIONAL_FILENAME),
                self::getPathFileUpgrade(self::VERSION_CHANGELOG_FILENAME),
                self::getPathFileUpgrade(self::VERSION_README_FILENAME),
                self::getPathFileUpgrade(self::VERSION_UPDATE_SCRIPT_FILENAME)
            ];

            foreach ($files AS $path) {
                if (FileInfo::isTypeFile($path))
                    FileInfo::unlink($path);
            }
        }

    }

?>