<?php


namespace Bx\Model\Gen\Interfaces;


interface FieldNameModifierInterface
{
    public function setterName(string $name): string;
    public function getterName(string $name): string;
    public function nameForSelect(string $name): string;
    public function nameForFilter(string $name): string;
    public function externalName(string $name): string;
}