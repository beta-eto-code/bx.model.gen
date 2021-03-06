<?php


namespace Bx\Model\Gen\Readers;


use Bitrix\Main\Db\SqlQueryException;
use Bx\Model\Gen\Fields\Modifiers\HlModifier;
use Bx\Model\Gen\Interfaces\BitrixContextInterface;
use Bx\Model\Gen\Interfaces\EntityReaderInterface;
use Bx\Model\Gen\Interfaces\FieldGeneratorInterface;
use Bx\Model\Gen\Interfaces\FieldNameModifierInterface;
use Nette\PhpGenerator\PhpNamespace;

final class HlBlockReader implements EntityReaderInterface
{
    /**
     * @var TableReader
     */
    private $tableReader;

    public function __construct(
        array $hlBlockData,
        BitrixContextInterface $bitrixContext,
        PhpNamespace $namespace = null
    )
    {
        $tableName = $hlBlockData['TABLE_NAME'] ?? '';
        $this->tableReader = new TableReader($tableName, $bitrixContext, $namespace);
    }

    /**
     * @param int $hlBlockId
     * @param BitrixContextInterface $bitrixContext
     * @param PhpNamespace|null $namespace
     * @return HlBlockReader
     */
    public function initById(
        int $hlBlockId,
        BitrixContextInterface $bitrixContext,
        PhpNamespace $namespace = null
    ): HlBlockReader
    {
        return new self(
            $bitrixContext->getHlBlockById($hlBlockId),
            $bitrixContext,
            $namespace
        );
    }

    /**
     * @param string $hlBlockCode
     * @param BitrixContextInterface $bitrixContext
     * @param PhpNamespace|null $namespace
     * @return HlBlockReader
     */
    public function initByCode(
        string $hlBlockCode,
        BitrixContextInterface $bitrixContext,
        PhpNamespace $namespace = null
    ): HlBlockReader
    {
        return new self(
            $bitrixContext->getHlBlockByCode($hlBlockCode),
            $bitrixContext,
            $namespace
        );
    }

    /**
     * @param FieldNameModifierInterface|null $fieldNameModifier
     * @return array
     * @throws SqlQueryException
     */
    public function getFields(FieldNameModifierInterface $fieldNameModifier = null): array
    {
        return $this->tableReader->getFields($fieldNameModifier ?? new HlModifier);
    }

    /**
     * @return FieldGeneratorInterface|null
     * @throws SqlQueryException
     */
    public function getPrimaryField(): ?FieldGeneratorInterface
    {
        return $this->tableReader->getPrimaryField();
    }
}