<?php

namespace Plugin\DataTableWidget\Widget\DataTable;

/**
 * Class TableRepository
 *
 * Repository for all tables.
 *
 * @package Plugin\DataTableWidget\Widget\DataTable
 */
class TableRepository
{
    /**
     * Name of the DB table
     */
    const DATA_TABLE_REPOSITORY = 'data_table_repository';

    /**
     * Imports the data of a source into a table
     *
     * @param $dataSource
     */
    public static function import($dataSource)
    {
        $sourceId = $dataSource->getId();

        if (self::isTableExistingFor($sourceId)) {
            self::incrementUsageCounter($sourceId);
            return;
        }

        $table = self::createTable($dataSource);

        self::addDataTableRepositoryEntry($table->getMetaData(), $sourceId);
    }

    /**
     * Checks if table for a source is already existing
     *
     * @param $sourceId
     * @return bool
     */
    public static function isTableExistingFor($sourceId)
    {
        try {
            $results = ipDb()->selectValue(self::DATA_TABLE_REPOSITORY, 'id', array('sourceId' => $sourceId));
            return $results !== null;
        } catch (\Ip\Exception\Db $e) {
            ipLog()->error("Could not check if table exists for $sourceId. Message:" . $e->getMessage());
        }

        return false;
    }

    /**
     * Gets the corresponding table of a source
     *
     * @param $sourceId
     * @return null|Table
     */
    public static function getTableOf($sourceId)
    {
        try {
            $dataTableEntry = ipDb()->selectRow(self::DATA_TABLE_REPOSITORY, '*', array('sourceId' => $sourceId));

            if ($dataTableEntry == null) {
                return null;
            }

            return Table::getTable(json_decode($dataTableEntry['tableMetaData'], true));
        } catch (\Ip\Exception\Db $e) {
            ipLog()->error("Could not get table of $sourceId. Message:" . $e->getMessage());
        }

        return null;
    }

    /**
     * Removes a table of a source
     *
     * @param $sourceId
     */
    public static function removeTableOf($sourceId)
    {
        try {
            $usageCounter = ipDb()->selectValue(self::DATA_TABLE_REPOSITORY, 'usageCounter', array('sourceId' => $sourceId));

            if (intval($usageCounter) > 1) {
                self::decrementUsageCounter($sourceId);
                return;
            }

            $dataTableEntry = ipDb()->selectRow(self::DATA_TABLE_REPOSITORY, '*', array('sourceId' => $sourceId));

            if ($dataTableEntry == null) {
                return;
            }

            Table::deleteTable(json_decode($dataTableEntry['tableMetaData'], true));
            ipDb()->delete(self::DATA_TABLE_REPOSITORY, array('sourceId' => $sourceId));

        } catch (\Ip\Exception\Db $e) {
            ipLog()->error("Could not remove table, Message: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $dataSource
     * @return Table
     */
    private static function createTable($dataSource)
    {
        $sourceReader = $dataSource->getSourceReader();
        $headerRow = $sourceReader->currentRow();

        $table = Table::createTable($headerRow);

        $sourceReader->moveToNextRow();

        while ($sourceReader->isValid()) {
            $table->addRow($sourceReader->currentRow());
            $sourceReader->moveToNextRow();
        }
        return $table;
    }

    /**
     * @param $metaData
     * @param $sourceId
     */
    private static function addDataTableRepositoryEntry($metaData, $sourceId)
    {
        $serializedMetaData = json_encode(\Ip\Internal\Text\Utf8::checkEncoding($metaData));

        try {
            ipDb()->insert(self::DATA_TABLE_REPOSITORY, array('tableMetaData' => $serializedMetaData, 'sourceId' => $sourceId, 'usageCounter' => 1));
        } catch (\Ip\Exception\Db $e) {
            ipLog()->error("Could not insert data into data table, Message: " . $e->getMessage());
        }
    }

    /**
     * @param $sourceId
     */
    private static function incrementUsageCounter($sourceId)
    {
        $ipTable = ipTable(self::DATA_TABLE_REPOSITORY);
        $sql = "UPDATE $ipTable SET `usageCounter`=`usageCounter`+1 WHERE `sourceId` = '$sourceId';";

        try {
            ipDb()->execute($sql);
        } catch (\Ip\Exception\Db $e) {
            ipLog()->error("Could not increment table usage counter. Statement: $sql, Message: " . $e->getMessage());
        }
    }

    /**
     * @param $sourceId
     */
    private static function decrementUsageCounter($sourceId)
    {
        $ipTable = ipTable(self::DATA_TABLE_REPOSITORY);
        $sql = "UPDATE $ipTable SET `usageCounter`=`usageCounter`-1 WHERE `sourceId` = '$sourceId';";

        try {
            ipDb()->execute($sql);
        } catch (\Ip\Exception\Db $e) {
            ipLog()->error("Could not decrement table usage counter. Statement: $sql, Message: " . $e->getMessage());
        }
    }
}