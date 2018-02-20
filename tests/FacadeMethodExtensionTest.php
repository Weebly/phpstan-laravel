<?php

namespace Tests\Weebly\PHPStan\Laravel;

use PHPStan\Testing\TestCase;
use PHPStan\Broker\Broker;
use Weebly\PHPStan\Laravel\MethodReflectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPStan\Reflection\Php\PhpMethodReflectionFactory;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\FileTypeMapper;
use Weebly\PHPStan\Laravel\FacadeMethodExtension;
use Illuminate\Support\Facades\Facade;

/**
 * @package Tests\Weebly\PHPStan\Laravel
 */
class FacadeMethodExtensionTest extends TestCase
{
    /**
     * @var Broker
     */
    private $broker;

    public function testHasMethod()
    {
        // Native accessor method
        $this->assertTrue($this->hasMethod(TestFacade::class, 'someMethod'));
        $this->assertFalse($this->hasMethod(TestFacade::class, 'fakeMethod'));
        // Method from accessor mixin
        $this->assertTrue($this->hasMethod(TestFacade::class, 'table'));
        $this->assertTrue($this->hasMethod(TestFacade::class, 'shouldUse'));
    }

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->broker = $this->createBroker();
    }

    /**
     * @return MethodReflectionFactory
     */
    private function makeMethodReflectionFactoryMock()
    {
        /** @var MockObject|PhpMethodReflectionFactory $phpMethodReflectionFactory */
        $phpMethodReflectionFactory = $this
            ->getMockBuilder(PhpMethodReflectionFactory::class)
            ->getMockForAbstractClass();
        $methodReflectionMock = $this
            ->getMockBuilder(PhpMethodReflection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $phpMethodReflectionFactory->method('create')->willReturn($methodReflectionMock);
        /** @var FileTypeMapper $fileTypeMapper */
        $fileTypeMapper = $this->getContainer()->createInstance(FileTypeMapper::class);

        return new MethodReflectionFactory($phpMethodReflectionFactory, $fileTypeMapper);
    }

    /**
     * Check existence of the method in given class
     *
     * @param string $className
     * @param string $methodName
     * @return bool
     */
    private function hasMethod(string $className, string $methodName): bool
    {
        $extension = new FacadeMethodExtension($this->makeMethodReflectionFactoryMock());
        $extension->setBroker($this->broker);

        return $extension->hasMethod($this->broker->getClass($className), $methodName);
    }
}

/**
 * @mixin \Illuminate\Database\Connection
 * @mixin \Illuminate\Auth\AuthManager
 */
class TestFacadeAccessor {
    function someMethod() {
        return true;
    }
}

class TestFacade extends Facade {
    /**
     * @inheritdoc
     */
    protected static function getFacadeAccessor()
    {
        return new TestFacadeAccessor();
    }
}
