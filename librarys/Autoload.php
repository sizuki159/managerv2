<?php

    namespace Librarys;

    if (defined('SP') == false)
        define('SP', DIRECTORY_SEPARATOR);

    if (defined('LOADED') == false)
        exit;

    require_once('File' . SP . 'FileInfo.php');

    use Librarys\File\FileInfo;

    final class Autoload
    {

        private $boot;
        private $prefixs;

        public function __construct(Boot $boot)
        {
            $this->boot    = $boot;
            $this->prefixs = array();
        }

        public function execute($classes = null)
        {
            if ($classes == false) {
                $prefixNamespace = env('app.autoload.prefix_namespace');

                if ($prefixNamespace != null) {
                    $array = explode('\\', $prefixNamespace);

                    if (is_array($array) == false)
                        $array = array($prefixNamespace);

                    $this->prefixs = $array;
                }

                return spl_autoload_register(array($this, __FUNCTION__));
            }

            if (($path = $this->isFileLibrarys($classes)) !== false)
                require_once($path);
            else
                die('Class ' . $classes . ' not require');
        }

        public function isFileLibrarys($classes)
        {
            if ($classes == null)
                return false;

            $array_class  = explode('\\', $classes);

            if (is_array($array_class) == false)
                $array_class = array($classes);

            $length = count($array_class);
            $path   = null;

            for ($i = 0; $i < $length; ++$i) {
                $entry_class = trim($array_class[$i]);

                if (isset($this->prefixs[$i]) == false || strcmp(trim($this->prefixs[$i]), $entry_class) !== 0) {
                    $path .= $entry_class;

                    if ($i + 1 < $length)
                        $path .= SP;
                }
            }

            $absolute = $path . env('app.autoload.prefix_class_mime');

            if (FileInfo::isTypeFile(env('app.path.librarys') . SP . $absolute))
                return env('app.path.librarys') . SP . $absolute;

            return false;
        }

    }

?>