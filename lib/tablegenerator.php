<?php


namespace Bx\Model\Gen;


use Bx\Model\Gen\Interfaces\BitrixContextInterface;
use Bx\Model\Gen\Readers\TableReader;
use Bx\Model\Gen\Interfaces\EntityGeneratorInterface;

class TableGenerator extends BaseGenerator
{
    /**
     * @var string
     */
    private $tableName;

    public function __construct(string $tableName, string $module, BitrixContextInterface $bitrixContext)
    {
        $this->tableName = $tableName;
        $this->module = $module;
        $this->reader = new TableReader($tableName, $bitrixContext);
        parent::__construct($module, $bitrixContext);
    }

    protected function getInternalServiceClassName(): string
    {
        return $this->toCamelCase($this->tableName."_service");
    }

    protected function getInternalModelClassName(): string
    {
        return $this->toCamelCase($this->tableName."_model");
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