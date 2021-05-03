<?php


namespace Bx\Model\Gen\Entities;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bx\Model\AbsOptimizedModel;
use Bx\Model\BaseModelService;
use Bx\Model\Gen\Entities\Traits\Helper;
use Bx\Model\Gen\Interfaces\EntityGeneratorInterface;
use Bx\Model\Gen\Interfaces\EntityReaderInterface;
use Bx\Model\Gen\Interfaces\FieldGeneratorInterface;
use Bx\Model\Interfaces\UserContextInterface;
use Bx\Model\ModelCollection;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\ClassType;

class ServiceGenerator implements EntityGeneratorInterface
{
    use Helper;

    /**
     * @var EntityReaderInterface
     */
    protected $reader;
    /**
     * @var PhpNamespace
     */
    protected $namespace;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var ClassType
     */
    protected $class;
    /**
     * @var string
     */
    protected $entityClass;
    /**
     * @var string
     */
    protected $modelClass;
    /**
     * @var PhpFile
     */
    protected $phpFile;

    public function __construct(
        EntityReaderInterface $reader,
        string $name,
        string $entityClass,
        string $modelClass,
        string $namespace,
        string $path
    )
    {
        $this->reader = $reader;
        $this->entityClass = $this->getShortClassName($entityClass);
        $this->modelClass = $this->getShortClassName($modelClass);
        $this->phpFile = new PhpFile();
        $this->namespace = $this->phpFile->addNamespace($namespace);
        $this->namespace->addUse(BaseModelService::class);
        $this->namespace->addUse($modelClass);
        $this->namespace->addUse($entityClass);

        $this->class = $this->namespace->addClass($name);
        $this->class->addExtend(BaseModelService::class);
        $this->path = $path;
    }

    protected function addBxExceptionComment(Method $method)
    {
        $this->namespace->addUse(ArgumentException::class);
        $this->namespace->addUse(ObjectPropertyException::class);
        $this->namespace->addUse(SystemException::class);
        $method->addComment('@throws ArgumentException');
        $method->addComment('@throws ObjectPropertyException');
        $method->addComment('@throws SystemException');
    }

    protected function initParams(Method $method)
    {
        $params = $method->addParameter('params');
        $params->setType('array');
    }

    protected function initUserContext(Method $method)
    {
        $userContext = $method->addParameter('userContext', null);
        $this->namespace->addUse(UserContextInterface::class);
        $userContext->setType(UserContextInterface::class);
        $userContext->setNullable(true);
    }

    protected function addGetListMethod()
    {
        $method = $this->class->addMethod('getList');
        $method->setPublic();
        $this->initParams($method);
        $this->initUserContext($method);
        $this->namespace->addUse(ModelCollection::class);
        $selectList = array_map(function (FieldGeneratorInterface $field) {
            return $field->getSelectName();
        }, $this->reader->getFields());

        $select = "[\n\t\"".implode("\",\n\t\"", $selectList)."\"\n]";
        $method->setBody(<<<PHP
\$params['select'] = \$params['select'] ?? {$select};
\$list = {$this->entityClass}::getList(\$params);

return new ModelCollection(\$list, {$this->modelClass}::class);
PHP
        );

        $method->setReturnType(ModelCollection::class);
        $method->addComment('@param array $params');
        $method->addComment('@param UserContextInterface|null $userContext');
        $method->addComment("@return {$this->modelClass}[]|ModelCollection");
        $this->addBxExceptionComment($method);
    }

    private function addGetByIdMethod()
    {
        $method = $this->class->addMethod('getById');
        $id = $method->addParameter('id');
        $id->setType('int');
        $this->initUserContext($method);

        $method->setBody(<<<PHP
\$params = [
    'filter' => [
        '=id' => \$id,
    ],
];
\$collection = \$this->getList(\$params, \$userContext);

return \$collection->first();
PHP
        );

        $this->namespace->addUse(AbsOptimizedModel::class);
        $method->setReturnType(AbsOptimizedModel::class);
        $method->setReturnNullable(true);
        $method->addComment('@param int $id');
        $method->addComment('@param UserContextInterface|null $userContext');
        $method->addComment("@return {$this->modelClass}|AbsOptimizedModel|null");
        $this->addBxExceptionComment($method);
    }

    private function addGetCountMethod()
    {
        $method = $this->class->addMethod('getCount');
        $method->setPublic();
        $this->initParams($method);
        $this->initUserContext($method);

        $method->setBody(<<<PHP
\$params['count_total'] = true;
return {$this->entityClass}::getList(\$params)->getCount();
PHP
        );

        $method->setReturnType('int');
        $method->addComment('@param array $params');
        $method->addComment('@param UserContextInterface|null $userContext');
        $method->addComment("@return int");
        $this->addBxExceptionComment($method);
    }

    private function addDeleteMethod()
    {
        $method = $this->class->addMethod('delete');
        $method->setPublic();
        $id = $method->addParameter('id');
        $id->setType('int');
        $this->initUserContext($method);

        $this->namespace->addUse(Error::class);
        $this->namespace->addUse(Result::class);

        $method->setBody(<<<PHP
\$item = \$this->getById(\$id, \$userContext);
if (!(\$item instanceof {$this->modelClass})) {
    return (new Result)->addError(new Error('Не найдена запись для удаления'));
}

return {$this->entityClass}::delete(\$id);
PHP
        );

        $method->setReturnType(Result::class);
        $method->addComment('@param int $id');
        $method->addComment('@param UserContextInterface|null $userContext');
        $method->addComment('@return Result');
        $this->addBxExceptionComment($method);
        $this->namespace->addUse('Exception');
        $method->addComment('@throws Exception');
    }

    protected function addSaveMethod()
    {
        $method = $this->class->addMethod('save');
        $method->setPublic();
        $model = $method->addParameter('model');

        $this->namespace->addUse(Result::class);
        $this->namespace->addUse(AbsOptimizedModel::class);

        $method->setBody(<<<PHP
\$dataInfo = {$this->getSaveArray("\$model")};
\$data = [];
foreach(\$dataInfo as \$name => \$info) {
    if ((bool)\$info['isFill']) {
        \$data[\$name] = \$info['value'];
    }
}

if (\$model->getId() > 0) {
    return {$this->entityClass}::update(\$model->getId(), \$data);
}
    
\$result = {$this->entityClass}::add(\$data);
if (\$result->isSuccess()) {
    \$model->setId(\$result->getId());
}
        
return \$result;
PHP
        );

        $model->setType(AbsOptimizedModel::class);
        $this->initUserContext($method);
        $method->setReturnType(Result::class);
        $method->addComment("@param {$this->modelClass} \$model");
        $method->addComment('@param UserContextInterface|null $userContext');
        $method->addComment('@return Result');
        $this->namespace->addUse('Exception');
        $method->addComment('@throws Exception');
    }

    private function addGetFilterFieldsMethod()
    {
        $method = $this->class->addMethod('getFilterFields');
        $method->setPublic();
        $method->setStatic();
        $method->setReturnType('array');
        $method->setBody("return {$this->getMapFields()};");
        $method->addComment('@return array');
    }

    private function addGetSortFieldsMethod()
    {
        $method = $this->class->addMethod('getSortFields');
        $method->setPublic();
        $method->setStatic();
        $method->setReturnType('array');
        $method->setBody("return {$this->getMapFields()};");
        $method->addComment('@return array');
    }

    /**
     * @throws SystemException
     */
    public function run()
    {
        $this->addGetListMethod();
        $this->addGetByIdMethod();
        $this->addGetCountMethod();
        $this->addDeleteMethod();
        $this->addSaveMethod();
        $this->addGetSortFieldsMethod();
        $this->addGetFilterFieldsMethod();

        $this->saveFile($this->path, $this->phpFile);
    }

    protected function getReader(): EntityReaderInterface
    {
        return $this->reader;
    }
}
