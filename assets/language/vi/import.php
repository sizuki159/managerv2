<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Nhập khẩu',

        'form' => [
            'input' => [
                'label_import'         => 'Nhập khẩu:',
                'url'                  => 'Đường dẫn nhập khẩu:',
                'filename'             => 'Tên tập tin cho đường dẫn:',
                'label_exists_func'    => 'Chế độ ghi đè khi có tập tin đã tồn tại',
                'exists_func_override' => 'Ghi đè',
                'exists_func_skip'     => 'Bỏ qua',
                'exists_func_rename'   => 'Đổi tên',
                'label_mode_import'    => 'Chế độ nhập khẩu',
                'mode_import_curl'     => 'Curl',
                'mode_import_socket'   => 'Socket'
            ],

            'button' => [
                'more'   => 'Thêm...',
                'import' => 'Nhập khẩu',
                'cancel' => 'Quay lại'
            ],

            'placeholder' => [
                'input_url'      => 'Nhập đường dẫn nhập khẩu',
                'input_filename' => 'Nhập tên tập tin cho đường dẫn'
            ]
        ],

        'alert' => [
            'tips'                                        => 'Để trống tên tập tin cho đường dẫn hệ thống sẽ tự động đặt tên. ' .
                                                             'Có thể viết <strong>http://</strong> hoặc không cũng được. ' .
                                                             'Hỗ trợ tải tập tin không mật khẩu từ <strong>www.mediafire.com</strong>, hệ thống sẽ tự động kiểm tra đường dẫn để nhập khẩu về.',

            'data_empty_or_not_validate'                  => 'Dữ liệu trống hoặc không hợp lệ',
            'url_import_not_validate'                     => 'Đường dẫn <strong>{$url}</strong> không hợp lệ',
            'name_url_import_not_validate'                => 'Tên tập tin <strong>{$name}</strong> không hợp lệ, không được chứa bất kỳ ký tự <strong>{$validate}</strong>',
            'not_input_urls'                              => 'Chưa nhập đường dẫn nào',
            'exists_func_not_validate'                    => 'Chế độ ghi đè khi có tập tin đã tồn tại không hợp lệ',
            'mode_import_not_validate'                    => 'Chế độ nhập khẩu tập tin không hợp lệ',
            'address_not_found'                           => 'Địa chỉ <strong>{$url}</strong> không tồn tại',
            'file_not_found'                              => 'Địa chỉ tập tin <strong>{$url}</strong> không tồn tại trên trang này',
            'auto_redirect_url_failed'                    => 'Địa chỉ <strong>{$url}</strong> chuyển hướng thất bại',
            'connect_url_failed'                          => 'Kết nối tới địa chỉ <strong>{$url}</strong> thất bại',
            'error_unknown'                               => 'Không rõ lỗi cho địa chỉ <strong>{$url}</strong>',
            'file_is_exists'                              => 'Tập tin <strong>{$filename}</strong> đã tồn tại',
            'path_file_error_is_directory'                => 'Thư mục nhập khẩu đã chứa một thư mục với tên này <strong>{$filename}</strong> không thể ghi đè',
            'path_file_is_exists_and_skip'                => 'Thư mục nhập khẩu đã tồn tại một tập tin <strong>{$filename}</strong>, đã bỏ qua tập tin này',
            'error_delete_file_exists'                    => 'lng{import.alert.file_is_exists}, xóa thất bại để ghi đè',
            'import_file_exists_override_is_failed'       => 'lng{import.alert.file_is_exists}, nhập khẩu và ghi đè thất bại',
            'import_file_exists_override_is_success'      => 'lng{import.alert.file_is_exists}, <strong>{$size}</strong> nhập khẩu và ghi đè thành công, mất <strong>{$time}</strong>',
            'create_new_filename_exists_rename_is_failed' => 'lng{upload.alert.file_is_exists}, tạo tên mới cho tập tin thất bại',
            'import_file_exists_rename_is_failed'         => 'lng{upload.alert.file_is_exists}, nhập khẩu và thay đổi tên thất bại',
            'import_file_exists_rename_is_success'        => 'lng{upload.alert.file_is_exists}, <strong>{$size}</strong> nhập khẩu và thay đổi tên thành công, mất <strong>{$time}</strong>',
            'import_file_is_failed'                       => 'Nhập khẩu ghi tập tin <strong>{$filename}</strong> thất bại',
            'import_file_is_success'                      => 'Nhập khẩu ghi tập tin <strong>{$filename}</strong>, <strong>{$size}</strong> thành công, mất <strong>{$time}</strong>'
        ]
    ];

?>
