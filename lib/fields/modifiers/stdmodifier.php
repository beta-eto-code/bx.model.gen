<?php


namespace Bx\Model\Gen\Fields\Modifiers;


use Bx\Model\Gen\Fields\Modifiers\Traits\StringHelper;
use Bx\Model\Gen\Interfaces\FieldNameModifierInterface;

class StdModifier implements FieldNameModifierInterface
{
    use StringHelper;

    /**
     * @param string $name
     * @return string
     */
    public function setterName(string $name): string
    {
        $name = $this->toCamelCase($name);
        return "set{$name}";
    }

    /**
     * @param string $name
     * @return string
     */
    public function getterName(string $name): string
    {
        $name = $this->toCamelCase($name);
        return "get{$name}";
    }

    /**
     * @param string $name
     * @return string
     */
    public function nameForSelect(string $name): string
    {
        return $name;
    }

    /**
     * @param string $name
     * @return string
     */
    public function nameForFilter(string $name): string
    {
        return $name;
    }

    /**
     * @param string $name
     * @return string
     */
    public function externalName(string $name): string
    {
        return strtolower($name);
    }

    public function nameForSort(string $name): string
    {
        return $this->nameForSelect($name);
    }

    public function nameForSave(string $name): string
    {
        return $this->nameForSelect($name);
    }
}