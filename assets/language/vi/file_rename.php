<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page_directory' => 'Đổi tên thư mục',
        'title_page_file'      => 'Đổi tên tập tin',

        'form' => [
            'input' => [
                'name_directory' => 'Tên thư mục:',
                'name_file'      => 'Tên tập tin:'
            ],

            'button' => [
                'rename' => 'Đổi tên',
                'cancel' => 'Quay lại'
            ],

            'placeholder' => [
                'input_name_directory' => 'Nhập tên thư mục',
                'input_name_file'      => 'Nhập tên tập tin'
            ]
        ],

        'alert' => [
            'not_input_name_directory'    => 'Chưa nhập tên thư mục',
            'not_input_name_file'         => 'Chưa nhập tên tập tin',
            'name_directory_not_validate' => 'Tên thư mục không hợp lệ, không được chứa bất kỳ ký tự <strong>{$validate}</strong>',
            'name_file_not_validate'      => 'Tên tập tin không hợp lệ, không được chứa bất kỳ ký tự <strong>{$validate}</strong>',
            'name_not_change'             => 'Tên không có gì thay đổi',
            'rename_directory_failed'     => 'Đổi tên thư mục <strong>{$filename}</strong> thất bại',
            'rename_file_failed'          => 'Đổi tên tập tin <strong>{$filename}</strong> thất bại',
            'rename_directory_success'    => 'Đổi tên thư mục <strong>{$filename}</strong> thành công',
            'rename_file_success'         => 'Đổi tên tập tin <strong>{$filename}</strong> thành công'
        ]
    ];

?>