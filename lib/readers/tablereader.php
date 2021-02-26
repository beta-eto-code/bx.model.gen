<?php


namespace Bx\Model\Gen\Readers;

use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bx\Model\Gen\Fields\Modifiers\StdModifier;
use Bx\Model\Gen\Fields\StringField;
use Bx\Model\Gen\Interfaces\BitrixContextInterface;
use Bx\Model\Gen\Interfaces\EntityReaderInterface;
use Bx\Model\Gen\Interfaces\FieldGeneratorInterface;
use Bx\Model\Gen\Interfaces\FieldNameModifierInterface;
use Nette\PhpGenerator\PhpNamespace;

final class TableReader implements EntityReaderInterface
{
    /**
     * @var string
     */
    private $tableName;
    /**
     * @var PhpNamespace|null
     */
    private $namespace;
    /**
     * @var BitrixContextInterface
     */
    private $bitrixContext;

    /**
     * @var array
     */
    private $fields;

    public function __construct(
        string $tableName,
        BitrixContextInterface $bitrixContext,
        PhpNamespace $namespace = null
    )
    {
        $this->tableName = $tableName;
        $this->namespace = $namespace;
        $this->bitrixContext = $bitrixContext;
    }

    /**
     * @return ScalarField[]|array
     * @throws SqlQueryException
     */
    private function getFieldsData(): array
    {
        $connection = $this->bitrixContext->getConnection();
        return $connection->getTableFields($this->tableName);
    }

    /**
     * @param ScalarField $field
     * @param FieldNameModifierInterface $nameModifier
     * @return FieldGeneratorInterface
     */
    private function getFieldGenerator(
        ScalarField $field,
        FieldNameModifierInterface $nameModifier
    ): FieldGeneratorInterface
    {
        if ($field instanceof IntegerField) {
            return new \Bx\Model\Gen\Fields\IntegerField($field->getName(), $nameModifier);
        }

        if ($field instanceof FloatField) {
            return new \Bx\Model\Gen\Fields\FloatField($field->getName(), $nameModifier);
        }

        if ($field instanceof ArrayField) {
            return new \Bx\Model\Gen\Fields\ArrayField($field->getName(), $nameModifier);
        }

        if ($field instanceof DatetimeField) {
            return new \Bx\Model\Gen\Fields\DateTimeField($field->getName(), $nameModifier, $this->namespace);
        }

        if ($field instanceof DateField) {
            return new \Bx\Model\Gen\Fields\DateField($field->getName(), $nameModifier, $this->namespace);
        }

        if ($field instanceof BooleanField) {
            return new \Bx\Model\Gen\Fields\BooleanField($field->getName(), $nameModifier);
        }

        return new StringField($field->getName(), $nameModifier);
    }

    /**
     * @return FieldGeneratorInterface[]|array
     * @throws SqlQueryException
     */
    public function getFields(): array
    {
        if (!empty($this->fields)) {
            return $this->fields;
        }

        $this->fields = [];
        $nameModifier = new StdModifier();
        foreach ($this->getFieldsData() as $field) {
            $this->fields[] = $this->getFieldGenerator($field, $nameModifier);
        }

        return $this->fields;
    }
}