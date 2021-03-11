<?php


namespace Bx\Model\Gen\Fields;


use Bx\Model\Gen\Interfaces\FieldNameModifierInterface;

class StringField extends BaseFieldGenerator
{
    public function __construct(string $name, FieldNameModifierInterface $fieldNameModifier)
    {
        parent::__construct($name, 'string', $fieldNameModifier);
    }

    public function getOrmClass(): string
    {
        return \Bitrix\Main\ORM\Fields\StringField::class;
    }
}