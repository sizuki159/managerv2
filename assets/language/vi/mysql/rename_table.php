<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Đổi tên bảng',

        'form' => [
            'input' => [
                'table_name'      => 'Tên bảng:',
                'collection'      => 'Mã hóa - Đối chiếu:',
                'collection_none' => 'Không có lựa chọn'
            ],

            'placeholder' => [
                'input_table_name' => 'Nhập tên cơ sở dữ liệu'
            ],

            'button' => [
                'rename' => 'Đổi tên',
                'cancel' => 'Quay lại'
            ]
        ],

        'alert' => [
            'not_input_table_name'       => 'Chưa nhập tên bảng',
            'table_name_is_exists'       => 'Tên bảng đã tồn tại',
            'collection_not_validate'    => 'Mã hóa - Đối chiếu không hợp lệ',
            'has_not_changed'            => 'Không có gì thay đổi',
            'not_set_collection_to_none' => 'Không thể đặt mã hóa đối chiếu rỗng',
            'rename_table_failed'        => 'Đổi tên bảng <strongt>{$name}</strong> thất bại: <strong>{$error}</strong>',
            'rename_table_success'       => 'Đổi tên bảng <strong>{$name}</strong> thành công',
            'change_collection_failed'   => 'Thay đổi mã hóa - đối chiếu thất bại <strong>{$error}</strong>',
            'change_collection_success'  => 'Thay đổi mã hóa - đối chiếu cơ sở dữ liệu <strong>{$name}</strong> thành công'
        ]
    ];

?>