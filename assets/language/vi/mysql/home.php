<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Kết nối cơ sở dữ liệu',

        'form' => [
            'input' => [
                'host'     => 'Máy chủ:',
                'username' => 'Tên người dùng:',
                'password' => 'Mật khẩu:',
                'name'     => 'Tên cơ sở dữ liệu:'
            ],

            'button' => [
                'connect' => 'Kết nối',
                'cancel'  => 'Quay lại'
            ],

            'placeholder' => [
                'input_host'     => 'Nhập tên máy chủ',
                'input_username' => 'Nhập tên người dùng',
                'input_password' => 'Nhập mật khẩu',
                'input_name'     => 'Nhập tên cơ sở dữ liệu'
            ]
        ],

        'alert' => [
            'tips' => 'Để trống <strong>tên cơ sở dữ liệu</strong> để quản lý toàn bộ cơ sở dữ liệu, khi kết nối và ngắt kết nối sẽ chạy sleep ${app.sleep_time_redirect} giây do trình duyệt cache trang',

            'not_input_mysql_host'     => 'Chưa nhập đường dẫn máy chủ',
            'not_input_mysql_username' => 'Chưa nhập tên người dùng',

            'mysql_connect_failed'     => 'Kết nối cơ sở dữ liệu thất bại: <strong>{$error}</strong>',
            'mysql_connect_success'    => 'Kết nối cơ sở dữ liệu thành công',
            'mysql_is_already_connect' => 'Bạn đang ở trạng thái kết nối cơ sở dữ liệu',
            'mysql_connect_database_name_failed' => 'Kết nối cơ sở dữ liệu <strong>{$name}</strong> thất bại',

            'mysql_write_config_failed' => 'Viết cấu hình cơ sở dữ liệu thất bại',

            'disconnect_failed'  => 'Ngắt kết nối thất bại',
            'disconnect_success' => 'Ngắt kết nối thành công',

            'mysql_database_not_exists'      => 'Cơ sở dữ liệu không tồn tại',
            'mysql_database_name_not_exists' => 'Cơ sở dữ liệu <strong>{$name}</strong> không tồn tại',
            'mysql_table_not_exists'         => 'Bảng không tồn tại trong cơ sở dữ liệu <strong>{$database}</strong>',
            'mysql_table_name_not_exists'    => 'Bảng <strong>{$table}</strong> không tồn tại trong cơ sở dữ liệu <strong>{$database}</strong>'
        ],

        'menu_action' => [
            'create_table'    => 'Tạo bảng',
            'restore_upload'  => 'Tải lên bản sao lưu',
            'restore_manager' => 'Quản lý sao lưu ({$count})',
            'list_table'      => 'Danh sách bảng',
            'list_database'   => 'Danh sách cơ sở dữ liệu',
            'disconnect'      => 'Ngắt kết nối'
        ]
    ];

?>
