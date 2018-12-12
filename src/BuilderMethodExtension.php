<?php declare(strict_types = 1);

namespace Webparking\PHPStan\Lumen;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\MethodReflection;
use Webparking\PHPStan\Lumen\Utils\AnnotationsHelper;

final class BuilderMethodExtension implements MethodsClassReflectionExtension, BrokerAwareExtension
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
     * @var \Webparking\PHPStan\Lumen\MethodReflectionFactory
     */
    private $methodReflectionFactory;

    /**
     * @var AnnotationsHelper
     */
    private $annotationsHelper;

    /**
     * BuilderMethodExtension constructor.
     *
     * @param \Webparking\PHPStan\Lumen\MethodReflectionFactory $methodReflectionFactory
     * @param AnnotationsHelper $annotationsHelper
     */
    public function __construct(MethodReflectionFactory $methodReflectionFactory, AnnotationsHelper $annotationsHelper)
    {
        $this->methodReflectionFactory = $methodReflectionFactory;
        $this->annotationsHelper = $annotationsHelper;
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
        if (!isset($this->methods[$classReflection->getName()]) && (
                $classReflection->isSubclassOf(Model::class)
                || in_array(Builder::class, $this->annotationsHelper->getMixins($classReflection))
        )) {
            $builder = $this->broker->getClass(Builder::class);
            $this->methods[$classReflection->getName()] = $this->createWrappedMethods($classReflection, $builder);

            $queryBuilder = $this->broker->getClass(QueryBuilder::class);
            $this->methods[$classReflection->getName()] += $this->createMethods($classReflection, $queryBuilder);
        }

        if ($classReflection->getName() === Builder::class && !isset($this->methods[Builder::class])) {
            $queryBuilder = $this->broker->getClass(QueryBuilder::class);
            $this->methods[Builder::class] = $this->createMethods($classReflection, $queryBuilder);
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
     * @param \PHPStan\Reflection\ClassReflection $queryBuilder
     *
     * @return \PHPStan\Reflection\MethodReflection[]
     */
    private function createMethods(ClassReflection $classReflection, ClassReflection $queryBuilder): array
    {
        $methods = [];
        foreach ($queryBuilder->getNativeReflection()->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic()) {
                continue;
            }

            $methods[$method->getName()] = $this->methodReflectionFactory->create($classReflection, $method);
        }

        return $methods;
    }

    /**
     * @param ClassReflection $classReflection
     * @param \PHPStan\Reflection\ClassReflection $builder
     *
     * @return \PHPStan\Reflection\MethodReflection[]
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    private function createWrappedMethods(ClassReflection $classReflection, ClassReflection $builder): array
    {
        $methods = [];
        foreach ($builder->getNativeReflection()->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[$method->getName()] = $this->methodReflectionFactory->create(
                $classReflection,
                $method,
                ReflectionMethodAlwaysStatic::class
            );
        }

        return $methods;
    }
}
