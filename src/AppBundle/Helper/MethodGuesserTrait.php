<?php

namespace AppBundle\Helper;

use AppBundle\Exception\Exception;

trait MethodGuesserTrait {

    /**
     * @param string|object $class
     * @param string $propertyName
     * @return string
     * @throws Exception
     */
    protected function findGetter($class, $propertyName) {
        return $this->checkMethods($class, [
            'get'. ucfirst($propertyName),
            'has'. ucfirst($propertyName),
            'is'. ucfirst($propertyName)
        ]);
    }

    /**
     * @param string|object $class
     * @param string $propertyName
     * @return string
     * @throws Exception
     */
    protected function findSetter($class, $propertyName) {
        return $this->checkMethods($class, ['set'. ucfirst($propertyName)]);
    }

    /**
     * @param string|object $class
     * @param array<string> $methods
     * @return string
     * @throws Exception
     */
    protected function checkMethods($class, array $methods) {
        $foundMethod = false;
        foreach ($methods as $method) {
            if (method_exists($class, $method)) {
                $foundMethod = $method;
                break;
            };
        }
        if (!$foundMethod) {
            $methods = implode(', ', $methods);
            throw new Exception("There is no method(s) $methods in model class $class");
        }
        return $foundMethod;
    }
}