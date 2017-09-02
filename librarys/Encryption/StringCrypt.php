<?php

    namespace Librarys\Encryption;

    if (defined('LOADED') == false)
        exit;

    class StringCrypt
    {

        public static function randomToken($length = 32)
        {
            if ($length == null || $length <= 0)
                $length = 32;

            $token = null;

            if (function_exists('random_bytes'))
                $token = random_bytes($length);
            else if (function_exists('openssl_random_pseudo_bytes'))
                $token = openssl_random_pseudo_bytes($length);
            else if (function_exists('mcrypt_create_iv'))
                $token = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            else
                return trigger_error('Not support random token');

            return bin2hex($token);
        }

        public static function randomSalt()
        {
            $token      = self::randomToken();
            $token      = hex2bin($token);
            $saltBuffer = base64_encode($token);
            $saltBuffer = strtr($saltBuffer, '+', '.');

            // $2y$ is the blowfish algorithm
            $saltBuffer = sprintf("$2y$%02d$", 10) . $saltBuffer;

            return $saltBuffer;
        }

        public static function createCrypt($string, $salt = null)
        {
            if ($salt == null)
                $salt = self::randomSalt();

            return @crypt($string, $salt);
        }

        public static function hashEqualsString($stringSalt, $string)
        {
            $hashed = crypt($string, $stringSalt);

            if (function_exists('hash_equals')) {
                return hash_equals($hashed, $stringSalt);
            } else {
                if (strlen($hashed) != strlen($stringSalt)) {
                    return false;
                } else {
                    $res = $stringSalt ^ $hashed;
                    $ret = 0;

                    for ($i = strlen($res) - 1; $i >= 0; --$i)
                        $ret |= ord($res[$i]);

                    return $ret == false;
                }
            }

            return false;
        }

        public static function encodeCryptOfKey($string, $key)
        {
            $key     = md5($key);
            $keySize = mcrypt_get_key_size(MCRYPT_3DES, MCRYPT_MODE_CBC);
            $ivSize  = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_CBC);
            $iv      = mcrypt_create_iv($ivSize);
            $key     = substr($key, 0, $keySize);

            if (function_exists('mcrypt_encrypt'))
                $string  = mcrypt_encrypt(MCRYPT_3DES, $key, $string, MCRYPT_MODE_ECB, $iv);
            else if (function_exists('mcrypt_ecb'))
                $string = mcrypt_ecb(MCRYPT_3DES, $key, $string, MCRYPT_ENCRYPT);

            return base64_encode($string);
        }

        public static function decodeCryptOfKey($string, $key)
        {
            $string  = base64_decode($string);
            $key     = md5($key);
            $keySize = mcrypt_get_key_size(MCRYPT_3DES, MCRYPT_MODE_CBC);
            $ivSize  = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_CBC);
            $iv      = mcrypt_create_iv($ivSize);
            $key     = substr($key, 0, $keySize);

            if (function_exists('mcrypt_decrypt'))
                $string = mcrypt_decrypt(MCRYPT_3DES, $key, $string, MCRYPT_MODE_ECB, $iv);
            else if (function_exists('mcrypt_ecb'))
                $string = mcrypt_ecb(MCRYPT_3DES, $key, $string, MCRYPT_DECRYPT);

            return rtrim($string);
        }

    }