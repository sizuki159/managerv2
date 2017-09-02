<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Xóa bảng',

        'form' => [
            'accept_delete_table' => 'Bạn có thực sự muốn xóa bảng <strong>{$name}</strong> này không, toàn bộ dữ liệu của bảng sẽ mất hết?',

            'button' => [
                'delete' => 'Xóa',
                'cancel' => 'Quay lại'
            ]
        ],

        'alert' => [
            'delete_table_failed'  => 'Xóa bảng <strong>{$name}</strong> thất bại: <strong>{$error}</strong>',
            'delete_table_success' => 'Xóa bảng <strong>{$name}</strong> thành công'
        ]
    ];

?>