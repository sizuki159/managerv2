<?php

    namespace Librarys\App;

    if (defined('LOADED') == false)
        exit;

    use Librarys\File\FileInfo;

    final class AppFileUnzip
    {

        private $isSession;
        private $directory;
        private $name;
        private $path;
        private $existsFunc;

        const SESSION_KEY           = 'UNZIP_INFOS';
        const ARRAY_KEY_DIRECTORY   = 'directory';
        const ARRAY_KEY_NAME        = 'name';
        const ARRAY_KEY_PATH        = 'path';
        const ARRAY_KEY_EXISTS_FUNC = 'exists_func';

        public function __construct()
        {
            if (isset($_SESSION[self::SESSION_KEY])) {
                $array = $_SESSION[self::SESSION_KEY];

                if (isset($array[self::ARRAY_KEY_DIRECTORY]) && isset($array[self::ARRAY_KEY_NAME])) {
                    $this->setDirectory($array[self::ARRAY_KEY_DIRECTORY]);
                    $this->setName($array[self::ARRAY_KEY_NAME]);
                }

                if (isset($array[self::ARRAY_KEY_PATH]))
                    $this->setPath($array[self::ARRAY_KEY_PATH]);

                if (isset($array[self::ARRAY_KEY_EXISTS_FUNC]))
                    $this->setExistsFunc($array[self::ARRAY_KEY_EXISTS_FUNC]);
                else
                    $this->setExistsFunc(1);

                $this->isSession = true;
            } else {
                $this->isSession = false;
            }
        }

        public function setSession($directory, $name, $existsFunc)
        {
            $appFileCopy = new AppFileCopy();

            if ($appFileCopy->isSession())
                $appFileCopy->clearSession();

            $this->setDirectory($directory);
            $this->setName($name);
            $this->setPath(FileInfo::filterPaths($directory));
            $this->setExistsFunc($existsFunc);
            $this->flushSession();
        }

        public function flushSession()
        {
            $_SESSION[self::SESSION_KEY] = [
                self::ARRAY_KEY_DIRECTORY   => $this->directory,
                self::ARRAY_KEY_NAME        => $this->name,
                self::ARRAY_KEY_PATH        => $this->path,
                self::ARRAY_KEY_EXISTS_FUNC => $this->existsFunc
            ];
        }

        public function clearSession()
        {
            unset($_SESSION[self::SESSION_KEY]);
        }

        public function setDirectory($directory)
        {
            $this->directory = $directory;
        }

        public function getDirectory()
        {
            return $this->directory;
        }

        public function setName($name)
        {
            $this->name = $name;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setPath($path)
        {
            $this->path = $path;
        }

        public function getPath()
        {
            return $this->path;
        }

        public function setExistsFunc($existsFunc)
        {
            $this->existsFunc = $existsFunc;
        }

        public function getExistsFunc()
        {
            return $this->existsFunc;
        }

        public function isSession()
        {
            return $this->isSession;
        }

    }

?>