<?php
/**
 * functions.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/1 5:39 PM
 */

/**
 * 格式化输出
 * @param mixed $arg
 * @param bool  $isKill
 */
function dd($arg, bool $isKill = true): void
{
    \Framework\Library\Debug::dump($arg, $isKill);
}

/**
 * @return \Framework\Log
 */
function log()
{
    return \Framework\Container::pull(\Framework\Log::class);
}