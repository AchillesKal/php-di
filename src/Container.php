<?php declare(strict_types=1);

namespace AchillesKal\DI;

use ReflectionClass;

class Container
{
    private $bindings = [];

    public function set($abstract, callable $factory)
    {
        $this->bindings[$abstract] = $factory;
    }

    public function get($abstract)
    {
        if(isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]($this);
        }

        $reflection = new ReflectionClass($abstract);
        $dependencies = $this->buildDependencies($reflection);

        return $reflection->newInstanceArgs($dependencies);
    }

    public function debug() {
        var_dump($this->bindings);
    }

    private function buildDependencies(ReflectionClass $reflection)
    {
        if(!$constructor = $reflection->getConstructor()) {
            return [];
        }

        $params = $constructor->getParameters();

        return array_map(function($param) {
            if(!$type = $param->getType()) {
                throw new \RuntimeException();
            }

            return $this->get($type->getName());
        }, $params);
    }
}
