<?php

namespace Bx\Model\Gen\Entities\Traits;

use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\SystemException;
use Bx\Model\Gen\Interfaces\EntityReaderInterface;
use Bx\Model\Gen\Interfaces\FieldGeneratorInterface;
use Nette\PhpGenerator\PhpNamespace;

trait Helper
{
    abstract protected function getReader(): EntityReaderInterface;

    /**
     * @param string $varName
     * @return string
     */
    protected function toArrayMap(string $varName): string
    {
        $mapArray = array_map(function (FieldGeneratorInterface $field) use ($varName) {
            return "\t\"{$field->getExternalName()}\" => {$varName}->{$field->getterName()}(),";
        }, $this->getReader()->getFields());

        return "[\n".implode("\n", $mapArray)."\n]";
    }

    /**
     * @return string
     */
    protected function getMapFields(): string
    {
        $mapArray = array_map(function (FieldGeneratorInterface $field) {
            return "\t\"{$field->getExternalName()}\" => \"{$field->getSortName()}\",";
        }, $this->getReader()->getFields());

        return "[\n".implode("\n", $mapArray)."\n]";
    }

    /**
     * @param string $varName
     * @param string ...$excludeFields
     * @return string
     */
    protected function getSaveArray(string $varName, string ...$excludeFields): string
    {
        $excludeFields = $excludeFields ?? [];
        $saveArray = array_map(function (FieldGeneratorInterface $field) use ($varName, $excludeFields) {
            if (in_array($field->getSaveName(), $excludeFields)) {
                return '';
            }

            return "\t\"{$field->getSaveName()}\" => {$varName}->{$field->getterName()}(),\n";
        }, $this->getReader()->getFields());

        return "[\n".implode("", $saveArray)."]";
    }

    /**
     * @param string $className
     * @return string
     */
    private function getShortClassName(string $className): string
    {
        $classParts = explode('\\', $className);
        if ($classParts === false) {
            return $className;
        }

        return end($classParts);
    }

    /**
     * @param PhpNamespace $namespace
     * @return string
     */
    protected function getTableMap(PhpNamespace $namespace): string
    {
        $mapArray = array_map(function (FieldGeneratorInterface $field) use ($namespace) {
            $className = $field->getOrmClass();
            $shortClassName = $this->getShortClassName($className);

            $namespace->addUse($className);
            $fieldName = $field->getSelectName();
            $isPrimary = $field->isPrimary() ? 'true' : 'false';
            $isRequired = $field->isRequired() ? 'true' : 'false';
            $isUnique = $field->isUnique() ? 'true' : 'false';
            $defaultValue = (string)($field->getDefaultValue() ?? 'null');
            if ($defaultValue !== 'null') {
                $defaultValue = "'{$defaultValue}'";
            }

            return <<<PHP
    '{$fieldName}' => new {$shortClassName}('{$fieldName}', [
        'primary' => {$isPrimary},
        'required' => {$isRequired},
        'unique' => {$isUnique},
        'default_value' => {$defaultValue},
    ])
PHP;
        }, $this->getReader()->getFields());

        return "[\n".implode(",\n", $mapArray)."\n]";
    }

    /**
     * @param string $file
     * @param $data
     * @throws SystemException
     */
    protected function saveFile(string $file, $data)
    {
        $fileInfo = pathinfo($file);
        $pathDir = $fileInfo['dirname'] ?? null;
        if (!empty($pathDir) && !is_dir($pathDir)) {
            $isSuccess = mkdir($pathDir, 0777, true);
            if (!$isSuccess) {
                throw new SystemException("Не удалось создать директорию: {$pathDir}");
            }
        }

        file_put_contents($file, $data);
    }
}