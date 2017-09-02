<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Tạo dữ liệu',

        'form' => [
            'input' => [
                'column_name' => 'Cột <strong>`{$name}`</strong> <strong>{$type}</strong>:'
            ],

            'placeholder' => [
                'input_column_data' => 'Nhập dữ liệu cho cột `{$name}`'
            ],

            'button' => [
                'create_and_continue' => 'Tiếp tục',
                'create'              => 'Tạo',
                'cancel'              => 'Quay lại'
            ]
        ],

        'alert' => [
            'tips'                     => 'Những cột có kiểu dữ liệu là số sẽ không nhập được chữ trên một số trình duyệt chứ không phải bị lỗi',
            'columns_in_table_is_zero' => 'Không có cột nào trong bảng <strong>{$name}</strong> hãy tạo cột để tạo dữ liệu',
            'create_data_failed'       => 'Tạo dữ liệu thất bại: <strong>{$error}</strong>',
            'create_data_success'      => 'Tạo dữ lữ liệu thành công'
        ]
    ];

?>