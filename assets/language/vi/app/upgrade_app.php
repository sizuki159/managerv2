<?php

    return [
        'title_page' => 'Nâng cấp ứng dụng',

        'info' => [
            'label_server_name'   => 'Địa chỉ tải về:',
            'label_version'       => 'Phiên bản:',
            'label_build_last'    => 'Đóng gói lúc:',
            'label_type_bin'      => 'Loại cài đặt:',
            'label_data_length'   => 'Kích thước dữ liệu:',
            'label_md5_bin_check' => 'MD5 sum:',
            'label_readme'        => 'Thông tin',
            'label_changelog'     => 'Thay đổi của phiên bản',

            'value_type_bin_install_upgrdae'    => 'Nâng cấp',
            'value_type_bin_install_additional' => 'Bổ sung'
        ],

        'form' => [
            'button' => [
                'upgrade'    => 'Cài đặt gói nâng cấp',
                'additional' => 'Cài đặt gói bổ sung'
            ]
        ],

        'alert' => [
            'error_check_upgrade_file_not_found'                     => 'Tập tin nâng cấp không tồn tại',
            'error_check_upgrade_additional_update_not_found'        => 'Gói cài đặt bổ sung không tồn tại',
            'error_check_upgrade_file_data_error'                    => 'Dữ liệu tập tin nâng cấp bị lỗi',
            'error_check_upgrade_file_data_additional_update_error'  => 'Gói cài đặt bổ sung bị lỗi',
            'error_check_upgrade_md5_check_failed'                   => 'Tập tin nâng cấp có thể đã bị thay đổi, mã md5 không khớp với tập tin nâng cấp',
            'error_check_upgrade_md5_additional_update_check_failed' => 'Gói cài đặt bổ sung có thể đã bị thay đổi, mã md5 không khớp với tập tin nâng cấp',
            'error_zip_not_open_file_upgrade'                        => 'Mở tập tin nâng cấp thất bại',
            'error_zip_extract_file_upgrade'                         => 'Giải nén tập tin nâng cấp thất bại',
            'error_upgrade_not_list_file_app'                        => 'Quét danh sách tập tin ứng dụng thất bại',
            'error_unknown'                                          => 'Lỗi không xác định',
            'install_upgrade_app_success'                            => 'Cài đặt gói nâng cấp lên phiên bản <strong>{$version}</strong> thành công',
            'install_additional_app_success'                         => 'Cài đặt gói bổ sung cho phiên bản <strong>{$version}</strong> thành công'
        ]
    ];

?>