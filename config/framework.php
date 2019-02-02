<?php
/**
 * 核心配置文件
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/2 12:05 PM
 */
return [
    'debug' => [
        'is_open' => true,
        'report_level' => E_ALL,
        'trace' => true,
    ],

    'log' => [
        'path' => ROOT_PATH . '/runtime/log',
        'per_time' => 'Y-m-d',
    ],
];