<?php


namespace Bx\Model\Gen\Entities;


use Bitrix\Main\ORM\Objectify\State;
use Bitrix\Main\Result;
use Bx\Model\AbsOptimizedModel;
use Bx\Model\BaseLinkedModelService;
use Bx\Model\FetcherModel;
use Bx\Model\Gen\Interfaces\EntityReaderInterface;
use Bx\Model\Gen\Interfaces\FieldGeneratorInterface;
use Bx\Model\Interfaces\ModelServiceInterface;
use Bx\Model\ModelCollection;

class IblockServiceGenerator extends ServiceGenerator
{
    /**
     * @var string
     */
    private $elementObjectClass;

    public function __construct(
        EntityReaderInterface $reader,
        string $name,
        string $elementObjectClass,
        string $entityClass,
        string $modelClass,
        string $namespace,
        string $path
    )
    {
        $this->elementObjectClass = $elementObjectClass;
        parent::__construct($reader, $name, $entityClass, $modelClass, $namespace, $path);


        $this->namespace->addUse(BaseLinkedModelService::class);
        $this->namespace->addUse(ModelServiceInterface::class);
        $this->class->setExtends(BaseLinkedModelService::class);
        $fileServiceProperty = $this->class->addProperty('fileService');
        $fileServiceProperty->addComment("\n@var ModelServiceInterface\n");

        $constructor = $this->class->addMethod('__construct');
        $fileServiceParameter = $constructor->addParameter('fileService');
        $fileServiceParameter->setType(ModelServiceInterface::class);
        $constructor->setBody('$this->fileService = $fileService;');
    }

    protected function addGetListMethod()
    {
        $method = $this->class->addMethod('getInternalList');
        $method->setProtected();
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

    protected function addSaveMethod()
    {
        $method = $this->class->addMethod('save');
        $method->setPublic();
        $model = $method->addParameter('model');

        $this->namespace->addUse(Result::class);
        $this->namespace->addUse(AbsOptimizedModel::class);
        $this->namespace->addUse($this->elementObjectClass);
        $this->namespace->addUse(State::class);
        $saveArray = array_map(function (FieldGeneratorInterface $field) {
            if (in_array($field->getSaveName(), ['ID', 'IBLOCK_ID', 'TIMESTAMP_X'])) {
                return '';
            }

            return <<<PHP
\nif(\$model->isFill('{$field->getOriginalName()}')) {
    \$element->set('{$field->getSaveName()}', \$model->{$field->getterName()}());
}\n
PHP;
        }, $this->getReader()->getFields());



        $saveList = implode("", $saveArray);

        $method->setBody(<<<PHP
\$element = new {$this->elementObjectClass}();
{$saveList}
if (\$model->getId() > 0) {
    \$element->setId(\$model->getId());
    \$element->sysChangeState(State::CHANGED);
    return \$element->save();
}
    
\$result = \$element->save();
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

    private function addLinkedFieldsMethod()
    {
        $this->namespace->addUse(FetcherModel::class);
        $method = $this->class->addMethod('getLinkedFields');
        $method->setProtected();
        $method->setReturnType('array');
        $method->setBody(<<<PHP

return [
    'preview_file' => new FetcherModel(
        \$this->fileService,
        'preview_file',
        'PREVIEW_PICTURE',
        'ID'
    ),
    'detail_file' => new FetcherModel(
        \$this->fileService,
        'detail_file',
        'DETAIL_PICTURE',
        'ID'
    ),
];
PHP
        );
    }

    public function run()
    {
        $this->addLinkedFieldsMethod();
        parent::run();
    }
}