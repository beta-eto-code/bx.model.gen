<?php

namespace Bx\Model\Gen\Entities\Traits;

use Bx\Model\Gen\Interfaces\EntityReaderInterface;
use Bx\Model\Gen\Interfaces\FieldGeneratorInterface;

trait Helper
{
    abstract protected function getReader(): EntityReaderInterface;

    protected function toArrayMap(string $varName): string
    {
        $mapArray = array_map(function (FieldGeneratorInterface $field) use ($varName) {
            return "\t\"{$field->getExternalName()}\" => {$varName}->{$field->getterName()}(),";
        }, $this->getReader()->getFields());

        return "[\n".implode("\n", $mapArray)."\n]";
    }

    protected function getMapFields(): string
    {
        $mapArray = array_map(function (FieldGeneratorInterface $field) {
            return "\t\"{$field->getExternalName()}\" => \"{$field->getSelectName()}\",";
        }, $this->getReader()->getFields());

        return "[\n".implode("\n", $mapArray)."\n]";
    }

    /**
     * @return string
     */
    protected function getSaveArray(string $varName): string
    {
        $saveArray = array_map(function (FieldGeneratorInterface $field) use ($varName) {
            return "\t\"{$field->getSelectName()}\" => {$varName}->{$field->getterName()}(),";
        }, $this->getReader()->getFields());

        return "[\n".implode("\n", $saveArray)."\n]";
    }
}