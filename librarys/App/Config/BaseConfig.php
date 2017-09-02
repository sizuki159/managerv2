<?php

    namespace Librarys\App\Config;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Boot;
    use Librarys\App\AppUser;
    use Librarys\File\FileInfo;

    abstract class BaseConfig
    {

        protected $boot;

        protected $pathConfig;
        protected $pathConfigSystem;

        protected $fileConfigName;

        protected $configArray;
        protected $configSystemArray;

        protected $cacheArray;
        protected $envProtectedArray;

        protected $spacingWrite;

        public function __construct(Boot $boot, $pathConfigSystem, $fileConfigName = null)
        {
            $this->configArray       = array();
            $this->configSystemArray = array();
            $this->cacheArray        = array();

            $this->setPathConfigSystem($pathConfigSystem);
            $this->setFileConfigName($fileConfigName);
            $this->setSpacingWrite('    ');
        }

        public function getPathConfig()
        {
            return $this->pathConfig;
        }

        public function setPathConfigSystem($path)
        {
            if ($path == null)
                return false;
            else
               $this->pathConfigSystem = $path;

            return true;
        }

        public function getPathConfigSystem()
        {
            return $this->pathConfigSystem;
        }

        public function setFileConfigName($fileConfigName)
        {
            if ($fileConfigName == null)
                return false;
            else
                $this->fileConfigName = $fileConfigName;

            return true;
        }

        public function getFileConfigName()
        {
            return $this->fileConfigName;
        }

        public function getConfigArray()
        {
            return $this->configArray;
        }

        public function getConfigArraySystem()
        {
            return $this->configSystemArray;
        }

        public function hasEntryConfigArray()
        {
            return is_array($this->configArray) && count($this->configArray) > 0;
        }

        public function hasEntryConfigArraySystem()
        {
            return is_array($this->configSystemArray) && count($this->configSystemArray) > 0;
        }

        public function hasEntryConfigArrayAny()
        {
            return $this->hasEntryConfigArray() || $this->hasEntryConfigArraySystem();
        }

        public function execute($appUser = null, $idUser = null)
        {
            if (($appUser != null && $appUser->isLogin()) || $idUser !== null) {
                $directory = env('app.path.user');
                $isMkdir   = true;

                if ($appUser !== null)
                    $idUser = $appUser->getId();

                $this->pathConfig = FileInfo::filterPaths($directory . SP . $idUser);

                if (FileInfo::isTypeDirectory($directory) == false)
                    $isMkdir = FileInfo::mkdir($directory);

                if ($isMkdir) {
                    if (FileInfo::isTypeDirectory($this->pathConfig) == false)
                        $isMkdir = FileInfo::mkdir($this->pathConfig);

                    if ($isMkdir) {
                        $this->pathConfig = FileInfo::filterPaths($this->pathConfig . SP . $this->fileConfigName);

                        if (FileInfo::isTypeFile($this->pathConfig))
                            return $this->parse();
                    }
                }
            } else {
                $this->pathConfig = $this->pathConfigSystem;
            }

            $this->parse();
        }

        public function parse($parseSystem = false)
        {
            $path = $this->pathConfig;

            if ($parseSystem == false) {
                if (is_null($path) || FileInfo::isTypeFile($path) == false)
                    $path = $this->pathConfigSystem;

                if (FileInfo::isTypeFile($path) == false)
                    return;
            } else {
                $path = $this->pathConfigSystem;

                if (is_null($path) || FileInfo::isTypeFile($path) == false)
                    return;
            }

            if ($parseSystem)
                $this->configSystemArray = require_once($path);
            else
                $this->configArray = require_once($path);

            if (is_array($this->configSystemArray) == false)
                $this->configSystemArray = array();

            if (is_array($this->configArray) == false)
                $this->configArray = array();
        }

        private function splitNames($name, &$nameSplits, &$nameSplitsCount)
        {
            if ($name == null)
                return false;

            $nameSplits = array();

            if (strpos($name, '.') === false)
                $nameSplits[] = $name;
            else
                $nameSplits = explode('.', $name);

            $nameSplitsCount = count($nameSplits);

            return true;
        }

        public function set($name, $value, $isSystem = false)
        {
            if ($this->splitNames($name, $nameSplits, $nameSplitsCount) == false)
                return false;

            $configArray = null;

            if ($isSystem)
                $configArray = &$this->configSystemArray;
            else
                $configArray = &$this->configArray;

            for ($i = 0; $i < $nameSplitsCount; ++$i) {
                $nameEntry = $nameSplits[$i];

                if ($i >= $nameSplitsCount - 1) {
                    $configArray[$nameEntry] = $value;
                } else {
                    if (array_key_exists(trim($nameEntry), $configArray) == false)
                        $configArray[$nameEntry] = null;

                    $configArray = &$configArray[$nameEntry];
                }
            }

            if (array_key_exists($name, $this->cacheArray) == true)
                $this->receiverToCache($name);

            return true;
        }

        public function setSystem($name, $value)
        {
            return $this->set($name, $value, true);
        }

        public function get($name, $default = null, $recache = false)
        {
            if (array_key_exists($name, $this->cacheArray) && $recache == false)
                return $this->cacheArray[$name];

            return $this->receiverToCache($name, $default);
        }

        public function hasKey($name, $isSystem = false)
        {
            if ($this->splitNames($name, $nameSplits, $nameSplitsCount) == false)
                return false;

            if ($isSystem)
                $configArray = &$this->configSystemArray;
            else
                $configArray = &$this->configArray;

            for ($i = 0; $i < $nameSplitsCount; ++$i) {
                $nameEntry = $nameSplits[$i];

                if ($i >= $nameSplitsCount - 1) {
                    if (is_array($configArray) == false || array_key_exists(trim($nameEntry), $configArray) == false)
                        return false;

                    return true;
                } else {
                    if (array_key_exists(trim($nameEntry), $configArray) == false)
                        return false;

                    $configArray = &$configArray[$nameEntry];
                }
            }

            return true;
        }

        public function remove($name, $isSystem = false)
        {
            if ($this->splitNames($name, $nameSplits, $nameSplitsCount) == false)
                return false;

            if ($isSystem)
                $configArray = &$this->configSystemArray;
            else
                $configArray = &$this->configArray;

            for ($i = 0; $i < $nameSplitsCount; ++$i) {
                $nameEntry = $nameSplits[$i];

                if ($i === $nameSplitsCount - 1) {
                    unset($configArray[$nameEntry]);
                } else {
                    if (isset($configArray[$nameEntry]) == false)
                        $configArray[$nameEntry] = null;

                    $configArray = &$configArray[$nameEntry];
                }
            }

            if (array_key_exists($name, $this->cacheArray) == true)
                unset($this->cacheArray[$name]);

            return true;
        }

        public function removeSystem($name)
        {
            return $this->remove($name, true);
        }

        private function receiverToCache($name, $default = null)
        {
            return ($this->cacheArray[$name] = urlSeparatorMatches($this->receiver($name, $default)));
        }

        private function receiver($key, $default = null, $array = null)
        {
            if (is_string($key) && empty($key) == false) {
                // Config of user
                if ($array == null)
                    $array = $this->configArray;

                if (is_array($array) == false)
                    return null;

                $keys  = explode('.', $key);

                if (is_array($keys) == false)
                    $keys = array($key);

                foreach ($keys AS $entry) {
                    $entry = trim($entry);

                    if (array_key_exists($entry, $array) == false) {
                        $array = null;
                        break;
                    } else {
                        $array = $array[$entry];
                    }
                }

                if ($array === null) {
                    // Config of system
                    $array = $this->configSystemArray;

                    if (is_array($array) == false)
                        return null;

                    foreach ($keys AS $entry) {
                        $entry = trim($entry);

                        if (array_key_exists($entry, $array) == false)
                            return $default;

                        $array = $array[$entry];
                    }
                }

                return $this->envMatchesString($array);
            }

            return $default;
        }

        public function envMatchesString($str)
        {
            if (is_array($str) || preg_match('/\$\{(.+?)\}/si', $str, $matches) == false)
                return $str;

            return preg_replace_callback('/\$\{(.+?)\}/si', function($matches) {
                $result = null;

                if (isset($GLOBALS[$matches[1]]))
                    $result = $GLOBALS[$matches[1]];
                else if (defined($matches[1]))
                    $result = constant($matches[1]);
                else
                    $result = env(trim($matches[1]));

                if (is_array($result))
                    return 'Array';
                else if (is_object($result))
                    return 'Object';
                else if (is_resource($result))
                    return 'Resource';

                return $result;
            }, $str);
        }

        public function requireEnvProtected($path)
        {
            if (FileInfo::isTypeFile($path) == false)
                return false;

            $envProtecteds = require($path);

            if (is_array($envProtecteds) == false)
                return false;

            foreach ($envProtecteds AS $envKey => $envFlag) {
                if ($envFlag == false)
                    $this->addEnvProtected($envKey, true);
            }
        }

        public function addEnvProtected($name, $disabledCreateToUser = true)
        {
            if (is_array($this->envProtectedArray) == false)
                $this->envProtectedArray = array();

            $this->envProtectedArray[$name] = $disabledCreateToUser;
        }

        public function isEnvEnabled($name)
        {
            return $this->isEnvDisabled($name) == false;
        }

        public function isEnvDisabled($name)
        {
            if (is_array($this->envProtectedArray) && array_key_exists($name, $this->envProtectedArray))
                return $this->envProtectedArray[$name] == true;

            return false;
        }

        public function setSpacingWrite($spacing)
        {
            $this->spacingWrite = $spacing;
        }

        public function getSpacingWrite()
        {
            return $this->spacingWrite;
        }

        public abstract function callbackPreWrite();

        public abstract function takeConfigArrayWrite();

        public abstract function takePathConfigWrite();

        public function write($isWriteSystem = false)
        {
            if ($this->callbackPreWrite() == false && $isWriteSystem == false)
                return false;

            if ($this->takePathConfigWrite() == null && $isWriteSystem == false)
                return false;

            $config = null;
            $path   = null;

            if ($isWriteSystem) {
                $config = $this->getConfigArraySystem();
                $path   = $this->getPathConfigSystem();
            } else {
                $config = $this->takeConfigArrayWrite();
                $path   = $this->takePathConfigWrite();
            }

            if (is_array($config)) {
                $this->sortArrayWrite($config);

                $buffer  = '<?php' . "\n\n";
                $buffer .= $this->spacingWrite . 'if (defined(\'LOADED\') == false)' . "\n";
                $buffer .= $this->spacingWrite . $this->spacingWrite . 'exit;' . "\n\n";
                $buffer .= $this->spacingWrite . 'return [' . "\n";

                foreach ($config AS $key => $entry)
                    $this->writeBufferEntry($isWriteSystem, $buffer, $key, $entry, $this->spacingWrite);

                $buffer .= $this->spacingWrite . '];' . "\n\n";
                $buffer .= '?>';

                if (FileInfo::fileWriteContents($path, $buffer) !== false)
                    return true;
            }

            return false;
        }

        private function writeBufferEntry($isWriteSystem, &$buffer, $key, &$entry, $spacing = null, $envKey = null)
        {
            $spacing .= '    ';

            if ($envKey == null)
                $envKey = $key;
            else
                $envKey .= '.' . $key;

            if ($isWriteSystem == false && $this->isEnvDisabled($envKey))
                return;

            if (is_array($entry)) {
                $this->sortArrayWrite($entry);
                $buffer .= $spacing . '\'' . $key . '\' => [' . "\n";

                foreach ($entry AS $keyWith => $entryWith)
                    $this->writeBufferEntry($isWriteSystem, $buffer, $keyWith, $entryWith, $spacing, $envKey);

                $buffer .= $spacing . '],' . "\n\n";
            } else {
                $type = null;

                if ($entry != null)
                    $type = getType($entry);

                if ($type == null && is_numeric($entry)) {
                    if (is_int($entry))
                        $type = 'integer';
                    else if (is_float($entry))
                        $type = 'float';
                    else if (is_double($entry))
                        $type = 'double';
                }

                $buffer .= $spacing . '\'' . $key . '\' => ';

                if ($type == 'string') {
                    $buffer .= '\'' . $entry . '\'';
                } else if ($type == 'integer') {
                    $buffer .= intval($entry);
                } else if ($type == 'float') {
                    $buffer .= floatval($entry);
                } else if ($type == 'double') {
                    $buffer .= doubleval($entry);
                } else if ($type == 'boolean' || $entry === false) {
                    if (boolval($entry) == true)
                        $buffer .= 'true';
                    else
                        $buffer .= 'false';
                } else if ($type === null) {
                    $buffer .= '\'\'';
                } else {
                    $buffer .= $entry;
                }

                $buffer .= ",\n";
            }
        }

        protected function sortArrayWrite($array)
        {
            if (is_array($array))
                krsort($array);
        }

    }

?>