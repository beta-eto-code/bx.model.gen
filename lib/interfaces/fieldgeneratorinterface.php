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
}