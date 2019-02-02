<?php
/**
 * Debug.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/1 5:59 PM
 */

namespace Framework\Library;

class Debug
{
    /**
     * @param mixed $arg
     * @param bool  $isKill
     */
    public static function dump($arg, bool $isKill = true): void
    {
        http_response_code(500);
        ob_start();
        var_dump($arg);

        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

        if (PHP_SAPI == 'cli') {
            $output = PHP_EOL . $output . PHP_EOL;
        } else {
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, ENT_SUBSTITUTE);
            }
            $output = '<pre>' . $output . '</pre>';
        }
        echo $output;
        if ($isKill) {
            die(1);
        }
    }
}