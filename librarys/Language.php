<?php

    namespace Librarys;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Environment;
    use Librarys\File\FileInfo;

    final class Language
    {

        private $boot;
        private $lang;
        private $cache;

        private static $instance;
        private static $params;

        public function __construct(Boot $boot)
        {
            $this->boot  = $boot;
            $this->lang  = array();
            $this->cache = array();

            self::$instance = $this;
        }

        public function execute()
        {

        }

        public static function lng($name, $params = array())
        {
            if (self::$instance instanceof Language == false)
                return null;

            if ($name == null || empty($name))
                trigger_error('Name is null');

            if (preg_match('/^[a-zA-Z0-9_]+\..+?$/si', $name)) {

                if (array_key_exists($name, self::$instance->cache))
                    return self::$instance->cache[$name];

                $filepath   = null;
                $prefixKey  = null;
                $keyCurrent = $name;
                $array      = self::load($name, $filepath, true, $prefixKey);

                if ($prefixKey != null)
                    $keyCurrent = substr($keyCurrent, strlen($prefixKey));

                if (strpos($keyCurrent, '.') === 0)
                    $keyCurrent = substr($keyCurrent, 1);

                $arrayKeys = explode('.', $keyCurrent);

                if (is_array($arrayKeys) == false)
                    return trigger_error('Key "' . $name . '" not found in language "' . $filepath . '"');

                foreach ($arrayKeys AS $entry) {
                    $entry = trim($entry);

                    if (is_array($array) == false || array_key_exists($entry, $array) == false)
                        return trigger_error('Key "' . $name . '" not found in language "' . $filepath . '"');

                    $array = $array[$entry];
                }

                if (is_array($array))
                    return (self::$instance->cache[$name] = 'Array');
                else if (is_object($array))
                    return (self::$instance->cache[$name] = 'Object');
                else if (is_resource($array))
                    return (self::$instance->cache[$name] = 'Resource');

                $array = Environment::envMatchesString($array);
                $array = Language::langMatchesString($array, $params);

                if (is_array($params) && count($params) > 0) {
                    $count = count($params);

                    if ($count % 2 == 0) {
                        for ($i = 0; $i < $count; $i += 2) {
                            $key   = $i;
                            $value = null;

                            if (isset($params[$i]))
                                $key = $params[$i];

                            if (isset($params[$i + 1]))
                                $value = $params[$i + 1];

                            $array = str_replace('{$' . $key . '}', $value, $array);
                        }
                    }

                    return $array;
                }

                return (self::$instance->cache[$name] = $array);
            }

            return trigger_error('Key "' . $name . '" not found in language "' . $filepath . '"');
        }

        public static function lngToJson(array $args = null)
        {
            if (self::$instance instanceof Language == false)
                return json_encode([]);

            if (is_array($args)) {
                foreach ($args AS $lang)
                    self::load($lang);
            }

            return self::$instance->toJson();
        }

        public static function load($filename, &$filepath = null, $loadRequire = true, &$prefixKey = null)
        {
            if (strpos($filename, '.') === false)
                return trigger_error('File name "' . $filename . '" not matches symbol "."');

            $container = env('app.language.path');
            $mime      = env('app.language.mime');
            $locale    = env('app.language.locale', 'en');
            $key       = null;

            // Split string to array of symbol "."
            $splitFilename = explode('.', $filename);

            // Check array split name is array
            if (is_array($splitFilename) == false || count($splitFilename) <= 0)
                return trigger_error('File name "' . $filename . '" is wrong');

            $path = null;

            // Find path file language
            foreach ($splitFilename AS $index => $value) {
                if ($index === 0) {
                    $path = $container . SP . $locale . SP . $value;

                    // Check file in locale set of user is exists
                    if (FileInfo::isTypeDirectory($path) == false) {
                        if (FileInfo::isTypeFile($path . $mime)) {
                            $path .= $mime;
                            $key   = $locale . '.' . $value;

                            break;
                        } else {
                            $locale = 'en';
                            $path   = $container . SP . $locale . SP . $value;

                            // Check file in locale default is exists
                            if (FileInfo::isTypeDirectory($path) == false) {
                                if (FileInfo::isTypeFile($path . $mime) == false) {
                                    return trigger_error('File name "' . $filename . '" not found');
                                } else {
                                    $path .= $mime;
                                    $key   = $locale . '.' . $value;

                                    break;
                                }
                            } else {
                                $locale . '.' . $value;
                            }
                        }
                    } else {
                        $key = $locale . '.' . $value;
                    }
                } else if (FileInfo::isTypeDirectory($path . SP . $value)) {
                    $path .= SP . $value;
                } else if (FileInfo::isTypeFile($path . SP . $value . $mime)) {
                    $path .= SP . $value . $mime;
                    $key  .= '.' . $value;

                    break;
                }
            }

            $prefixKey = substr($key, strlen($locale) + 1);
            $array     = array();
            $filepath  = $path;

            if (array_key_exists($key, self::$instance->lang))
                $array = self::$instance->lang[$key];
            else if ($path != null && FileInfo::isTypeFile($path))
                self::$instance->lang[$key] = ($array = require_once($path));
            else
                return trigger_error('File language "' . $filename . '" not found');

            if ($loadRequire && is_array($array))
                return self::loadRequire($array);

            return $array;
        }

        private static function loadRequire(array &$array)
        {
            if (is_array($array) == false)
                return $array;

            foreach ($array AS &$value) {
                if (is_array($value))
                    self::loadRequire($value);
                else if (preg_match_all('/\#\{([a-zA-Z0-9_]+)\.(.+?)\}/si', $value, $matches))
                    self::load($matches[1][0], $filepath, false);
            }

            return $array;
        }

        protected static function langMatchesString($str, $params)
        {
            if (is_array($str) || (preg_match('/\#\{(.+?)\}/si', $str) == false && preg_match('/lng\{(.+?)\}/si', $str, $matches) == false))
                return $str;

            self::$params = $params;

            $str = preg_replace_callback('/\#\{(.+?)\}/si', function($matches) {
                return lng(trim($matches[1]), self::$params);
            }, $str);

            $str = preg_replace_callback('/lng\{(.+?)\}/si', function($matches) {
                return lng(trim($matches[1]), self::$params);
            }, $str);

            return $str;
        }

        public static function toJson()
        {
            return json_encode(self::$instance->lang);
        }

    }

?>
