<?php declare(strict_types = 1);

namespace Tests\Weebly\PHPStan\Laravel {

use PHPStan\Testing\TestCase;
use Weebly\PHPStan\Laravel\ReflectionMethodFunctionProxy;

final class ReflectionMethodFunctionProxyTest extends TestCase
{
    /**
     * @var \ReflectionFunction
     */
    private $reflectionFunction;

    /**
     * @var \Weebly\PHPStan\Laravel\ReflectionMethodFunctionProxy
     */
    private $reflectionMethod;

    public function setUp()
    {
        $this->reflectionFunction = new \ReflectionFunction(
            function (string $foo, int $bar): bool {
                return $bar > 0;
            }
        );

        $this->reflectionMethod = new ReflectionMethodFunctionProxy(
            'Foo\TestThing',
            'testMethodFoo',
            $this->reflectionFunction
        );
    }

    public function testInterestingThings()
    {
        $this->assertSame(
            'Foo\TestThing',
            $this->reflectionMethod->getDeclaringClass()->getName()
        );

        $this->assertTrue($this->reflectionMethod->isPublic());

        $this->assertNull($this->reflectionMethod->setAccessible(true));

        $this->assertSame('Foo', $this->reflectionMethod->getNamespaceName());

        $this->assertSame('testMethodFoo', $this->reflectionMethod->getName());
        $this->assertSame('testMethodFoo', $this->reflectionMethod->getShortName());

        $this->assertTrue($this->reflectionMethod->inNamespace());
    }

    /**
     * @dataProvider falseMethods
     */
    public function testFalse(string $method)
    {
        $this->assertFalse($this->reflectionMethod->$method());
    }

    /**
     * @dataProvider returnsMethodValue
     */
    public function testMethodRetuns(string $method)
    {
        $value = $this->reflectionFunction->$method();

        if (is_object($value)) {
            $this->assertSame(
                get_class($value),
                get_class($this->reflectionMethod->$method())
            );
        } elseif (is_array($value)) {
            $this->assertSame(
                count($value),
                count($this->reflectionMethod->$method())
            );
        } else {
            $this->assertSame($value, $this->reflectionMethod->$method());
        }
    }

    public function falseMethods(): array
    {
        return array_map(function (string $method): array {
            return [$method];
        }, $this->methodsReturnFalse);
    }

    public function returnsMethodValue(): array
    {
        return array_map(function (string $method): array {
            return [$method];
        }, $this->methodsReturnFunctionValues);
    }

    /**
     * @var string[]
     */
    private $methodsReturnFalse = [
        'isAbstract',
        'isConstructor',
        'isDestructor',
        'isFinal',
        'isPrivate',
        'isProtected',
        'isStatic',
        'isClosure',
    ];

    /**
     * @var string[]
     */
    private $methodsReturnFunctionValues = [
        'getClosureScopeClass',
        'getClosureThis',
        'getDocComment',
        'getEndLine',
        'getExtension',
        'getExtensionName',
        'getFileName',
        'getNumberOfParameters',
        'getNumberOfRequiredParameters',
        'getParameters',
        'getReturnType',
        'getStartLine',
        'getStaticVariables',
        'hasReturnType',
        'isDeprecated',
        'isGenerator',
        'isInternal',
        'isUserDefined',
        'isVariadic',
        'returnsReference',
    ];
}
}

namespace Foo {
    class TestThing {}
};
