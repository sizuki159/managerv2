<?php

    namespace Librarys\App;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Boot;
    use Librarys\File\FileInfo;
    use Librarys\Zip\PclZip;
    use Librarys\App\Config\AppAboutConfig;
    use Librarys\App\Config\AppUpgradeConfig;

    final class AppUpgrade
    {

        private $boot;
        private $appAboutConfig;
        private $appUpgradeConfig;
        private $isHasUpgradeLocal;
        private $typeBinInstall;

        const LOG_FILENAME_UPGRADE    = 'upgrade.log';
        const LOG_FILENAME_ADDITIONAL = 'additional.log';

        const ERROR_ZIP_NOT_OPEN_FILE_UPGRADE    = 1;
        const ERROR_ZIP_EXTRACT_FILE_UPGRADE     = 2;
        const ERROR_ZIP_NOT_OPEN_FILE_ADDITIONAL = 3;
        const ERROR_ZIP_EXTRACT_FILE_ADDITIONAL  = 4;

        const ERROR_UPGRADE_NOT_LIST_FILE_APP = 1;

        const ERROR_CHECK_UPGRADE_NONE                               = 0;
        const ERROR_CHECK_UPGRADE_FILE_NOT_FOUND                     = 1;
        const ERROR_CHECK_UPGRADE_ADDITIONAL_UPDATE_NOT_FOUND        = 2;
        const ERROR_CHECK_UPGRADE_FILE_DATA_ERROR                    = 3;
        const ERROR_CHECK_UPGRADE_FILE_DATA_ADDITIONAL_UPDATE_ERROR  = 4;
        const ERROR_CHECK_UPGRADE_MD5_CHECK_FAILED                   = 5;
        const ERROR_CHECK_UPGRADE_MD5_ADDITIONAL_UPDATE_CHECK_FAILED = 6;

        const TYPE_BIN_INSTALL_UPGRADE    = 1;
        const TYPE_BIN_INSTALL_ADDITIONAL = 2;

        public function __construct(Boot $boot, $appAboutConfig = null, $appUpgradeConfig = null)
        {
            $this->boot = $boot;

            if ($appAboutConfig == null)
                $this->appAboutConfig = new AppAboutConfig($boot);
            else
                $this->appAboutConfig = $appAboutConfig;

            if ($appUpgradeConfig == null)
                $this->appUpgradeConfig = new AppUpgradeConfig($boot);
            else
                $this->appUpgradeConfig = $appUpgradeConfig;
        }

        public function checkHasUpgradeLocal(&$errorCheckUpgrade = null)
        {
            global $appUser;

            if ($appUser->isPositionAdminstrator() == false)
                return false;

            if ($this->appUpgradeConfig->hasEntryConfigArrayAny() == false)
                return false;

            $versionUpdate  = $this->appUpgradeConfig->get(AppUpdate::ARRAY_DATA_KEY_VERSION);
            $versionCurrent = $this->appAboutConfig->get(AppAboutConfig::ARRAY_KEY_VERSION);
            $buildUpdate    = $this->appUpgradeConfig->get(AppUpdate::ARRAY_DATA_KEY_BUILD_LAST);
            $buildCurrent   = $this->appAboutConfig->get(AppAboutConfig::ARRAY_KEY_BUILD_AT);

            if (AppUpdate::validateVersionValue($versionCurrent, $versionCurrentMatches) == false)
                return false;

            if (AppUpdate::validateVersionValue($versionUpdate, $versionUpdateMatches) == false) {
                if (FileInfo::fileExists($this->appUpgradeConfig->getPathConfigSystem())) {
                    AppUpdate::cleanUpgrade();
                    FileInfo::unlink($this->appUpgradeConfig->getPathConfigSystem());
                }

                return false;
            }

            if (AppUpdate::versionCurrentIsOld($versionCurrent, $versionUpdate)) {
                $binFilePath = AppUpdate::getPathFileUpgrade(AppUpdate::VERSION_BIN_FILENAME);

                if (FileInfo::isTypeFile($binFilePath) == false) {
                    $errorCheckUpgrade = self::ERROR_CHECK_UPGRADE_FILE_NOT_FOUND;
                    return false;
                }

                if (FileInfo::fileSize($binFilePath) <= 0) {
                    $errorCheckUpgrade = self::ERROR_CHECK_UPGRADE_FILE_DATA_ERROR;
                    return false;
                }

                if (strcmp($this->appUpgradeConfig->get(AppUpdate::ARRAY_DATA_KEY_MD5_BIN_CHECK), @md5_file($binFilePath)) !== 0) {
                    $errorCheckUpgrade = self::ERROR_CHECK_UPGRADE_MD5_CHECK_FAILED;
                    return false;
                }

                $this->typeBinInstall = self::TYPE_BIN_INSTALL_UPGRADE;
                return true;
            } else if ($buildUpdate > $buildCurrent) {
                $additionalFilePath = AppUpdate::getPathFileUpgrade(AppUpdate::VERSION_ADDITIONAL_FILENAME);

                if (FileInfo::isTypeFile($additionalFilePath) == false) {
                    $errorCheckUpgrade = self::ERROR_CHECK_UPGRADE_FILE_ADDITIONAL_UPDATE_ERROR;
                    return false;
                }

                if (FileInfo::fileSize($additionalFilePath) <= 0) {
                    $errorCheckUpgrade = self::ERROR_CHECK_UPGRADE_FILE_DATA_ADDITIONAL_UPDATE_ERROR;
                    return false;
                }

                if (strcmp($this->appUpgradeConfig->get(AppUpdate::ARRAY_DATA_KEY_MD5_ADDITIONAL_CHECK), @md5_file($additionalFilePath)) !== 0) {
                    $errorCheckUpgrade = self::ERROR_CHECK_UPGRADE_MD5_ADDITIONAL_UPDATE_CHECK_FAILED;
                    return false;
                }

                $this->typeBinInstall = self::TYPE_BIN_INSTALL_ADDITIONAL;
                return true;
            }

            return false;
        }

        public function installUpgradeNow($checkHasUpgradeLocal = false, &$errorZipExtract = null, &$errorUpgrade = null)
        {
            global $appUser;

            if ($appUser->isPositionAdminstrator() == false)
                return false;

            if ($checkHasUpgradeLocal && $this->checkHasUpgradeLocal() == false)
                return false;

            $logHandle   = FileInfo::fileOpen(AppUpdate::getPathFileUpgrade(self::LOG_FILENAME_UPGRADE), 'wa+');
            $binFilePath = AppUpdate::getPathFileUpgrade(AppUpdate::VERSION_BIN_FILENAME);
            $pclZip      = new PclZip($binFilePath);

            FileInfo::fileWrite($logHandle, "Info: Open file upgrade zip begin\n");

            if ($pclZip === false) {
                $errorZipExtract = self::ERROR_ZIP_NOT_OPEN_FILE_UPGRADE;

                FileInfo::fileWrite($logHandle, $pclZip->errorInfo(true) . "\n");
                FileInfo::fileClose($logHandle);

                return false;
            }

            FileInfo::fileWrite($logHandle, "Info: Open file upgrade zip end\n");
            FileInfo::fileWrite($logHandle, "Info: Get list content in zip begin\n");
            $listContent = $pclZip->listContent();

            if (is_array($listContent) == false || count($listContent) <= 0) {
                $errorZipExtract = self::ERROR_ZIP_NOT_LIST_CONTENT;

                FileInfo::fileWrite($logHandle, $pclZip->errorInfo(true));
                FileInfo::fileClose($logHandle);

                return false;
            }

            FileInfo::fileWrite($logHandle, "Info: Get list content in zip end\n");

            $prefixDirectory = 'directory_';
            $prefixFile      = 'file_';
            $appPath         = env('app.path.root');
            $appContent      = FileInfo::listContent($appPath, $appPath, true, true, $prefixDirectory, $prefixFile);

            if (is_array($appContent) == false || count($appContent) <= 0) {
                $errorUpgrade = self::ERROR_UPGRADE_NOT_LIST_FILE_APP;

                FileInfo::fileWrite($logHandle, "Error: Not get list content app\n");
                FileInfo::fileClose($logHandle);

                return false;
            }

        if ($pclZip->extract(PCLZIP_OPT_PATH, FileInfo::filterPaths($appPath), PCLZIP_CB_PRE_EXTRACT, 'installUpgradeCallbackExtractZip') != false) {
                FileInfo::fileWrite($logHandle, "Info: Extract upgrade success\n");
                FileInfo::fileWrite($logHandle, "Info: Check file recycle in app begin\n");

                foreach ($listContent AS $entrys) {
                    $entryFilename = FileInfo::filterPaths($entrys['stored_filename']);

                    if ($entrys['folder'] == false && array_key_exists($prefixFile . $entryFilename, $appContent))
                        unset($appContent[$prefixFile . $entryFilename]);
                }

                $appEntryIgones = $this->appUpgradeConfig->get(AppUpdate::ARRAY_DATA_KEY_ENTRY_IGONE_REMOVE);

                foreach ($appContent AS $key => $entrys) {
                    $entryFilepath = $entrys['filepath'];
                    $entryIsIgone  = false;

                    foreach ($appEntryIgones AS $entryIgone) {
                        if (strpos($entryFilepath, $entryIgone) === 0) {
                            $entryIsIgone = true;
                            break;
                        }
                    }

                    $appEntryPath = FileInfo::filterPaths($appPath . SP . $entryFilepath);

                    if ($entryIsIgone) {
                        unset($appContent[$key]);
                    } else if ($entrys['is_directory'] == false) {
                        if (FileInfo::unlink($appEntryPath) != false)
                            FileInfo::fileWrite($logHandle, 'Success: Remove file ' . $appEntryPath . "\n");
                        else
                            FileInfo::fileWrite($logHandle, 'Failed: Remove file ' . $appEntryPath . "\n");
                    } else if ($entrys['is_directory'] == false) {
                        FileInfo::fileWrite($logHandle, 'Info: Skip remove file ' . $appEntryPath . "\n");
                    }
                }

                FileInfo::fileWrite($logHandle, "Info: Check file recycle in app end\n");
                FileInfo::fileWrite($logHandle, "Info: Check directory empty in app begin\n");

                foreach ($appContent AS $entrys) {
                    if ($entrys['is_directory']) {
                        $entryFilepath      = $entrys['filepath'];
                        $appEntryPath       = FileInfo::filterPaths($appPath . SP . $entryFilepath);
                        $globDirectoryEntry = FileInfo::globDirectory($appEntryPath . SP . '*');

                        if ($globDirectoryEntry === false ||  count($globDirectoryEntry) <= 0) {
                            if (FileInfo::rmdir($appEntryPath) != false)
                                FileInfo::fileWrite($logHandle, 'Success: Remove directory empty ' . $appEntryPath . "\n");
                            else
                                FileInfo::fileWrite($logHandle, 'Failed: Remove directory empty ' . $appEntryPath . "\n");
                        } else {
                            FileInfo::fileWrite($logHandle, 'Info: Skip remove directory ' . $appEntryPath . "\n");
                        }
                    }
                }

                FileInfo::fileWrite($logHandle, "Info: Check directory empty in app end\n");
                FileInfo::fileWrite($logHandle, "Info: Clone and remove file upgrade begin\n");

                return $this->installEnd($logHandle, $binFilePath);
            } else {
                $errorZipExtract = self::ERROR_ZIP_EXTRACT_FILE_UPGRADE;

                FileInfo::fileWrite($logHandle, $pclZip->errorInfo(true) . "\n");
                FileInfo::fileClose($logHandle);
            }

            return false;
        }

        public function installAdditionalNow($checkHasUpgradeLocal = false, &$errorZipExtract = null, &$errorUpgrade = null)
        {
            global $appUser;

            if ($appUser->isPositionAdminstrator() == false)
                return false;

            if ($checkHasUpgradeLocal && $this->checkHasUpgradeLocal() == false)
                return false;

            $logHandle          = FileInfo::fileOpen(AppUpdate::getPathFileUpgrade(self::LOG_FILENAME_ADDITIONAL), 'wa+');
            $additionalFilePath = AppUpdate::getPathFileUpgrade(AppUpdate::VERSION_ADDITIONAL_FILENAME);
            $pclZip             = new PclZip($additionalFilePath);
            $appPath            = env('app.path.root');

            FileInfo::fileWrite($logHandle, "Info: Open file additional zip begin\n");

            if ($pclZip === false) {
                $errorZipExtract = self::ERROR_ZIP_NOT_OPEN_FILE_ADDITIONAL;

                FileInfo::fileWrite($logHandle, $pclZip->errorInfo(true) . "\n");
                FileInfo::fileClose($logHandle);

                return false;
            }

            FileInfo::fileWrite($logHandle, "Info: Open file additional zip end\n");

            if ($pclZip->extract(PCLZIP_OPT_PATH, FileInfo::filterPaths($appPath), PCLZIP_CB_PRE_EXTRACT, 'installAdditionalCallbackExtractZip') != false) {
                FileInfo::fileWrite($logHandle, "Info: Extract additional update success\n");
                return $this->installEnd($logHandle, $additionalFilePath);
            } else {
                $errorZipExtract = self::ERROR_ZIP_EXTRACT_FILE_UPGRADE;

                FileInfo::fileWrite($logHandle, $pclZip->errorInfo(true) . "\n");
                FileInfo::fileClose($logHandle);
            }

            return false;
        }

        private function installEnd($logHandle, $fileUpdatePath)
        {
            $updateScriptPath   = AppUpdate::getPathFileUpgrade(AppUpdate::VERSION_UPDATE_SCRIPT_FILENAME);
            $changelogFilePath  = AppUpdate::getPathFileUpgrade(AppUpdate::VERSION_CHANGELOG_FILENAME);
            $readmeFilePath     = AppUpdate::getPathFileUpgrade(AppUpdate::VERSION_README_FILENAME);
            $resourceDirectory  = env('app.path.resource');

            if (FileInfo::isTypeFile($updateScriptPath)) {
                FileInfo::fileWrite($logHandle, "Info: Run update script begin\n");

                require_once($updateScriptPath);

                FileInfo::unlink($updateScriptPath);
                FileInfo::fileWrite($logHandle, "Info: Run update script end\n");
            }

            FileInfo::fileWrite($logHandle, "Info: Clone and remove file update begin\n");

            if (FileInfo::isTypeFile($fileUpdatePath) && FileInfo::unlink($fileUpdatePath))
                FileInfo::fileWrite($logHandle, "Success: Remove file: " . $fileUpdatePath . "\n");
            else
                FileInfo::fileWrite($logHandle, "Failed: Remove file: " . $fileUpdatePath . "\n");

            if (FileInfo::isTypeFile($changelogFilePath)) {
                FileInfo::copySystem($changelogFilePath, AppUpdate::getPathFileUpgrade(AppUpdate::VERSION_CHANGELOG_FILENAME, $resourceDirectory));

                if (FileInfo::unlink($changelogFilePath))
                    FileInfo::fileWrite($logHandle, "Success: Remove file: " . $changelogFilePath . "\n");
                else
                    FileInfo::fileWrite($logHandle, "Failed: Remove file: " . $changelogFilePath . "\n");
            }

            if (FileInfo::isTypeFile($readmeFilePath)) {
                FileInfo::copySystem($readmeFilePath, AppUpdate::getPathFileUpgrade(AppUpdate::VERSION_README_FILENAME, $resourceDirectory));

                if (FileInfo::unlink($readmeFilePath))
                    FileInfo::fileWrite($logHandle, "Success: Remove file: " . $readmeFilePath . "\n");
                else
                    FileInfo::fileWrite($logHandle, "Failed: Remove file: " . $readmeFilePath . "\n");
            }

            if (FileInfo::isTypeFile(env('resource.config.upgrade')))
                FileInfo::unlink(env('resource.config.upgrade'));

            FileInfo::fileWrite($logHandle, "Info: Clone and remove file update end\n");
            FileInfo::fileWrite($logHandle, "Info: Update about update begin\n");

            $this->appAboutConfig->setSystem(AppAboutConfig::ARRAY_KEY_VERSION,    $this->appUpgradeConfig->get(AppUpdate::ARRAY_DATA_KEY_VERSION));
            $this->appAboutConfig->setSystem(AppAboutConfig::ARRAY_KEY_CHECK_AT,   $this->appAboutConfig->get(AppAboutConfig::ARRAY_KEY_CHECK_AT));
            $this->appAboutConfig->setSystem(AppAboutConfig::ARRAY_KEY_BUILD_AT,   $this->appUpgradeConfig->get(AppUpdate::ARRAY_DATA_KEY_BUILD_LAST));
            $this->appAboutConfig->setSystem(AppAboutConfig::ARRAY_KEY_UPGRADE_AT, time());
            $this->appAboutConfig->write();

            FileInfo::fileWrite($logHandle, "Info: Update about update end\n");

            if ($this->typeBinInstall === self::TYPE_BIN_INSTALL_UPGRADE)
                FileInfo::fileWrite($logHandle, "Info: Install upgrade success");
            else
                FileInfo::fileWrite($logHandle, "Info: Install additional update success");

            FileInfo::fileClose($logHandle);
            AppUpdate::cleanUpgrade();
            AppClean::scanAutoClean(true);

            return true;
        }

        public function getAppAboutConfig()
        {
            return $this->appAboutConfig;
        }

        public function getAppUpgradeConfig()
        {
            return $this->appUpgradeConfig;
        }

        public function getVersionUpgrade()
        {
            if ($this->appUpgradeConfig->hasEntryConfigArrayAny() == false)
                return null;

            return $this->appUpgradeConfig->get(AppUpdate::ARRAY_DATA_KEY_VERSION);
        }

        public function getTypeBinInstall()
        {
            return $this->typeBinInstall;
        }

    }

?>