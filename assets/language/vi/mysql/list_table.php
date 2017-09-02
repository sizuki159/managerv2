<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page'   => 'Danh sách bảng',
        'column_count' => 'cột',

        'form' => [
            'input' => [
                'checkbox_all_entry' => 'Chọn tất cả'
            ]
        ],

        'alert' => [
            'tips' => 'Cơ sở dữ liệu đang kết nối bằng <strong>{$type}</strong>',

            'empty_list_table' => 'Không có bảng nào'
        ],

        'menu_action' => [
            'info_table'    => 'Thông tin bảng',
            'create_column' => 'Tạo cột',
            'create_data'   => 'Tạo dữ liệu',
            'truncate_data' => 'Xóa tất cả dữ liệu',
            'rename_table'  => 'Đổi tên bảng',
            'delete_table'  => 'Xóa bảng',
            'list_data'     => 'Danh sách dữ liệu',
            'list_column'   => 'Danh sách cột',
            'list_table'    => 'Danh sách bảng',
            'list_database' => 'Danh sách cơ sở dữ liệu'
        ],

        'action_multi' => [
            'delete'   => 'Xóa',
            'truncate' => 'Xóa sạch dữ liệu',
            'backup'   => 'Sao lưu'
        ]
    ];

?>