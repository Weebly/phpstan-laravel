<?php declare(strict_types = 1);

namespace Weebly\PHPStan\Laravel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\MethodReflection;

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
     * @var \Weebly\PHPStan\Laravel\MethodReflectionFactory
     */
    private $methodReflectionFactory;

    /**
     * BuilderMethodExtension constructor.
     *
     * @param \Weebly\PHPStan\Laravel\MethodReflectionFactory $methodReflectionFactory
     */
    public function __construct(MethodReflectionFactory $methodReflectionFactory)
    {
        $this->methodReflectionFactory = $methodReflectionFactory;
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
        if ($classReflection->isSubclassOf(Model::class) && !isset($this->methods[$classReflection->getName()])) {
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
