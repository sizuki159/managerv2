<?php

    namespace Librarys\App;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Boot;
    use Librarys\File\FileInfo;

    final class AppChecker
    {

        private $boot;

        private $isAccept;
        private $isInstallDirectory;
        private $isDirectoryPermissionExecute;
        private $isConfigValidate;

        private $applicationDirectory;
        private $applicationPath;
        private $applicationParentPath;

        private $directoryPath;
        private $directoryWrite;
        private $directoryFound;

        private $configFile;
        private $configFound;
        private $configReadable;
        private $configError;
        private $configUpdate;

        public function __construct(Boot $boot)
        {
            $this->boot = $boot;

            $this->isAccept                     = true;
            $this->isInstallDirectory           = true;
            $this->isDirectoryPermissionExecute = true;
            $this->isConfigValidate             = true;
            $this->isUserValidate               = true;
        }

        public function execute()
        {
            $this->installDirectory();
            $this->directoryPermissionExecute();
            $this->configValidate();

            return $this;
        }

        /**
         * Check directory install application is not root
         */
        public function installDirectory()
        {
            if ($this->isAccept == false)
                return;

            $current = null;

            if (defined('ROOT'))
                $current = new FileInfo(realpath(ROOT));
            else
                $current  = new FileInfo(realpath('.'));

            $path     = $current->getFilePath();
            $pathRoot = FileInfo::filterPaths(env('SERVER.DOCUMENT_ROOT'));
            $isRoot   = stripos($pathRoot, $path) === 0;

            if ($isRoot) {
                $this->applicationDirectory  = substr($path, strlen($pathRoot) + 1);
                $this->applicationPath       = $this->applicationDirectory;
                $this->applicationParentPath = dirname($path);
            } else {
                $this->applicationDirectory  = $path == SP ? $path : substr($path, strrpos($path, SP) + 1);
                $this->applicationPath       = $path;
                $this->applicationParentPath = dirname($path);
            }

            $this->applicationDirectory  = FileInfo::filterPaths($this->applicationDirectory);
            $this->applicationPath       = FileInfo::filterPaths($this->applicationPath);
            $this->applicationParentPath = FileInfo::filterPaths($this->applicationParentPath);

            $this->isInstallDirectory = $isRoot == false;
            $this->isAccept           = $this->isInstallDirectory;

            env('application.directory',   $this->applicationDirectory);
            env('application.path',        $this->applicationPath);
            env('application.parent_path', $this->applicationParentPath);
        }

        /**
         * Check permission write of directory install application
         */
        public function directoryPermissionExecute()
        {
            if ($this->isAccept == false)
                return;

            $root = FileInfo::filterPaths(env('SERVER.DOCUMENT_ROOT'));
            $path = FileInfo::filterPaths($this->applicationPath);

            if (strpos($path, SP) !== false) {
                $split    = explode(SP, $path);
                $count    = count($split);
                $current  = null;
                $validate = false;
                $found    = true;
                $write    = true;

                for ($i = 0; $i < $count; ++$i) {
                    if ($i > 0)
                        $current .= SP;

                    $current .= $split[$i];

                    if ($validate == false && strcmp($root, $current) === 0) {
                        $validate = true;
                    } else if ($validate) {
                        if (FileInfo::isTypeDirectory($current)) {
                            if (FileInfo::isReadable($current)) {
                                $validate = true;
                                $found    = true;
                                $write    = true;
                            } else {
                                $validate = false;
                                $found    = true;
                                $write    = false;

                                break;
                            }
                        } else {
                            $validate = false;
                            $found    = false;
                            $write    = false;

                            break;
                        }
                    }
                }

                $this->directoryPath  = $current;
                $this->directoryFound = $found;
                $this->directoryWrite = $write;

                $this->isDirectoryPermissionExecute = $validate;
                $this->isAccept                     = $validate;
            }
        }

        /**
         * Check config application
         * Option array:
         *                  'type' => 'string', 'int'
         *                  'default' => Value default of set 'condition' => 'optional'
         *                  'null' => true or false
         *                  'condition' => 'optional' or default is 'force'
         */
        public function configValidate()
        {
            if ($this->isAccept == false)
                return;

            $this->isConfigValidate = true;
            $this->isAccept         = $this->isConfigValidate;
        }

        /**
         * Return result check is success
         */
        public function isAccept()
        {
            return $this->isAccept;
        }

        /**
         * Return result check directory install application
         */
        public function isInstallDirectory()
        {
            return $this->isInstallDirectory;
        }

        /**
         * Return result check permission write of directory install application
         */
        public function isDirectoryPermissionExecute()
        {
            return $this->isDirectoryPermissionExecute;
        }

        /**
         * Return result check config application
         */
        public function isConfigValidate()
        {
            return $this->isConfigValidate;
        }

        /**
         * Return directory name application
         */
        public function getApplicationDirectory()
        {
            return $this->applicationDirectory;
        }

        /**
         * Return path application
         */
        public function getApplicationPath()
        {
            return $this->applicationPath;
        }

        /**
         * Return parent path application
         */
        public function getApplicationParentPath()
        {
            return $this->applicationParentPath;
        }

        /**
         * Return path check permission write
         */
        public function getDirectoryPath()
        {
            return $this->directoryPath;
        }

        /**
         * Return result check found directory
         */
        public function isDirectoryFound()
        {
            return $this->directoryFound;
        }

        /**
         * Return result check write directory
         */
        public function isDirectoryWrite()
        {
            return $this->directoryWrite;
        }

        /**
         * Get path config
         */
        public function getConfigFile()
        {
            return $this->configFile;
        }

        /**
         * Return result check config found
         */
        public function isConfigFound()
        {
            return $this->configFound;
        }

        /**
         * Return result check config readable
         */
        public function isConfigReadable()
        {
            return $this->configReadable;
        }

        /**
         * Return result check config error
         */
        public function isConfigError()
        {
            return $this->configError;
        }

        /**
         * Return result check config update
         */
        public function isConfigUpdate()
        {
            return $this->configUpdate;
        }

        /**
         * Read config file
         */
        public static function readConfig($file, &$configs, &$configFound = true, &$configReadable = true, &$configError = false, &$configUpdate = false, &$configsWrite = null)
        {

            $configFound    = false;
            $configReadable = false;
            $configError = true;
            $configUpdate = false;

            if (is_array($configs) == false)
                return false;

            if (FileInfo::isTypeFile($file)) {
                $configFound = true;

                if (FileInfo::isReadable($file)) {
                    $content = @file_get_contents($file);
                    $matches = null;

                    if ($content !== false && preg_match_all('#\$([a-zA-Z0-9_]+)\s*\=\s*"([a-zA-Z0-9\-_\.]*)";#si', $content, $matches)) {
                        $configReadable = true;
                        $configError = false;

                        foreach ($configs AS $key => $value) {
                            $search = 0;
                            $optional = isset($value['condition']) && $value['condition'] === 'optional';

                            if (($search = array_search(trim($key), $matches[1])) !== false || $optional) {

                                if ($optional == false && $value['null'] == false && $matches[2][$search] !== '0' && empty($matches[2][$search])) {
                                    $configError = true;
                                } else {
                                    $set = null;

                                    if ($search !== false && is_array($matches))
                                        $set = $matches[2][$search];
                                    else if (isset($value['default']))
                                        $set = $value['default'];

                                    if (isset($value['type'])) {
                                        $type = $value['type'];

                                        if ($type === 'int' || $type === 'intval')
                                            $set = intval($set);
                                        else
                                            $set = strval($set);
                                    } else {
                                        $set = strval($set);
                                    }

                                    if ($configsWrite != null)
                                        $configsWrite[$key] = $set;
                                    else
                                        $configs[$key] = $set;
                                }
                            } else {
                                $configUpdate = true;
                            }

                            if ($configError || $configUpdate)
                                break;
                        }

                        return ($configError || $configUpdate) != true;
                    }
                }
            }

            return false;
        }

    }

?>