<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Cài đặt cấu hình',

        'form' => [
            'input' => [
                'paging_file_home_list'      => 'Phân trang danh sách tập tin:',
                'paging_file_view_zip'       => 'Phân trang xem tập tin zip:',
                'paging_file_edit_text'      => 'Phân trang sửa văn bản:',
                'paging_file_edit_text_line' => 'Phân trang sửa văn bản theo dòng:',

                'paging_mysql_list_data' => 'Phân trang danh sách dữ liệu mysql:',

                'enable_disable_label'                           => 'Bật/Tắt',
                'enable_forgot_password'                         => 'Lấy lại mật khẩu',
                'enable_disable_button_save_on_javascript'       => 'Phím Ctrl + S để lưu lại',
                'enable_disable_auto_focus_input_last'           => 'Tự động focus con trỏ chuột vào khung nhập',
                'enable_disable_count_checkbox_file_javascript'  => 'Hiện số mục được checked trong danh sách tập tin',
                'enable_disable_count_checkbox_mysql_javascript' => 'Hiện số mục được checked trong danh sách cơ sở dữ liệu',

                'auto_redirect_label'                   => 'Chuyển hướng',
                'enable_auto_redirect_file_rename'      => 'Khi thay đổi tên tập tin, thư mục',
                'enable_auto_redirect_file_chmod'       => 'Khi phân quyền tập tin, thư mục',
                'enable_auto_redirect_create_directory' => 'Khi tạo thư mục',
                'enable_auto_redirect_create_file'      => 'Khi tạo tập tin',
                'enable_auto_redirect_create_database'  => 'Khi tạo cơ sở dữ liệu',
                'enable_auto_redirect_rename_database'  => 'Khi đổi tên cơ sở dữ liệu'
            ],

            'button' => [
                'save'   => 'Lưu lại',
                'cancel' => 'Quay lại'
            ],

            'placeholder' => [
                'input_paging_file_home_list'      => 'Nhập phân trang danh sách tập tin',
                'input_paging_file_view_zip'       => 'Nhập phân trang xem tập tin zip',
                'input_paging_file_edit_text'      => 'Nhập phân trang sửa văn bản',
                'input_paging_file_edit_text_line' => 'Nhập phân trang sửa văn bản theo dòng',

                'input_paging_mysql_list_data' => 'Nhập phân trang danh sách cơ sở dữ liệu mysql'
            ]
        ],

        'alert' => [
        	'tips'                 => 'Phân trang cài <strong>0</strong> sẽ không phân trang, ' .
                                      'bật tắt chuyển hướng cho tạo thư mục, tập tin, cơ sở dữ liệu thì khi tạo những mục này thành công sẽ tự động chuyển hướng tới mục vừa được tạo',
            'save_setting_failed'  => 'Lưu cài đặt thất bại',
            'save_setting_success' => 'Lưu cài đặt thành công'
        ],

        'menu_action' => [
            'setting_system'  => 'Cài đặt cấu hình',
            'setting_theme'   => 'Cài đặt giao diện',
            'setting_profile' => 'Cài đặt tài khoản',
            'manager_user'    => 'Quản lý người dùng'
        ]
    ];

?>