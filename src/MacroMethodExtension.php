<?php declare(strict_types = 1);

namespace Webparking\PHPStan\Lumen;

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
     * @param \PHPStan\Reflection\Php\PhpMethodReflectionFactory $methodReflectionFactory
     * @param \PHPStan\Type\FileTypeMapper $fileTypeMapper
     */
    public function __construct(PhpMethodReflectionFactory $methodReflectionFactory, FileTypeMapper $fileTypeMapper)
    {
        $this->methodReflectionFactory = $methodReflectionFactory;
        $this->fileTypeMapper = $fileTypeMapper;
    }

    /**
     * @inheritdoc
     */
    public function setBroker(Broker $broker)
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
                    $this->methods[$classReflection->getName()][$macro] = $this->createMethod(
                        $classReflection,
                        new \ReflectionFunction($callable),
                        $macro
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

    /**
     * @param \PHPStan\Reflection\ClassReflection $classReflection
     * @param \ReflectionFunction $functionReflection
     * @param string $methodName
     *
     * @return \PHPStan\Reflection\MethodReflection
     */
    private function createMethod(ClassReflection $classReflection, \ReflectionFunction $functionReflection, string $methodName): MethodReflection
    {

        $phpDocParameterTypes = [];
        $phpDocReturnType = null;
        if ($functionReflection->getFileName() !== false && $functionReflection->getDocComment() !== false) {
            $resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
                $functionReflection->getFileName(),
                null,
                $functionReflection->getDocComment()
            );

            $phpDocParameterTypes = array_map(function (ParamTag $tag): Type {
                return $tag->getType();
            }, $resolvedPhpDoc->getParamTags());
            $phpDocReturnType = $resolvedPhpDoc->getReturnTag() !== null ? $resolvedPhpDoc->getReturnTag()->getType() : null;
        }

        return $this->methodReflectionFactory->create(
            $classReflection,
            new ReflectionMethodFunctionProxy($classReflection->getName(), $methodName, $functionReflection),
            $phpDocParameterTypes,
            $phpDocReturnType
        );
    }
}
