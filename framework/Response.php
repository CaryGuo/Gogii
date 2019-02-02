<?php
/**
 * Response.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/2 3:03 PM
 */

namespace Framework;

abstract class Response
{
    protected $data;

    protected $code = 200;

    protected $content = '';

    public function __construct($data, int $code = 200)
    {
        $this->data = $data;
        $this->code = $code;
        $this->format();
    }

    abstract function send(): void ;

    abstract function format(): void ;
}