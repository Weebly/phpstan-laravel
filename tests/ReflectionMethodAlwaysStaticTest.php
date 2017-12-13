<?php declare(strict_types = 1);

namespace Tests\Weebly\PHPStan\Laravel;

use PHPStan\Testing\TestCase;
use Weebly\PHPStan\Laravel\ReflectionMethodAlwaysStatic;

final class ReflectionMethodAlwaysStaticTest extends TestCase
{
    public function test()
    {
        $class = new class {
            public function testMethod() {}
        };

        $reflectionMethod = new ReflectionMethodAlwaysStatic(
            new \ReflectionMethod($class, 'testMethod')
        );

        $this->assertTrue($reflectionMethod->isStatic());
    }
}
