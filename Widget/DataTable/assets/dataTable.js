var dataTableWidgetPopup = (function ($) {
    "use strict";

    var publicMethods = {};

    var $popup,
        $tableTypeIdSelect,
        $sourceFileInput,
        $confirmButton,
        $selectFileButton,
        $removeFileButton,
        $errorContainer;
    var loadedWidget;
    var save;

    publicMethods.init = function () {
        if ($popup) {
            return;
        }

        $popup = $('#ipWidgetDataTablePopup');
        $tableTypeIdSelect = $popup.find('select[name=tableTypeId]');
        $sourceFileInput = $popup.find('input[name=sourceFile]');
        $confirmButton = $popup.find('.ipsConfirm');
        $selectFileButton = $popup.find('.ipsSelectSourceFile');
        $removeFileButton = $popup.find('.ipsRemoveSourceFile');
        $errorContainer = $popup.find('.name-sourceFile .help-error');

        $tableTypeIdSelect.on('change', updateLoadedWidget);
        $sourceFileInput.on('change', updateLoadedWidget);
        $sourceFileInput.on('change', updateFileButtonState);
    };

    publicMethods.load = function (widgetData, saveCallback) {
        loadedWidget = {
            tableTypeId: widgetData.tableTypeId,
            sourceFile: widgetData.sourceFile
        };

        save = saveCallback;

        $confirmButton.off();
        $confirmButton.addClass('disabled');
        publicMethods.hideError();

        if (loadedWidget.tableTypeId) {
            $tableTypeIdSelect.val(loadedWidget.tableTypeId);
        } else {
            $tableTypeIdSelect.find('option:selected').prop('selected', false);
            $tableTypeIdSelect.find('option:first').prop('selected', 'selected');
        }

        if (loadedWidget.sourceFile) {
            addFile(loadedWidget.sourceFile);
        } else {
            removeFile();
        }
    };

    publicMethods.show = function () {
        $popup.modal();
    };

    publicMethods.hide = function () {
        $popup.modal('hide');
    };

    publicMethods.hideError = function () {
        $errorContainer.html('');
        $errorContainer.hide();
    };

    publicMethods.showError = function (error) {
        $errorContainer.html(error);
        $errorContainer.show();
    };

    function updateLoadedWidget() {
        loadedWidget.tableTypeId = $tableTypeIdSelect.val();
        loadedWidget.sourceFile = $sourceFileInput.val();

        if (loadedWidget.tableTypeId == "" || loadedWidget.sourceFile == "") {
            $confirmButton.off();
            $confirmButton.addClass('disabled');
        } else {
            $confirmButton.off().on('click', saveWidget);
            $confirmButton.removeClass('disabled');
        }
    }

    function updateFileButtonState() {
        if ($sourceFileInput.val() == "") {
            $selectFileButton.off().on('click', browseFile);
            $selectFileButton.removeClass('disabled');

            $removeFileButton.off();
            $removeFileButton.addClass('disabled');
        } else {
            $selectFileButton.off();
            $selectFileButton.addClass('disabled');

            $removeFileButton.off().on('click', removeFile);
            $removeFileButton.removeClass('disabled');
        }
    }

    function browseFile(){
        ipBrowseFile(function (files) {
            addFile(files[0].fileName);
        }, {preview: 'list', secure: 1});
    }

    function addFile(file) {
        $sourceFileInput.val(file).trigger('change');
    }

    function removeFile(){
        $sourceFileInput.val("").trigger('change');
    }

    function saveWidget() {
        save(loadedWidget);
    }

    return publicMethods;

})(jQuery);

var IpWidget_DataTable = function () {
    "use strict";

    var context = {};

    this.init = function ($widgetObject, data) {
        context.data = data;
        context.widgetObject = $widgetObject;
        context.widgetOverlay = $('<div></div>');
        context.widgetOverlay.on('click', openPopup);
        context.widgetObject.prepend(context.widgetOverlay);

        dataTableWidgetPopup.init();

        $(document).on('ipWidgetResized', fixOverlay);

        $(window).on('resize', fixOverlay);

        fixOverlay();
    };

    this.onAdd = function () {
        openPopup();
    };

    function openPopup() {
        dataTableWidgetPopup.load(context.data, onSave);
        dataTableWidgetPopup.show();
    }

    function fixOverlay() {
        context.widgetOverlay
            .css('position', 'absolute')
            .css('z-index', 1000) // should be higher enough but lower than widget controls
            .width(context.widgetObject.width())
            .height(context.widgetObject.height());
    }

    function onSave(widgetData) {
        if (context.data.tableTypeId == widgetData.tableTypeId && context.data.sourceFile == widgetData.sourceFile) {
            dataTableWidgetPopup.hide();
            return;
        }

        validateOnServer(widgetData);
    }

    function validateOnServer(widgetData) {
        $.ajax({
            type: 'POST',
            url: ip.baseUrl,
            data: {
                'aa': 'DataTableWidget.checkSourceFile',
                'securityToken': ip.securityToken,
                'sourceFile': widgetData.sourceFile
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    dataTableWidgetPopup.hideError();
                    save(widgetData);
                } else {
                    dataTableWidgetPopup.showError(response.errors);
                }
            },
            error: function () {
                //TODO message i18n
                dataTableWidgetPopup.showError('Could not check file.');
            }
        });
    }

    function onSaved($newWidget) {
        var table = $newWidget.find('table');
        table  .dataTable(table.data('datatableconfiguration'));
    }

    function save(widgetData) {
        context.data.tableTypeId = widgetData.tableTypeId;
        context.data.sourceFile = widgetData.sourceFile;

        context.widgetObject.save(widgetData, true, onSaved);

        dataTableWidgetPopup.hide();
    }
};