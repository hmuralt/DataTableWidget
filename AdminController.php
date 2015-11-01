<?php

namespace Plugin\DataTableWidget;

use Plugin\DataTableWidget\Widget\DataTable\SourceImporter;
use Plugin\DataTableWidget\Widget\DataTable\TableType;
use Plugin\DataTableWidget\Widget\DataTable\DataSource;

class AdminController
{
    /**
     * @ipSubmenu Table types
     */
    public function index()
    {
        ipAddJs('Ip/Internal/Core/assets/js/angular.js');
        ipAddJs('Plugin/DataTableWidget/assets/tableTypesController.js');

        $data = array(
            'createForm' => AdminFormHelper::createForm(true),
            'updateForm' => AdminFormHelper::updateForm(true),
            'deleteForm' => AdminFormHelper::deleteForm()
        );

        ipAddJsVariable('tableTypes', TableType::getAllAsArray());
        ipAddJsVariable('columnOptions', TableType::getColumnOptions());
        ipAddJsVariable('languages', ipContent()->getLanguages());

        return ipView('view/layout.php', $data)->render();
    }

    public function addTableType(){
        ipRequest()->mustBePost();
        $post = ipRequest()->getPost();
        $form = AdminFormHelper::createForm(false);
        $form->removeSpamCheck();
        $errors = $form->validate($post);

        if (!empty($errors)) {
            $data = array(
                'status' => 'error',
                'errors' => $errors
            );
            return new \Ip\Response\Json($data);
        }

        $tableTypeData = self::convertPostToTableTypeData($post);
        $tableType = TableType::create($tableTypeData);
        $tableType->save();

        $data = array(
            'status' => 'ok',
            'tableType' => $tableType->toArray()
        );

        return new \Ip\Response\Json($data);
    }

    public function editTableType(){
        ipRequest()->mustBePost();
        $post = ipRequest()->getPost();
        $form = AdminFormHelper::updateForm(false);
        $form->removeSpamCheck();
        $errors = $form->validate($post);

        if (!empty($errors)) {
            $data = array(
                'status' => 'error',
                'errors' => $errors
            );
            return new \Ip\Response\Json($data);
        }

        $tableTypeData = self::convertPostToTableTypeData($post);
        $tableType = TableType::replace($tableTypeData);

        if($tableType != null){
            $tableType->save();
            $data = array(
                'status' => 'ok',
                'tableType' => $tableType->toArray()
            );
        }else{
            $data = array(
                'status' => 'error',
                'errors' => __('Could not replace table type.', 'DataTableWidget-admin')
            );
        }

        return new \Ip\Response\Json($data);
    }

    public function deleteTableType(){
        ipRequest()->mustBePost();
        $post = ipRequest()->getPost();

        if(!isset($post['id'])){
            $data = array(
                'status' => 'error'
            );

            return new \Ip\Response\Json($data);
        }

        $deleted = TableType::remove($post['id']);

        if($deleted){
            $data = array(
                'status' => 'ok'
            );
        }else{
            $data = array(
                'status' => 'error',
                'errors' => __('Could not delete table type.', 'DataTableWidget-admin')
            );
        }

        return new \Ip\Response\Json($data);
    }

    public function checkSourceFile(){
        ipRequest()->mustBePost();
        $post = ipRequest()->getPost();

        if(!isset($post['sourceFile'])){
            $data = array(
                'status' => 'error',
                'errors' => __('Could not check file.', 'DataTableWidget-admin')
            );

            return new \Ip\Response\Json($data);
        }

        $dataSource = new DataSource($post['sourceFile']);
        $errors = $dataSource->validate();

        if(empty($errors)){
            $data = array(
                'status' => 'success'
            );
        }else{
            $data = array(
                'status' => 'error',
                'errors' => $errors
            );
        }

        return new \Ip\Response\Json($data);
    }

    private static function convertPostToTableTypeData($postData){
        $columnOptions = TableType::getColumnOptions();
        $columnOption = $columnOptions[$postData['columnOption']];

        if (!isset($postData['specificColumns']) || !$columnOption['isColumnFilter']) {
            $postData['specificColumns'] = array();
        }

        return $postData;
    }
}
