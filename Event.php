<?php

namespace Plugin\DataTableWidget;

class Event
{
    public static function ipBeforeController()
    {
        if(!ipRoute()->isAdmin()){
            return;
        }

        ipAddCss('assets/dataTableWidgetPlugin.css');
    }
}