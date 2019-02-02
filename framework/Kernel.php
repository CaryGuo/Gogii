<?php
/**
 * Kernel.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/1/30 3:25 PM
 */

namespace Framework;

class Kernel
{
    private $isInitialize = false;

    public function __construct()
    {

    }

    public function run()
    {
        try {
            $this->initialize();

            // todo


        } catch (\Exception $exception) {
            // todo
        }
    }

    public function initialize(): void
    {
        if ($this->isInitialize) {
            return;
        }
        $this->isInitialize = true;

        $this->autoload();

        Error::initialize();
    }


    public function autoload(): void
    {
        include ROOT_PATH . '/vendor/autoload.php';

        include_once ROOT_PATH . '/framework/functions.php';

        spl_autoload_register(function (string $className) {
            $route = explode('\\', $className);

            $top = array_shift($route);

            include_once ROOT_PATH . DIRECTORY_SEPARATOR . strtolower($top) . DIRECTORY_SEPARATOR .
                implode(DIRECTORY_SEPARATOR, $route) . '.php';
        });
    }
}