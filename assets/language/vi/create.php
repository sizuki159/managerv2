<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Tạo mới',

        'form' => [
            'input' => [
                'name'                        => 'Tên thư mục hoặc tập tin:',
                'radio_choose_type_directory' => 'Thư mục',
                'radio_choose_type_file'      => 'Tập tin'
            ],

            'button' => [
                'create'              => 'Tạo',
                'create_and_continue' => 'Tiếp tục',
                'cancel'              => 'Quay lại'
            ],

            'placeholder' => [
                'input_name' => 'Nhập tên thư mục hoặc tập tin'
            ]
        ],

        'alert' => [
            'not_input_name_directory'           => 'Chưa nhập tên thư mục',
            'not_input_name_file'                => 'Chưa nhập tên tập tin',
            'not_choose_type'                    => 'Chưa chọn tạo thư mục hay tập tin',
            'name_is_exists_type_directory'      => 'Tên này đã tồn tại là một thư mục',
            'name_is_exists_type_file'           => 'Tên này đã tồn tại là một tập tin',
            'name_not_validate_type_directory'   => 'Tên thư mục không hợp lệ, không được chứa bất kỳ ký tự <strong>{$validate}</strong>',
            'name_not_validate_type_file'        => 'Tên tập tin không hợp lệ, không được chứa bất kỳ ký tự <strong>{$validate}</strong>',
            'name_not_permission_type_directory' => 'Xin lỗi bạn đang tạo thư mục trong đường dẫn của ứng dụng',
            'name_not_permission_type_file' => 'Xin lỗi bạn đang tạo tập tin trong đường dẫn của ứng dụng',

            'create_directory_failed'  => 'Tạo thư mục <strong>{$filename}</strong> thất bại',
            'create_directory_success' => 'Tạo thư mục <strong>{$filename}</strong> thành công',
            'create_file_failed'       => 'Tạo tập tin <strong>{$filename}</strong> thất bại',
            'create_file_success'      => 'Tạo tập tin <strong>{$filename}</strong> thành công'
        ]
    ];

?>