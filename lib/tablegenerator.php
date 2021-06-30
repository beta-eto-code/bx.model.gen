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
        $this->baseName = $this->toCamelCase($tableName);
        parent::__construct($module, $bitrixContext);
    }

    protected function getInternalServiceClassName(): string
    {
        return $this->baseName."Service";
    }

    protected function getInternalModelClassName(): string
    {
        return $this->baseName."Model";
    }

    public function getInternalEntityClassName(): string
    {
        return $this->baseName."Table";
    }

    public function run()
    {
        $this->initEntityClassGenerator($this->tableName)->run();
        $this->initModelGenerator()->run();
        $this->initServiceGenerator()->run();
    }
}
