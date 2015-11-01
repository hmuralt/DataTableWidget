<?php

namespace Plugin\DataTableWidget\Widget\DataTable;

require_once dirname(__FILE__) . '/lib/PHPExcel/IOFactory.php';

/**
 * Class DataSource
 *
 * Represents a data source like a file in this case.
 * Interface can be extracted and other data sources can be created if needed.
 *
 * @package Plugin\DataTableWidget\Widget\DataTable
 */
class DataSource {

    /**
     * Server file repository location
     */
    const FILE_LOCATION = 'file/secure/';


    private static $SUPPORTED_SOURCE_FILE_EXTENSIONS = array('csv', 'xls', 'xlsx');
    private $_sourceFile = null;
    private $_file = null;
    private $_sourceId = null;

    /**
     * @param $sourceFile
     */
    public function __construct($sourceFile)
    {
        $this->_sourceFile = $sourceFile;
        $this->_file = ipFile(self::FILE_LOCATION . $this->_sourceFile);
        $this->_sourceId = self::createSourceId($this->_file);
    }

    /**
     * Validates the source
     *
     * @return null/string returns the errors if anything is wrong
     */
    public function validate()
    {
        if (!file_exists($this->_file)) {
            return __('File could not be checked.', 'DataTableWidget-admin');
        }

        $pathParts = pathinfo($this->_file);

        if (!in_array($pathParts['extension'], self::$SUPPORTED_SOURCE_FILE_EXTENSIONS, true)) {
            return __('File type is not supported.', 'DataTableWidget-admin');
        }

        if($this->getSourceReader() == null){
            return __('Could not read file content.', 'DataTableWidget-admin');
        }

        return null;
    }

    /**
     * Gets the source id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_sourceId;
    }

    /**
     * Gets the source reader
     *
     * @return null|SourceReader the source reader or null if could not be created
     */
    public function getSourceReader(){
        try{
            $objPHPExcel = \PHPExcel_IOFactory::load($this->_file);
            $rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
            return new SourceReader($rowIterator);
        }catch (PHPExcel_Exception $e){
            return null;
        }
    }

    /**
     * @param $file
     * @return string
     */
    private static function createSourceId($file)
    {
        return md5(hash_file('md5', $file).$file);
    }
} 