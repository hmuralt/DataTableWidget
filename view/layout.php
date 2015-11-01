<div ng-app="TableTypes" ng-controller="mainController" id="tableTypeController">
    <div class="_menu">
        <div class="_actions">
            <button class="btn btn-new" ng-click="addModal()"><i class="fa fa-plus"></i> <?php _e('Add', 'Ip-admin'); ?></button>
        </div>
        <ul>
            <li ng-repeat="tableType in tableTypes" ng-class="[tableType.id == activeTableType.id ? 'active' : '']" ng-cloak>
                <a href="" ng-click="activateTableType(tableType)">{{tableType.name}}</a>
            </li>
        </ul>
    </div>
    <div class="page-header">
        <h1><?php _e('Table type', 'DataTableWidget-admin'); ?></h1>
    </div>
    <div ng-show="activeTableType" ng-cloak>
        <div class="_actions clearfix">
            <button class="btn btn-danger pull-right" role="button" ng-click="deleteModal()"><?php _e('Delete', 'Ip-admin'); ?><i class="fa fa-fw fa-trash-o"></i></button>
            <button class="btn btn-new" role="button" ng-click="updateModal()"><?php _e('Edit', 'Ip-admin'); ?> <i class="fa fa-fw fa-edit"></i></button>
        </div>
        <div>
            <div class="row">
                <div class="col-md-5">
                    <h3><?php _e('Name', 'Ip-admin'); ?></h3>
                    <p>{{activeTableType.name}}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <h3><?php _e('Target language', 'DataTableWidget-admin'); ?></h3>
                    <p ng-repeat="language in languages" ng-show="language.id==activeTableType.language">
                        {{language.longDescription}}
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <h3><?php _e('Columns and headings', 'DataTableWidget-admin'); ?></h3>
                    <div>
                        <p>Option: {{columnOptions[activeTableType.columnOption].name}}</p>
                    </div>
                    <ul id="specificColumnList">
                        <li ng-repeat="specificColumn in activeTableType.specificColumns">&quot;{{specificColumn.heading}}&quot; <span ng-show="specificColumn.displayName"><?php _e('display as', 'DataTableWidget-admin'); ?> &quot;{{specificColumn.displayName}}&quot;</span></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php echo ipView('Plugin/DataTableWidget/view/addModal.php', array('createForm' => $createForm))->render(); ?>
    <?php echo ipView('Plugin/DataTableWidget/view/updateModal.php', array('updateForm' => $updateForm))->render(); ?>
    <?php echo ipView('Plugin/DataTableWidget/view/deleteModal.php', array('deleteForm' => $deleteForm))->render(); ?>
</div>
