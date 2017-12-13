<?php declare(strict_types = 1);

namespace Weebly\PHPStan\Laravel;

final class ReflectionMethodAlwaysStatic extends \ReflectionMethod
{
    /**
     * @param \ReflectionMethod $reflectionMethod
     *
     * @throws \ReflectionException
     */
    public function __construct(\ReflectionMethod $reflectionMethod)
    {
        parent::__construct($reflectionMethod->getDeclaringClass()->getName(), $reflectionMethod->getName());
    }

    public function isStatic()
    {
        return true;
    }
}
