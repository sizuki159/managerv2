<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Xóa sạch dữ liệu',

        'form' => [
            'accept_truncate_data' => 'Bạn có thực sự muốn xóa sạch dữ liệu của bảng <strong>{$name}</strong> này không?',

            'button' => [
                'truncate' => 'Xóa sạch',
                'cancel'   => 'Quay lại'
            ]
        ],

        'alert' => [
            'truncate_data_failed'  => 'Xóa sạch dữ liệu của bảng <strong>{$name}</strong> thất bại: <strong>{$error}</strong>',
            'truncate_data_success' => 'Xóa sạch dữ liệu của bảng <strong>{$name}</strong> thành công'
        ]
    ];

?>