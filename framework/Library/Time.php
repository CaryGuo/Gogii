<?php
/**
 * Time.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/2 1:07 PM
 */

namespace Framework\Library;

class Time
{
    public static function microDate($timeStamp, int $precision = 3)
    {
        $timeStamp = round($timeStamp, $precision);
        $times = explode('.', $timeStamp);
        return date("Y-m-d H:i:s", $times[0]) . '.' . $times[1];
    }
}