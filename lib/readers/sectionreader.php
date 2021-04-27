<?php


namespace Bx\Model\Gen\Readers;


use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Bx\Model\Gen\Fields\ArrayField;
use Bx\Model\Gen\Fields\BooleanField;
use Bx\Model\Gen\Fields\DateField;
use Bx\Model\Gen\Fields\DateTimeField;
use Bx\Model\Gen\Fields\FloatField;
use Bx\Model\Gen\Fields\IntegerField;
use Bx\Model\Gen\Fields\Modifiers\HlModifier;
use Bx\Model\Gen\Fields\Modifiers\IblockPropertyModifier;
use Bx\Model\Gen\Fields\Modifiers\StdModifier;
use Bx\Model\Gen\Fields\StringField;
use Bx\Model\Gen\Interfaces\BitrixContextInterface;
use Bx\Model\Gen\Interfaces\EntityReaderInterface;
use Bx\Model\Gen\Interfaces\FieldGeneratorInterface;
use Bx\Model\Gen\Interfaces\FieldNameModifierInterface;
use Iterator;
use Nette\PhpGenerator\PhpNamespace;

class SectionReader implements EntityReaderInterface
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $code;
    /**
     * @var BitrixContextInterface
     */
    private $bitrixContext;
    /**
     * @var PhpNamespace|null
     */
    private $namespace;

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
        return $this->bitrixContext->getSectionProperties($this->type, $this->code);
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
            'GLOBAL_ACTIVE' => PropertyTable::TYPE_STRING,
            'IBLOCK_ID' => PropertyTable::TYPE_NUMBER,
            'MODIFIED_BY' => PropertyTable::TYPE_NUMBER,
            'CREATED_BY' => PropertyTable::TYPE_NUMBER,
            'DATE_CREATE' => IblockReader::TYPE_DATE_TIME,
            'SORT' => PropertyTable::TYPE_NUMBER,
            'PICTURE' => PropertyTable::TYPE_NUMBER,
            'DETAIL_PICTURE' => PropertyTable::TYPE_NUMBER,
            'SOCNET_GROUP_ID' => PropertyTable::TYPE_NUMBER,
            'DESCRIPTION' => PropertyTable::TYPE_STRING,
            'DESCRIPTION_TYPE' => PropertyTable::TYPE_STRING,
            'SEARCHABLE_CONTENT' => PropertyTable::TYPE_STRING,
            'CODE' => PropertyTable::TYPE_STRING,
            'XML_ID' => PropertyTable::TYPE_STRING,
            'TMP_ID' => PropertyTable::TYPE_STRING,
            'IBLOCK_SECTION_ID' => PropertyTable::TYPE_NUMBER,
            'LEFT_MARGIN' => PropertyTable::TYPE_NUMBER,
            'RIGHT_MARGIN' => PropertyTable::TYPE_NUMBER,
            'DEPTH_LEVEL' => PropertyTable::TYPE_NUMBER,
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
            case 'integer':
                return new IntegerField($fieldName, $fieldNameModifier);
            case 'double':
                return new FloatField($fieldName, $fieldNameModifier);
            case PropertyTable::TYPE_LIST:
            case 'list':
                return new ArrayField($fieldName, $fieldNameModifier);
            case IblockReader::TYPE_DATE_TIME:
            case 'datetime':
                return new DateTimeField($fieldName, $fieldNameModifier, $this->namespace);
            case 'date':
                return new DateField($fieldName, $fieldNameModifier, $this->namespace);
            case 'boolean':
                return new BooleanField($fieldName, $fieldNameModifier);
            default:
                return new StringField($fieldName, $fieldNameModifier);
        }
    }

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

        $nameModifier = new HlModifier();
        foreach ($this->getPropertiesData() as $property) {
            $typeCode = $property['USER_TYPE_ID'];
            $propertyCode = $property['FIELD_NAME'];
            $isMultiple = $property['MULTIPLE'] === 'Y';
            if ($isMultiple) {
                $typeCode = 'list';
            }

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