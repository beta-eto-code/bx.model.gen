<?php

namespace Bx\Model\Gen\Fields\Modifiers\Traits;

trait StringHelper
{
    protected function toCamelCase(string $name): string
    {
        $name = strtolower($name);
        $list = explode('_', $name);
        if ($list === false) {
            return ucfirst($name);
        }

        foreach ($list as &$part) {
            $part = ucfirst($part);
        }
        unset($part);

        return implode('', $list);
    }
}