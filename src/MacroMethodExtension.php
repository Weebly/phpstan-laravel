<?php declare(strict_types = 1);

namespace Weebly\PHPStan\Laravel;

use Illuminate\Support\Traits\Macroable;
use PHPStan\Broker\Broker;
use PHPStan\PhpDoc\Tag\ParamTag;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\PhpMethodReflectionFactory;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Type;

final class MacroMethodExtension implements MethodsClassReflectionExtension, BrokerAwareExtension
{
    /**
     * @var \PHPStan\Broker\Broker
     */
    private $broker;

    /**
     * @var \PHPStan\Reflection\MethodReflection[]
     */
    private $methods = [];

    /**
     * @var \PHPStan\Reflection\Php\PhpMethodReflectionFactory
     */
    private $methodReflectionFactory;

    /**
     * @var \PHPStan\Type\FileTypeMapper
     */
    private $fileTypeMapper;

    /**
     * MacroMethodExtension constructor.
     *
     * @param MethodReflectionFactory $methodReflectionFactory
     * @param \PHPStan\Type\FileTypeMapper $fileTypeMapper
     */
    public function __construct(MethodReflectionFactory $methodReflectionFactory, FileTypeMapper $fileTypeMapper)
    {
        $this->methodReflectionFactory = $methodReflectionFactory;
        $this->fileTypeMapper = $fileTypeMapper;
    }

    /**
     * @inheritdoc
     */
    public function setBroker(Broker $broker): void
    {
        $this->broker = $broker;
    }

    /**
     * @inheritdoc
     */
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if ($classReflection->hasTraitUse(Macroable::class)) {
            /** @var \Illuminate\Support\Traits\Macroable $macroable */
            $macroable = $classReflection->getName();

            if ($macroable::hasMacro($methodName) && !isset($this->methods[$classReflection->getName()])) {
                $refObject = new \ReflectionClass($macroable);
                $refProperty = $refObject->getProperty('macros');
                $refProperty->setAccessible(true);

                foreach ($refProperty->getValue() as $macro => $callable) {
                    $this->methods[$classReflection->getName()][$macro] = $this->methodReflectionFactory->create(
                        $classReflection,
                        new ReflectionMethodFunctionProxy($classReflection->getName(), $macro, new \ReflectionFunction($callable))
                    );
                }
            }
        }

        return isset($this->methods[$classReflection->getName()][$methodName]);
    }

    /**
     * @inheritdoc
     */
    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return $this->methods[$classReflection->getName()][$methodName];
    }

}
