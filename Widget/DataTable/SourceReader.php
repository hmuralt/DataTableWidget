<?php

namespace Plugin\DataTableWidget\Widget\DataTable;

/**
 * Class SourceReader for files
 *
 * @package Plugin\DataTableWidget\Widget\DataTable
 */
class SourceReader implements ISourceReader
{
    private $_rowIterator;
    private $_currentRow;

    /**
     * @param $rowIterator
     */
    public function __construct($rowIterator)
    {
        $this->_rowIterator = $rowIterator;
        $this->setCurrentRow();
    }

    public function currentRow()
    {
        return $this->_currentRow;
    }

    public function moveToNextRow()
    {
        $this->_rowIterator->next();

        if ($this->_rowIterator->valid()) {
            $this->setCurrentRow();
        } else {
            $this->_currentRow = array();
        }
    }

    public function isValid()
    {
        return $this->_rowIterator->valid();
    }

    private function setCurrentRow()
    {
        $this->_currentRow = array();
        $cellIterator = $this->_rowIterator->current()->getCellIterator();
        foreach ($cellIterator as $column => $cell) {
            $this->_currentRow[$column] = $cell->getValue();
        }
    }
}