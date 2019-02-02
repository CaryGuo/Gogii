<?php
/**
 * String.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/1 2:07 PM
 */

namespace Framework\Library;

class Str
{
    const Camel  = 0;    // 小驼峰
    const Pascal = 1;    // 大驼峰
    const Underline = 2; // 下划线

    /**
     * variable name formatting
     * @param string $string
     * @param int    $type
     * @return string
     */
    public static function varNameFormat(string $string, int $type = self::Pascal): string
    {
        if ($type == self::Underline) {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $string), "_"));
        }

        $string = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $string);
        return $type === self::Pascal ? ucfirst($string) : lcfirst($string);
    }

    /**
     * kv 形式构造字符串
     * @brief  用key:value 形式 填充sql字符串
     * @tips   本身没有防注入验证，需要自己处理
     *
     * @exp    $pattern = "SELECT :fields FROM `:table` WHERE `user_id` = :user_id LIMIT :order,:by";
     *         $data    = [
     *             'fields'  => 'id, user_id, status, title',
     *             'table'   => 'TABLE_INQUIRY_NEW',
     *             'user_id' => 10,
     *             'order'   => 0,
     *             'by'      => 20,
     *         ];
     *         $sql = Service_Tools_Sql::buildSql($pattern, $data);
     *         var_dump($sql);
     *         -------------------
     *         Result:
     *         SELECT id, user_id, status, title FROM `TABLE_INQUIRY_NEW` WHERE `user_id` = 10 LIMIT 0,20
     *
     * @param  string $pattern    sql格式
     * @param  array  $data       数据索引数组
     * @param  bool   $mergeSpace 是否过滤连续空格|换行|制表符
     * @param  string $identifier 替换标识符
     * @return mixed
     */
    public static function printf($pattern, $data, $mergeSpace = false, $identifier = ':')
    {
        $keys = array_keys($data);
        foreach ($keys as $idx => $key) {
            $keys[$idx] = $identifier . $key;
        }
        $res = str_replace($keys, $data, $pattern);
        if ($mergeSpace) {
            $res = self::mergeSpaces($res);
        }
        return $res;
    }

    /**
     * 将连续空格|换行|制表符 替换为一个空格
     * @param  string $string
     * @return string
     */
    public static function mergeSpaces($string): string
    {
        return preg_replace('/\s+/', " ", $string);
    }
}