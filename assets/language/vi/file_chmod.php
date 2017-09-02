<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page_directory' => 'Phân quyền thư mục',
        'title_page_file'      => 'Phân quyền tập tin',

        'form' => [
            'input' => [
                'chmod_directory' => 'Phân quyền thư mục:',
                'chmod_file'      => 'Phân quyền tập tin:',

                'chmod_label_system' => 'Hệ thống',
                'chmod_label_group'  => 'Nhóm',
                'chmod_label_user'   => 'Người dùng',

                'chmod_value_read'    => 'Đọc',
                'chmod_value_write'   => 'Viết',
                'chmod_value_execute' => 'Thực thi'
            ],

            'button' => [
                'chmod'  => 'Phân quyền',
                'cancel' => 'Quay lại'
            ],

            'placeholder' => [
                'input_chmod_directory' => 'Nhập phân quyền thư mục',
                'input_chmod_file'      => 'Nhập phân quyền tập tin'
            ]
        ],

        'alert' => [
            'not_input_chmod_permission_directory'         => 'Chưa nhập phân quyền thư mục',
            'not_input_chmod_permission_file'              => 'Chưa nhập phân quyền tập tin',
            'chmod_permission_directory_failed'            => 'Phân quyền thư mục <strong>{$filename}</strong> thất bại',
            'chmod_permission_directory_failed_not_change' => 'Phân quyền thư mục <strong>{$filename}</strong> thất bại, phân quyền chưa thay đổi',
            'chmod_permission_directory_success'           => 'Phân quyền thư mục <strong>{$filename}</strong> thành công',
            'chmod_permission_file_failed'                 => 'Phân quyền tập tin <strong>{$filename}</strong> thất bại',
            'chmod_permission_file_failed_not_change'      => 'Phân quyền tập tin <strong>{$filename}</strong> thất bại, phân quyền chưa thay đổi',
            'chmod_permission_file_success'                => 'Phân quyền tập tin <strong>{$filename}</strong> thành công'
        ]
    ];

?>