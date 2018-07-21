<?php declare(strict_types = 1);

namespace Weebly\PHPStan\Laravel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\MethodReflection;
use Weebly\PHPStan\Laravel\Utils\AnnotationsHelper;
use PHPStan\Type\Type;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VoidType;
use PHPStan\Type\UnionType;
use PHPStan\Type\StringType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IterableType;
use PHPStan\Type\IntegerType;
use PHPStan\Reflection\Php\NativeBuiltinMethodReflection;
use Weebly\PHPStan\Laravel\ReflectionMethodAlwaysStatic;
use Weebly\PHPStan\Laravel\Types\WhereClosureType;
use PHPStan\Reflection\Php\PhpMethodReflectionFactory;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\PassedByReference;
use ReflectionMethod;

final class BuilderMethodExtension implements MethodsClassReflectionExtension, BrokerAwareExtension
{
    /**
     * @var \PHPStan\Broker\Broker
     */
    private $broker;

    /**
     * @var \PHPStan\Reflection\MethodReflection[][]
     */
    private $methods = [];

    /**
     * @var \PHPStan\Reflection\Php\PhpMethodReflectionFactory
     */
    private $methodReflectionFactory;

    /**
     * @var AnnotationsHelper
     */
    private $annotationsHelper;

    public function __construct(
        PhpMethodReflectionFactory $methodReflectionFactory,
        AnnotationsHelper $annotationsHelper
    ) {
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
        if (
            $classReflection->isSubclassOf(Model::class) ||
            $classReflection->getName() === HasMany::class ||
            $classReflection->getName() === Builder::class
        ) {
            if ($methodName === 'where') {
                $phpDocParameterTypes = [
                    'column' => new UnionType([
                        // TODO array support
                        new StringType(),
                        new WhereClosureType(),
                    ]),
                    'operator' => new UnionType([
                        new IntegerType(),
                        new StringType(),
                        new VoidType(),
                    ]),
                    'value' => new UnionType([
                        new StringType(),
                        new VoidType(),
                    ]),
                    'boolean' => new UnionType([
                        new StringType(),
                        new VoidType(),
                    ]),
                ];
                $methodReflection = new ReflectionMethod(Builder::class, 'where');
                $returnType = new ObjectType(Builder::class);
            } elseif ($methodName === 'orderBy') {
                $phpDocParameterTypes = [
                    new StringType(),
                    new StringType(),
                ];
                $methodReflection = new ReflectionMethod(QueryBuilder::class, 'orderBy');
                $returnType = new ObjectType(Builder::class);
            } elseif ($methodName === 'with' || $methodName === 'withCount') {
                $phpDocParameterTypes = [
                    new ArrayType(new UnionType([
                        new IntegerType(),
                        new StringType(),
                    ]), new UnionType([
                        new WhereClosureType(),
                        new StringType(),
                    ])),
                ];
                $methodReflection = new ReflectionMethod(Builder::class, $methodName);
                $returnType = new ObjectType(Builder::class);
            } elseif ($methodName === 'groupBy') {
                $phpDocParameterTypes = [
                    new StringType(),
                ];
                $methodReflection = new ReflectionMethod(QueryBuilder::class, 'groupBy');
                $returnType = new ObjectType(Builder::class);
            } elseif ($methodName === 'create') {
                $phpDocParameterTypes = [
                    new ArrayType(new StringType(), new StringType()),
                ];
                $methodReflection = new ReflectionMethod(Builder::class, 'create');

                if ($classReflection->isSubclassOf(Model::class)) {
                    $returnType = new ObjectType($classReflection->getName());
                } else {
                    $returnType = new ObjectType(Model::class);
                }
            } elseif ($methodName === 'find') {
                $phpDocParameterTypes = [
                    new UnionType([
                        new StringType(),
                        new ArrayType(new StringType(), new StringType()),
                    ]),
                ];
                $methodReflection = new ReflectionMethod(Builder::class, 'find');

                $returnType = null;
            } elseif ($methodName === 'firstOrNew' || $methodName === 'first') {
                $phpDocParameterTypes = [
                    new UnionType([
                        new VoidType(),
                        new StringType(),
                    ]),
                ];
                $methodReflection = new ReflectionMethod(Builder::class, $methodName);

                if ($classReflection->isSubclassOf(Model::class)) {
                    $returnType = new ObjectType($classReflection->getName());
                } else {
                    $returnType = new ObjectType(Model::class);
                }
            } else {
                return false;
            }
            $this->methods[$classReflection->getName()][$methodName] = $this->createMethod(
                $classReflection,
                $methodReflection,
                $phpDocParameterTypes,
                $returnType
            );
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return $this->methods[$classReflection->getName()][$methodName];
    }

    private function createMethod(
        ClassReflection $classReflection,
        ReflectionMethod $methodReflection,
        array $phpDocParameterTypes,
        ?Type $returnType
    ): PhpMethodReflection {
        return $this->methodReflectionFactory->create(
            $classReflection,
            null,
            new ReflectionMethodAlwaysStatic($methodReflection),
            $phpDocParameterTypes,
            $returnType,
            null,
            false,
            false,
            false
        );
    }
}
