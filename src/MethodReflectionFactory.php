<?php declare(strict_types = 1);

namespace Webparking\PHPStan\Lumen;

use PHPStan\Broker\Broker;
use PHPStan\PhpDoc\PhpDocBlock;
use PHPStan\PhpDoc\Tag\ParamTag;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\PhpMethodReflectionFactory;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Type;

final class MethodReflectionFactory
{
    /**
     * @var \PHPStan\Reflection\Php\PhpMethodReflectionFactory
     */
    private $methodReflectionFactory;

    /**
     * @var \PHPStan\Type\FileTypeMapper
     */
    private $fileTypeMapper;

    /**
     * MethodReflectionFactory constructor.
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
     * @param \PHPStan\Reflection\ClassReflection $classReflection
     * @param \ReflectionMethod $methodReflection
     * @param string|null $methodWrapper
     *
     * @return \PHPStan\Reflection\MethodReflection
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    public function create(ClassReflection $classReflection, \ReflectionMethod $methodReflection, string $methodWrapper = null): MethodReflection
    {
        $phpDocParameterTypes = [];
        $phpDocReturnType = null;
        if ($methodReflection->getDocComment() !== false) {
            $phpDocBlock = PhpDocBlock::resolvePhpDocBlockForMethod(
                Broker::getInstance(),
                $methodReflection->getDocComment(),
                $methodReflection->getDeclaringClass()->getName(),
                $methodReflection->getName(),
                $methodReflection->getFileName()
            );

            $resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc(
                $phpDocBlock->getFile(),
                $phpDocBlock->getClass(),
                $phpDocBlock->getDocComment()
            );
            $phpDocParameterTypes = array_map(function (ParamTag $tag): Type {
                return $tag->getType();
            }, $resolvedPhpDoc->getParamTags());
            $phpDocReturnType = $resolvedPhpDoc->getReturnTag() !== null ? $resolvedPhpDoc->getReturnTag()->getType() : null;
        }

        if ($methodWrapper) {
            $methodReflection = new $methodWrapper($methodReflection);
        }

        return $this->methodReflectionFactory->create(
            $classReflection,
            $methodReflection,
            $phpDocParameterTypes,
            $phpDocReturnType
        );
    }
}
