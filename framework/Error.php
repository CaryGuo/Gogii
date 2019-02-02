<?php
/**
 * Error.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/2 11:42 AM
 */

namespace Framework;

use Framework\Exception\Handler;

class Error
{
    /**
     * @var Handler
     */
    private static $handler;

    public static function initialize(): void
    {
        error_reporting(E_ALL);
        set_error_handler([__CLASS__, 'error']);
        set_exception_handler([__CLASS__, 'exception']);
        register_shutdown_function([__CLASS__, 'shutdown']);
    }

    public static function exception(\Throwable $exception): void
    {
        Container::pull(Log::class)->write($exception, Log::ERROR);
        Container::pull(Log::class)->save();

        // todo response
    }

    public static function error(int $errNo, string $errMsg, string $errFile = '', int $errLine = 0): void
    {
        $exception = new \ErrorException($errNo, $errMsg, $errFile, $errLine);

        if (error_reporting() & $errNo) {
            throw $exception;
        }

        Container::pull(Log::class)->write($exception, Log::ERROR);

        // todo response
    }

    /**
     * Shutdown 事件注册方法
     * @brief 本方法实际上是runtime的终点
     *        应在这里对日志等操作做终结
     *        在部分Core级别错误时，本事件不一定可以正常触发
     */
    public static function shutdown(): void
    {
        if (!is_null($error = error_get_last()) && self::isFatal($error['type'])) {
            $exception = new \ErrorException($error['type'], $error['message'], $error['file'], $error['line']);
            self::exception($exception);
        }
    }

    protected static function isFatal(int $type): bool
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }

    protected static function response()
    {

    }
}