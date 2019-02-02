<?php
/**
 * NotFoundException.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/1 1:56 PM
 */

namespace Framework\Exception;

use Framework\Exception;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{

}