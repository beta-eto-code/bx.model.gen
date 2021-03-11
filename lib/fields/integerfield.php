<?php


namespace Bx\Model\Gen\Fields;


use Bx\Model\Gen\Interfaces\FieldNameModifierInterface;

class IntegerField extends BaseFieldGenerator
{
    public function __construct(string $name, FieldNameModifierInterface $fieldNameModifier)
    {
        parent::__construct($name, 'int', $fieldNameModifier);
    }

    public function getOrmClass(): string
    {
        return \Bitrix\Main\ORM\Fields\IntegerField::class;
    }
}