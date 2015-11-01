<?php

namespace Plugin\DataTableWidget;


class SpecificColumn extends \Ip\Form\Field
{
    public function render($doctype, $environment)
    {
        $input = '<input class="form-control"' . $this->getValidationAttributesStr($doctype) .' name="specificColumns[{{$index}}][heading]" type="text" ng-model="specificColumn.heading" placeholder="- ' . __('original heading', 'DataTableWidget-admin', false) . ' -" />';
        $input .= '<input class="form-control" name="specificColumns[{{$index}}][displayName]" type="text" ng-model="specificColumn.displayName" placeholder="- ' . __('display name (optional)', 'DataTableWidget-admin', false) . ' -" />';
        $buttons = '<div class="input-group-btn">
            <button type="button" class="btn btn-default ipsFieldRemove" ng-click="removeSpecificColumnInput($index)"><i class="fa fa-trash-o"> </i></button>
        </div>';

        return '<div class="input-group  ' . implode(' ', $this->getClasses()) . '" ng-repeat="specificColumn in editTableType.specificColumns" ng-show="editTableType.columnOption == 1">' . $input . $buttons . '</div>';
    }


}