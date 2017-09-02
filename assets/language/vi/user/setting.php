<?php

    return [
        'title_page' => 'Cài đặt tài khoản',

        'form' => [
            'input' => [
                'username'        => 'Tên tài khoản:',
                'email'           => 'Địa chỉ email:',
                'password_old'    => 'Mật khẩu cũ:',
                'password_new'    => 'Mật khẩu mới:',
                'password_verify' => 'Nhập lại mật khẩu mới:'
            ],

            'placeholder' => [
                'input_username'        => 'Nhập tên người dùng',
                'input_email'           => 'Nhập địa chỉ email',
                'input_password_old'    => 'Nhập mật khẩu cũ',
                'input_password_new'    => 'Nhập mật khẩu mới',
                'input_password_verify' => 'Nhập lại mật khẩu mới'
            ],

            'button' => [
                'change' => 'Thay đổi',
                'cancel' => 'Quay lại'
            ]
        ],

        'alert' => [
            'tips'                                   => 'Nhập mật khẩu để xác nhận thay đổi thông tin, nhập mật khẩu mới nếu muốn thay đổi mật khẩu',
            'not_input_username'                     => 'Chưa nhập tên đăng nhập',
            'not_input_email'                        => 'Chưa nhập email',
            'not_input_password_old'                 => 'Chưa nhập mật khẩu cũ',
            'not_input_password_verify'              => 'Chưa nhập lại mật khẩu mới để thay đổi mật khẩu',
            'username_not_validate'                  => 'Tên đăng nhập không hợp lệ, không được chứa bất kỳ ký tự <strong>{$validate}</strong>',
            'email_not_validate'                     => 'Email không hợp lệ',
            'password_old_wrong'                     => 'Mật khẩu cũ không đúng',
            'password_new_not_equal_password_verify' => 'Hai mật khẩu mới không giống nhau',
            'nothing_change'                         => 'Không có gì thay đổi',
            'change_config_info_failed'              => 'Thay đổi thông tin thất bại',
            'change_config_password_failed'          => 'Thay đổi mật khẩu thất bại',
            'save_change_config_failed'              => 'Lưu lại thay đổi thất bại',
            'save_change_config_success'             => 'Lưu lại thay đổi thành công, hãy đăng nhập lại'
        ]
    ];

?>