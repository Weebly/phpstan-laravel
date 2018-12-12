<?php declare(strict_types = 1);

namespace Webparking\PHPStan\Lumen;

use Illuminate\Auth\AuthManager;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\Facades\Facade;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\MethodReflection;
use Webparking\PHPStan\Lumen\Utils\AnnotationsHelper;
use PHPStan\Broker\ClassNotFoundException;

final class FacadeMethodExtension implements MethodsClassReflectionExtension, BrokerAwareExtension
{
    /**
     * @var \PHPStan\Broker\Broker
     */
    private $broker;

    /**
     * @var string[]
     */
    private $extensions = [
        AuthManager::class => 'guard',
        BroadcastManager::class => 'driver',
    ];

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
     * FacadeMethodExtension constructor.
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
        if ($classReflection->isSubclassOf(Facade::class)) {
            if (!isset($this->methods[$classReflection->getName()])) {

                /** @var \Illuminate\Support\Facades\Facade $class */
                $class = $classReflection->getName();
                $instance = $class::getFacadeRoot();

                $instanceReflection = $this->broker->getClass(get_class($instance));
                $this->methods[$classReflection->getName()] = $this->createMethods($classReflection, $instanceReflection);

                foreach ($this->annotationsHelper->getMixins($instanceReflection) as $mixin) {
                    try {
                        $mixinInstanceReflection = $this->broker->getClass($mixin);
                    } catch (ClassNotFoundException $e) {
                        continue;
                    }
                    $this->methods[$classReflection->getName()] += $this->createMethods($classReflection, $mixinInstanceReflection);
                }

                if (isset($this->extensions[$instanceReflection->getName()])) {
                    $extensionMethod = $this->extensions[$instanceReflection->getName()];
                    $extensionReflection = $this->broker->getClass(get_class($instance->$extensionMethod()));
                    $this->methods[$classReflection->getName()] += $this->createMethods($classReflection, $extensionReflection);
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
     * @param \PHPStan\Reflection\ClassReflection $instance
     *
     * @return \PHPStan\Reflection\MethodReflection[]
     *
     * @throws \PHPStan\ShouldNotHappenException
     */
    private function createMethods(ClassReflection $classReflection, ClassReflection $instance): array
    {
        $methods = [];
        foreach ($instance->getNativeReflection()->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[$method->getName()] = $this->methodReflectionFactory->create(
                $classReflection,
                $method,
                ReflectionMethodAlwaysStatic::class
            );
        }

        return $methods;
    }
}
