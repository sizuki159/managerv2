<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Kiểm tra cập nhật',

        'info' => [
            'label' => [
                'last_check_update' => 'Thời gian kiểm tra cuối:',
                'last_upgrade'      => 'Thời gian nâng cấp cuối:',
                'last_build'        => 'Thời gian đóng gói cuối:',
                'version_current'   => 'Phiên bản hiện tại:',
                'server_check'      => 'Địa chỉ máy chủ kiểm tra ({$index}):'
            ],

            'value' => [
                'not_last_check_update' => 'Chưa kiểm tra cập nhật lần nào',
                'not_last_upgrade'      => 'Chưa nâng cấp lần nào',
            ]
        ],

        'form' => [
            'button' => [
                'check' => 'Kiểm tra'
            ]
        ],

        'alert' => [
            'tips'                                      => 'Nếu bạn tải về hoặc nâng cấp ứng dụng thất bại bạn hãy truy cập vào một trong số địa chỉ trên để tải về bản cài đặt mới',
            'not_server_check'                          => 'Không có địa chỉ máy chủ nào để kiểm tra',
            'address_not_found'                         => 'Địa chỉ <strong>{$url}</strong> không tồn tại',
            'file_not_found'                            => 'Địa chỉ tập tin <strong>{$url}</strong> không tồn tại trên trang này',
            'auto_redirect_url_failed'                  => 'Địa chỉ <strong>{$url}</strong> chuyển hướng thất bại',
            'connect_url_failed'                        => 'Kết nối tới địa chỉ <strong>{$url}</strong> thất bại',
            'error_json_data'                           => 'Lỗi dữ liệu địa chỉ <strong>{$url}</strong> có vấn đề',
            'error_json_data_not_validate'              => 'Lỗi dữ liệu địa chỉ <strong>{$url}</strong> không hợp lệ',
            'error_not_found_list_version_in_server'    => 'Không tìm thấy danh sách phiên bản trên địa chỉ <strong>{$url}</strong>',
            'error_not_found_parameter_guest'           => 'Lỗi địa chỉ truy vấn sai tới địa chỉ <strong>{$url}</strong>',
            'error_not_found_parameter_build'           => 'Lỗi địa chỉ truy vấn sai tới địa chỉ <strong>{$url}</strong>',
            'error_version_guest_not_validate'          => 'Phiên bản của bạn truy vấn tới địa chỉ <strong>{$url}</strong> không hợp lệ',
            'error_version_server_not_validate'         => 'Phiên bản của địa chỉ <strong>{$url}</strong> không hợp lệ',
            'error_not_found_version_current_in_server' => 'Lỗi phiên bản hiện tại không tồn tại trên địa chỉ <strong>{$url}</strong>',
            'error_write_info_failed'                   => 'Ghi thông tin cập nhật của địa chỉ <strong>{$url}</strong> thất bại',
            'error_mkdir_save_data_upgrade'             => 'Tạo thư mục chứa dữ liệu nâng cấp cho địa chỉ <strong>{$url}</strong> thất bại',
            'error_decode_compress_data'                => 'Giải mã dữ liệu nâng cấp cho địa chỉ <strong>{$url}</strong> thất bại',
            'error_decode_compress_additional_update'   => 'Giải mã dữ liệu gói cài đặt bổ sung cho địa chỉ <strong>{$url}</strong> thất bại',
            'error_decode_compress_update_script'       => 'Giải mã dữ liệu tập tin lệnh cho địa chỉ <strong>{$url}</strong> thất bại',
            'error_write_data_upgrade'                  => 'Ghi dữ liệu nâng cấp cho địa chỉ <strong>{$url}</strong> thất bại',
            'error_write_additional_update'             => 'Ghi dữ liệu gói cài đặt bổ sung cho địa chỉ <strong>{$url}</strong> thất bại',
            'error_write_update_script'                 => 'Ghi dữ liệu tập tin lệnh cập nhật cho địa chỉ <strong>{$url}</strong} thất bại',
            'error_md5_bin_check'                       => 'Dữ liệu nâng cấp tải về từ địa chỉ <strong>{$url}</strong> bị lỗi không khớp với mã md5',
            'error_md5_additional_update_check'         => 'Dữ liệu gói cài đặt bổ sung tải về từ địa chỉ <strong>{$url}</strong> bị lỗi không khớp với mã md5',
            'error_unknown'                             => 'Không rõ lỗi cho địa chỉ <strong>{$url}</strong>',
            'version_is_old'                            => 'Phiên bản <strong>{$version_current}</strong> hiện tại đã cũ, đã có phiên bản mới <strong>{$version_update}</strong> đã tải về cập nhật, bạn hãy nhấn cài đặt gói nâng cấp để lên phiên bản mới',
            'version_is_latest'                         => 'Phiên bản <strong>{$version_current}</strong> hiện tại của bạn là mới nhất và cũng không có gói cài đặt bổ sung nào mới',
            'has_additional'                            => 'Phiên bản <strong>{$version_current}</strong> có một gói cài đặt bổ sung, đã tải về bạn hãy nhấn cài đặt gói bổ sung để cập nhật gói bổ sung mới nhất'
        ]
    ];

?>