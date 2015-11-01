<?php

namespace Plugin\DataTableWidget\Widget\DataTable;

/**
 * Class FormHelper
 *
 * @package Plugin\DataTableWidget\Widget\DataTable
 */
class FormHelper
{
    /**
     * Creates the widgets edit form
     *
     * @return \Ip\Form
     * @throws \Ip\Exception
     */
    public static function editForm()
    {
        $form = new \Ip\Form();
        $form->setEnvironment(\Ip\Form::ENVIRONMENT_ADMIN);

        $form->addFieldset(new \Ip\Form\Fieldset(__('Source data', 'DataTableWidget-admin', false)));

        $field = new SingleFile(
            array(
                'name' => 'sourceFile',
                'label' => __('File', 'DataTableWidget-admin', false) . ':'
            ));
        $form->addField($field);

        $form->addFieldset(new \Ip\Form\Fieldset(__('View', 'DataTableWidget-admin', false)));

        $tableTypes = TableType::getAllAsArray();

        $values = array();
        foreach ($tableTypes as $tableType) {
            $values[] = array($tableType['id'], $tableType['name']);
        }

        $field = new \Ip\Form\Field\Select(
            array(
                'name' => 'tableTypeId',
                'label' => __('Table type', 'DataTableWidget-admin', false) . ':',
                'values' => $values
            ));
        $field->addValidator('Required');
        $form->addField($field);

        return $form;
    }
}