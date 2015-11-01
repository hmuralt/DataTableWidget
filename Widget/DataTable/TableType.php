<?php

namespace Plugin\DataTableWidget\Widget\DataTable;

/**
 * Class TableType
 *
 * Represents a table type. That is a definition of columns and options to use for displaying a DataTable.
 * @package Plugin\DataTableWidget\Widget\DataTable
 */
class TableType
{
    /**
     * Show all columns as is option
     */
    const SHOW_ALL_COLUMNS = 0;

    /**
     * Show only specific columns as defined
     */
    const SHOW_SPECIFIC_COLUMNS = 1;

    /**
     * Option name
     */
    const OPTION = 'DataTableWidget.tableTypes';

    private $_id;
    private $_name;
    private $_language;
    private $_columnOption;
    private $_specificColumns;

    /**
     * @param $id
     * @param $name
     * @param $language
     * @param $columnOption
     * @param $specificColumns
     */
    private function __construct($id, $name, $language, $columnOption, $specificColumns)
    {
        $this->_id = $id;
        $this->_name = $name;
        $this->_language = $language;
        $this->_columnOption = is_int($columnOption) ? $columnOption : intval($columnOption);
        $this->_specificColumns = $specificColumns;
    }

    /**
     * Creates a table type object with passed data
     *
     * @param $data
     * @return TableType
     */
    public static function create($data)
    {
        $name = $data['name'];
        $language = $data['language'];
        $columnOption = $data['columnOption'];
        $specificColumns = $data['specificColumns'];
        return new TableType(self::generateId($name), $name, $language, $columnOption, $specificColumns);
    }

    /**
     * Replaces a TableType
     *
     * @param $data
     * @return null|TableType
     */
    public static function replace($data)
    {
        $id = $data['id'];
        $name = $data['name'];
        $language = $data['language'];
        $columnOption = $data['columnOption'];
        $specificColumns = $data['specificColumns'];
        $savedTableTypes = ipGetOption(self::OPTION);

        if (!isset($savedTableTypes[$id])) {
            return null;
        }

        return new TableType($id, $name, $language, $columnOption, $specificColumns);
    }

    /**
     * Gets the TableType with passed id
     *
     * @param $id
     * @return null|TableType
     */
    public static function get($id)
    {
        $savedTableTypes = ipGetOption(self::OPTION);

        if (!isset($savedTableTypes[$id])) {
            return null;
        }

        $tableTypeAsArray = $savedTableTypes[$id];

        $id = $tableTypeAsArray['id'];
        $name = $tableTypeAsArray['name'];
        $language = $tableTypeAsArray['language'];
        $columnOption = $tableTypeAsArray['columnOption'];
        $specificColumns = $tableTypeAsArray['specificColumns'];

        return new TableType($id, $name, $language, $columnOption, $specificColumns);
    }

    /**
     * Removes the TableType with passed id
     *
     * @param $id
     * @return bool
     */
    public static function remove($id)
    {
        $savedTableTypes = ipGetOption(self::OPTION);

        if (!isset($savedTableTypes[$id])) {
            return false;
        }

        unset($savedTableTypes[$id]);

        ipSetOption(self::OPTION, $savedTableTypes);

        return true;
    }

    /**
     * Gets all TableType as array
     *
     * @return array
     */
    public static function getAllAsArray()
    {
        $savedTableTypes = ipGetOption(self::OPTION);
        $array = array();

        foreach ($savedTableTypes as $savedTableType) {
            array_push($array, $savedTableType);
        }

        return $array;
    }

    /**
     * Gets the column options
     *
     * @return array
     */
    public static function getColumnOptions()
    {
        return array(
            TableType::SHOW_ALL_COLUMNS => array('name' => __('Show all columns and their headings', 'DataTableWidget-admin', false), 'isColumnFilter' => false),
            TableType::SHOW_SPECIFIC_COLUMNS => array('name' => __('Show specific columns and/or different headings', 'DataTableWidget-admin', false), 'isColumnFilter' => true)
        );
    }

    /**
     * Gets the TableType id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Gets the TableType name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Gets the TableType language
     *
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->_language;
    }

    /**
     * Gets the column option
     *
     * @return int
     */
    public function getColumnOption()
    {
        return $this->_columnOption;
    }

    /**
     * Gets the columns to use
     *
     * @return mixed
     */
    public function getSpecificColumns()
    {
        return $this->_specificColumns;
    }

    /**
     * Saves the TableType
     */
    public function save()
    {
        $savedTableTypes = ipGetOption(self::OPTION);
        $savedTableTypes[$this->getId()] = $this->toArray();
        ipSetOption(self::OPTION, $savedTableTypes);
    }

    /**
     * Serializes the TableType to an array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->_id,
            'name' => $this->_name,
            'language' => $this->_language,
            'columnOption' => $this->_columnOption,
            'specificColumns' => $this->_specificColumns
        );
    }

    /**
     * @param $name
     * @return string
     */
    private static function generateId($name)
    {
        return md5($name . date(DATE_ATOM));
    }
}