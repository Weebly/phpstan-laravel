<?php declare(strict_types = 1);

namespace Webparking\PHPStan\Lumen;

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
