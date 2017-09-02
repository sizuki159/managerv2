<?php

	namespace Librarys\File;

    if (defined('LOADED') == false)
        exit;

	final class FileMime
	{

		private static $mimes = [
			// Text
			'txt'  => 'text/plain',
			'htm'  => 'text/html',
			'html' => 'text/html',
			'php'  => 'text/html',
			'css'  => 'text/css',
			'js'   => 'application/javascript',
			'json' => 'application/json',
			'xml'  => 'application/xml',
			'swf'  => 'application/x-shockwave-flash',
			'flv'  => 'video/x-flv',

			// Image
			'png'  => 'image/png',
			'jpe'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg'  => 'image/jpeg',
			'gif'  => 'image/gif',
			'bmp'  => 'image/bmp',
			'ico'  => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif'  => 'image/tiff',
			'svg'  => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// Archive
			'zip'  => 'application/zip',
			'rar'  => 'application/x-rar-compressed',
			'exe'  => 'application/x-msdownload',
			'msi'  => 'application/x-msdownload',
			'cab'  => 'application/vnd.ms-cab-compressed',

			// Audio
			'mp3'  => 'audio/mpeg',
			'qt'   => 'video/quicktime',
			'mov'  => 'video/quicktime',

			// Adobe
			'pdf'  => 'application/pdf',
			'psd'  => 'image/vnd.adobe.photoshop',
			'ai'   => 'application/postscript',
			'ps'   => 'application/postscript',

			// MS office
			'doc'  => 'application/msword',
			'rtf'  => 'application/rtf',
			'xls'  => 'application/vnd.ms-excel',
			'ppt'  => 'application/vnd.ms-powerpoint',

			// Open office
			'odt'  => 'application/vnd.oasis.opendocument.text',
			'ods'  => 'application/vnd.oasis.opendocument.spreadsheet'
        ];

        private static $formats = [
            'image' => [
                'jpg',
                'jpeg',
                'gif',
                'png',
                'bmp',
                'ico'
            ],

            'text' => [
                'txt'
            ],

            'code' => [
                'h',
                'xml',
                'asp',
                'cpp',
                'sql',
                'c',
                'py',
                'php',
                'java',
                'css',
                'html',
                'tpl',
                'ini',
                'pl',
                'rb',
                'rss',
                'csv',
                'htaccess',
                'js',
                'lng',
                'pas',
                'sh',
                'cnf',
                'config',
                'conf',
                'conv',
                'md'
            ],

            'archive' => [
                'zip',
                'rar',
                'jar',
                'jar1',
                'jar2',
                '7z',
                'tar',
                'tarz'
            ],

            'audio' => [
                'wav',
                'aac',
                'mid',
                'mp3'
            ],

            'video' => [
                '3gp',
                'mpg',
                'flv',
                'm4v',
                'avi',
                'mp4',
                'mp4',
                'mov',
                'swf'
            ],

            'document' => [
                'pps',
                'xlxs',
                'ppt',
                'docx',
                'xls',
                'doc',
                'pdf'
            ],

            'binary' => [
                'dot',
                'exe',
                'dat',
                'pak',
                'deb'
            ],

            'source' => [
                'changelog',
                'copyright',
                'license',
                'readme'
            ],

            'other' => [

            ],

            'zip' => [
                'zip',
                'jar',
                'jar1',
                'jar2'
            ]
        ];

        private $fileInfo;

        public function __construct(FileInfo $fileInfo)
        {
            $this->fileInfo = $fileInfo;
        }

		public static function get(FileInfo $fileinfo)
		{
			if ($fileinfo == null || $fileinfo->getFileExt() == null)
				return 'application/octet-stream';

			if (array_key_exists($fileinfo->getFileExt(), self::$mimes))
			 	return self::$mimes[$fileinfo->getFileExt()];

			if (function_exists('finfo_open') && defined('FILEINFO_MIME')) {
				$open = finfo_open(FILEINFO_MIME);
				$mime = finfo_file($open, $fileinfo->getFilePath());

				if ($open !== false)
					finfo_close($open);

				return $mime;
			}

			return 'application/octet-stream';
		}

        private function isFormat($array)
        {
            if (in_array($this->fileInfo->getFileExt(), $array))
                return true;

            return false;
        }

        public function isFormatImage()
        {
            return $this->isFormat(self::$formats['image']);
        }

        public function isFormatText()
        {
            return $this->isFormat(self::$formats['text']);
        }

        public function isFormatArchive()
        {
            return $this->isFormat(self::$formats['archive']);
        }

        public function isFormatAudio()
        {
            return $this->isFormat(self::$formats['audio']);
        }

        public function isFormatVideo()
        {
            return $this->isFormat(self::$formats['video']);
        }

        public function isFormatBinary()
        {
            return $this->isFormat(self::$formats['binary']);
        }

        public function isFormatDocument()
        {
            return $this->isFormat(self::$formats['document']);
        }

        public function isFormatCode()
        {
            return $this->isFormat(self::$formats['code']);
        }

        public function isFormatSource()
        {
            $name = $this->fileInfo->getFileName();
            $ext  = $name;

            if (strpos($name, '.') !== false)
                $ext = substr($name, 0, strpos($name, '.'));
            else
                $ext = $this->fileInfo->getFileExt();

            if (in_array($ext, self::$formats['source']))
                return true;

            return false;
        }

        public function isFormatOther()
        {
            if (in_array($this->fileInfo->getFileExt(), self::$formats['other']))
                return true;

            return false;
        }

        public function isFormatArchiveZip()
        {
            return $this->isFormat(self::$formats['zip']);
        }

        public function isFormatTextAsEdit()
        {
            if ($this->isFormatText()   ||
                $this->isFormatSource() ||
                $this->isFormatCode()
            )
                return false;

            return true;
        }

        public function isFormatTextEdit()
        {
            if ($this->isFormatArchive() ||
                $this->isFormatAudio()   ||
                $this->isFormatVideo()   ||
                $this->isFormatImage()   ||
                $this->isFormatDocument()
            )
                return false;

            return true;
        }

	}

?>
