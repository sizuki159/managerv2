<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page' => 'Thông tin ứng dụng',

		'info' => [
			'label' => [
				'author'     => 'Tác giả:',
				'version'    => 'Phiên bản:',
				'email'      => 'Email:',
				'github'     => 'Github:',
				'facebook'   => 'Facebook:',
				'phone'      => 'Số điện thoại:',
				'create_at'  => 'Tạo ngày:',
                'upgrade_at' => 'Nâng cấp ngày:',
                'check_at'   => 'Kiểm tra ngày:',
                'build_at'   => 'Đóng gói ngày:'
			]
		],

        'menu_action' => [
            'about'        => 'Thông tin ứng dụng',
            'check_update' => 'Kiểm tra cập nhật',
            'upgrade_app'  => 'Nâng cấp ứng dụng',
            'validate_app' => 'Kiểm tra ứng dụng',
            'help'         => 'Trợ giúp',
            'feedback'     => 'Góp ý',
        ]
    ];

?>