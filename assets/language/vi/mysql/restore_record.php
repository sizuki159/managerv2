<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Khôi phục bản sao lưu',

        'form' => [
            'input' => [
                'accept_message' => 'Bạn có thực sự muốn khôi phục bản sao lưu <strong>{$name}</strong> này không, những dữ liệu cũ sẽ có thể bị mất đi?'
            ],

            'button' => [
                'restore' => 'Khôi phục',
                'cancel'  => 'Quay lại'
            ]
        ],

        'alert' => [
            'record_file_not_exists' => 'Bản sao lưu không tồn tại',
            'restore_record_failed'  => 'Khôi phục bản sao lưu <strong>{$name}</strong> thất bại: <strong>{$error}</strong>',
            'restore_record_success' => 'Khôi phục bản sao lưu <strong>{$name}</strong> thành công'
        ]
    ];

?>