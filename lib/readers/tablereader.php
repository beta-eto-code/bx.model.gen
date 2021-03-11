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
     * @param array $fieldDesc
     * @param FieldNameModifierInterface $nameModifier
     * @return FieldGeneratorInterface
     */
    private function getFieldGenerator(
        ScalarField $field,
        array $fieldDesc,
        FieldNameModifierInterface $nameModifier
    ): FieldGeneratorInterface
    {
        $isPrimary = $fieldDesc['Key'] === 'PRI';
        $isRequired = $fieldDesc['Null'] === 'NO';
        $isUnique = $isPrimary || $fieldDesc['Key'] === 'MUL';
        $defaultValue = $fieldDesc['Default'];
        $fieldGenerator = null;

        if ($field instanceof IntegerField) {
            $fieldGenerator = new \Bx\Model\Gen\Fields\IntegerField($field->getName(), $nameModifier);
        } else if ($field instanceof FloatField) {
            $fieldGenerator = new \Bx\Model\Gen\Fields\FloatField($field->getName(), $nameModifier);
        } else if ($field instanceof ArrayField) {
            $fieldGenerator = new \Bx\Model\Gen\Fields\ArrayField($field->getName(), $nameModifier);
        } else if ($field instanceof DatetimeField) {
            $fieldGenerator = new \Bx\Model\Gen\Fields\DateTimeField($field->getName(), $nameModifier);
        } else if ($field instanceof DateField) {
            $fieldGenerator = new \Bx\Model\Gen\Fields\DateField($field->getName(), $nameModifier);
        } else if ($field instanceof BooleanField) {
            $fieldGenerator = new \Bx\Model\Gen\Fields\BooleanField($field->getName(), $nameModifier);
        }

        $fieldGenerator = $fieldGenerator ?? new StringField($field->getName(), $nameModifier);
        $fieldGenerator->setPrimary($isPrimary);
        $fieldGenerator->setRequired($isRequired);
        $fieldGenerator->setUnique($isUnique);
        $fieldGenerator->setDefaultValue($defaultValue);

        return $fieldGenerator;
    }

    /**
     * @return array
     * @throws SqlQueryException
     */
    private function getTableDesc(): array
    {
        $result = [];
        $connection = $this->bitrixContext->getConnection();
        $query = $connection->query("DESC {$this->tableName}");
        while ($fieldDesc = $query->fetch()) {
            $code = $fieldDesc['Field'];
            $result[$code] = $fieldDesc;
        }

        return $result;
    }

    /**
     * @param ScalarField $field
     * @param FieldGeneratorInterface $fieldGenerator
     * @return FieldGeneratorInterface
     */
    private function prepareFieldGenerator(
        ScalarField $field,
        FieldGeneratorInterface $fieldGenerator
    ): FieldGeneratorInterface
    {
        $isPrimary = $field->getParameter('primary');
        if ($field->isPrimary()) {
            $fieldGenerator->setPrimary(true);
        }

        if ($field->isRequired()) {
            $fieldGenerator->setRequired(true);
        }

        return $fieldGenerator;
    }

    /**
     * @return FieldGeneratorInterface[]|array
     * @throws SqlQueryException
     */
    public function getFields(FieldNameModifierInterface $fieldNameModifier = null): array
    {
        if (!empty($this->fields)) {
            return $this->fields;
        }

        $this->fields = [];
        $nameModifier = $fieldNameModifier ?? new StdModifier();
        $descFields = $this->getTableDesc();
        foreach ($this->getFieldsData() as $field) {
            $fieldDesc = $descFields[$field->getName()];
            $this->fields[] = $this->getFieldGenerator($field, $fieldDesc, $nameModifier);
        }

        return $this->fields;
    }

    /**
     * @return FieldGeneratorInterface|null
     * @throws SqlQueryException
     */
    public function getPrimaryField(): ?FieldGeneratorInterface
    {
        foreach ($this->getFields() as $field) {
            if ($field->isPrimary()) {
                return $field;
            }
        }

        return null;
    }
}