<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page'          => 'Danh sách cơ sở dữ liệu',
        'empty_list_database' => 'Không có cơ sở dữ liệu nào',

        'menu_action' => [
            'create_database' => 'Tạo cơ sở dữ liệu',
            'delete_database' => 'Xóa cơ sở dữ liệu',
            'rename_database' => 'Đổi tên cơ sở dữ liệu',
            'info_database'   => 'Thông tin cơ sở dữ liệu'
        ],

        'alert' => [
            'tips' => 'Cơ sở dữ liệu đang kết nối bằng <strong>{$type}</strong>',

            'mysql_is_not_connect_root' => 'Bạn đang kết nối ở chế độ quản lý cho cơ sở dữ liệu <strong>{$name}</strong>, không có quyền quản lý cơ sở dữ liệu khác'
        ]
    ];

?>