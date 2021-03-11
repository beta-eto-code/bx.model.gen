<?php


namespace Bx\Model\Gen;

use Bx\Model\Gen\Readers\IblockReader;
use Bx\Model\Gen\Interfaces\BitrixContextInterface;
use Exception;

class IblockGenerator extends BaseGenerator
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $code;

    public function __construct(string $type, string $code, string $module, BitrixContextInterface $bitrixContext)
    {
        $this->type = $type;
        $this->code = $code;
        parent::__construct($module, $bitrixContext);
        $this->reader = new IblockReader($type, $code, $bitrixContext);
    }

    protected function getInternalServiceClassName(): string
    {
        return $this->toCamelCase("{$this->type}_{$this->code}_service");
    }

    protected function getInternalModelClassName(): string
    {
        return $this->toCamelCase("{$this->type}_{$this->code}_model");
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getInternalEntityClassName(): string
    {
        $iblock = $this->bitrixContext->getIblock($this->type, $this->code);
        if (empty($iblock)) {
            throw new Exception("Iblock {$this->type}:{$this->code} is not found!");
        }

        $apiCode = $iblock['API_CODE'];
        if (empty($apiCode)) {
            $apiCode = $this->code;
        }

        return 'Element'.ucfirst($apiCode).'Table';
    }

    /**
     * @return string
     */
    final public function getEntityNamespace(): string
    {
        if (!empty($this->entityNamespace)) {
            return $this->entityNamespace;
        }

        return $this->entityNamespace = '\Bitrix\Iblock\Elements';
    }

    public function run()
    {
        $this->initModelGenerator()->run();
        $this->initServiceGenerator()->run();
    }
}