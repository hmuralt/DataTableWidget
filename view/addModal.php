<div id="ipsAddModal" class="modal fade" ng-cloak>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php _e('Table type', 'DataTableWidget-admin'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo $createForm ?>
                <div class="_actions" ng-show="columnOptions[editTableType.columnOption].isColumnFilter">
                    <button class="btn btn-new" ng-click="addSpecificColumnInput()"><i class="fa fa-plus"></i> <?php _e('Add', 'Ip-admin'); ?></button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Cancel', 'Ip-admin'); ?></button>
                <button type="button" class="ipsSave btn btn-primary"><?php _e('Save', 'Ip-admin'); ?></button>
            </div>
        </div>
    </div>
</div>
