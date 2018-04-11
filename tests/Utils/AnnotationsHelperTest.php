<?php

namespace Tests\Weebly\PHPStan\Laravel\Utils;

use PHPUnit\Framework\TestCase;
use PHPStan\Reflection\ClassReflection;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Weebly\PHPStan\Laravel\Utils\AnnotationsHelper;

class AnnotationsHelperTest extends TestCase
{
    public function testGetMixins()
    {
        $annotationHelper = new AnnotationsHelper();
        $reflection = $this->makeClassReflectionMock(<<<EOF
/**
 * Class AnnotationsHelperTest
 * @package Tests\Weebly\PHPStan\Laravel\Utils
 * @mixin \PHPUnit\Framework\TestCase
 *
 * @mixin PHPStan\Reflection\ClassReflection
 */
EOF
);
        $this->assertEquals(
            [TestCase::class, ClassReflection::class],
            $annotationHelper->getMixins($reflection)
        );
        $this->assertEquals(
            [],
            $annotationHelper->getMixins($this->makeClassReflectionMock(''))
        );
    }

    /**
     * @param string $docBlock
     * @return ClassReflection|MockObject
     */
    private function makeClassReflectionMock(string $docBlock)
    {
        $reflectionClass = $this
            ->getMockBuilder(ReflectionClass::class)
            ->disableOriginalConstructor()
            ->getMock();
        $reflectionClass->method('getDocComment')->willReturn($docBlock);

        $classReflection = $this
            ->getMockBuilder(ClassReflection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $classReflection->method('getNativeReflection')->willReturn($reflectionClass);

        return $classReflection;
    }
}
