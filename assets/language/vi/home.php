<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page_root'      => 'Danh sách',
        'title_page_directory' => 'Danh sách: ',

        'directory_empty'    => 'Thư mục trống',
        'checkbox_all_entry' => 'Chọn tất cả',

        'alert' => [
            'features_is_construct'          => 'Tính năng đang trong quá trình xây dựng',
            'path_not_exists'                => 'Đường dẫn không tồn tại',
            'path_not_receiver_list'         => 'Không thể lấy danh sách tập tin',
            'path_not_permission'            => 'Đường dẫn <strong>"{$path}"</strong> bạn truy cập thuộc của ứng dụng nên không thể truy cập',
            'path_not_is_file'               => 'Đường dẫn này không phải một tập tin',
            'password_user_is_equal_default' => 'Bạn chưa thay đổi mật khẩu mặc định, điều này rất nguy hiểm hãy thay đổi ngay. Thông báo sẽ xuất hiện <strong>{$time}</strong> lần trong mỗi lần đăng nhập'
        ],

        'action_multi' => [
            'rename' => 'Đổi tên',
            'copy'   => 'Sao chép',
            'delete' => 'Xóa',
            'zip'    => 'Nén zip',
            'chmod'  => 'Phân quyền'
        ],

        'menu_action' => [
            'create' => 'Tạo mới',
            'upload' => 'Tải lên',
            'import' => 'Nhập khẩu'
        ]
    ];

?>
