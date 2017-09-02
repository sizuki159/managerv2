<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page'          => 'Quản lý bản sao lưu',
        'empty_backup_record' => 'Không có bản sao lưu nào',

        'title' => [
            'delete'   => 'Xóa nhiều bản sao lưu',
            'download' => 'Tải về nhiều bản sao lưu'
        ],

        'form' => [
            'input' => [
                'checkbox_all_entry' => 'Chọn tất cả',

                'delete' => [
                    'accept_message' => 'Bạn có thực sự muốn xóa những bản sao lưu đã chọn, một khi đã xóa không thể khôi phục hãy kiểm tra thật kỹ trước khi xóa?'
                ]
            ],

            'button' => [
                'delete'   => 'Xóa',
                'download' => 'Tải xuống',
                'cancel'   => 'Quay lại'
            ]
        ],

        'alert' => [
            'not_record_select' => 'Chưa chọn bản sao lưu nào',

            'delete' => [
                'delete_record_failed' => 'Xóa bản sao lưu <strong>{$name}</strong> thất bại',
                'delete_success'       => 'Xóa nhiều bản sao lưu thành công'
            ],

            'download' => [
                'download_failed'        => 'Tải về nhiều bản sao lưu thất bại',
                'download_record_failed' => 'Tải về bản sao lưu <strong>{$name}</strong> thất bại: <strong>{$error}</strong>'
            ]
        ],

        'action_multi' => [
            'delete'   => 'Xóa',
            'download' => 'Tải xuống'
        ]
    ];

?>