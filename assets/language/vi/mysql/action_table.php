<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Hành động',

        'title' => [
            'delete'   => 'Xóa nhiều bảng',
            'truncate' => 'Xóa sạch dữ liệu nhiều bảng',
            'backup'   => 'Sao lưu nhiều bảng'
        ],

        'form' => [
            'input' => [
                'checkbox_all_entry' => 'Chọn tất cả',

                'delete' => [
                    'accept_message' => 'Bạn có thực sự muốn xóa những bảng đã chọn, cả dữ liệu cũng sẽ bị xóa cùng, hãy kiểm tra kỹ trước khi xóa, một khi đã xóa không thể khôi phục lại?'
                ],

                'truncate' => [
                    'accept_message' => 'Bạn có thực sự muốn xóa sạch dữ liệu những bảng đã chọn, hãy kiểm tra kỹ nếu có dữ liệu quan trọng hãy tiến hành sao lưu trước khi xóa?'
                ],

                'backup' => [
                    'filename' => 'Tên tập tin sao lưu:'
                ]
            ],

            'placeholder' => [
                'backup' => [
                    'input_filename' => 'Nhập tên tập tin sao lưu'
                ]
            ],

            'button' => [
                'delete'   => 'Xóa',
                'truncate' => 'Xóa sạch dữ liệu',
                'backup'   => 'Sao lưu',
                'cancel'   => 'Quay lại'
            ]
        ],

        'alert' => [
            'not_table_select'    => 'Không có bảng nào được chọn',
            'no_table'            => 'Không có bảng nào được tạo',
            'action_not_validate' => 'Hành động không hợp lệ',

            'delete' => [
                'delete_table_failed' => 'Xóa bảng <strong>{$name}</strong> thất bại: <strong>{$error}</strong>',
                'delete_success'      => 'Xóa nhiều bảng thành công'
            ],

            'truncate' => [
                'truncate_table_failed' => 'Xóa sạch dữ liệu bảng <strong>{$name}</strong> thất bại: <strong>{$error}</strong>',
                'truncate_success'      => 'Xóa sạch dữ liệu nhiều bảng thành công'
            ],

            'backup' => [
                'not_input_filename'       => 'Chưa nhập tên tập tin sao lưu',
                'filename_not_validate'    => 'Tên tập tin sao lưu không hợp lệ, không được chứa bất kỳ ký tự <strong>{$validate}</strong>',
                'filename_is_exists'       => 'Tên tập tin <strong>{$name}</strong> sao lưu đã tồn tại',
                'backup_infomation_failed' => 'Sao lưu thông tin cơ sở dữ liệu <strong>{$name}</strong> thất bại: <strong>{$error}</strong>',
                'backup_table_failed'      => 'Sao lưu bảng <strong>{$name}</strong> thất bại: <strong>{$error}</strong>',
                'backup_failed'            => 'Sao lưu nhiều bảng vào tập tin <strong>{$name}</strong> thất bại',
                'backup_success'           => 'Sao lưu nhiều bảng vào tập tin <strong>{$name}</strong> thành công, kích thước <strong>{$size}</strong>'
            ]
        ]
    ];

?>