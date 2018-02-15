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

/**
 * @package Tests\Weebly\PHPStan\Laravel
 */
class BuilderMethodExtensionTest extends TestCase
{
    /**
     * @var Broker
     */
    private $broker;

    public function testHasMethodInSubclassOfModel()
    {
        $this->assertFalse($this->hasMethod(stdClass::class, 'find'));
        $this->assertTrue($this->hasMethod(ChildOfModel::class, 'find'));
        $this->assertFalse($this->hasMethod(stdClass::class, 'select'));
        $this->assertTrue($this->hasMethod(ChildOfModel::class, 'select'));
    }

    public function testHasMethodInClassWithMixinAnnotation()
    {
        $this->assertFalse($this->hasMethod(stdClass::class, 'find'));
        $this->assertTrue($this->hasMethod(HasAMixinAnnotation::class, 'find'));
        $this->assertFalse($this->hasMethod(stdClass::class, 'select'));
        $this->assertTrue($this->hasMethod(HasAMixinAnnotation::class, 'select'));
    }

    public function testHasMethodInBuilder()
    {
        $this->assertFalse($this->hasMethod(stdClass::class, 'find'));
        $this->assertTrue($this->hasMethod(Builder::class, 'find'));
        $this->assertFalse($this->hasMethod(stdClass::class, 'select'));
        $this->assertTrue($this->hasMethod(Builder::class, 'select'));
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
        $extension = new BuilderMethodExtension($this->makeMethodReflectionFactoryMock());
        $extension->setBroker($this->broker);

        return $extension->hasMethod($this->broker->getClass($className), $methodName);
    }
}

class ChildOfModel extends Model {}

/**
 * Some description
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @method string getString()
 */
class HasAMixinAnnotation {}
