<?php
/**
 * Log.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/1 2:57 PM
 */

namespace Framework;

use Framework\Library\Str;
use Framework\Library\Time;
use Framework\Log\Slice;
use Psr\Log\LoggerInterface;

class Log implements LoggerInterface
{
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';
    const SQL       = 'sql';
    /**
     * @var Slice
     */
    private $data = [];

    private $startTime;
    private $startMemory;
    private $sapiName;
    private $scriptName;
    private $serverInfo;
    private $isTrace;
    private $config;

    public function __construct()
    {
        $this->startTime   = strtotime(true);
        $this->startMemory = memory_get_usage();
        $this->sapiName    = php_sapi_name();
        $this->scriptName  = $this->sapiName == 'cli' ? realpath($_SERVER['argv'][0]) : $_SERVER['SCRIPT_FILENAME'];
        $this->serverInfo  = $_SERVER;
        $this->isTrace = Config::get('framework.debug.trace');
        $this->config  = Config::get('framework.log');
    }

    public function record($message, $type = self::NOTICE): void
    {
        $this->data[] = new Slice($message, $type);
    }

    public function save()
    {
        $contentTemp  = "=======================\r\n";
        $contentTemp .= "Process started at [:beginTime], ended at [:endTime], cost [:costTimems], \r\n";
        $contentTemp .= "Memory initialize cost [:beginMemory], cost pick is [:pickMemory] \r\n";

        $endTime = strtotime(true);

        $content = Str::printf($contentTemp, [
            'beginTime' => Time::microDate($this->startTime),
            'endTime' => Time::microDate($endTime),
            'costTime' => $endTime - $this->startTime,
            'beginMemory' => $this->startMemory,
            'pickMemory' => memory_get_usage(),
        ]);

        foreach ($this->data as $key => $slice) {
            $content .= "{{$key}}: " . $this->formatSlice($slice);
        }

        return $this->write($content);
    }

    public function formatSlice(Slice $slice): string
    {
        $content = "[{$slice->type}]: {$slice->msg}";
        if (!empty($slice->file)) {
            $content .= "[{$slice->file}:{$slice->line}]";
        }
        $content .= "Mem:[{$slice->memory}]Time: [{$slice->time}]\r\n";
        if ($this->isTrace) {
            $content .= $slice->trace . "\r\n";
        }
        return $content;
    }

    /**
     * 直接写
     * @param mixed  $message
     * @param string $type
     * @return bool|int
     */
    public function bomb($message, $type = self::NOTICE)
    {
        $content = $this->formatSlice(new Slice($message, $type));
        return $this->write($content);
    }

    private function getFileName(): string
    {
        return $this->config['path'] . '/' . date($this->config['pre_time'], $this->startTime) . '.log';
    }

    /**
     * @param string $content
     * @return bool|int
     */
    private function write(string $content)
    {
        return file_put_contents($this->getFileName(), $content, FILE_APPEND);
    }


    public function emergency($message, array $context = array())
    {
        $this->record($message, self::EMERGENCY);
    }

    public function alert($message, array $context = array())
    {
        $this->record($message, self::ALERT);
    }

    public function critical($message, array $context = array())
    {
        $this->record($message, self::CRITICAL);
    }

    public function error($message, array $context = array())
    {
        $this->record($message, self::WARNING);
    }

    public function warning($message, array $context = array())
    {
        $this->record($message, self::WARNING);
    }

    public function notice($message, array $context = array())
    {
        $this->record($message, self::NOTICE);
    }

    public function info($message, array $context = array())
    {
        $this->record($message, self::INFO);
    }

    public function debug($message, array $context = array())
    {
        $this->record($message, self::DEBUG);
    }

    public function log($level, $message, array $context = array())
    {
        $this->record($message, $level);
    }
}