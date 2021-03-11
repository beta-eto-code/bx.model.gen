<?php


namespace Bx\Model\Gen\Fields\Modifiers;


use Bx\Model\Gen\Fields\Modifiers\Traits\StringHelper;
use Bx\Model\Gen\Interfaces\FieldNameModifierInterface;

class HlModifier implements FieldNameModifierInterface
{
    use StringHelper;

    private function prepareName(string $name)
    {
        return str_replace(['UF_', 'uf_'], '', $name);
    }

    /**
     * @param string $name
     * @return string
     */
    public function setterName(string $name): string
    {
        $name = $this->toCamelCase($this->prepareName($name));
        return "set{$name}";
    }

    /**
     * @param string $name
     * @return string
     */
    public function getterName(string $name): string
    {
        $name = $this->toCamelCase($this->prepareName($name));
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
        return strtolower($this->prepareName($name));
    }
}