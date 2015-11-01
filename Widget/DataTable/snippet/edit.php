<div class="ip">
    <div id="ipWidgetDataTablePopup" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php _e('Data table widget settings', 'DataTableWidget-admin') ?></h4>
                </div>
                <div class="modal-body">
                    <?php echo $form->render(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default ipsCancel" data-dismiss="modal"><?php _e('Cancel', 'ipAdmin') ?></button>
                    <button type="button" class="btn btn-primary ipsConfirm disabled"><?php _e('Confirm', 'ipAdmin') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>