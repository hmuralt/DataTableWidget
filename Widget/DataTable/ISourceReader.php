<?php

namespace Plugin\DataTableWidget\Widget\DataTable;

/**
 * Interface ISourceReader
 *
 * @package Plugin\DataTableWidget\Widget\DataTable
 */
interface ISourceReader
{
    /**
     * Gets the current row
     */
    function currentRow();

    /**
     * Moves to the next row
     *
     * @return array row with keys as column number/id and values as cell values
     */
    function moveToNextRow();

    /**
     * Checks if row is valid
     *
     * @return boolean
     */
    function isValid();
}