<?php

namespace Plugin\DataTableWidget;

use Plugin\DataTableWidget\Widget\DataTable\TableType;

class AdminFormHelper
{
    public static function createForm($isSpecificColumnRequired)
    {
        $form = self::createBaseForm($isSpecificColumnRequired);

        $field = new \Ip\Form\Field\Hidden(
            array(
                'name' => 'aa',
                'value' => 'DataTableWidget.addTableType'
            ));
        $form->addField($field);

        return $form;
    }

    public static function updateForm($isSpecificColumnRequired)
    {
        $form = self::createBaseForm($isSpecificColumnRequired);

        $field = new \Ip\Form\Field\Hidden(
            array(
                'name' => 'id'
            ));
        $field->addAttribute('ng-value', 'editTableType.id');
        $form->addField($field);

        $field = new \Ip\Form\Field\Hidden(
            array(
                'name' => 'aa',
                'value' => 'DataTableWidget.editTableType'
            ));
        $form->addField($field);

        return $form;
    }

    public static function deleteForm()
    {
        $form = new \Ip\Form();
        $form->setEnvironment(\Ip\Form::ENVIRONMENT_ADMIN);

        $field = new \Ip\Form\Field\Hidden(
            array(
                'name' => 'id'
            ));
        $field->addAttribute('ng-value', 'activeTableType.id');
        $form->addField($field);

        $field = new \Ip\Form\Field\Hidden(
            array(
                'name' => 'aa',
                'value' => 'DataTableWidget.deleteTableType'
            ));
        $form->addField($field);

        return $form;
    }

    private static function createBaseForm($isSpecificColumnRequired)
    {
        $form = new \Ip\Form();
        $form->setEnvironment(\Ip\Form::ENVIRONMENT_ADMIN);

        // GENERAL
        $form->addFieldset(new \Ip\Form\Fieldset(__('General', 'Ip-admin', false)));

        $field = new \Ip\Form\Field\Text(
            array(
                'name' => 'name',
                'label' => __('Name', 'Ip-admin', false),
            ));
        $field->addValidator("Required");
        $field->addAttribute('ng-model', 'editTableType.name');
        $form->addField($field);

        $languages = array();

        foreach(ipContent()->getLanguages() as $language){
            $languages[] = array(intval($language->getId()), $language->getTitle());
        }

        $field = new \Ip\Form\Field\Select(
            array(
                'name' => 'language',
                'label' => __('Target language', 'DataTableWidget-admin', false),
                'values' => $languages
            ));
        $field->addValidator("Required");
        $form->addField($field);

        // DISPLAY OPTIONS
        $form->addFieldset(new \Ip\Form\Fieldset(__('Display options', 'DataTableWidget-admin', false)));

        $columnOptionsValues = array();

        foreach(TableType::getColumnOptions() as $key => $tableOption){
            $columnOptionsValues[] = array($key, $tableOption['name']);
        }

        $field = new \Ip\Form\Field\Radio(
            array(
                'name' => 'columnOption',
                'label' => __('Columns and headings', 'DataTableWidget-admin', false),
                'values' => $columnOptionsValues
            ));
        $field->addAttribute('ng-model', 'editTableType.columnOption');
        $field->addAttribute('ng-change', 'selectedColumnOptionChanged()');
        $form->addField($field);

        $field = new SpecificColumn();
        $field->removeAttribute('id');
        if($isSpecificColumnRequired){
            $field->addValidator("Required");
        }
        $field->setLayout(\Ip\Form\Field::LAYOUT_NO_LABEL);
        $form->addField($field);

        return $form;
    }
}
