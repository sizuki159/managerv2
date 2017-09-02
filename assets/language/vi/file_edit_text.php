<?php

    if (defined('LOADED') == false)
        exit;

    return [
        'title_page'    => 'Sửa văn bản',
        'title_page_as' => 'Sửa dạng văn bản',

        'form' => [
            'input' => [
                'content_file' => 'Nội dung tập tin:'
            ],

            'button' => [
                'save'   => 'Lưu lại',
                'cancel' => 'Quay lại'
            ]
        ],

        'alert' => [
            'file_is_not_format_text_edit' => 'Tập tin không phải định dạng văn bản có thể sửa được',
            'save_text_success'            => 'Lưu văn bản thành công',
            'save_text_failed'             => 'Lưu văn bản thất bại'
        ]
    ];

?>
