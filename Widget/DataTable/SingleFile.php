<?php

namespace Plugin\DataTableWidget\Widget\DataTable;

class SingleFile extends \Ip\Form\Field
{
    public function render($doctype, $environment)
    {
        $input = '<input ' . $this->getAttributesStr($doctype) . ' class="form-control ' . implode(
            ' ',
            $this->getClasses()
        ) . '" name="' . escAttr($this->getName()) . '" ' . $this->getValidationAttributesStr(
            $doctype
        ) . ' type="text" value="' . escAttr($this->getValue()) . '" />';

        $buttons = '<div class="input-group-btn">
            <button type="button" class="btn btn-default ipsSelectSourceFile"><i class="fa fa-file-o"> </i></button>
            <button type="button" class="btn btn-default ipsRemoveSourceFile"><i class="fa fa-trash-o"> </i></button>
        </div>';

        return '<div class="input-group">' . $input . $buttons . '</div>';
    }


}