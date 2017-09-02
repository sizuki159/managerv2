<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Đăng nhập',

        'form' => [
            'input_username_placeholder' => 'Tên đăng nhập',
            'input_password_placeholder' => 'Mật khẩu',

            'forgot_password' => 'Quên mật khẩu',
            'button_login'    => 'Đăng nhập'
        ],

        'alert' => [
            'not_input_username_or_password' => 'Chưa nhập tên đăng nhập hoặc mật khẩu',
            'username_or_password_wrong'     => 'Tên đăng nhập hoặc mật khẩu sai',
            'user_not_exists'                => 'Người dùng không tồn tại',
            'login_success'                  => 'Đăng nhập thành công',
            'login_already'                  => 'Bạn hiện đang ở trạng thái đăng nhập',
            'not_login'                      => 'Bạn chưa đăng nhập',

            'exit_session_failed'  => 'Đăng xuất thất bại',
            'exit_session_success' => 'Đăng xuất thành công'
        ]
    ];

?>