<?php

	namespace Librarys\File;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Zip\PclZip;

	final class FileInfo
	{

		private $filePath;
		private $fileExt;
		private $fileMime;

        const FILENAME_VALIDATE = '\\/:*?"<>|';

		public function __construct($filePath, $receiverMime = true)
		{
			$this->setFilePath($filePath, $receiverMime);
		}

		public function isFile()
		{
			return self::isTypeFile($this->filePath);
		}

		public function isDirectory()
		{
			return self::isTypeDirectory($this->filePath);
		}

		public function readFile()
		{
			return @readfile($this->filePath);
		}

		public function setFilePath($filePath, $receiverMime = true)
		{
			$this->filePath = separator($filePath, SP);
			$this->fileExt  = self::extFile($this->getFileName());

            if (empty($this->fileExt))
                $this->fileExt = null;

            if ($receiverMime)
    			$this->fileMime = FileMime::get($this);
		}

		public function getFilePath()
		{
			return $this->filePath;
		}

		public function getFileName()
		{
			return basename($this->filePath);
		}

		public function getFileExt()
		{
			return $this->fileExt;
		}

		public function getFileMime()
		{
            if ($this->fileMime == null)
                $this->fileMime = FileMime::get($this);

			return $this->fileMime;
		}

        public function getFileSize()
        {
            return self::fileSize($this->filePath);
        }

        public static function isNameValidate($name)
        {
            return strpbrk($name, self::FILENAME_VALIDATE) == false;
        }

        public static function fileNameFix($name)
        {
            if ($name == null || self::isNameValidate($name))
                return $name;

            $chars = str_split($name, 1);

            if (is_array($chars) == false)
                return $name;

            $buffer = null;

            foreach ($chars AS $char) {
                if (self::isNameValidate($char))
                    $buffer .= $char;
                else
                    $buffer .= '_';
            }

            return $buffer;
        }

		public static function extFile($file)
		{
			if ($file instanceof FileInfo)
				$file = $file->getFileName();
			else
				$file = basename($file);

			$lastIndex = strrpos($file, '.');

			if ($lastIndex !== false)
				return strtolower(substr($file, $lastIndex + 1));

			return null;
		}

        public static function chmod($path, $chmod = null)
        {
            if ($chmod == null) {
                $perms = @fileperms($path);

                if ($perms !== false) {
                    $perms = decoct($perms);
                    $perms = substr($perms, strlen($perms) == 5 ? 2 : 3, 3);
                } else {
                    $perms = "000";
                }

                return $perms;
            } else if (@chmod($path, $chmod)) {
                return true;
            }

            return false;
        }

        public static function mkdir($path, $superParentCreate = false)
        {
            if ($superParentCreate == false)
                return @mkdir($path);

            $rootPath        = env('server.document_root');
            $pathAbsolute    = $path;
            $isRootPathFirst = false;
            $separator       = SP;

            if (stripos($path, $rootPath) === 0) {
                $pathAbsolute    = substr($path, strlen($rootPath));
                $isRootPathFirst = true;

                if (strpos($pathAbsolute, $separator) === 0)
                    $pathAbsolute = substr($pathAbsolute, 1);
            }

            $pathSplits  = explode($separator, $pathAbsolute);
            $countSplits = count($pathSplits);

            if (is_array($pathSplits) == false && $countSplits > 0)
                return @mkdir($path);

            $pathBuffer         = null;
            $isFirstIndexBuffer = false;

            if ($separator === '/' && $isRootPathFirst == false)
                $pathBuffer = $separator;
            else if ($isRootPathFirst)
                $pathBuffer = $rootPath;

            for ($i = 0; $i < $countSplits; ++$i) {
                $entry = $pathSplits[$i];

                if (empty($entry) == false) {
                    if ($isFirstIndexBuffer == false) {
                        if ($isRootPathFirst == false && $separator === '/')
                            $pathBuffer .= $entry;
                        else if ($isRootPathFirst)
                            $pathBuffer .= $separator . $entry;
                        else
                            $pathBuffer .= $entry;

                        $isFirstIndexBuffer = true;
                    } else {
                        $pathBuffer = self::filterPaths($pathBuffer . $separator . $entry);
                    }

                    if (self::fileExists($pathBuffer) == false && @mkdir($pathBuffer) == false)
                        return false;
                }
            }

            return true;
        }

        public static function copySystem($old, $new)
        {
            return @copy($old, $new);
        }

        /**
         * @param $old = Array list path copy | Path directory, file copy
         * @param $new = Path directory parent copy of $old = array list path | Path directory, file copy to
         * @param $parent = Path directory parent copy to of $old = array list path | Not set of $old is not array list path
         * @param $move = Is delete source of copy success
         * @param $isHasFileAppPermission = Result check has file of app in path
         * @param $callbackIsFileExists = Callback if file is exists function($directory, $filename, $isDirectory) {}
         * @return boolean
         */
        public static function copy($old, $new, $parent = true, $move = false, & $isHasFileAppPermission = false, & $callbackIsFileExists = null)
        {
            if ($callbackIsFileExists == null) {
                $callbackIsFileExists = function($directory, $filename, $isDirectory) {
                    return $directory . SP . $filename;
                };
            }

            if (is_array($old)) {
                foreach ($old AS $entry) {
                    $path = $new . SP . $entry;

                    if (self::permissionDenyPath($path)) {
                        $isHasFileAppPermission = true;
                    } else if (self::isTypeFile($path)) {
                        $file = $parent . SP . $entry;

                        if (id_file($file))
                            $file = $callbackIsFileExists($parent, $entry, false);

                        // If file is null skip file
                        if ($file == null)
                            return true;

                        if (self::copySystem($path, $file) == false)
                            return false;

                        if ($move)
                            self::unlink($path);
                    } else if (self::isTypeDirectory($path)) {
                        $file = $parent . SP . $entry;

                        if (self::isTypeDirectory($file))
                            $file = $callbackIsFileExists($parent, $entry, true);

                        // If file is null skip file
                        if ($file == null)
                            return true;

                        if (self::copy($path, $file, $move, $isHasFileAppPermission) == false)
                            return false;
                    } else {
                        return false;
                    }
                }

                return true;
            } else if (self::isTypeFile($old)) {
                if (self::permissionDenyPath($old) || self::permissionDenyPath($new)) {
                    $isHasFileAppPermission = true;
                } else {
                    if (self::isTypeFile($new)) {
                        $separatorLastIndex = strrpos($new, SP);

                        if ($separatorLastIndex === false)
                            return false;

                        $directory = substr($new, 0, $separatorLastIndex);
                        $filename  = substr($new, $separatorLastIndex + 1);

                        $new = $callbackIsFileExists($directory, $filename, false);
                    }

                    if ($new == null)
                        return true;

                    if (self::copySystem($old, $new) == false)
                        return false;

                    if ($move)
                        self::unlink($old);
                }

                return true;
            } else if (self::isTypeDirectory($old)) {
                if (self::permissionDenyPath($old) || self::permissionDenyPath($new)) {
                    $isHasFileAppPermission = true;
                } else {
                    $handle = self::scanDirectory($old);

                    if ($handle !== false) {
                        if (($parent && $old != SP) || $parent == false) {
                            if (self::isTypeFile($new))
                                return false;

                            if (self::isTypeDirectory($new)) {
                                $separatorLastIndex = strrpos($new, SP);

                                if ($separatorLastIndex === false)
                                    return false;

                                $directory = substr($new, 0, $separatorLastIndex);
                                $filename  = substr($new, $separatorLastIndex + 1);
                                $new       = $callbackIsFileExists($directory, $filename, true);
                            }

                            if ($new == null)
                                return true;

                            if (self::isTypeDirectory($new) == false && self::mkdir($new) == false)
                                return false;
                        }

                        foreach ($handle AS $entry) {
                            if ($entry != '.' && $entry != '..') {
                                $source = $old . SP . $entry;
                                $dest   = $new . SP . $entry;

                                if (self::isTypeFile($source)) {
                                    if (self::isTypeFile($dest))
                                        $dest = $callbackIsFileExists($new, $entry, false);

                                    if ($dest == null)
                                        return true;

                                    if (self::copySystem($source, $dest) == false)
                                        return false;

                                    if ($move)
                                        self::unlink($source);
                                } else if (self::isTypeDirectory($source)) {
                                     if (self::isTypeDirectory($dest))
                                         $dest = $callbackIsFileExists($new, $entry, true);

                                    if ($dest == null)
                                        return true;

                                    if (self::copy($source, $dest, false, $move, $isHasFileAppPermission, $callbackIsFileExists) == false)
                                        return false;
                                } else {
                                    return false;
                                }
                            }
                        }

                        if ($move)
                            return self::rrmdir($old, null, $isHasFileAppPermission);
                        else
                            return true;
                    }
                }

                return true;
            }

            return false;
        }

        public static function copyFile($old, $new)
        {
            if (self::isTypeDirectory($old))
                return false;

            if (self::fileExists($new))
                return false;

            return @copy($old, $new);
        }

        public static function invalid($name)
        {
            return strpbrk($name, "\\/?%*:|\"<>") == false;
        }

        public static function rename($old, $new)
        {
            return rename($old, $new);
        }

        public static function rmdir($path)
        {
            return @rmdir($path);
        }

        /**
         * [rrmdir Delete directory and delete entry in directory]
         * @param  [string|array]  $path                    [Path directory or array entry]
         * @param  [string]        $directory               [Path directory container array entry, set of $path is array entry]
         * @param  boolean         &$isHasFileAppPermission [Flag set true if has directory app]
         * @return [boolean]                                [Return true if delete success]
         */
        public static function rrmdir($path, $directory = null, &$isHasFileAppPermission = false, $isCheckPermissionApp = true)
        {
            if (is_array($path)) {

                foreach ($path AS $entry) {
                    $filename = $directory . SP . $entry;

                    if ($isCheckPermissionApp && self::permissionDenyPath($filename)) {
                        $isHasFileAppPermission = true;
                    } else if (self::isTypeFile($filename)) {
                        if (self::unlink($filename) == false)
                            return false;
                    } else if (self::isTypeDirectory($filename)) {
                        if (self::rrmdir($filename, null, $isCheckPermissionApp, $isHasFileAppPermission) == false)
                            return false;
                    } else {
                        return false;
                    }
                }

                return true;
            } else if (self::isReadable($path) && self::isTypeFile($path)) {
                return self::unlink($path);
            } else {
                $handle = self::scanDirectory($path);

                if ($handle !== false) {
                    $directoryCurrentHasPermission = false;

                    foreach ($handle AS $entry) {
                        if ($entry != '.' && $entry != '..') {
                            $filename = $path . SP . $entry;

                            if ($isCheckPermissionApp && self::permissionDenyPath($filename)) {
                                $isHasFileAppPermission        = true;
                                $directoryCurrentHasPermission = true;
                            } else if (self::isTypeFile($filename)) {
                                if (self::unlink($filename) == false)
                                    return false;
                            } else if (self::isTypeDirectory($filename)) {
                                if (self::rrmdir($filename, null, $isCheckPermissionApp, $isHasFileAppPermission) == false)
                                    return false;
                            } else {
                                return false;
                            }
                        }
                    }

                    if ($directoryCurrentHasPermission)
                        return true;

                    return self::rmdir($path);
                }
            }

            return false;
        }

        public static function rrmdirSystem($path, $directory = null, &$isHasFileAppPermission = false)
        {
            return self::rrmdir($path, $directory, $isHasFileAppPermission, false);
        }

        public static function sizeToString($size)
        {
            $size = @intval($size);

            if ($size < 1024)
                $size = $size . 'B';
            else if ($size < 1048576)
                $size = round($size / 1024, 2) . 'KB';
            else if ($size < 1073741824)
                $size = round($size / 1048576, 2) . 'MB';
            else
                $size = round($size / 1073741824, 2) . 'GB';

            return $size;
        }

        public static function unlink($path)
        {
            return @unlink($path);
        }

        public static function filterPaths($path, $isPathZIP = false)
        {
            $SP = SP;

            if ($SP == '\\')
                $SP = '\\\\';

            $path = str_replace('\\', $SP, $path);
            $path = str_replace('/',  $SP, $path);

            $path = preg_replace('#\\{1,}#', $SP, $path);
            $path = preg_replace('#/{1,}#',  $SP, $path);

            $path = preg_replace('#' . $SP . '\.'   . $SP . '#', $SP . $SP, $path);
            $path = preg_replace('#' . $SP . '\.\.' . $SP . '#', $SP . $SP, $path);

            $path = preg_replace('#' . $SP . '\.{1,2}$#', $SP . $SP, $path);
            $path = preg_replace('#' . $SP . '{2,}#',     $SP,       $path);

            if ($isPathZIP)
                $path = preg_replace('#' . $SP . '?(.+?)' . $SP . '?$#', '$1', $path);
            else
                $path = preg_replace('#(.+?)' . $SP . '$#', '$1', $path);

            return $path;
        }

        public static function fileExists($path)
        {
            return @file_exists($path);
        }

        /**
         * Check permission path
         * If path is deny permission return true
         */
        public static function permissionDenyPath($path, $isUseName = false)
        {
            if ($path != null && empty($path) == false) {
                $sp = SP;

                if (SP == '\\')
                    $sp = SP . SP;

                $path = strtolower($path);
                $path = self::filterPaths($path);

                if ($isUseName)
                    $reg = env('application.directory');
                else
                    $reg = env('application.path');

                if ($reg != null)
                    $reg = strtolower($reg);

                $reg = self::filterPaths($reg);

                if (SP == '\\')
                    $reg = str_replace(SP, $sp, $reg);

                if (preg_match('#^' . $reg . '$#si', $path))
                    return true;
                else if (preg_match('#^' . $reg . $sp . '(^\/+|^\\\\+)(.*?)$#si', $path))
                    return true;
                else if (preg_match('#^' . $reg . $sp . '(.*?)$#si', $path))
                    return true;
            }

            return false;
        }

        public static function getChmodPermission($path)
        {
            $perms = fileperms($path);

            if ($perms !== false) {
                $perms = decoct($perms);
                $perms = substr($perms, strlen($perms) == 5 ? 2 : 3, 3);
            } else {
                $perms = 0;
            }

            return $perms;
        }

        public static function fileSize($path, $convertToString = false)
        {
            if ($convertToString == false)
                return @filesize($path);

            return self::sizeToString(@filesize($path));
        }

        public static function scanDirectory($directory)
        {
            return @scandir($directory);
        }

        public static function globDirectory($pattern, $flag = 0)
        {
            return @glob($pattern, $flag);
        }

        public static function isTypeFile($path)
        {
            return @is_file($path);
        }

        public static function isTypeDirectory($path)
        {
            return @is_dir($path);
        }

        public static function isLink($path)
        {
            return @is_link($path);
        }

        public static function isReadable($path)
        {
            return @is_readable($path);
        }

        public static function isWriteable($path)
        {
            return @is_writeable($path);
        }

        public static function isExecutable($path)
        {
            return @is_executable($path);
        }

        public static function fileOpen($path, $mode)
        {
            return @fopen($path, $mode);
        }

        public static function fileSockOpen($host, $port = -1, &$errno = null, &$errstr = null, $timeout = 30)
        {
            return @fsockopen($host, $port, $errno, $errstr, $timeout);
        }

        public static function fileClose($handle)
        {
            return @fclose($handle);
        }

        public static function fileRead($handle, $length)
        {
            return @fread($handle, $length);
        }

        public static function fileSeek($handle, $offet, $whence = SEEK_SET)
        {
            return @fseek($handle, $offet, $whence);
        }

        public static function fileEndOfFile($handle)
        {
            return @feof($handle);
        }

        public static function fileWrite($handle, $string, $length = null)
        {
            if ($string == null)
                $string = '';

            if ($length == null)
                $length = strlen($string);

            return @fwrite($handle, $string, $length) && @fflush($handle);
        }

        public static function fileFlush($handle)
        {
            return @fflush($handle);
        }

        public static function fileReadContents($path)
        {
            if (($handle = self::fileOpen($path, 'ra')) !== false) {
                if (($data = self::fileRead($handle, self::fileSize($path))) !== false) {
                    self::fileClose($handle);
                    return $data;
                }

                self::fileClose($handle);
            }

            return false;
        }

        public static function fileWriteContents($path, $buffer)
        {
            if (($handle = self::fileOpen($path, 'wa+')) !== false) {
                if (self::fileWrite($handle, $buffer) !== false) {
                    self::fileFlush($handle);
                    self::fileClose($handle);

                    return true;
                }

                self::fileClose($handle);
            }

            return false;
        }

        public static function fileGetsLine($handle, $length = 1024)
        {
            return @fgets($handle, $length);
        }

        public static function fileReadsToArray($filename, $flag = 0, $context = null)
        {
            return @file($filename, $flag, $context);
        }

        public static function fileMTime($path)
        {
            return @filemtime($path);
        }

        public static function fileOwner($path)
        {
            return @fileowner($path);
        }

        public static function fileGroup($path)
        {
            return @filegroup($path);
        }

        public static function listContent(
            $path,
            $pathRemove              = null,
            $isGetSize               = false,
            $isGetChmodRWX           = false,
            $prefixKeyDirectoryArray = null,
            $prefixKeyFileArray      = null,
            $parentNotModify         = null,
            &$entryNotModify         = null
        ) {
            if (is_array($entryNotModify) == false)
                $entryNotModify = array();

            if (self::isTypeDirectory($path)) {
                if ($parentNotModify == null) {
                    $parentNotModify = $path;

                    if ($pathRemove !== null)
                        $pathRemove = self::filterPaths($pathRemove);
                }

                if ($path !== $parentNotModify) {
                    $pathDirectory = $path;

                    if ($pathRemove !== null && strpos($path, $pathRemove) === 0)
                        $pathDirectory = substr($path, strlen($pathRemove) + 1);

                    $array = [
                        'filepath'      => $pathDirectory,
                        'filename'      => basename($path),
                        'filesize'      => 0,
                        'is_directory'  => true,
                        'is_readable'   => $isGetChmodRWX ? self::isReadable($path)   : null,
                        'is_writable'   => $isGetChmodRWX ? self::isWriteable($path)  : null,
                        'is_executable' => $isGetChmodRWX ? self::isExecutable($path) : null
                    ];

                    if ($prefixKeyDirectoryArray !== null)
                        $entryNotModify[$prefixKeyDirectoryArray . $array['filepath']] = $array;
                    else
                        $entryNotModify[] = $array;
                }

                $handleScan = self::scanDirectory($path);

                if ($handleScan === false)
                    return false;

                foreach ($handleScan AS $filename) {
                    if ($filename != '.' && $filename != '..' && $filename != '.git') {
                        $filepath = self::filterPaths($path . SP . $filename);

                        if (self::isTypeDirectory($filepath)) {
                            if (self::listContent(
                                $filepath,
                                $pathRemove,
                                $isGetSize,
                                $isGetChmodRWX,
                                $prefixKeyDirectoryArray,
                                $prefixKeyFileArray,
                                $parentNotModify,
                                $entryNotModify
                            ) == false) {
                                return false;
                            }
                        } else {
                            $entrypath = $filepath;

                            if ($pathRemove !== null && strpos($entrypath, $pathRemove) === 0)
                                $entrypath = substr($entrypath, strlen($pathRemove) + 1);

                            $array = [
                                'filepath'      => $entrypath,
                                'filename'      => basename($filepath),
                                'filesize'      => $isGetSize ? self::fileSize($filepath) : 0,
                                'is_directory'  => false,
                                'is_readable'   => $isGetChmodRWX ? self::isReadable($filepath)   : null,
                                'is_writable'   => $isGetChmodRWX ? self::isWriteable($filepath)  : null,
                                'is_executable' => $isGetChmodRWX ? self::isExecutable($filepath) : null
                            ];

                            if ($prefixKeyFileArray !== null)
                                $entryNotModify[$prefixKeyFileArray . $array['filepath']] = $array;
                            else
                                $entryNotModify[] = $array;
                        }
                    }
                }
            }

            if ($path == $parentNotModify)
                return $entryNotModify;
            else
                return true;
        }

	}

?>
