<?php declare(strict_types = 1);

namespace Weebly\PHPStan\Laravel;

class ReflectionMethodFunctionProxy extends \ReflectionMethod
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var \ReflectionFunction
     */
    private $reflectionFunction;

    /**
     * @param string $className
     * @param string $methodName
     * @param \ReflectionFunction $reflectionFunction
     */
    public function __construct(string $className, string $methodName, \ReflectionFunction $reflectionFunction)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->reflectionFunction = $reflectionFunction;
    }

    public function getDeclaringClass()
    {
        return new \ReflectionClass($this->className);
    }

    public function isAbstract()
    {
        return false;
    }

    public function isConstructor()
    {
        return false;
    }

    public function isDestructor()
    {
        return false;
    }

    public function isFinal()
    {
        return false;
    }

    public function isPrivate()
    {
        return false;
    }

    public function isProtected()
    {
        return false;
    }

    public function isPublic()
    {
        return true;
    }

    public function isStatic()
    {
        return false;
    }

    public function setAccessible($accessible)
    {
        //
    }

    public function getClosureScopeClass()
    {
        return $this->reflectionFunction->getClosureScopeClass();
    }

    public function getClosureThis()
    {
        return $this->reflectionFunction->getClosureThis();
    }

    public function getDocComment()
    {
        return $this->reflectionFunction->getDocComment();
    }

    public function getEndLine()
    {
        return $this->reflectionFunction->getEndLine();
    }

    public function getExtension()
    {
        return $this->reflectionFunction->getExtension();
    }

    public function getExtensionName()
    {
        return $this->reflectionFunction->getExtensionName();
    }

    public function getFileName()
    {
        return $this->reflectionFunction->getFileName();
    }

    public function getName()
    {
        return $this->getNamespaceName() . '\\' . $this->getShortName();
    }

    public function getNamespaceName()
    {
        return (new \ReflectionClass($this->className))->getNamespaceName();
    }

    public function getNumberOfParameters()
    {
        return $this->reflectionFunction->getNumberOfParameters();
    }

    public function getNumberOfRequiredParameters()
    {
        return $this->reflectionFunction->getNumberOfRequiredParameters();
    }

    public function getParameters()
    {
        return $this->reflectionFunction->getParameters();
    }

    public function getReturnType()
    {
        return $this->reflectionFunction->getReturnType();
    }

    public function getShortName()
    {
        return $this->methodName;
    }

    public function getStartLine()
    {
        return $this->reflectionFunction->getStartLine();
    }

    public function getStaticVariables()
    {
        return $this->reflectionFunction->getStaticVariables();
    }

    public function hasReturnType()
    {
        return $this->reflectionFunction->hasReturnType();
    }

    public function inNamespace()
    {
        return (new \ReflectionClass($this->className))->inNamespace();
    }

    public function isClosure()
    {
        return false;
    }

    public function isDeprecated()
    {
        return $this->reflectionFunction->isDeprecated();
    }

    public function isGenerator()
    {
        return $this->reflectionFunction->isGenerator();
    }

    public function isInternal()
    {
        return $this->reflectionFunction->isInternal();
    }

    public function isUserDefined()
    {
        return $this->reflectionFunction->isUserDefined();
    }

    public function isVariadic()
    {
        return $this->reflectionFunction->isVariadic();
    }

    public function returnsReference()
    {
        return $this->reflectionFunction->returnsReference();
    }
}
