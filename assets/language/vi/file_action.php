<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Hành động',

        'title' => [
            'rename' => 'Đổi tên nhiều mục',
            'copy'   => 'Sao chép nhiều mục',
            'delete' => 'Xóa nhiều mục',
            'zip'    => 'Nén zip nhiều mục',
            'chmod'  => 'Phân quyền nhiều mục'
        ],

        'form' => [
            'input' => [
                'rename' => [
                    'label_name_directory'       => 'Tên thư mục (<strong>{$name}</strong>):',
                    'label_name_file'            => 'Tên tập tin (<strong>{$name}</strong>):'
                ],

                'copy' => [
                    'label_path_copy'              => 'Đường dẫn sao chép đến:',
                    'label_mode_copy'              => 'Sao chép',
                    'label_mode_move'              => 'Di chuyển',
                    'label_if_has_entry_is_exists' => 'Nếu có thư mục hoặc tập tin đã tồn tại:',
                    'label_exists_func_override'   => 'Ghi đè',
                    'label_exists_func_skip'       => 'Bỏ qua',
                    'label_exists_func_rename'     => 'Đổi tên'
                ],

                'delete' => [
                    'accept_message'             => 'Bạn có thực sự muốn xóa những mục đã chọn?'
                ],

                'zip' => [
                    'label_path_create_zip'         => 'Đường dẫn tạo tập tin zip:',
                    'label_name_zip'                => 'Tên tập tin zip:',
                    'label_more_options'            => 'Tùy chọn',
                    'label_override_zip'            => 'Ghi đè zip',
                    'label_delete_source'           => 'Xóa nguồn'
                ],

                'chmod' => [
                    'label_chmod_directory'       => 'Phân quyền thư mục:',
                    'label_chmod_file'            => 'Phân quyền tập tin:'
                ]
            ],

            'placeholder' => [
                'rename' => [
                    'input_name_directory' => 'Nhập tên thư mục',
                    'input_name_file'      => 'Nhập tên tập tin'
                ],

                'copy' => [
                    'input_path_copy'        => 'Nhập đường dẫn sao chép đến'
                ],

                'zip' => [
                    'input_path_create_zip'   => 'Nhập đường dẫn tạo tin zip',
                    'input_name_zip'          => 'Nhập tên tập tin zip'
                ],

                'chmod' => [
                    'input_chmod_directory' => 'Nhập phân quyền thư mục',
                    'input_chmod_file'      => 'Nhập phân quyền tập tin'
                ]
            ],

            'button' => [
                'rename' => 'Đổi tên',
                'copy'   => 'Sao chép',
                'delete' => 'Xóa',
                'zip'    => 'Nén zip',
                'chmod'  => 'Phân quyền',
                'cancel' => 'Quay lại'
            ]
        ],

        'alert' => [
            'not_file_select'         => 'Chưa có mục nào được chọn',
            'action_not_validate'     => 'Hành động không hợp lệ',
            'no_item_selected_exists' => 'Không có mục nào được chọn tồn tại',

            'rename' => [
                'nothing_changes'                         => 'Không có gì thay đổi',
                'list_entrys_modifier_is_zero'            => 'Không có mục tên sửa đổi nào được gửi tới',
                'name_directory_not_set_null'             => 'Không được để trống tên thư mục <strong>{$name}</strong>',
                'name_file_not_set_null'                  => 'Không được để trống tên tập tin <strong>{$name}</strong>',
                'name_directory_not_validate'             => 'Tên thư mục <strong>{$name}</strong> không hợp lệ, không được chứa bất kỳ ký tự <strong>{$validate}</strong>',
                'name_file_not_validate'                  => 'Tên tập tin <strong>{$name}</strong> không hợp lệ, không được chứa bất kỳ ký tự <strong>{$validate}</strong>',
                'name_directory_is_exists_in_input_other' => 'Tên thư mục <strong>{$name}</strong> đã tồn tại trong khung nhập khác',
                'name_file_is_exists_in_input_other'      => 'Tên tập tin <strong>{$name}</strong> đã tồn tại trong khung nhập khác',
                'name_directory_is_exists'                => 'Tên thư mục <strong>{$name}</strong> đã tồn tại',
                'name_file_is_exists'                     => 'Tên tập tin <strong>{$name}</strong> đã tồn tại',
                'rename_directory_failed'                 => 'Đổi tên thư mục <strong>{$name}</strong> thất bại',
                'rename_file_failed'                      => 'Đổi tên tập tin <strong>{$name}</strong> thất bại',
                'rename_directory_success'                => 'Đổi tên thư mục <strong>{$name}</strong> thành công',
                'rename_file_success'                     => 'Đổi tên tập tin <strong>{$name}</strong> thành công',
                'rename_failed'                           => 'Đổi tên nhiều mục thất bại',
                'rename_success'                          => 'Đổi tên nhiều mục thành công'
            ],

            'copy' => [
                'not_input_path_copy'              => 'Chưa nhập đường dẫn sao chép',
                'mode_not_validate'                => 'Hành động không hợp lệ',
                'exists_func_not_validate'         => 'Hành động khi thư mục hoặc tập tin tồn tại không hợp lệ',
                'path_copy_is_equal_path_current'  => 'Đường dẫn sao chép phải khác đường dẫn hiện tại',
                'path_move_is_equal_path_current'  => 'Đường dẫn di chuyển phải khác đường dẫn hiện tại',
                'path_copy_not_exists'             => 'Đường dẫn sao chép không tồn tại',
                'not_copy_file_to_directory_app'   => 'Bạn không thể sao chép vào thư mục của ứng dụng',
                'has_file_app_not_permission_copy' => 'Thư mục chứa một số mục của ứng dụng, đã bỏ qua các mục này',
                'copy_directory_failed'            => 'Sao chép thư mục <strong>{$name}</strong> thất bại',
                'copy_file_failed'                 => 'Sao chép tập tin <strong>{$name}</strong> thất bại',
                'copy_success'                     => 'Sao chép nhiều mục thành công',
                'copy_some_items_success'          => 'Sao chép một số mục thành công'
            ],

            'delete' => [
                'not_delete_file_app'     => 'Một số mục có chứa thư mục hoặc tập tin của ứng dụng không thể xóa hoàn toàn được',
                'delete_directory_failed' => 'Xóa thư mục <strong>{$name}</strong> thất bại',
                'delete_file_failed'      => 'Xóa tập tin <strong>{$name}</strong> thất bại',
                'delete_success'          => 'Xóa nhiều mục thành công'
            ],

            'zip' => [
                'not_input_path_create_zip'              => 'Chưa nhập đường dẫn tạo tập tin zip',
                'not_input_name_zip'                     => 'Chưa nhập tên tập tin zip',
                'not_create_zip_to_path_app'             => 'Bạn không thể tạo tập tin zip ở thư mục ứng dụng',
                'name_zip_not_validate'                  => 'Tên tập tin zip không hợp lệ, không được chứa bất kỳ ký tự <strong>{$validate}</strong>',
                'path_create_zip_is_delete_source'       => 'Đường dẫn tạo tin zip <strong>{$path}</strong> sẽ bị xóa sau khi tạo xong',
                'path_file_zip_is_exists'                => 'Đường dẫn tập tin zip <strong>{$path}</strong> đã tồn tại, hãy chọn ghi đè tập tin zip',
                'path_file_zip_is_exists_type_directory' => 'Đường dẫn tập tin zip <strong>{$path}</strong> đã tồn tại là một thư mục không thể ghi đè',
                'delete_file_zip_old_failed'             => 'Xóa tập tin zip để ghi đè thất bại',
                'has_file_app_not_permission_zip'        => 'Một số mục chứa thư mục hoặc tập tin của ứng dụng không thể nén lại',
                'zip_success'                            => 'Nén zip nhiều mục thành công'
            ],

            'chmod' => [
                'not_input_chmod_directory' => 'Chưa nhập phân quyền thư mục',
                'not_input_chmod_file'      => 'Chưa nhập phân quyền tập tin',
                'chmod_directory_failed'    => 'Phân quyền thư mục <strong>{$name}</strong> thất bại',
                'chmod_file_failed'         => 'Phân quyền tập tin <strong>{$name}</strong> thất bại',
                'chmod_success'             => 'Phân quyền nhiều mục thành công'
            ]
        ]
    ];

?>