<?php


namespace Bx\Model\Gen\Fields\Modifiers;


use Bx\Model\Gen\Fields\Modifiers\Traits\StringHelper;
use Bx\Model\Gen\Interfaces\FieldNameModifierInterface;

class IblockPropertyModifier implements FieldNameModifierInterface
{
    use StringHelper;

    private function stripValuePrefix(string $name): string
    {
        return str_replace('_VALUE', '', $name);
    }

    /**
     * @param string $name
     * @return string
     */
    public function setterName(string $name): string
    {
        $name = $this->toCamelCase($this->stripValuePrefix($name));
        return "set{$name}";
    }

    public function getterName(string $name): string
    {
        $name = $this->toCamelCase($this->stripValuePrefix($name));
        return "get{$name}";
    }

    public function nameForSelect(string $name): string
    {
        $stripedName = $this->stripValuePrefix($name);
        return "{$name}\" => \"{$stripedName}.VALUE";
    }

    public function nameForFilter(string $name): string
    {
        return $this->stripValuePrefix($name).".VALUE";
    }

    public function externalName(string $name): string
    {
        return strtolower($this->stripValuePrefix($name));
    }

    public function nameForSort(string $name): string
    {
        return $this->stripValuePrefix($name).".VALUE";
    }

    public function nameForSave(string $name): string
    {
        return $this->stripValuePrefix($name);
    }
}