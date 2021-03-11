<?php


namespace Bx\Model\Gen\Interfaces;


interface EntityReaderInterface
{
    /**
     * @param FieldNameModifierInterface|null $fieldNameModifier
     * @return array
     */
    public function getFields(FieldNameModifierInterface $fieldNameModifier = null): array;

    /**
     * @return FieldGeneratorInterface|null
     */
    public function getPrimaryField(): ?FieldGeneratorInterface;
}