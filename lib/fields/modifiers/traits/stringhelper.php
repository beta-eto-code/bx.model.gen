<?php

namespace Bx\Model\Gen\Fields\Modifiers\Traits;

trait StringHelper
{
    /**
     * @param string $name
     * @return string
     */
    protected function toCamelCase(string $name): string
    {
        return $this->modifyName($name, '_', '');
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getNameSpace(string $name): string
    {
        return $this->modifyName($name, '.', '\\');
    }

    /**
     * @param string $namespace
     * @param string|null $className
     * @return string
     */
    protected function getPathByNamespace(string $namespace, string $className = null): string
    {
        if (!empty($className)) {
            $namespace .= "/{$className}.php";
        }

         return str_replace('\\', '/', strtolower($namespace));
    }

    /**
     * @param string $name
     * @param string $inSeparator
     * @param string $outSeparator
     * @return string
     */
    private function modifyName(string $name, string $inSeparator, string $outSeparator): string
    {
        $list = explode($inSeparator, strtolower($name));
        if ($list === false) {
            return ucfirst($name);
        }

        foreach ($list as &$part) {
            $part = ucfirst($part);
        }
        unset($part);

        return implode($outSeparator, $list);
    }
}