<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Xóa cơ sơ dữ liệu',

        'form' => [
            'accept_delete_database' => 'Bạn có thực sự muốn xóa cơ sở dữ liệu <strong>{$name}</strong> này không?',

            'button' => [
                'delete' => 'Xóa',
                'cancel' => 'Quay lại'
            ]
        ],

        'alert' => [
            'delete_database_failed'  => 'Xóa cơ sở dữ liệu <strong>{$name}</strong> thất bại: <strong>{$error}</strong>',
            'delete_database_success' => 'Xóa cơ sở dữ liệu <strong>{$name}</strong> thành công'
        ]
    ];

?>