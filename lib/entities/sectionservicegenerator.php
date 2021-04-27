<?php


namespace Bx\Model\Gen\Entities;

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\Model\Section;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
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
use CIBlockSection;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\ClassType;

class SectionServiceGenerator implements EntityGeneratorInterface
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
    protected $modelClass;
    /**
     * @var PhpFile
     */
    protected $phpFile;
    /**
     * @var string
     */
    private $iblockType;
    /**
     * @var string
     */
    private $iblockCode;

    public function __construct(
        EntityReaderInterface $reader,
        string $name,
        string $iblockType,
        string $iblockCode,
        string $modelClass,
        string $namespace,
        string $path
    )
    {
        $this->iblockType = $iblockType;
        $this->iblockCode = $iblockCode;
        $this->reader = $reader;
        $this->modelClass = $this->getShortClassName($modelClass);
        $this->phpFile = new PhpFile();
        $this->namespace = $this->phpFile->addNamespace($namespace);
        $this->namespace->addUse(BaseModelService::class);
        $this->namespace->addUse($modelClass);

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



    protected function addGetIblockIdMethod()
    {
        $this->namespace->addUse(Loader::class);
        $this->namespace->addUse(IblockTable::class);
        $iblockIdProperty = $this->class->addProperty('iblockId');
        $iblockIdProperty->setComment("\n@var int\n");
        $iblockIdProperty->setPrivate();

        $method = $this->class->addMethod('getIblockId');
        $method->setPrivate();
        $method->setReturnType('int');

        $method->setBody(<<<PHP
if(!empty(\$this->iblockId)) {
    return (int)\$this->iblockId;
}

Loader::includeModule('iblock');
\$iblockData = IblockTable::getRow([
    'filter' => [
        '=IBLOCK_TYPE_ID' => '{$this->iblockType}',
        '=CODE' => '{$this->iblockCode}',
    ],
    'select' => [
        'ID'
    ],
]);

return \$this->iblockId = (int)\$iblockData['ID'];
PHP
        );
    }

    protected function addGetEntityObjectMethod()
    {
        $entityPropery = $this->class->addProperty('entity');
        $entityPropery->setPrivate();
        $entityPropery->addComment("\n@var DataManager\n");

        $this->namespace->addUse(Loader::class);
        $this->namespace->addUse(Section::class);
        $this->namespace->addUse(DataManager::class);
        $method = $this->class->addMethod('getEntityObjectMethod');
        $method->setPrivate();


        $method->setBody(<<<PHP
if (\$this->entity instanceof DataManager) {
    return \$this->entity;
}

Loader::includeModule('iblock');
\$iblockId = \$this->getIblockId();
return \$this->entity = Section::compileEntityByIblock(\$iblockId);
PHP
        );

        $method->addComment("@return DataManager");
        $this->addBxExceptionComment($method);
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
\$params['filter']['=IBLOCK_ID'] = \$this->getIblockId();
\$list = \$this->getEntityObjectMethod()::getList(\$params);

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
return \$this->getEntityObjectMethod()::getList(\$params)->getCount();
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

return \$this->getEntityObjectMethod()::delete(\$id);
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
        $this->namespace->addUse(CIBlockSection::class);
        $this->namespace->addUse(Result::class);
        $this->namespace->addUse(Error::class);

        $method->setBody(<<<PHP
\$result = new Result();
\$data = {$this->getSaveArray("\$model", 'ID', 'GLOBAL_ACTIVE', 'MODIFIED_BY', 'CREATED_BY', 'DATE_CREATE', 'SEARCHABLE_CONTENT', 'TMP_ID', 'TIMESTAMP_X')};
\$oSection = new CIBlockSection();
if (\$model->getId() > 0) {
    \$isSuccess = (bool)\$oSection->Update(\$model->getId(), \$data);
    if (!\$isSuccess) {
        return \$result->addError(new Error(\$oSection->LAST_ERROR));
    }
    return \$result;
}
    
\$id = (int)\$oSection->Add(\$data);
if (\$id > 0) {
    \$model->setId(\$id);
    return \$result;
}
        
return \$result->addError(new Error(\$oSection->LAST_ERROR));
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
        $this->addGetIblockIdMethod();
        $this->addGetEntityObjectMethod();
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
