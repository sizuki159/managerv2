<?php

    namespace Librarys\App;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Boot;
    use Librarys\File\FileInfo;

    final class AppDirectory
    {

        private $boot;
        private $directory;
        private $name;
        private $aliasName;
        private $directoryEncode;
        private $nameEncode;
        private $aliasNameEncode;
        private $page;
        private $permissionDeny;
        private $accessParentPath;

        const PARAMETER_DIRECTORY_URL  = 'directory';
        const PARAMETER_NAME_URL       = 'name';
        const PARAMETER_ALIAS_NAME_URL = 'alias_name';
        const PARAMETER_PAGE_URL       = 'pager';

        public function __construct(Boot $boot)
        {
            $this->boot = $boot;
        }

        public function execute()
        {
            if (isset($_GET[self::PARAMETER_DIRECTORY_URL]) && empty($_GET[self::PARAMETER_DIRECTORY_URL]) == false)
                $this->directory = self::rawDecode($_GET[self::PARAMETER_DIRECTORY_URL]);
            else
                $this->directory = env('SERVER.DOCUMENT_ROOT');

            if ($this->directory != null) {
                $this->directory       = FileInfo::filterPaths($this->directory);
                $this->directoryEncode = self::rawEncode($this->directory);
            }

            if (isset($_GET[self::PARAMETER_NAME_URL]) && empty($_GET[self::PARAMETER_NAME_URL]) == false) {
                $this->name       = self::rawDecode($_GET[self::PARAMETER_NAME_URL]);
                $this->nameEncode = self::rawEncode($this->name);
            } else {
                $this->name = null;
            }

            if (isset($_GET[self::PARAMETER_ALIAS_NAME_URL]) && empty($_GET[self::PARAMETER_ALIAS_NAME_URL]) == false) {
                $this->aliasName       = self::rawDecode($_GET[self::PARAMETER_ALIAS_NAME_URL]);
                $this->aliasNameEncode = self::rawEncode($this->aliasName);
            } else {
                $this->aliasName = null;
            }

            if (isset($_GET[self::PARAMETER_PAGE_URL]) && empty($_GET[self::PARAMETER_PAGE_URL]) == false)
                $this->page = intval($_GET[self::PARAMETER_PAGE_URL]);
            else
                $this->page = 1;

            if ($this->page <= 0)
                $this->page = 1;

            if ($this->directory != '.' && $this->directory != '..') {
                $isPermissionDenyDirectory     = FileInfo::permissionDenyPath(FileInfo::filterPaths($this->directory));
                $isPermissionDenyDirectoryName = false;

                if ($this->name != null)
                    $isPermissionDenyDirectoryName = FileInfo::permissionDenyPath(FileInfo::filterPaths($this->directory . SP . $this->name));

                if ($isPermissionDenyDirectory == false && $isPermissionDenyDirectoryName == false)
                    $this->permissionDeny = false;
                else
                    $this->permissionDeny = true;
            }

            if ($this->directory != null && $this->permissionDeny == false)
                $this->accessParentPath = strtolower($this->directory) == strtolower(env('application.parent_path'));
            else
                $this->accessParentPath = strtolower(env('application.parent_path')) == strtolower(env('SERVER.DOCUMENT_ROOT'));
        }

        public static function rawEncode($url)
        {
            return rawurlencode($url);
        }

        public static function rawEncodes($array)
        {
            if (is_array($array) == false)
                return $array;

            foreach ($array AS $value) {
                if (is_array($value))
                    self::rawEncodes($value);
                else
                    self::rawEncode($value);
            }
        }

        public static function rawDecode($url)
        {
            return str_replace('&#34;', '"',
                        str_replace('&#39;', '\'',
                            rawurldecode($url)
                        )
                    );
        }

        public static function rawDecodes($array)
        {
            if (is_array($array) == false)
                return $array;

            foreach ($array AS &$value) {
                if (is_array($value))
                    $value = self::rawDecodes($value);
                else
                    $value = self::rawDecode($value);
            }

            return $array;
        }

        public function setDirectory($directory)
        {
            $_GET[self::PARAMETER_DIRECTORY_URL] = $directory;

            $this->execute();
        }

        public function getDirectory()
        {
            return $this->directory;
        }

        public function getDirectoryEncode()
        {
            return $this->directoryEncode;
        }

        public function getName()
        {
            return $this->name;
        }

        public function getNameEncode()
        {
            return $this->nameEncode;
        }

        public function getAliasName()
        {
            return $this->aliasName;
        }

        public function getAliasNameEncode()
        {
            return $this->aliasNameEncode;
        }

        public function getDirectoryAndName()
        {
            if ($this->name != null)
                return $this->directory . SP . $this->name;

            return $this->directory;
        }

        public function getPage()
        {
            return $this->page;
        }

        public function isPermissionDenyPath()
        {
            return $this->permissionDeny;
        }

        public function isAccessParentPath()
        {
            return $this->accessParentPath;
        }

        public function isDirectoryExists()
        {
            if ($this->directory == null || empty($this->directory))
                return false;

            return FileInfo::isTypeDirectory($this->directory);
        }

        public function isDirectorySeparatorNameExists()
        {
            if ($this->isDirectoryExists() == false)
                return false;

            if ($this->name == null || empty($this->name))
                return false;

            return FileInfo::isTypeDirectory(FileInfo::filterPaths($this->directory . SP . $this->name));
        }

        public function isFileSeparatorNameExists()
        {
            if ($this->isDirectoryExists() == false)
                return false;

            if ($this->name == null || empty($this->name))
                return false;

            return FileInfo::isTypeFile(FileInfo::filterPaths($this->directory . SP . $this->name));
        }

        public function isFileExistsDirectory()
        {
            if ($this->directory == null || empty($this->directory))
                return false;

            return FileInfo::fileExists($this->directory);
        }

        public function isFileExistsDirectorySeparatorName()
        {
            if ($this->isDirectoryExists() == false)
                return false;

            if ($this->name == null || empty($this->name))
                return false;

            return FileInfo::fileExists(FileInfo::filterPaths($this->directory . SP . $this->name));
        }

    }

?>
