<?php declare(strict_types = 1);

namespace Tests\Weebly\PHPStan\Laravel;

use PHPStan\Testing\TestCase;
use PHPStan\Broker\Broker;
use Weebly\PHPStan\Laravel\MethodReflectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPStan\Reflection\Php\PhpMethodReflectionFactory;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\FileTypeMapper;
use Illuminate\Database\Eloquent\Model;
use Weebly\PHPStan\Laravel\BuilderMethodExtension;
use stdClass;
use Illuminate\Database\Eloquent\Builder;
use Weebly\PHPStan\Laravel\Utils\AnnotationsHelper;
use PHPStan\Broker\ClassNotFoundException;

/**
 * @package Tests\Weebly\PHPStan\Laravel
 */
class BuilderMethodExtensionTest extends TestCase
{
    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var string
     */
    private $childOfModelClassName;

    public function testHasMethodInSubclassOfModel()
    {
        try {
            $this->assertFalse($this->hasMethod(stdClass::class, 'find'));
            $this->assertTrue($this->hasMethod($this->childOfModelClassName, 'find'));
            $this->assertFalse($this->hasMethod(stdClass::class, 'select'));
            $this->assertTrue($this->hasMethod($this->childOfModelClassName, 'select'));
        } catch (ClassNotFoundException $e) {
            $this->markTestIncomplete($e->getMessage());
        }
    }

    public function testHasMethodInClassWithMixinAnnotation()
    {
        try {
            $this->assertFalse($this->hasMethod(stdClass::class, 'find'));
            $this->assertTrue($this->hasMethod(stdClass::class, 'find', true));
            $this->assertFalse($this->hasMethod(stdClass::class, 'select'));
            $this->assertTrue($this->hasMethod(stdClass::class, 'select', true));
        } catch (ClassNotFoundException $e) {
            $this->markTestIncomplete($e->getMessage());
        }
    }

    public function testHasMethodInBuilder()
    {
        try {
            $this->assertFalse($this->hasMethod(stdClass::class, 'find'));
            $this->assertTrue($this->hasMethod(Builder::class, 'find'));
            $this->assertFalse($this->hasMethod(stdClass::class, 'select'));
            $this->assertTrue($this->hasMethod(Builder::class, 'select'));
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
        $this->childOfModelClassName = get_class(new class() extends Model {});
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
     * @param bool $addBuilderMixin
     * @return bool
     * @throws ClassNotFoundException
     */
    private function hasMethod(string $className, string $methodName, bool $addBuilderMixin = false): bool
    {
        $extension = new BuilderMethodExtension(
            $this->makeMethodReflectionFactoryMock(),
            $this->makeAnnotationsHelperMock($addBuilderMixin)
        );
        $extension->setBroker($this->broker);

        return $extension->hasMethod($this->broker->getClass($className), $methodName);
    }

    /**
     * @param bool $withBuilder
     * @return AnnotationsHelper|MockObject
     */
    private function makeAnnotationsHelperMock(bool $withBuilder = false)
    {
        $annotationsHelper = $this
            ->getMockBuilder(AnnotationsHelper::class)
            ->getMock();
        $annotationsHelper->method('getMixins')->willReturn($withBuilder ? [Builder::class] : []);

        return $annotationsHelper;
    }
}
