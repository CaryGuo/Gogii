<?php
/**
 * Container.php
 * @author guokexin <guokexin01@baidu.com>
 * @date   2019/2/1 11:25 AM
 */

namespace Framework;

use Closure;
use Framework\Exception\ClassNotFoundException;
use Framework\Exception\FunctionNotFoundException;
use Framework\Library\Arr;
use Psr\Container\ContainerInterface;
use StdClass;

class Container implements ContainerInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    protected static $instance;
    /**
     * @var StdClass[]
     */
    protected $instances = [];
    /**
     * @var string[]
     */
    protected $bind = [];

    public static function getInstance(): Container
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public static function setInstance(StdClass $instance): void
    {
        static::$instance = $instance;
    }

    /**
     * get instance
     * @param string $abstract
     * @param array  $vars
     * @param bool   $newInstance
     * @return object
     */
    public static function pull(string $abstract, array $vars = [], bool $newInstance = false): StdClass
    {
        return static::getInstance()->make($abstract, $vars, $newInstance);
    }

    /**
     * @param string $abstract
     * @return StdClass
     */
    public function get($abstract): StdClass
    {
        if ($this->has($abstract)) {
            return $this->make($abstract);
        }

        throw new ClassNotFoundException('class not exists: ' . $$abstract, $$abstract);
    }

    /**
     * @param mixed $abstract
     * @param mixed $concrete
     * @return Container
     */
    public function bind($abstract, $concrete = null): Container
    {
        if (is_array($abstract)) {
            $this->bind = array_merge($this->bind, $abstract);
        } elseif ($concrete instanceof Closure) {
            $this->bind[$abstract] = $concrete;
        } elseif (is_object($concrete)) {
            if (isset($this->bind[$abstract])) {
                $abstract = $this->bind[$abstract];
            }
            $this->instances[$abstract] = $concrete;
        } else {
            $this->bind[$abstract] = $concrete;
        }

        return $this;
    }

    public function instance(string $abstract, $instance): Container
    {
        if ($instance instanceof Closure) {
            $this->bind[$abstract] = $instance;
        } else {
            if (isset($this->bind[$abstract])) {
                $abstract = $this->bind[$abstract];
            }
            $this->instances[$abstract] = $instance;
        }
        return $this;
    }

    public function has($abstract): bool
    {
        return isset($this->bind[$abstract]) || isset($this->instances[$abstract]);
    }

    public function exists(string $abstract): bool
    {
        if (isset($this->bind[$abstract])) {
            $abstract = $this->bind[$abstract];
        }

        return isset($this->instances[$abstract]);
    }

    /**
     * make instance
     * @param string $abstract
     * @param array  $vars
     * @param bool   $newInstance
     * @return StdClass
     */
    public function make(string $abstract, array $vars = [], bool $newInstance = false): StdClass
    {
        if (isset($this->instances[$abstract]) && !$newInstance) {
            return $this->instances[$abstract];
        }

        if (!isset($this->bind[$abstract])) {
            $object = $this->invokeClass($abstract, $vars);
        } else {
            $concrete = $this->bind[$abstract];
            if (!($concrete instanceof Closure)) {
                return $this->make($concrete, $vars, $newInstance);
            }
            $object = $this->invokeFunction($concrete, $vars);
        }

        if (!$newInstance) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * unset instances
     * @param $abstract
     */
    public function delete($abstract): void
    {
        foreach ((array) $abstract as $name) {
            if (isset($this->instances[$name])) {
                unset($this->instances[$name]);
            }
        }
    }

    public function all(): array
    {
        return $this->instances;
    }

    public function flush(): void
    {
        $this->instances = [];
        $this->bind      = [];
    }

    public function invokeFunction(callable $function, array $vars = [])
    {
        try {
            $reflect = new \ReflectionFunction($function);
            $args = $this->bindParams($reflect, $vars);
            return call_user_func_array($function, $args);
        } catch (\ReflectionException $e) {
            throw new FunctionNotFoundException('function not exists: ' . $function . '()');
        }
    }

    public function invokeMethod(callable $method, array $vars = [])
    {
        try {
            if (!is_array($method)) {
                $reflect = new \ReflectionMethod($method);
            } else {
                $class = is_object($method[0]) ? $method[0] : $this->invokeClass($method[0]);
                $reflect = new \ReflectionMethod($class, $method[1]);
            }
            $args = $this->bindParams($reflect, $vars);
            return $reflect->invokeArgs(isset($class) ? $class : null, $args);
        } catch (\ReflectionException $e) {
            throw new Exception('method not exists: ' . (is_array($method) ? $method[0] . '::' . $method[1] : $method) . '()');
        }
    }

    public function invokeReflectMethod(StdClass $instance, \ReflectionFunctionAbstract $reflect, array $vars = [])
    {
        $args = $this->bindParams($reflect, $vars);
        // ReflectionFunctionAbstract 有两个子类
        // ReflectionFunction & ReflectionMethod 均实现了invokeArgs()方法
        return $reflect->invokeArgs($instance, $args);
    }

    public function invoke(callable $callable, array $vars = [])
    {
        if ($callable instanceof Closure) {
            return $this->invokeFunction($callable, $vars);
        }
        return $this->invokeMethod($callable, $vars);
    }

    public function invokeClass(string $class, array $vars = [])
    {
        try {
            $reflect = new \ReflectionClass($class);
            if ($reflect->hasMethod('__make')) {
                $method = new \ReflectionMethod($class, '__make');

                if ($method->isPublic() && $method->isStatic()) {
                    $args = $this->bindParams($method, $vars);
                    return $method->invokeArgs(null, $args);
                }
            }
            $constructor = $reflect->getConstructor();
            $args = $constructor ? $this->bindParams($constructor, $vars) : [];
            return $reflect->newInstanceArgs($args);
        } catch (\ReflectionException $e) {
            throw new ClassNotFoundException('class not exists: ' . $class, $class);
        }
    }

    /**
     * 绑定参数
     * @param \ReflectionFunctionAbstract $reflect
     * @param array                       $vars
     * @return array
     */
    protected function bindParams(\ReflectionFunctionAbstract $reflect, array $vars = []): array
    {
        if ($reflect->getNumberOfParameters() == 0) {
            return [];
        }
        $isAssoc = Arr::isAssoc($vars);
        $params  = $reflect->getParameters();
        $args    = [];
        foreach ($params as $param) {
            $name  = $param->getName();
            $class = $param->getClass();
            if ($class) {
                $args[] = $this->getObjectParam($class->getName(), $vars);
            } elseif (!$isAssoc && !empty($vars)) {
                $args[] = array_shift($vars);
            } elseif ($isAssoc && isset($vars[$name])) {
                $args[] = $vars[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \InvalidArgumentException('method param miss:' . $name);
            }
        }
        return $args;
    }

    protected function getObjectParam(string $className, array &$vars)
    {
        $array = $vars;
        $value = array_shift($array);

        if (!($value instanceof $className)) {
            $result = $this->make($className);
        } else {
            $result = $value;
            array_shift($vars);
        }

        return $result;
    }

    public function __set($name, $value)
    {
        $this->bind($name, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __isset($name): bool
    {
        return $this->has($name);
    }

    public function __unset($name): void
    {
        $this->delete($name);
    }

    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetGet($key)
    {
        return $this->make($key);
    }

    public function offsetSet($key, $value)
    {
        $this->bind($key, $value);
    }

    public function offsetUnset($key): void
    {
        $this->delete($key);
    }

    public function count()
    {
        return count($this->instances);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->instances);
    }
}