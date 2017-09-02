<?php

    if (defined('LOADED') == false)
        exit;

    // Disable env set to config user
    // Disable env set to false, enable env set to true

    return [
        'http_host' => false,

        'login'                          => false,
        'login.enable_forgot_password'   => false,
        'login.enable_lock_count_failed' => false,
        'login.max_lock_count'           => false,
        'login.time_lock'                => false,

        'cache'              => false,
        'cache.css'          => false,
        'cache.js'           => false,
        'cache.css.enable'   => false,
        'cache.css.minify'   => false,
        'cache.css.lifetime' => false,
        'cache.js.enable'    => false,
        'cache.js.minify'    => false,
        'cache.js.lifetime'  => false,

        'tmp'          => false,
        'tmp.lifetime' => false,
        'tmp.limit'    => false
    ];

?>