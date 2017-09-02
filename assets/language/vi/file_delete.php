<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page_directory' => 'Xóa thư mục',
        'title_page_file'      => 'Xóa tập tin',

        'form' => [
            'accept_delete_directory' => 'Bạn có thực sự muốn xóa thư mục <strong>{$filename}</strong> không?',
            'accept_delete_file'      => 'Bạn có thực sự muốn xóa tập tin <strong>{$filename}</strong> không?',

            'button' => [
                'delete' => 'Xóa',
                'cancel' => 'Quay lại'
            ]
        ],

        'alert' => [
            'delete_directory_failed'           => 'Xóa thư mục <strong>{$filename}</strong> thất bại',
            'delete_directory_success'          => 'Xóa thư mục <strong>{$filename}</strong> thành công',
            'delete_file_failed'                => 'Xóa tập tin <strong>{$filename}</strong> thất bại',
            'delete_file_success'               => 'Xóa tập tin <strong>{$filename}</strong> thành công',
            'delete_entry_in_directory_success' => 'Xóa các thư mục và tập tin trong thư mục <strong>{$filename}</strong> thành công',
            'not_delete_file_app'               => 'Thư mục <strong>{$filename}</strong> chứa thư mục ứng dụng không thể xóa thư mục này hoàn toàn'
        ]
    ];

?>