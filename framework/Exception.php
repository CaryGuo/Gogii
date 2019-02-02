<?php
/**
 * Exception.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/1 1:42 PM
 */

namespace Framework;

class Exception extends \RuntimeException
{
    protected $data = [];
}