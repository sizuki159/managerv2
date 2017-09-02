<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Tải lên bản sao lưu',

        'form' =>[
            'input' => [
                'choose_file'            => 'Chọn tập tin .sql...',
                'name_store'             => 'Tên tập tin nếu lưu trữ:',
                'label_more_options'     => 'Tùy chọn tải lên',

                'radio_func_upload_store_file'        => 'Lưu trữ',
                'radio_func_upload_restore'           => 'Khôi phục',
                'radio_func_upload_store_and_restore' =>'Lưu trữ và khôi phục'
            ],

            'placeholder' => [
                'input_name_store' => 'Nhập tên tập tin nếu lưu trữ'
            ],

            'button' =>[
                'upload' => 'Tải lên',
                'cancel' => 'Quay lại'
            ]
        ],

        'alert' => [
            'tips'                             => 'Để trống tên tập tin lưu trữ sẽ lấy tên tập tin tải lên nếu nó đã tồn tại sẽ tự động bị đổi tên',
            'empty_file_upload'                => 'Chưa chọn tập tin tải lên',
            'func_upload_not_validate'         => 'Tùy chọn tải lên không hợp lệ',
            'file_upload_error_size'           => 'Tập tin <strong>{$name}</strong> vượt quá kích thước',
            'file_upload_not_validate'         => 'Tập tin <strong>{$name}</strong> không hợp lệ',
            'file_name_store_not_validate'     => 'Tên tập tin lưu trữ <strong>{$name}</strong> không hợp lệ',
            'store_file_failed'                => 'Lưu trữ tập tin <strong>{$name}</strong> thất bại',
            'store_file_success'               => 'Lưu trữ tập tin <strong>{$name}</strong> thành công',
            'restore_record_failed'            => 'Khôi phục bản sao lưu <strong>{$name}</strong> thất bại: <strong>{$error}</strong>',
            'restore_record_success'           => 'Khôi phục bản sao lưu <strong>{$name}</strong> thành công, kích thước <strong>{$size}</strong>',
            'store_and_restore_record_success' => 'Lưu trữ và khôi phục bản sao lưu <strong>{$name}</strong> thành công, kích thước <strong>{$size}</strong>'
        ]
    ];

?>