(function ($) {
    "use strict";

    var app = angular.module('TableTypes', []);

    var mainController = function ($scope, $location) {

        $scope.tableTypes = tableTypes;
        $scope.columnOptions = columnOptions;
        $scope.languages = languages;
        $scope.activeTableType = null;
        $scope.editTableType = null;

        function specificColumn() {
            return {heading:"", displayName:""};
        }

        function onSave(popup){
            ipInitForms();
            this.submit();
        }

        $scope.activateTableType = function (tableType) {
            $scope.activeTableType = tableType;
        };

        $scope.addModal = function () {
            var popup = $('#ipsAddModal');

            $scope.editTableType = {
                id:null,
                name:'',
                language:0,
                columnOption: 0,
                specificColumns:[]
            };

            popup.find('.ipsSave').off('click').on('click', onSave.bind($('#ipsAddModal form'), popup));

            popup.find('form').off('ipSubmitResponse').on('ipSubmitResponse', function (e, response) {
                if (response && response.status == 'ok') {
                    $scope.activeTableType = response.tableType;
                    $scope.tableTypes.push($scope.activeTableType);
                    $scope.$apply();
                    popup.modal('hide');
                }
            });

            popup.modal();
        };

        $scope.updateModal = function () {
            var popup = $('#ipsUpdateModal');

            $scope.editTableType = angular.copy($scope.activeTableType);

            popup.find('.ipsSave').off('click').on('click', onSave.bind($('#ipsAddModal form'), popup));

            popup.find('form').off('ipSubmitResponse').on('ipSubmitResponse', function (e, response) {
                if (response && response.status == 'ok') {
                    $scope.activeTableType = response.tableType;
                    angular.forEach($scope.tableTypes, function(tableType, key) {
                        if (tableType.id == $scope.activeTableType.id) {
                            $scope.tableTypes[key] = $scope.activeTableType;
                        }
                    });
                    $scope.$apply();
                    popup.modal('hide');
                }
            });

            popup.modal();
        };

        $scope.deleteModal = function () {
            var popup = $('#ipsDeleteModal');

            popup.find('.ipsDelete').off('click').on('click', function () {
                $('#ipsDeleteModal form').submit();
            });

            popup.find('form').off('ipSubmitResponse').on('ipSubmitResponse', function (e, response) {
                if (response && response.status == 'ok') {
                    var index = $scope.tableTypes.indexOf($scope.activeTableType);
                    $scope.tableTypes.splice(index, 1);
                    $scope.activeTableType = null;
                    $scope.$apply();
                    popup.modal('hide');
                }
            });

            popup.modal();
        };

        $scope.selectedColumnOptionChanged = function() {
            if(!$scope.columnOptions[$scope.editTableType.columnOption].isColumnFilter) {
                return;
            }

            if($scope.editTableType.specificColumns.length < 1){
                $scope.addSpecificColumnInput();
            }
        };

        $scope.addSpecificColumnInput = function() {
            $scope.editTableType.specificColumns.push(new specificColumn());
        };

        $scope.removeSpecificColumnInput= function($index){
            $scope.editTableType.specificColumns.splice($index, 1);
        };
    };

    app.controller("mainController", ["$scope", "$location", mainController]);

})(jQuery);


