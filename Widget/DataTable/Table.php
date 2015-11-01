<?php

namespace Plugin\DataTableWidget\Widget\DataTable;

/**
 * Class Table model
 *
 * @package Plugin\DataTableWidget\Widget\DataTable
 */
class Table
{
    /**
     * DB Table name prefix
     */
    const TABLE_NAME_PREFIX = 'data_table_';

    /**
     * DB table column id prefix
     */
    const COLUMN_ID_PREFIX = 'c';


    private $_name;
    private $_columns;
    private $_lookup;

    /**
     * @param $name
     * @param $columns
     * @param $lookup
     */
    private function __construct($name, $columns, $lookup)
    {
        $this->_name = $name;
        $this->_columns = $columns;
        $this->_lookup = $lookup;
    }

    /**
     * Creates a table
     *
     * @param $tableHeadings
     * @return Table
     */
    public static function createTable($tableHeadings)
    {
        $name = self::createTableName();
        $columns = self::createColumns($tableHeadings);
        $lookup = self::createLookup($columns);

        $table = new Table($name, $columns, $lookup);
        $table->createDbTableIfNotExists();
        return $table;
    }

    /**
     * Gets a table object
     *
     * @param $tableMetaData
     * @return Table
     */
    public static function getTable($tableMetaData)
    {
        return new Table($tableMetaData['name'], $tableMetaData['columns'], $tableMetaData['lookup']);
    }

    /**
     * @param $tableMetaData
     */
    public static function deleteTable($tableMetaData){
        try {
            $ipTable = ipTable($tableMetaData['name']);
            ipDb()->execute("DROP TABLE $ipTable");
        } catch (\Ip\Exception\Db $e) {
            ipLog()->error("Could not drop data table. Message:" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gets records of a table
     *
     * @param array $specificColumns columns to get
     * @param int $start starting row number
     * @param null $length amount of rows
     * @param null $orderBy order by column ids
     * @param null $orderDirection
     * @param null $searchValue
     * @return array
     */
    public function getRecords($specificColumns = array(), $start = 0, $length = null, $orderBy = null, $orderDirection = null, $searchValue = null)
    {
        $params = array();
        $where = ' WHERE ';
        $attributeSelection = '';
        $columnSelection = array();

        $filteredColumns = $this->getColumnsFilteredBy($specificColumns);

        $columnCounter = count($filteredColumns);
        foreach($filteredColumns as $id => $column){
            $columnSelection[$id] = isset($column['displayName']) ? $column['displayName'] : $column['heading'];
            $attribute = $column['attribute'];
            $attributeSelection .= '`' . $attribute . '` as `' . $id . '`';
            $where .= '`' . $attribute . '` LIKE ?';
            $params[] = '%'.$searchValue.'%';

            if (--$columnCounter > 0) {
                $attributeSelection .= ',';
                $where .= ' OR ';
            }
        }

        $sql = 'SELECT ' . $attributeSelection . ' FROM ' . ipTable($this->_name);
        $sqlFilteredCount = 'SELECT COUNT(*) as `recordsFiltered` FROM ' . ipTable($this->_name);

        if(!empty($searchValue)){
            $sql .= $where;
            $sqlFilteredCount .= $where;
        }

        if(!empty($orderBy) && isset($this->_columns[$orderBy])){
            $orderByAttribute = $this->_columns[$orderBy]['attribute'];
            $direction = $orderDirection == 'desc' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY `$orderByAttribute` $direction";
        }

        if (!empty($length)) {
            $sql .= " LIMIT $start, $length";
        }

        $result = array(
            'columns' => $columnSelection,
            'data' => array(),
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );

        try {
            $result['recordsTotal'] = intval(ipDb()->selectValue($this->_name, "COUNT(*)", array(), ''));
            $result['recordsFiltered'] = intval(ipDb()->fetchAll($sqlFilteredCount, $params)[0]['recordsFiltered']);
            $result['data'] = ipDb()->fetchAll($sql, $params);
        } catch (\Ip\Exception\Db $e) {
            ipLog()->error("Could not insert data into data table, Message: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * Adds a row to the table
     *
     * @param $row
     */
    public function addRow($row)
    {
        $insertArray = array();

        foreach ($this->_columns as $column) {
            if (isset($row[$column['number']])) {
                $insertArray[$column['attribute']] = $row[$column['number']];
            }
        }

        try {
            ipDb()->insert($this->_name, $insertArray);
        } catch (\Ip\Exception\Db $e) {
            ipLog()->error("Could not insert data into data table, Message: " . $e->getMessage());
        }
    }

    /**
     * Gets description data of the table
     *
     * @return array
     */
    public function getMetaData()
    {
        return array(
            'name' => $this->_name,
            'columns' => $this->_columns,
            'lookup' => $this->_lookup
        );
    }

    /**
     * Creates the table columns
     *
     * @param $tableHeadings
     * @return array
     */
    private static function createColumns($tableHeadings)
    {
        $columns = array();

        foreach ($tableHeadings as $columnNumber => $heading) {
            $columnId = self::COLUMN_ID_PREFIX . $columnNumber;
            $columns[$columnId]['number'] = $columnNumber;
            $columns[$columnId]['heading'] = $heading;
            $columns[$columnId]['attribute'] = self::createAttributeName($heading, $columnNumber);
        }

        return $columns;
    }

    /**
     * Creates a column lookup
     *
     * @param $columns
     * @return array
     */
    private static function createLookup($columns)
    {
        $lookup = array();

        foreach ($columns as $columnId => $column) {
            $lookup[md5($column['heading'])] = $columnId;
        }

        return $lookup;
    }

    /**
     * Creates the attribute names for the DB table
     *
     * @param $heading
     * @param $columnNumber
     * @return string
     */
    private static function createAttributeName($heading, $columnNumber)
    {
        $headingCleaned = str_replace(' ', '_', $heading);
        $headingCleaned = preg_replace('/[^A-Za-z0-9\-]/', '', $headingCleaned);
        $headingCleaned = preg_replace('/_+/', '_', $headingCleaned);
        return $headingCleaned . $columnNumber;
    }

    /**
     * Creates the table name
     *
     * @return string
     */
    private static function createTableName()
    {
        $generateNames = true;

        while ($generateNames) {
            $name = self::TABLE_NAME_PREFIX . time();

            $sql = "SHOW TABLES LIKE '$name'";
            try {
                $generateNames = ipDb()->execute($sql) > 0;
            } catch (\Ip\Exception\Db $e) {
                $generateNames = false;
                ipLog()->error("Could not get tables like $name. Message: " . $e->getMessage());
            }
        }

        return $name;
    }

    /**
     * @param $specificColumns
     * @return array
     */
    private function getColumnsFilteredBy($specificColumns){
        if(count($specificColumns) === 0){
            return $this->_columns;
        }

        $columns = array();

        foreach($specificColumns as $specificColumn){
            $lookupKey = md5($specificColumn['heading']);

            if(empty($this->_lookup[$lookupKey])){
                continue;
            }

            $columnId = $this->_lookup[$lookupKey];
            $columns[$columnId] = $this->_columns[$columnId];

            if($specificColumn['displayName']){
                $columns[$columnId]['displayName'] = $specificColumn['displayName'];
            }
        }

        return $columns;
    }

    /**
     * Creates the DB table
     */
    private function createDbTableIfNotExists()
    {
        $ipTable = ipTable($this->_name);

        $attributeDefinition = '';
        foreach ($this->_columns as $column) {
            $attributeDefinition .= ' `'.$column['attribute'].'` varchar(255),';
        }

        $sql = "
          CREATE TABLE IF NOT EXISTS $ipTable
          (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            $attributeDefinition
            PRIMARY KEY (`id`)
          ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

        try {
            ipDb()->execute($sql);
        } catch (\Ip\Exception\Db $e) {
            ipLog()->error("Could not create data table. Statement: $sql, Message: " . $e->getMessage());
            throw $e;
        }
    }

}