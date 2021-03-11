<?php


namespace Bx\Model\Gen;


use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Connection;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bx\Model\Gen\Interfaces\BitrixContextInterface;
use Iterator;

class BitrixContext implements BitrixContextInterface
{
    /**
     * @var array
     */
    private $iblockList;

    private function getIblockList(): array
    {
        if (!empty($this->iblockList)) {
            return (array)$this->iblockList;
        }

        Loader::includeModule('iblock');
        $iblockQuery = IblockTable::getList([]);
        while ($iblockData = $iblockQuery->fetch()) {
            $type = $iblockData['IBLOCK_TYPE_ID'];
            $code = $iblockData['CODE'];
            $this->iblockList[$type][$code] = $iblockData;
        }

        return $this->iblockList;
    }

    /**
     * @param string $type
     * @param string $code
     * @return array
     */
    public function getIblock(string $type, string $code): array
    {
        $iblockList = $this->getIblockList();

        return $iblockList[$type][$code] ?? [];
    }

    /**
     * @param string $type
     * @param string $code
     * @return int
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getIblockId(string $type, string $code): int
    {
        Loader::includeModule('iblock');
        $iblockData = IblockTable::getRow([
            'filter' => [
                '=IBLOCK_TYPE_ID' => $type,
                '=CODE' => $code,
            ],
            'select' => [
                'ID'
            ],
        ]);

        return (int)$iblockData['ID'];
    }

    /**
     * @param string $type
     * @param string $code
     * @return Iterator
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getIblockProperties(string $type, string $code): Iterator
    {
        Loader::includeModule('iblock');
        $propertyQuery = PropertyTable::getList([
            'filter' => [
                '=IBLOCK.IBLOCK_TYPE_ID' => $type,
                '=IBLOCK.CODE' => $code,
            ],
        ]);

        while ($property = $propertyQuery->fetch()) {
            yield $property;
        }

        return new \EmptyIterator();
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return Application::getConnection();
    }

    /**
     * @param int $id
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getHlBlockById(int $id): array
    {
        Loader::includeModule('highloadblock');
        return HighloadBlockTable::getById($id)->fetch();
    }

    /**
     * @param string $code
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getHlBlockByCode(string $code): array
    {
        Loader::includeModule('highloadblock');
        return HighloadBlockTable::getRow([
            'filter' => [
                '=NAME' => $code
            ],
        ]);
    }
}