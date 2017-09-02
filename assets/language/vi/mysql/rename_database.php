<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Đổi tên cơ sở dữ liệu',

        'form' => [
            'input' => [
                'database_name'   => 'Tên cơ sở dữ liệu:',
                'collection'      => 'Mã hóa - Đối chiếu:',
                'collection_none' => 'Không có lựa chọn'
            ],

            'placeholder' => [
                'input_database_name' => 'Nhập tên cơ sở dữ liệu'
            ],

            'button' => [
                'rename' => 'Đổi tên',
                'cancel' => 'Quay lại'
            ]
        ],

        'alert' => [
            'not_input_database_name'    => 'Chưa nhập tên cơ sở dữ liệu',
            'database_name_is_exists'    => 'Tên cơ sở dữ liệu đã tồn tại',
            'collection_not_validate'    => 'Mã hóa - Đối chiếu không hợp lệ',
            'has_not_changed'            => 'Không có gì thay đổi',
            'not_set_collection_to_none' => 'Không thể đặt mã hóa đối chiếu rỗng',
            'rename_database_failed'     => 'Đổi tên cơ sở dữ liệu <strongt>{$name}</strong> thất bại: <strong>{$error}</strong>',
            'rename_is_warning_error'    => 'Đổi tên cơ sở dữ liệu xảy ra sự cố hãy thử lại: <strong>{$error}</strong>',
            'rename_database_success'    => 'Đổi tên cơ sở dữ liệu <strong>{$name}</strong> thành công',
            'change_collection_failed'   => 'Thay đổi mã hóa - đối chiếu thất bại <strong>{$error}</strong>',
            'change_collection_success'  => 'Thay đổi mã hóa - đối chiếu cơ sở dữ liệu <strong>{$name}</strong> thành công'
        ]
    ];

?>