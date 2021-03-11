<?php


namespace Bx\Model\Gen\Entities;


use Bitrix\Main\SystemException;
use Bx\Model\AbsOptimizedModel;
use Bx\Model\Gen\Entities\Traits\Helper;
use Bx\Model\Gen\Interfaces\EntityGeneratorInterface;
use Bx\Model\Gen\Interfaces\EntityReaderInterface;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\ClassType;

class ModelGenerator implements EntityGeneratorInterface
{
    use Helper;

    /**
     * @var EntityReaderInterface
     */
    private $reader;
    /**
     * @var PhpNamespace
     */
    private $namespace;
    /**
     * @var PhpFile
     */
    private $phpFile;
    /**
     * @var string
     */
    private $path;
    /**
     * @var ClassType
     */
    private $class;

    public function __construct(
        EntityReaderInterface $reader,
        string $name,
        string $namespace,
        string $path
    )
    {
        $this->reader = $reader;
        $this->phpFile = new PhpFile();
        $this->namespace = $this->phpFile->addNamespace($namespace);
        $this->namespace->addUse(AbsOptimizedModel::class);

        $this->class = $this->namespace->addClass($name);
        $this->class->addExtend(AbsOptimizedModel::class);
        $this->path = $path;
    }

    private function addToArrayMethod()
    {
        $method = $this->class->addMethod('toArray');
        $method->setProtected();
        $method->setReturnType('array');
        $method->addComment('@return array');

        $method->setBody(<<<PHP
return {$this->toArrayMap("\$this")};
PHP
        );
    }

    private function initGettersSetters()
    {
        foreach ($this->reader->getFields() as $field) {
            $field->initUse($this->namespace);
            $field->initGetter($this->class);
            $field->initSetter($this->class);
        }
    }

    protected function getReader(): EntityReaderInterface
    {
        return $this->reader;
    }

    /**
     * @throws SystemException
     */
    public function run()
    {
        $this->addToArrayMethod();
        $this->initGettersSetters();

        $this->saveFile($this->path, $this->phpFile);
    }
}