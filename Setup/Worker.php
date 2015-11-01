<?php

namespace Plugin\DataTableWidget\Setup;

use Plugin\DataTableWidget\Widget\DataTable\TableRepository;
use Plugin\DataTableWidget\Widget\DataTable\TableType;

/**
 * Class Worker
 *
 * @package Plugin\DataTableWidget\Setup
 */
class Worker extends \Ip\SetupWorker
{
    /**
     *  @throws \Ip\Exception\Db
     */
    public function activate()
    {
        $savedTableTypes = ipGetOption(TableType::OPTION);

        if(empty($savedTableTypes)){
            ipSetOption(TableType::OPTION, array());
            TableType::create(array(
                'name' => 'default',
                'language' => ipContent()->getLanguages()[0]->getId(),
                'columnOption' => TableType::SHOW_ALL_COLUMNS,
                'specificColumns' => array()
            ))->save();
        }

        $this->createDataTableRepository();
    }

    /**
     * Not used
     * @ignore
     */
    public function deactivate()
    {
    }

    /**
     * Not used
     * @ignore
     */
    public function remove()
    {
        ipRemoveOption(TableType::OPTION);

        $this->dropDataTableRepository();
    }

    /**
     * Creates the datatable repository db table
     *
     * @throws \Ip\Exception\Db
     */
    private function createDataTableRepository()
    {
        $ipDataTableRepository = ipTable(TableRepository::DATA_TABLE_REPOSITORY);

        $dataTableRepositorySql = "
            CREATE TABLE IF NOT EXISTS $ipDataTableRepository
            (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tableMetaData` text NOT NULL,
            `sourceId` varchar(32) NOT NULL,
            `usageCounter` int(11) NOT NULL,
            UNIQUE KEY `uSource` (`sourceId`),
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
        ";

        try {
            ipDb()->execute($dataTableRepositorySql);
        } catch (Exception $e) {
            ipLog()->error("Could not create repository table. Statement: $dataTableRepositorySql, Message: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Drops the datatable repository db table
     */
    private function dropDataTableRepository()
    {
        $ipTable = ipTable(TableRepository::DATA_TABLE_REPOSITORY);

        $sql = "
            DROP TABLE $ipTable
        ";

        try {
            ipDb()->execute($sql);
        } catch (\Ip\Exception\Db $e) {
            ipLog()->error("Could not drop repository table. Statement: $sql, Message: " . $e->getMessage());
        }
    }
}