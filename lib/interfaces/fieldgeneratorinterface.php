<?php


namespace Bx\Model\Gen\Interfaces;


use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;

interface FieldGeneratorInterface
{
    /**
     * @param ClassType $class
     * @return Method
     */
    public function initGetter(ClassType $class): Method;

    /**
     * @param ClassType $classType
     * @return Method
     */
    public function initSetter(ClassType $class): Method;

    /**
     * @param PhpNamespace $namespace
     * @return void
     */
    public function initUse(PhpNamespace $namespace);

    /**
     * @return string
     */
    public function getSelectName(): string;

    /**
     * @return string
     */
    public function getFilterName(): string;

    /**
     * @return string
     */
    public function getExternalName(): string;

    /**
     * @return string
     */
    public function getterName(): string;

    /**
     * @return string
     */
    public function setterName(): string;

    /**
     * @return string
     */
    public function getOrmClass(): string;

    /**
     * @param bool $state
     * @return void
     */
    public function setPrimary(bool $state = true);

    /**
     * @param bool $state
     * @return void
     */
    public function setRequired(bool $state = true);

    /**
     * @param bool $state
     * @return mixed
     */
    public function setUnique(bool $state = true);

    /**
     * @param $value
     * @return mixed
     */
    public function setDefaultValue($value);

    /**
     * @return bool
     */
    public function isPrimary(): bool;

    /**
     * @return bool
     */
    public function isRequired(): bool;

    /**
     * @return bool
     */
    public function isUnique(): bool;

    /**
     * @return mixed
     */
    public function getDefaultValue();
}