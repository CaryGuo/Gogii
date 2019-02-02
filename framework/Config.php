<?php
/**
 * Config.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/1 4:36 PM
 */

namespace Framework;

class Config
{
    private static $config = [];

    public static function get($name)
    {
        $route = explode('.', $name);
        $path  = ROOT_PATH . '/config';
        $config = &self::$config;
        foreach ($route as $key) {
            $path .= '/' . $key;
            if (isset($config[$key])) {
                $config = &$config[$key];
                continue;
            }
            $filePath = $path . '.php';
            if (!file_exists($filePath)) {
                return null;
            }
            $config = include $filePath;
        }
        return $config;
    }

    public static function set($name, $value): void
    {
        $route = explode('.', $name);
        $path  = ROOT_PATH . '/config';
        $config = &self::$config;
        foreach ($route as $key) {
            $path .= '/' . $key;
            if (!isset($config[$key])) {
                $filePath = $path . '.php';
                if (!file_exists($filePath)) {
                    $config[$key] = [];
                } else {
                    $config[$key] = include $filePath;
                }
            }
            $config = &$config[$key];
        }
        $config = $value;
    }
}