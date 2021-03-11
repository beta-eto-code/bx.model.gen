<?php


namespace Bx\Model\Gen;

use Bx\Model\Gen\Interfaces\BitrixContextInterface;
use Bx\Model\Gen\Interfaces\EntityGeneratorInterface;
use Bx\Model\Gen\Readers\HlBlockReader;
use Exception;

class HlBlockGenerator extends BaseGenerator
{
    /**
     * @var string
     */
    private $hlBlockCode;
    /**
     * @var mixed|string
     */
    private $tableName;

    /**
     * HlBlockGenerator constructor.
     * @param string $hlBlockCode
     * @param string $module
     * @param BitrixContextInterface $bitrixContext
     * @throws Exception
     */
    public function __construct(string $hlBlockCode, string $module, BitrixContextInterface $bitrixContext)
    {
        parent::__construct($module, $bitrixContext);
        $this->hlBlockCode = $hlBlockCode;

        $hlBlockData = $this->bitrixContext->getHlBlockByCode($this->hlBlockCode);
        if (empty($hlBlockData)) {
            throw new Exception("Hl block {$hlBlockCode} is not found");
        }
        $this->tableName = $hlBlockData['TABLE_NAME'] ?? '';
        $this->reader = new HlBlockReader($hlBlockData, $bitrixContext);
    }

    protected function getInternalServiceClassName(): string
    {
        return $this->toCamelCase("{$this->tableName}_service");
    }

    protected function getInternalModelClassName(): string
    {
        return $this->toCamelCase("{$this->tableName}_model");
    }

    public function getInternalEntityClassName(): string
    {
        return $this->toCamelCase($this->tableName.'_table');
    }

    public function run()
    {
        $this->initEntityClassGenerator($this->tableName)->run();
        $this->initModelGenerator()->run();
        $this->initServiceGenerator()->run();
    }
}