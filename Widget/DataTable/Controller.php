<?php

namespace Plugin\DataTableWidget\Widget\DataTable;

/**
 * Class Controller
 *
 * @package Plugin\DataTableWidget\Widget\DataTable
 */
class Controller extends \Ip\WidgetController
{
    /**
     * Default page length (amount rows in DataTable)
     */
    const PAGE_LENGTH = 10;

    /**
     * Gets widget title
     *
     * @return string
     */
    public function getTitle()
    {
        return __('Data table', 'DataTableWidget-admin', false);
    }

    /**
     * Generates the HTML
     *
     * @param int $revisionId
     * @param int $widgetId
     * @param array $data
     * @param string $skin
     * @return string
     */
    public function generateHtml($revisionId, $widgetId, $data, $skin)
    {
        if (!empty($data['error']) || !isset($data['sourceId']) || !isset($data['tableTypeId'])) {
            return parent::generateHtml($revisionId, $widgetId, $data, $skin);
        }

        $table = TableRepository::getTableOf($data['sourceId']);

        if ($table == null) {
            $data['error'] = __('Error: table not found.', 'DataTableWidget-admin');
            return parent::generateHtml($revisionId, $widgetId, $data, $skin);
        }

        $tableId = "table$widgetId";
        $tableTypeId = $data['tableTypeId'];
        $tableType = TableType::get($tableTypeId);

        if ($tableType === null) {
            $data['error'] = __('Error: table type not found.', 'DataTableWidget-admin');
            return parent::generateHtml($revisionId, $widgetId, $data, $skin);
        }

        $records = $table->getRecords($tableType->getSpecificColumns(), 0, self::PAGE_LENGTH);

        $data['dataTableHtml'] = $this->renderTableHtml($tableId, $records);

        $dataTableConfiguration = json_encode(self::getDataTableConfiguration($widgetId, $tableTypeId, $records["columns"]));

        ipAddJs('Plugin/DataTableWidget/Widget/DataTable/assets/jquery.dataTables.min.js');

        //CSS Framework plugins
        $this->addDataTableFrameworkPlugins();

        ipAddJsContent($tableId, "$('#$tableId').dataTable($dataTableConfiguration);");

        return parent::generateHtml($revisionId, $widgetId, $data, $skin);
    }

    /**
     * Renders the DataTable
     *
     * @param $tableId the table element id
     * @param $records the records
     * @return string the html table
     * @throws \Ip\Exception\View
     */
    protected function renderTableHtml($tableId, $records)
    {
        $viewData = array(
            'tableId' => $tableId,
            'records' => $records
        );

        $output = ipView('view/table.php', $viewData)->render();

        return $output;
    }

    /**
     * Renders the widget admin html
     *
     * @return string
     * @throws \Ip\Exception\View
     */
    public function adminHtmlSnippet()
    {
        $form = FormHelper::editForm();

        $data = array(
            'form' => $form
        );

        return ipView('snippet/edit.php', $data)->render();
    }

    /**
     * Updates the widget
     *
     * @param int $widgetId
     * @param array $postData
     * @param array $currentData
     * @return array
     */
    public function update($widgetId, $postData, $currentData)
    {
        $dataSource = new DataSource($postData['sourceFile']);
        $sourceId = $dataSource->getId();

        $dataToSave = array(
            'tableTypeId' => $postData['tableTypeId'],
            'sourceFile' => $postData['sourceFile'],
            'sourceId' => $sourceId
        );

        $error = $dataSource->validate();

        if (!empty($error)) {
            $dataToSave['error'] = $error;
            return $dataToSave;
        }

        if (isset($currentData['sourceId']) && $currentData['sourceId'] != $sourceId) {
            TableRepository::removeTableOf($currentData['sourceId']);
        }

        TableRepository::import($dataSource);

        return $dataToSave;
    }

    /**
     * Executed when widget is duplicated
     *
     * Each DataTable widget needs to have own copy of table respectively inform repository about new usage due to page versioning.
     * Otherwise table will be removed when the first revision is deleted. Newer revisions of widget won't work anymore.
     * (Table saving logic is encapsulated within repository. At this time each widget using the same source is also using the same table.
     * A usage counter is used to track if a table is still in use. But can be changed so that each widget uses own table)
     *
     * @param int $oldId
     * @param int $newId
     * @param array $data
     * @return array
     */
    public function duplicate($oldId, $newId, $data)
    {
        $dataSource = new DataSource($data['sourceFile']);

        TableRepository::import($dataSource);

        return $data;
    }

    /**
     * Deletes a DataTable widget
     *
     * @param int $widgetId
     * @param array $data
     */
    public function delete($widgetId, $data)
    {
        if (!isset($data['sourceId'])) {
            return;
        }

        TableRepository::removeTableOf($data['sourceId']);
    }

    /**
     * Process ajax calls from DataTable widget to return records
     *
     * @param int $widgetId
     * @param array $data
     * @return \Ip\Response\Json
     */
    public function post($widgetId, $data)
    {
        $post = ipRequest()->getPost();

        if (isset($data['error']) || !isset($data['sourceId']) || !isset($data['tableTypeId'])) {
            return new \Ip\Response\Json(array('data' => ''));
        }

        $table = TableRepository::getTableOf($data['sourceId']);

        if ($table == null) {
            return new \Ip\Response\Json(array('data' => ''));
        }

        $tableType = TableType::get($data['tableTypeId']);

        $start = $post['start'];
        $length = $post['length'];
        $orderBy = $post['columns'][$post['order'][0]['column']]['data'];
        $orderDirection = $post['order'][0]['dir'];
        $searchValue = $post['search']['value'];

        $result = $table->getRecords($tableType->getSpecificColumns(), $start, $length, $orderBy, $orderDirection, $searchValue);

        return new \Ip\Response\Json($result);
    }

    //TODO create possibility for Plugin users to define own configuration (through Theme? Settings page?... tbd.)
    /**
     * Gets a default DataTable configuration
     *
     * @param $widgetId
     * @param $tableTypeId
     * @param $columns
     * @return array
     */
    private static function getDataTableConfiguration($widgetId, $tableTypeId, $columns)
    {
        $columnsData = array();

        foreach ($columns as $id => $heading) {
            $columnsData[] = array('data' => $id);
        }

        $configuration = array(
            'pageLength' => self::PAGE_LENGTH,
            'processing' => true,
            'serverSide' => true,
            'ajax' => array(
                'url' => ipConfig()->baseUrl(),
                'type' => 'POST',
                'data' => array(
                    'sa' => 'Content.widgetPost',
                    'securityToken' => ipSecurityToken(),
                    'widgetId' => $widgetId,
                    'tableTypeId' => $tableTypeId
                )
            ),
            'language' => array(
                'loadingRecords' => __('Loading data. Please wait...', 'DataTableWidget', false),
                'processing' => __('Processing data. Please wait...', 'DataTableWidget', false),
                'lengthMenu' => __('Show _MENU_ records per page', 'DataTableWidget', false),
                'zeroRecords' => __('No records found...', 'DataTableWidget', false),
                'info' => __('Show _START_ to _END_ of _TOTAL_ found records', 'DataTableWidget', false),
                'infoEmpty' => __('No records available', 'DataTableWidget', false),
                'infoFiltered' => __('- of total _MAX_ records', 'DataTableWidget', false),
                'infoThousands' => __(',', 'DataTableWidget', false),
                'search' => __('Search', 'DataTableWidget', false),
                'paginate' => array(
                    'first' => __('First page', 'DataTableWidget', false),
                    'last' => __('Last page', 'DataTableWidget', false),
                    'next' => __('Next', 'DataTableWidget', false),
                    'previous' => __('Previous',  'DataTableWidget', false)
                )
            ),
            'columns' => $columnsData,
        );

        return $configuration;
    }

    /**
     * Adds DataTable CSS framework plugins
     */
    private function addDataTableFrameworkPlugins()
    {
        $selectedPlugin = ipGetOption('DataTableWidget.cssFrameworkPlugin');

        if ($selectedPlugin === "Bootstrap") {
            ipAddCss('Plugin/DataTableWidget/Widget/DataTable/assets/plugins/dataTables.bootstrap.min.css');
            ipAddJs('Plugin/DataTableWidget/Widget/DataTable/assets/plugins/dataTables.bootstrap.min.js');
        } else if ($selectedPlugin === "Foundation") {
            ipAddCss('Plugin/DataTableWidget/Widget/DataTable/assets/plugins/dataTables.foundation.min.css');
            ipAddJs('Plugin/DataTableWidget/Widget/DataTable/assets/plugins/dataTables.foundation.min.js');
        } else if ($selectedPlugin === "jQueryUI") {
            ipAddCss('Plugin/DataTableWidget/Widget/DataTable/assets/plugins/dataTables.jqueryui.min.css');
            ipAddJs('Plugin/DataTableWidget/Widget/DataTable/assets/plugins/dataTables.jqueryui.min.js');
        }
    }
}
