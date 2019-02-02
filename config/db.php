<?php
/**
 * db.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/2 12:08 PM
 */
return [

    // 关系型数据库
    'rds0' => [
        'brand' => 'mysql', // 品牌
        'cluster' => [
            'master' => [ // 关系型数据库必须有一个master实例
                'host'  => '127.0.0.1',
                'port'  => '3310',
                'name'  => 'house',
            ],
            'slave1' => [ // 可选从库
                'host'  => '127.0.0.1',
                'port'  => '3310',
                'name'  => 'house',
            ],
            'slave2' => [ // 可选从库
                'host'  => '127.0.0.1',
                'port'  => '3310',
                'name'  => 'house',
            ],
        ],
    ],

    'redis' => [

    ],
];