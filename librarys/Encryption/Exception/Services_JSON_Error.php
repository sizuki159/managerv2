<?php

    namespace Librarys\Encryption\Exception;

    if (defined('LOADED') == false)
        exit;

    class Services_JSON_Error extends \Exception
    {
        function __construct($message = 'unknown error', $code = null, $mode = null, $options = null, $userinfo = null)
        {
            parent::Exception($message);
        }
    }

?>