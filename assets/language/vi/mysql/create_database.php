<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Tạo cơ sở dữ liệu',

        'form' => [
            'input' => [
                'database_name'   => 'Tên cơ sở dữ liệu:',
                'collection'      => 'Mã hóa - Đối chiếu:',
                'collection_none' => 'Không có lựa chọn'
            ],

            'button' => [
                'create' => 'Tạo',
                'cancel' => 'Quay lại'
            ],

            'placeholder' => [
                'input_database_name' => 'Nhập tên cơ sở dữ liệu'
            ]
        ],

        'alert' => [
            'not_input_database_name'      => 'Chưa nhập tên cơ sở dữ liệu',
            'database_name_is_exists'      => 'Tên cơ sở dữ liệu đã tồn tại',
            'collection_not_validate'      => 'Mã hóa - Đối chiếu không hợp lệ',
            'create_database_failed'       => 'Tạo cơ sở dữ liệu thất bại hoặc tên cơ sở dữ liệu đã tồn tại',
            'create_database_failed_error' => 'Tạo cơ sở dữ liệu thất bại: <strong>{$error}</strong>',
            'create_database_success'      => 'Tạo cơ sở dữ liệu <strong>{$name}</strong> thành công'
        ]
    ];

?>