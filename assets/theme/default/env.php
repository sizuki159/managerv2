<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'directory' => 'default',
        'http'      => env('app.http.theme') . '/default'
    ];

?>