<?php declare(strict_types = 1);

namespace Tests\Webparking\PHPStan\Lumen;

use PHPStan\Testing\TestCase;
use Webparking\PHPStan\Lumen\ReflectionMethodAlwaysStatic;

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
