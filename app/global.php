<?php

    if (defined('LOADED') == false)
        exit;

    define('ROOT', '..' . DIRECTORY_SEPARATOR);

    require_once(
        realpath(ROOT) .
        DIRECTORY_SEPARATOR . 'incfiles' .
        DIRECTORY_SEPARATOR . 'global.php'
    );

?>