<?php
/**
 * index.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/1/30 3:21 PM
 */

use Framework\Kernel;

define('ROOT_PATH', dirname(dirname(__FILE__)));

include ROOT_PATH . '/framework/Kernel.php';

(new Kernel())->run();
