<?php

    if (defined('SP') == false)
        define('SP', DIRECTORY_SEPARATOR);

    if (defined('LOADED') == false)
        exit;

    require_once(__DIR__ . SP . 'Environment.php');
    require_once(__DIR__ . SP . 'Language.php');
    require_once(__DIR__ . SP . 'File' . SP . 'FileInfo.php');

    use Librarys\Environment;
    use Librarys\Language;
    use Librarys\File\FileInfo;
    use Librarys\Encryption\Services_JSON;

    function env($name, $default = null, $renew = false)
    {
        return Environment::env($name, $default, $renew);
    }

    /**
     * [lng Load value for key language]
     * @param  [string] $name [Key name language]
     * @param  [array|string] $params [Array params replace in language result]
     * @return [string]       [Value for key language]
     */
    function lng($name)
    {
        $params = null;

        if (is_array($params) == false) {
            $nums = func_num_args() - 1;
            $args = func_get_args();

            if ($nums >= 1 && is_array($args[1]))
                $params = $args[1];
            else if ($nums > 0 && $nums % 2 == 0)
                $params = array_splice($args, 1, $nums);
        }

        return Language::lng($name, $params);
    }

    /**
     * [lngToJson List language to json]
     * @param  [array]  $argument... [List file language load]
     * @return [string] [Json array string]
     */
    function lngToJson()
    {
        if (is_array($load) == false) {
            $load = array();
            $nums = func_num_args();

            if ($nums > 0)
                $load = func_get_args();
        }

        return Language::lngToJson($load);
    }

    function separator($str, $separator = SP)
    {
        $str = str_replace('/',  $separator, $str);
        $str = str_replace('\\', $separator, $str);

        return $str;
    }

    function urlSeparatorMatches($str)
    {
        if (isValidateIP($str))
            $str = separator($str, '/');

        return $str;
    }

    function takeIP()
    {
        $arrayIP = array();

        if (getenv('HTTP_X_FORWARDED_FOR') !== false) {
            $forwarded = getenv('HTTP_X_FORWARDED_FOR');
            $forwarded = array_reverse(explode(',', $forwarded));

            if (is_array($forwarded)) {
                foreach ($forwarded AS $entry) {
                    $entry = trim($entry);

                    if (isValidateIP($entry))
                        $arrayIP[] = $entry;
                }
            }
        }

        if (getenv('HTTP_CLIENT_IP') !== false)
            $arrayIP[] = trim(getenv('HTTP_CLIENT_IP'));

        if (getenv('HTTP_PROXY_USER') !== false)
            $arrayIp[] = trim(getenv('HTTP_PROXY_USER'));

        if (getenv('REMOTE_ADDR') !== false)
            $arrayIP[] = trim(getenv('REMOTE_ADDR'));

        foreach ($arrayIP AS $ip) {
            if (isValidateIP($ip))
                return $ip;
        }

        return null;
    }

    function takeUserAgent()
    {
        if (getenv('HTTP_USER_AGENT') !== false)
            return getenv('HTTP_USER_AGENT');

        return null;
    }

    function isValidateIP($ip)
    {
        if ($ip === null || is_string($ip) == false)
            return false;

        if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip) != false)
            return true;
        else if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip) != false)
            return true;

        return false;
    }

    function isValidateURL($url)
    {
        if (empty($url))
            return false;

        $url = addPrefixHttpURL($url);

        if (function_exists('filter_var')) {
            if (filter_var($url, FILTER_VALIDATE_URL))
                return true;
        } else if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url)) {
            return true;
        }

        return false;
    }

    function isValidateEmail($email) {
        if (empty($email))
            return false;

        if (function_exists('filter_var') == false) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL))
                return true;
        } else if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $email)) {
            return true;
        }

        return false;
    }

    function bug($var)
    {
        echo('<pre>');
        var_dump($var);
        echo('</pre>');
    }

    function gotoURL($url)
    {
        header('Location:' . $url);
        exit(0);
    }

    function stripcslashesResursive(&$value)
    {
        if (is_array($value) == false)
            $value = stripslashes($value);
        else
            array_walk_recursive($value, __FUNCTION__);
    }

    function requireDefine($filename)
    {
        if ($filename == null)
            return;

        $path  = env('app.path.define') . SP;
        $path .= $filename . '.php';

        if (FileInfo::isTypeFile($path))
            require_once($path);
    }

    function countStringArray($array, $search, $isLowerCase = false)
    {
        $count = 0;

        if ($array != null && is_array($array)) {
            foreach ($array AS $entry) {
                if ($isLowerCase)
                    $entry = strtolower($entry);

                if ($entry == $search)
                    ++$count;
            }
        }

        return $count;
    }

    function isInArray($array, $search, $isLowerCase)
    {
        if ($array == null || !is_array($array))
            return false;

        foreach ($array AS $entry) {
            if ($isLowerCase)
                $entry = strtolower($entry);

            if ($entry == $search)
                return true;
        }

        return false;
    }

    function addslashesArray($array)
    {
        if (is_array($array) == false)
            return $array;

        foreach ($array AS &$value) {
            if (is_array($value))
                $value = addslashesArray($value);
            else
                $value = addslashes($value);
        }

        return $array;
    }

    function stripslashesArray($array)
    {
        if (is_array($array) == false)
            return $array;

        foreach ($array AS &$value) {
            if (is_array($value))
                $value = stripslashesArray($value);
            else
                $value = stripslashes($value);
        }

        return $array;
    }

    function addPrefixHttpURL($url, $prefix = 'http://')
    {
        $posHttp  = stripos($url, 'http://');
        $posHttps = stripos($url, 'https://');

        if ($posHttp === 0 || $posHttps === 0)
            return $url;

        if ($prefix == null || empty($prefix))
            $prefix = 'http://';

        return $prefix . $url;
    }

    function removePrefixHttpURL($url)
    {
        if (stripos($url, 'http://') !== false)
            return substr($url, 7);
        else if (stripos($url, 'https://') !== false)
            return substr($url, 8);

        return $url;
    }

    function baseNameURL($url)
    {
        $parseURLPath = @parse_url($url, PHP_URL_PATH);

        if ($parseURLPath === false)
            return $url;

        return basename($parseURLPath);
    }

    function jsonEncode($var)
    {
        if (function_exists('json_encode'))
            return @json_encode($var);

        require_once(__DIR__. SP . 'Encryption' . SP . 'Services_JSON.php');

        $json   = new Services_JSON();
        $result = $json->encode($var);

        return $result;
    }

    function jsonDecode($var, $assoc = true)
    {
        if (function_exists('json_decode'))
            return @json_decode($var, $assoc);

        require_once(__DIR__. SP . 'Encryption' . SP . 'Services_JSON.php');

        $json = new Services_JSON($assoc ? SERVICES_JSON_LOOSE_TYPE : 0);
        $result = $json->decode($var);

        return $result;
    }

    function installUpgradeCallbackExtractZip($event, $header)
    {
        if (FileInfo::isTypeFile($header['filename'])) {
            if (FileInfo::unlink($header['filename']) == false)
                return 0;
        }
        return 1;
    }

    function installAdditionalCallbackExtractZip($event, $header)
    {
        if (FileInfo::isTypeFile($header['filename'])) {
            if (FileInfo::unlink($header['filename']) == false)
                return 0;
        }
        return 1;
    }

?>