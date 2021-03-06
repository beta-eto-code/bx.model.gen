<?php


namespace Bx\Model\Gen;

use Bx\Model\Gen\Entities\IblockServiceGenerator;
use Bx\Model\Gen\Entities\ModelGenerator;
use Bx\Model\Gen\Interfaces\EntityGeneratorInterface;
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

    protected function getInternalModelClassName(): string
    {
        return $this->toCamelCase("{$this->code}_model");
    }

    protected function getInternalServiceClassName(): string
    {
        return $this->toCamelCase("{$this->code}_service");
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

        return 'Element'.$this->toCamelCase($apiCode).'Table';
    }


    /**
     * @return string
     * @throws Exception
     */
    public function getObjectEntityClassName(): string
    {
        $iblock = $this->bitrixContext->getIblock($this->type, $this->code);
        if (empty($iblock)) {
            throw new Exception("Iblock {$this->type}:{$this->code} is not found!");
        }

        $apiCode = $iblock['API_CODE'];
        if (empty($apiCode)) {
            $apiCode = $this->code;
        }

        return 'EO_Element'.$this->toCamelCase($apiCode);
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

    protected function addNamespace(): string
    {
        return '\\'.$this->toCamelCase($this->category ?? $this->type);
    }

    /**
     * @return EntityGeneratorInterface
     * @throws Exception
     */
    protected function initServiceGenerator(): EntityGeneratorInterface
    {
        $namespace = $this->getServiceNamespace();
        $className = $this->getServiceClassName();

        $entityNamespace = $this->getEntityNamespace();
        $entityObjectClassName = $this->getObjectEntityClassName();
        $entityClassName = $this->getEntityClassName();

        $modelNamespace = $this->getModelNamespace();
        $modelClassName = $this->getModelClassName();

        return new IblockServiceGenerator(
            $this->reader,
            $className,
            "{$entityNamespace}\\{$entityObjectClassName}",
            "{$entityNamespace}\\{$entityClassName}",
            "{$modelNamespace}\\{$modelClassName}",
            $namespace,
            $this->getAbsPath($this->getFileSave($namespace, $className))
        );
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        $this->initModelGenerator()->run();
        $this->initServiceGenerator()->run();
    }
}