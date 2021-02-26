<?php


namespace Bx\Model\Gen\Fields;


use Bitrix\Main\Type\DateTime;
use Bx\Model\Gen\Interfaces\FieldNameModifierInterface;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;

class DateTimeField extends BaseFieldGenerator
{
    public function __construct(
        string $name,
        FieldNameModifierInterface $fieldNameModifier,
        PhpNamespace $namespace = null
    )
    {
        if ($namespace instanceof PhpNamespace) {
            $this->initUse($namespace);
        }

        parent::__construct($name, DateTime::class, $fieldNameModifier);
    }

    public function initGetter(ClassType $class): Method
    {
        $method = parent::initGetter($class);
        $method->setReturnNullable();
        $method->setComment("@return ?DateTime");
        $method->setBody("\treturn \$this[\"{$this->name}\"] instanceof DateTime ? \$this[\"{$this->name}\"] : null;");
        return $method;
    }

    public function initSetter(ClassType $class): Method
    {
        $method = parent::initSetter($class);
        $method->setComment("@param DateTime \$value\n@return void");
        return $method;
    }

    public function initUse(PhpNamespace $namespace)
    {
        $namespace->addUse(DateTime::class);
    }
}