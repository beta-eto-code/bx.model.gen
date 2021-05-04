<?php


namespace Bx\Model\Gen\Fields;


use Bx\Model\Gen\Interfaces\FieldGeneratorInterface;
use Bx\Model\Gen\Interfaces\FieldNameModifierInterface;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;

abstract class BaseFieldGenerator implements FieldGeneratorInterface
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var FieldNameModifierInterface
     */
    private $nameModifier;
    /**
     * @var false
     */
    private $required;
    /**
     * @var false
     */
    private $primary;
    /**
     * @var bool
     */
    private $unique;
    /**
     * @var mixed
     */
    private $defaultValue;

    public function __construct(string $name, string $type, FieldNameModifierInterface $fieldNameModifier)
    {
        $this->name = $name;
        $this->type = $type;
        $this->nameModifier = $fieldNameModifier;
        $this->required = false;
        $this->primary = false;
    }

    /**
     * @param ClassType $class
     * @return Method
     */
    public function initGetter(ClassType $class): Method
    {
        $name = $this->getterName();
        $method = $class->addMethod($name);
        $method->setPublic();
        $method->setBody("\treturn ({$this->type})\$this[\"{$this->name}\"];");
        $method->addComment("@return {$this->type}");
        $method->setReturnType($this->type);

        return $method;
    }

    /**
     * @param ClassType $class
     * @return Method
     */
    public function initSetter(ClassType $class): Method
    {
        $name = $this->setterName();
        $method = $class->addMethod($name);
        $method->setPublic();
        $method->addParameter('value')->setType($this->type);
        $method->setBody("\t\$this[\"{$this->name}\"] = \$value;");
        $method->addComment("@param {$this->type} \$value");
        $method->addComment('@return void');

        return $method;
    }

    /**
     * @return string
     */
    public function getOriginalName(): string
    {
        return (string)$this->name;
    }

    /**
     * @return string
     */
    public function getSelectName(): string
    {
        return $this->nameModifier->nameForSelect($this->name);
    }

    /**
     * @return string
     */
    public function getSaveName(): string
    {
        return $this->nameModifier->nameForSave($this->name);
    }

    /**
     * @return string
     */
    public function getSortName(): string
    {
        return $this->nameModifier->nameForSort($this->name);
    }

    /**
     * @return string
     */
    public function getFilterName(): string
    {
        return $this->nameModifier->nameForFilter($this->name);
    }

    /**
     * @return string
     */
    public function getExternalName(): string
    {
        return $this->nameModifier->externalName($this->name);
    }

    public function getterName(): string
    {
        return $this->nameModifier->getterName($this->name);
    }

    public function setterName(): string
    {
        return $this->nameModifier->setterName($this->name);
    }

    public function initUse(PhpNamespace $namespace)
    {
        return;
    }

    public function setPrimary(bool $state = true)
    {
        $this->primary = $state;
    }

    public function setRequired(bool $state = true)
    {
        $this->required = $state;
    }

    public function isRequired(): bool
    {
        return (bool)$this->required;
    }

    public function isPrimary(): bool
    {
        return (bool)$this->primary;
    }

    public function setUnique(bool $state = true)
    {
        $this->unique = $state;
    }

    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
    }

    public function isUnique(): bool
    {
        return (bool)$this->unique;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}