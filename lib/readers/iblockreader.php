<?php


namespace Bx\Model\Gen\Readers;


use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Bx\Model\Gen\Fields\Modifiers\IblockPropertyModifier;
use Bx\Model\Gen\Fields\Modifiers\StdModifier;
use Bx\Model\Gen\Interfaces\BitrixContextInterface;
use Bx\Model\Gen\Interfaces\EntityReaderInterface;
use Bx\Model\Gen\Fields\IntegerField;
use Bx\Model\Gen\Fields\ArrayField;
use Bx\Model\Gen\Fields\StringField;
use Bx\Model\Gen\Fields\DateTimeField;
use Bx\Model\Gen\Interfaces\FieldGeneratorInterface;
use Bx\Model\Gen\Interfaces\FieldNameModifierInterface;
use EmptyIterator;
use Iterator;
use Nette\PhpGenerator\PhpNamespace;

final class IblockReader implements EntityReaderInterface
{
    const TYPE_DATE_TIME = 'datetime';

    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $code;
    /**
     * @var PhpNamespace|null
     */
    private $namespace;
    /**
     * @var BitrixContextInterface
     */
    private $bitrixContext;
    /**
     * @var FieldGeneratorInterface[]|array
     */
    private $fields;

    public function __construct(
        string $type,
        string $code,
        BitrixContextInterface $bitrixContext,
        PhpNamespace $namespace = null
    )
    {
        Loader::includeModule('iblock');

        $this->type = $type;
        $this->code = $code;
        $this->bitrixContext = $bitrixContext;
        $this->namespace = $namespace;
    }

    /**
     * @return Iterator
     */
    private function getPropertiesData(): Iterator
    {
        return $this->bitrixContext->getIblockProperties($this->type, $this->code);
    }

    /**
     * @return array
     */
    private function getFieldsData(): array
    {
        return [
            'ID' => PropertyTable::TYPE_NUMBER,
            'NAME' => PropertyTable::TYPE_STRING,
            'ACTIVE' => PropertyTable::TYPE_STRING,
            'IBLOCK_ID' => PropertyTable::TYPE_NUMBER,
            'DATE_CREATE' => IblockReader::TYPE_DATE_TIME,
            'ACTIVE_FROM' => IblockReader::TYPE_DATE_TIME,
            'ACTIVE_TO' => IblockReader::TYPE_DATE_TIME,
            'SORT' => PropertyTable::TYPE_NUMBER,
            'PREVIEW_PICTURE' => PropertyTable::TYPE_NUMBER,
            'PREVIEW_TEXT' => PropertyTable::TYPE_STRING,
            'DETAIL_PICTURE' => PropertyTable::TYPE_NUMBER,
            'DETAIL_TEXT' => PropertyTable::TYPE_STRING,
            'CODE' => PropertyTable::TYPE_STRING,
            'TAGS' => PropertyTable::TYPE_STRING,
            'IBLOCK_SECTION_ID' => PropertyTable::TYPE_STRING,
            'TIMESTAMP_X' => IblockReader::TYPE_DATE_TIME,
        ];
    }

    /**
     * @param string $fieldName
     * @param string $fieldType
     * @param FieldNameModifierInterface $fieldNameModifier
     * @return FieldGeneratorInterface
     */
    private function getFieldGenerator(
        string $fieldName,
        string $fieldType,
        FieldNameModifierInterface $fieldNameModifier
    ): FieldGeneratorInterface
    {
        switch ($fieldType) {
            case PropertyTable::TYPE_NUMBER:
            case PropertyTable::TYPE_SECTION:
            case PropertyTable::TYPE_ELEMENT:
                return new IntegerField($fieldName, $fieldNameModifier);
            case PropertyTable::TYPE_LIST:
                return new ArrayField($fieldName, $fieldNameModifier);
            case IblockReader::TYPE_DATE_TIME:
                return new DateTimeField($fieldName, $fieldNameModifier, $this->namespace);
            default:
                return new StringField($fieldName, $fieldNameModifier);
        }
    }

    /**
     * @return FieldGeneratorInterface[]|array
     */
    public function getFields(FieldNameModifierInterface $fieldNameModifier = null): array
    {
        if (!empty($this->fields)) {
            return $this->fields;
        }

        $this->fields = [];
        $nameModifier = $fieldNameModifier ?? new StdModifier();
        foreach ($this->getFieldsData() as $fieldCode => $fieldType) {
            $field = $this->getFieldGenerator($fieldCode, $fieldType, $nameModifier);
            if ($fieldCode === 'ID') {
                $field->setPrimary();
            }

            if (in_array($fieldCode, ['ID', 'NAME', 'ACTIVE'])) {
                $field->setRequired();
            }

            $this->fields[] = $field;
        }

        $nameModifier = new IblockPropertyModifier();
        foreach ($this->getPropertiesData() as $property) {
            $typeCode = $property['PROPERTY_TYPE'];
            $propertyCode = $property['CODE'];
            $this->fields[] = $this->getFieldGenerator($propertyCode, $typeCode, $nameModifier);
        }

        return $this->fields;
    }

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