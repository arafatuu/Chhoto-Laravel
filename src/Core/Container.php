<?php

namespace App\Core;

use App\Core\Exceptions\DuplicateDependencyException;
use App\Core\Exceptions\UnresolvedParameterException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class Container
{
    /**
     * Array to store bindings for dependency injection.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * Array to store resolved dependencies.
     *
     * @var array
     */
    protected $resolved = [];

    /**
     * Array to store instances of resolved dependencies.
     *
     * @var array
     */
    public $instances = [];

    /**
     * Bind a callback to the specified identifier for dependency resolution.
     *
     * @param string $abstract
     * @param callable $callback
     * @param bool $shared
     * @throws \DuplicateDependencyException
     */
    public function bind($abstract, $callback, $shared = false)
    {
        if (array_key_exists($abstract, $this->bindings)) {
            throw new DuplicateDependencyException($abstract);
        }

        $this->bindings[$abstract] = [
            'concrete' => $callback,
            'shared' => $shared,
        ];
    }

    /**
     * Register a singleton binding with the container.
     *
     * @param string $abstract
     * @param callable $callback
     * @return void
     */
    public function singleton($abstract, $callback)
    {
        $this->bind($abstract, $callback, true);
    }

    /**
     * Check if an abstract or identifier is bound in the container.
     *
     * @param string $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return array_key_exists($abstract, $this->bindings);
    }

    /**
     * Create an instance of the specified identifier using dependency injection.
     *
     * @param string $abstract
     * @param array $args
     * @return mixed|null 
     */
    public function make($abstract, $args = [])
    {
        if (array_key_exists($abstract, $this->bindings)) {
            $binding = $this->bindings[$abstract];
            $concrete = $binding['concrete'];

            if (is_callable($concrete)) {
                if ($binding['shared'] && $this->resolved($abstract)) {
                    return $this->instances[$abstract];
                }

                $instance = $concrete($this, $args);
                $this->pushResolvedInstance($abstract, $instance, $binding['shared']);
                return $instance;
            }

            return $concrete;
        }

        return $this->resolve($abstract, $args);
    }

    /**
     * Resolve dependencies and instantiate the specified class.
     *
     * @param string $abstract
     * @param array $args
     * @return mixed
     * @throws \ReflectionException
     * @throws \UnresolvedParameterException
     */
    public function resolve($abstract, $args = [])
    {
        $reflectionClass = new ReflectionClass($abstract);
        $reflectionClassName = $reflectionClass->getName();
        $constructor = $reflectionClass->getConstructor();
        $deps = $this->resolveDeps($constructor, $args, $reflectionClass);
        $instance = $reflectionClass->newInstanceArgs($deps);
        $this->pushResolvedInstance($reflectionClassName, $instance);
        return $instance;
    }

    /**
     * Resolve method dependencies based on reflection and provided arguments.
     *
     * @param string $method
     * @param array $args
     * @param ?string $class
     * @return array
     * @throws \ReflectionException
     * @throws \UnresolvedParameterException
     */
    public function resolveDeps($method, $args = [], $class = null)
    {
        if (!$method) return [];

        $deps = [];
        $nextArgInd = 0;

        $reflectionFunction = $this->getReflectionFunction($method, $class);
        $params = $reflectionFunction->getParameters();

        foreach ($params as $param) {
            $paramName = $param->getName();
            $paramReflectionClass = $this->getClass($param);

            if ($paramReflectionClass instanceof ReflectionClass) {
                $className = $paramReflectionClass->getName();
                $deps[] = $this->resolve($className);
            } else if (array_key_exists($nextArgInd, $args)) {
                $deps[] = $args[$nextArgInd++];
            } else if (!$param->isOptional()) {
                throw new UnresolvedParameterException($paramName);
            }
        }

        return $deps;
    }

    /**
     * Resolve reflection method or function.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    private function getReflectionFunction($method, $class = null)
    {
        if (is_null($class)) {
            if ($method instanceof ReflectionFunction) {
                return $method;
            }
            return new ReflectionFunction($method);
        }

        if ($method instanceof ReflectionMethod) {
            return $method;
        }
        return new ReflectionMethod($class, $method);
    }

    /**
     * Check if a dependency is resolved and instantiated.
     *
     * @param string $abstract
     * @return bool
     */
    public function resolved($abstract)
    {
        return array_key_exists($abstract, $this->resolved) && array_key_exists($abstract, $this->instances);
    }

    /**
     * Push a resolved instance to instances and mark it as resolved.
     *
     * @param string $abstract
     * @param mixed $instance
     * @param bool $shared
     * @return void
     */
    public function pushResolvedInstance($abstract, $instance, $shared = false)
    {
        $this->resolved[$abstract] = true;

        if ($shared) {
            $this->instances[$abstract] = $instance;
        }
    }

    /**
     * Get the ReflectionClass instance for the specified parameter type.
     *
     * @param \ReflectionParameter $parameter
     * @return \ReflectionClass|null
     * @throws \ReflectionException
     */
    public function getClass($parameter)
    {
        /**
         * @var \ReflectionNamedType $paramType
         */
        $paramType = $parameter->getType();

        if ($paramType && !$paramType->isBuiltin()) {
            return new ReflectionClass($paramType->getName());
        }
    }
}
