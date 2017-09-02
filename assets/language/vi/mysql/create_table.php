<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Tạo bảng',

        'form' => [
            'input' => [
                'table_name'     => 'Tên bảng:',
                'column_name'    => 'Tên cột:',
                'default_value'  => 'Giá trị mặc định:',
                'length_value'   => 'Độ dài dữ liệu:',
                'data_type'      => 'Kiểu dữ liệu:',
                'collection'     => 'Mã hóa - Đối chiếu:',
                'attribute'      => 'Thuộc tính:',
                'engine_storage' => 'Lưu trữ:',
                'field_key'      => 'Khóa:',
                'checkbox_more'  => 'Thêm',
                'is_null'        => 'Cho phép giá trị null',
                'auto_increment' => 'Tự động tăng giá trị khóa',

                'data_type_none'      => 'Trống',
                'collection_none'     => 'Trống',
                'attribute_none'      => 'Trống',
                'engine_storage_none' => 'Trống',
                'field_key_none'      => 'Trống'
            ],

            'button' => [
                'create' => 'Tạo',
                'cancel' => 'Quay lại'
            ],

            'placeholder' => [
                'input_table_name'    => 'Nhập tên bảng',
                'input_column_name'   => 'Nhập tên cột',
                'input_default_value' => 'Nhập giá trị mặc định',
                'input_length_value'  => 'Nhập độ dài dữ liệu'
            ]
        ],

        'alert' => [
            'not_input_table_name'     => 'Chưa nhập tên bảng',
            'not_input_column_name'    => 'Chưa nhập tên cột',
            'collection_not_validate'  => 'Mã hóa - Đối chiếu không hợp lệ',
            'length_data_not_validate' => 'Độ dài dữ liệu không hợp lệ',
            'table_name_is_exists'     => 'Tên bảng đã tồn tại',
            'create_table_failed'      => 'Tạo bảng thất bại: <strong>{$error}</strong>',
            'create_table_success'     => 'Tạo bảng <strong>{$name}</strong> thành công'
        ]
    ];

?>