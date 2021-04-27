<?php


namespace Bx\Model\Gen\Interfaces;


use Bitrix\Main\DB\Connection;
use Iterator;

interface BitrixContextInterface
{
    /**
     * @param string $type
     * @param string $code
     * @return array
     */
    public function getIblock(string $type, string $code): array;

    /**
     * @param string $type
     * @param string $code
     * @return int
     */
    public function getIblockId(string $type, string $code): int;

    /**
     * @param string $type
     * @param string $code
     * @return Iterator
     */
    public function getIblockProperties(string $type, string $code): Iterator;

    /**
     * @param string $type
     * @param string $code
     * @return Iterator
     */
    public function getSectionProperties(string $type, string $code): Iterator;

    /**
     * @return Connection
     */
    public function getConnection();

    /**
     * @param int $id
     * @return array
     */
    public function getHlBlockById(int $id): array;

    /**
     * @param string $code
     * @return array
     */
    public function getHlBlockByCode(string $code): array;
}