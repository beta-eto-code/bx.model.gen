<?php


namespace Bx\Model\Gen\Entities;


use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\SystemException;
use Bx\Model\Gen\Entities\Traits\Helper;
use Bx\Model\Gen\Interfaces\EntityGeneratorInterface;
use Bx\Model\Gen\Interfaces\EntityReaderInterface;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\ClassType;


class EntityClassGenerator implements EntityGeneratorInterface
{
    use Helper;

    /**
     * @var EntityReaderInterface
     */
    private $reader;
    /**
     * @var string
     */
    private $tableName;
    /**
     * @var PhpFile
     */
    private $phpFile;
    /**
     * @var PhpNamespace
     */
    private $namespace;
    /**
     * @var ClassType
     */
    private $class;
    /**
     * @var string
     */
    private $path;

    public function __construct(
        EntityReaderInterface $reader,
        string $tableName,
        string $className,
        string $namespace,
        string $path
    )
    {
        $this->reader = $reader;
        $this->tableName = $tableName;
        $this->phpFile = new PhpFile();
        $this->namespace = $this->phpFile->addNamespace($namespace);
        $this->class = $this->namespace->addClass($className);
        $this->class->setExtends(DataManager::class);
        $this->namespace->addUse(DataManager::class);
        $this->path = $path;
    }

    private function addGetTableNameMethod()
    {
        $method = $this->class->addMethod('getTableName');
        $method->setPublic();
        $method->setStatic();

        $method->setBody("return '{$this->tableName}';");
        $method->setReturnType('string');
        $method->addComment('@return string');
    }

    private function addGetMapMethod()
    {
        $method = $this->class->addMethod('getMap');
        $method->setPublic();
        $method->setStatic();

        $this->namespace->addUse(SystemException::class);
        $method->setBody("return {$this->getTableMap($this->namespace)};");
        $method->setReturnType('array');
        $method->addComment('@return array');
        $method->addComment('@throws SystemException');
    }

    /**
     * @throws SystemException
     */
    public function run()
    {
        $this->addGetTableNameMethod();
        $this->addGetMapMethod();

        $this->saveFile($this->path, $this->phpFile);
    }

    protected function getReader(): EntityReaderInterface
    {
        return $this->reader;
    }
}