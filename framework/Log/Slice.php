<?php
/**
 * Slice.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/1 3:36 PM
 */

namespace Framework\Log;

use Framework\Config;
use Framework\Log;
use Psr\Log\InvalidArgumentException;

class Slice
{
    public $time;
    public $memory;

    public $type;

    public $code;
    public $msg;
    public $trace;

    public $line;
    public $file;

    public function __construct(...$args)
    {
        $this->time   = strtotime(true);
        $this->memory = memory_get_usage();
        $argLen = count($args);
        if ($argLen == 0) {
            throw new InvalidArgumentException('log param miss: new ' . get_class());
        }
        $this->msg  = (string) $args[0];
        $this->type = Log::NOTICE;

        if ($argLen >= 2) {
            $this->type  = $args[1];
            if ($args[1] == Log::ERROR) {
                $this->trace = debug_backtrace();
            }
        } else if ($args[0] instanceof \Exception) {
            $this->type  = Log::ERROR;
            $this->trace = $args[0]->getTrace();

            $this->file = $args[0]->getFile();
            $this->line = $args[0]->getLine();
        }
    }
}
