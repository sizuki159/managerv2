<?php

    namespace Librarys\CFSR;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Encryption\StringCrypt;

    final class CFSRToken
    {

        private $name;
        private $token;
        private $time;
        private $path;

        private $isTokenUpdate;

        const TOKEN_NAME_NOT_FOUND = 1;
        const TOKEN_NOT_EQUAL      = 2;

        public function __construct()
        {
            if (env('app.cfsr.use_token') == false)
                return;

            $this->name = env('app.cfsr.key_name',    '_cfsr_token');
            $this->time = env('app.cfsr.time_live',   60000);
            $this->path = env('app.cfsr.path_cookie', '/');

            if (isset($_COOKIE[$this->name]) == false) {
                $this->token         = self::generator();
                $this->isTokenUpdate = true;
            } else {
                $this->token         = addslashes($_COOKIE[$this->name]);
                $this->isTokenUpdate = false;
            }

            setcookie($this->name, $this->token, env('SERVER.REQUEST_TIME') + $this->time, $this->path);
        }

        public static function generator()
        {
            return ('token' .
                md5(
                    base64_encode(
                        takeIP()             .
                        takeUserAgent() .
                        time()              .

                        StringCrypt::randomSalt()
                    )
                )
            );
        }

        public function validatePost()
        {
            if (env('app.cfsr.use_token') == false || env('app.cfsr.validate_post') == false)
                return true;

            if (env('SERVER.REQUEST_METHOD') == 'POST') {
                if (isset($_POST[$this->name]) == false || isset($_COOKIE[$this->name]) == false)
                    return self::TOKEN_NAME_NOT_FOUND;
                else if ($_POST[$this->name] != $_COOKIE[$this->name])
                    return self::TOKEN_NOT_EQUAL;
            }

            return true;
        }

        public function validateGet()
        {
            if (env('app.cfsr.use_token') == false || env('app.cfsr.validate_get') == false)
                return true;

            if (env('SERVER.REQUEST_METHOD') == 'GET') {
                if (isset($_GET[$this->name]) == false || isset($_COOKIE[$this->name]) == false)
                    return self::TOKEN_NAME_NOT_FOUND;
                else if ($_GET[$this->name] != $_COOKIE[$this->name])
                    return self::TOKEN_NOT_EQUAL;
            }

            return true;
        }

        public function getName()
        {
            return $this->name;
        }

        public function getTime()
        {
            return $this->time;
        }

        public function getToken()
        {
            return $this->token;
        }

        public function isTokenUpdate()
        {
            return $this->isTokenUpdate;
        }

    }

?>