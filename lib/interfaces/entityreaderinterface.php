<?php


namespace Bx\Model\Gen\Interfaces;

use Iterator;

interface EntityReaderInterface
{
    /**
     * @return FieldGeneratorInterface[]|array
     */
    public function getFields(): array;
}