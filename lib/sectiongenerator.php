<?php


namespace Bx\Model\Gen;

use Bx\Model\Gen\Entities\SectionServiceGenerator;
use Bx\Model\Gen\Interfaces\BitrixContextInterface;
use Bx\Model\Gen\Interfaces\EntityGeneratorInterface;
use Bx\Model\Gen\Readers\SectionReader;
use Exception;

class SectionGenerator extends BaseGenerator
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
        $this->reader = new SectionReader($type, $code, $bitrixContext);
    }

    protected function getInternalServiceClassName(): string
    {
        return $this->toCamelCase("{$this->code}_section_service");
    }

    protected function getInternalModelClassName(): string
    {
        return $this->toCamelCase("{$this->code}_section_model");
    }

    public function getInternalEntityClassName(): string
    {
        return '';
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

        $modelNamespace = $this->getModelNamespace();
        $modelClassName = $this->getModelClassName();

        return new SectionServiceGenerator(
            $this->reader,
            $className,
            $this->type,
            $this->code,
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