<?php


namespace Bx\Model\Gen;


use Bx\Model\Gen\Entities\EntityClassGenerator;
use Bx\Model\Gen\Entities\ModelGenerator;
use Bx\Model\Gen\Entities\ServiceGenerator;
use Bx\Model\Gen\Fields\Modifiers\Traits\StringHelper;
use Bx\Model\Gen\Readers\TableReader;
use Bx\Model\Gen\Interfaces\BitrixContextInterface;
use Bx\Model\Gen\Interfaces\EntityGeneratorInterface;

abstract class BaseGenerator implements EntityGeneratorInterface
{
    use StringHelper;

    /**
     * @var TableReader
     */
    protected $reader;
    /**
     * @var string
     */
    protected $module;
    /**
     * @var string
     */
    protected $modelClassName;
    /**
     * @var string
     */
    protected $entityClassName;
    /**
     * @var string
     */
    protected $serviceCassName;
    /**
     * @var string|null
     */
    protected $modelNamespace;
    /**
     * @var string|null
     */
    protected $entityNamespace;
    /**
     * @var string|null
     */
    protected $serviceNamespace;
    /**
     * @var string
     */
    protected $baseNamespace;
    /**
     * @var BitrixContextInterface
     */
    protected $bitrixContext;
    /**
     * @var string|null
     */
    protected $category;
    /**
     * @var string|null
     */
    protected $baseName;

    /**
     * @return string
     */
    abstract protected function getInternalServiceClassName(): string;

    /**
     * @return string
     */
    abstract protected function getInternalModelClassName(): string;

    /**
     * @return string
     */
    abstract public function getInternalEntityClassName(): string;

    public function __construct(string $module, BitrixContextInterface $bitrixContext)
    {
        $this->module = $module;
        $this->bitrixContext = $bitrixContext;
        $this->baseNamespace = $this->getNameSpace($module);
        $this->category = null;
    }

    /**
     * @param string $category
     * @return void
     */
    public function setCategory(string $category)
    {
        $this->category = $category;
    }
    /**
     * @param string $baseName
     * @return void
     */
    public function setBaseName(string $baseName)
    {
        if($baseName) {
          $this->baseName = $baseName;
        }
    }

    /**
     * @return string
     */
    public function getServiceClassName(): string
    {
        if (!empty($this->serviceCassName)) {
            return $this->serviceCassName;
        }

        return $this->serviceCassName = $this->getInternalServiceClassName();
    }

    /**
     * @return string
     */
    public function getModelClassName(): string
    {
        if (!empty($this->modelClassName)) {
            return $this->modelClassName;
        }

        return $this->modelClassName = $this->getInternalModelClassName();
    }

    /**
     * @return string
     */
    public function getEntityClassName(): string
    {
        if (!empty($this->entityClassName)) {
            return $this->entityClassName;
        }

        return $this->entityClassName = $this->getInternalEntityClassName();
    }

    public function setModelClassName(string $className, string $namespace = null)
    {
        $this->modelClassName = $className;
        $this->modelNamespace = $namespace;
    }

    public function setEntityClassName(string $className, string $namespace = null)
    {
        $this->entityClassName = $className;
        $this->entityNamespace = $namespace;
    }

    public function setServiceClassName(string $className, string $namespace = null)
    {
        $this->serviceCassName = $className;
        $this->serviceNamespace = $namespace;
    }

    protected function addNamespace(): string
    {
        return !empty($this->category) ? '\\'.$this->toCamelCase($this->category) : '';
    }

    /**
     * @return string
     */
    public function getModelNamespace(): string
    {
        if (!empty($this->modelNamespace)) {
            return $this->modelNamespace;
        }

        return $this->modelNamespace = "{$this->baseNamespace}\\Models".$this->addNamespace();
    }

    /**
     * @return string
     */
    public function getServiceNamespace(): string
    {
        if (!empty($this->serviceNamespace)) {
            return $this->serviceNamespace;
        }

        return $this->serviceNamespace = "{$this->baseNamespace}\\Services".$this->addNamespace();
    }

    /**
     * @return string
     */
    public function getEntityNamespace(): string
    {
        if (!empty($this->entityNamespace)) {
            return $this->entityNamespace;
        }

        return $this->entityNamespace = "{$this->baseNamespace}\\Entities";
    }

    protected function getAbsPath(string $path): string
    {
        return $_SERVER['DOCUMENT_ROOT'].$path;
    }

    protected function getFileSave(string $namespace, string $className): string
    {
        $moduleNamespace = strtolower($this->getNameSpace($this->module));
        $namespace = str_replace($moduleNamespace, '', strtolower($namespace));

        return "/local/modules/{$this->module}/lib".$this->getPathByNamespace($namespace, $className);
    }

    /**
     * @return EntityGeneratorInterface
     */
    protected function initModelGenerator(): EntityGeneratorInterface
    {
        $namespace = $this->getModelNamespace();
        $className = $this->getModelClassName();

        return new ModelGenerator(
            $this->reader,
            $className,
            $namespace,
            $this->getAbsPath($this->getFileSave($namespace, $className))
        );
    }

    /**
     * @return EntityGeneratorInterface
     */
    protected function initServiceGenerator(): EntityGeneratorInterface
    {
        $namespace = $this->getServiceNamespace();
        $className = $this->getServiceClassName();

        $entityNamespace = $this->getEntityNamespace();
        $entityClassName = $this->getEntityClassName();

        $modelNamespace = $this->getModelNamespace();
        $modelClassName = $this->getModelClassName();

        return new ServiceGenerator(
            $this->reader,
            $className,
            "{$entityNamespace}\\{$entityClassName}",
            "{$modelNamespace}\\{$modelClassName}",
            $namespace,
            $this->getAbsPath($this->getFileSave($namespace, $className))
        );
    }

    /**
     * @param string $tableName
     * @return EntityGeneratorInterface
     */
    protected function initEntityClassGenerator(string $tableName): EntityGeneratorInterface
    {
        $namespace = $this->getEntityNamespace();
        $className = $this->getEntityClassName();
        return new EntityClassGenerator(
            $this->reader,
            $tableName,
            $className,
            $namespace,
            $this->getAbsPath($this->getFileSave($namespace, $className))
        );
    }
}
