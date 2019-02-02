<?php
/**
 * Arr.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/1 2:08 PM
 */

namespace Framework\Library;

class Arr
{
    /**
     * 是否是索引数组
     * @param  array $array
     * @return bool
     */
    public static function isAssoc($array): bool
    {
        if(!is_array($array)) {
            return false;
        }
        $keys = array_keys($array);
        return $keys != array_keys($keys);
    }
}