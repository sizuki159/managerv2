<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Tạo cột',

        'form' => [
            'input' => [
                'column_name'    => 'Tên cột:',
                'default_value'  => 'Giá trị mặc định:',
                'length_value'   => 'Độ dài dữ liệu:',
                'data_type'      => 'Kiểu dữ liệu:',
                'collection'     => 'Mã hóa - Đối chiếu:',
                'attribute'      => 'Thuộc tính:',
                'position'       => 'Vị trí:',
                'field_key'      => 'Khóa:',
                'checkbox_more'  => 'Thêm',
                'is_null'        => 'Cho phép giá trị null',
                'auto_increment' => 'Tự động tăng giá trị khóa',

                'data_type_none'  => 'Trống',
                'collection_none' => 'Trống',
                'attribute_none'  => 'Trống',
                'field_key_none'  => 'Trống',

                'position_label_column'      => 'Cột',
                'position_label_after_first' => 'Đầu bảng',
                'position_label_after_last'  => 'Cuối bảng'
            ],

            'button' => [
                'create_and_continue' => 'Tiếp tục',
                'create'              => 'Tạo',
                'cancel'              => 'Quay lại'
            ],

            'placeholder' => [
                'input_column_name'   => 'Nhập tên cột',
                'input_default_value' => 'Nhập giá trị mặc định',
                'input_length_value'  => 'Nhập độ dài dữ liệu'
            ]
        ],

        'alert' => [
            'not_input_column_name'     => 'Chưa nhập tên cột',
            'collection_not_validate'   => 'Mã hóa - Đối chiếu không hợp lệ',
            'length_data_not_validate'  => 'Độ dài dữ liệu không hợp lệ',
            'position_not_validate'     => 'Vị trí không hợp lệ',
            'column_name_is_exists'     => 'Tên cột đã tồn tại',
            'create_column_failed'      => 'Tạo cột thất bại: <strong>{$error}</strong>',
            'create_column_success'     => 'Tạo cột <strong>{$name}</strong> thành công'
        ]
    ];

?>