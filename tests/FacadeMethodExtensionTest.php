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
use Weebly\PHPStan\Laravel\Utils\AnnotationsHelper;
use PHPStan\Broker\ClassNotFoundException;
use Illuminate\Database\Connection;
use Illuminate\Auth\AuthManager;
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
        $testFacade = new class() extends Facade{
            /**
             * @inheritdoc
             */
            protected static function getFacadeAccessor()
            {
                return new class() {
                    public function someMethod() {
                        return true;
                    }
                };
            }
        };

        try {
            // Native accessor method
            $this->assertTrue($this->hasMethod(get_class($testFacade), 'someMethod'));
            $this->assertFalse($this->hasMethod(get_class($testFacade), 'fakeMethod'));
            // Method from accessor mixin
            $this->assertTrue($this->hasMethod(get_class($testFacade), 'table'));
            $this->assertTrue($this->hasMethod(get_class($testFacade), 'shouldUse'));
        } catch (ClassNotFoundException $e) {
            $this->markTestIncomplete($e->getMessage());
        }
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
     * @return AnnotationsHelper|MockObject
     */
    private function makeAnnotationsHelperMock()
    {
        $annotationsHelper = $this
            ->getMockBuilder(AnnotationsHelper::class)
            ->getMock();
        $annotationsHelper->method('getMixins')->willReturn([Connection::class, AuthManager::class, 'Fake']);

        return $annotationsHelper;
    }

    /**
     * Check existence of the method in given class
     *
     * @param string $className
     * @param string $methodName
     * @return bool
     * @throws ClassNotFoundException
     */
    private function hasMethod(string $className, string $methodName): bool
    {
        $extension = new FacadeMethodExtension(
            $this->makeMethodReflectionFactoryMock(),
            $this->makeAnnotationsHelperMock()
        );
        $extension->setBroker($this->broker);

        return $extension->hasMethod($this->broker->getClass($className), $methodName);
    }
}
