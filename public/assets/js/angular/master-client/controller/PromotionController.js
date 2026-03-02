/**
 *Name    : PromotionController.js
 *Author  : Smoothgrph Connect Pvt. Ltd
 *Date    : 05/01/2018
 *Description: This controller used for promotion add and edit
 **/

(function() {
     //table colums setting
    _getInfo= function(fName,fType){
       var ind = fieldset.findIndex(x=>x.fieldName===fName);
       if(ind>=0){
            var r =false;
            if(fType==='sortable'){
              r= (fieldset[ind].sortable!==undefined)? fieldset[ind].sortable:false;
            }else if(fType==='width'){
              r= (fieldset[ind].width!==undefined)? fieldset[ind].width:100;
            }else if(fType==='align'){
               r= (fieldset[ind].align!==undefined)? 'text-'+fieldset[ind].align:'text-left';
            }
            return r;
       }else {
            if(fType==='width'){
                return 100;
            }else if(fType==='align'){
                return 'text-left';
            }else if(fType==='sortable'){
                return false;
            } 
       }
       
       return false;
    };

    var columsSetting = [{
            field: 'id',
            displayName: 'SlNo',
            cellTemplate: '<span><%grid.appScope.seqNumber(row)+1%></span>',            
            enableSorting : _getInfo('id','sortable'),            
            minWidth : _getInfo('id','width'),
            cellClass : _getInfo('id','align'),
        },
        {
            field: 'thumbnail_image',
            displayName: 'Image',
            displayName: 'Image',
            cellTemplate: '<span><img src="<%row.entity.thumbnail_image%>"></span>',
            minWidth : _getInfo('thumbnail_image','width'),
        },

        {
            field: 'name',
            displayName: 'Product Name',
            cellTooltip: true,
            enableSorting : _getInfo('name','sortable'),
            minWidth : _getInfo('name','width'),
            cellClass : _getInfo('name','align'),
        },
        {
            field: 'product_sku',
            displayName: 'Product Sku',
            cellTooltip: true,
            enableSorting : _getInfo('product_sku','sortable'),
            minWidth : _getInfo('product_sku','width'),
            cellClass : _getInfo('product_sku','align'),
        },
        { 
            field: 'parent_name',
            displayName: 'Parent Name',
            cellTooltip: true,
            enableSorting : _getInfo('name','sortable'),
            minWidth : _getInfo('name','width'),
            cellClass : _getInfo('name','align'),
        },
        {
            field: 'parent_sku',
            displayName: 'Parent Sku',
            cellTooltip: true,
            enableSorting : _getInfo('product_sku','sortable'),
            minWidth : _getInfo('product_sku','width'),
            cellClass : _getInfo('product_sku','align'),
        },
        {
            field: 'product_type',
            displayName: 'Type',
            minWidth : _getInfo('name','width'),
            cellClass : _getInfo('name','align'),

        },
        
        {
            field: 'initial_price',
            displayName: 'Initial Price',
            cellTemplate: '<span class="skyblue"><%row.entity.initial_price%> <%row.entity.currency_code%> </sapn>',
            enableSorting : _getInfo('initial_price','sortable'),
            minWidth : _getInfo('initial_price','width'),
            cellClass : _getInfo('initial_price','align'),

        },
        {
            field: 'special_price',
            displayName: 'Special Price',
            cellTemplate: '<span class="skyblue"><%row.entity.special_price%> <%row.entity.currency_code%> </sapn>',
            enableSorting : _getInfo('special_price','sortable'),
            minWidth : _getInfo('special_price','width'),
            cellClass : _getInfo('special_price','align'),
        }
    ];
  
    angular.module("sabinaAdminApp").controller('PromotionAddEdit', ['$scope', 'salesfactoryData', '$rootScope', '$parse', '$timeout', 'uiGridConstants', '$templateCache',
        function($scope, salesfactoryData, $rootScope, $parse, $timeout, uiGridConstants, $templateCache) {
            $scope.page = 0;
            $scope.tree = [];
            $scope.loadingMore = false;
            $scope.id = 0;
            $scope.edit = false;
            $scope.method = '';
            $scope.data = [];
            /*variable used in the Add and Edit Data*/
            $scope.rule_name = '';
            $scope.description = '';
            $scope.menu_order = 1;
            $scope.label = [];
            $scope.status = 1;
            $scope.customer_group_id = [];
            $scope.customer_groups = customer_groups;
            $scope.customer_groups_selected = '';
            $scope.product_id = [];
            $scope.statusdropdown = [];
            $scope.apply_actiondropdown = [];
            $scope.coupon_type = '2';
            $scope.dynamic = [];
            $scope.dynamic.use_auto_generation = false;
            $scope.user_per_coupon = 1;
            $scope.uses_per_customer = null;
            $scope.maximum_promotion_uses = null;
            $scope.dynamic.coupon_code = '';

            $scope.from_date = '';
            $scope.to_date = '';
            // $scope.apply_action = '1';
            $scope.dynamic.apply_action = '1';
            $scope.dynamic.discount_in_amount_or_percent = '1';
            $scope.dynamic.uses_per_customer_no_limit = false;

            $scope.statusdropdown.configs = [{
                    'name': 'Yes',
                    'value': '1'
                },
                {
                    'name': 'No',
                    'value': '0'
                }
            ];
            $scope.status = $scope.statusdropdown.configs[0];
            //$scope.apply_action= '1';
            //$scope.apply_action.isDefault = true;
            $scope.discount_amount = '';
            $scope.apply_to_shipping = '1';
            $scope.apply_to_free_shipping = '1';
            $scope.stop_rules_processing = '1';
            $scope.skip_special_price = '1';
            $scope.dynamic.promotion_rule_coupon = '';

            $scope.apply_actiondropdown.config = [{
                "name": "Percent of product price discount",
                "value": "by_percent"
            }, {
                "name": "Fixed amount discount",
                "value": "by_fixed"
            }, {
                "name": "Fixed amount discount for whole cart",
                "value": "cart_fixed"
            }];
            $scope.apply_action = $scope.apply_actiondropdown.config[0];


            $scope.dynamic.coupon_formats = [{
                "name": "Alphanumeric",
                "value": "alphanum"
            }, {
                "name": "Alphabetical",
                "value": "alpha"
            }, {
                "name": "Numeric",
                "value": "num"
            }];
            $scope.coupon_format = $scope.dynamic.coupon_formats[0];

            /******code for product listing listing*****/

            $scope.dynamic.product_action_conditions = [{
                "name": "All",
                "value": "1"
            }, {
                "name": "Selected Only",
                "value": "2"
            }, {
                "name": "Except Selected",
                "value": "3"
            }];
            $scope.product_action_condition = $scope.dynamic.product_action_conditions[0];

            /**
             * This product listing  section
             * @columsSetting 
             */
            $scope.filetrFileldName = {};
            $scope.addtableRowData = {};
            $scope.salesData = [];
            $scope.settingPanel = [];
            $scope.dataLength = 0;
            $scope.filterDataObj = {};
            $scope.enabletick = true;
            $scope.selectItemTotalActive = 0;
            $scope.selectItemTotalAll = 0;
            $scope.selectItemTotalFilter = 0;
            $scope.displayTotalNumItems = 0;
            //configration section of setting panel
            $scope.tableSettingConfig = (tableConfig.filter !==undefined)? tableConfig.filter : false;
            //configration of filter button table
            $scope.tableFilterConfig = true;
            /***** hhide show table filter container******/
            $scope.tableFilterContainer = false;
            /******* This variable used for select button config section ********/
            $scope.tableSelectBtnConfig = (tableConfig.chk_action !==undefined)? tableConfig.chk_action : false;
            /**** This variable used for headre section pagination config***********/
            $scope.tableHeaderPaginationConfig = showHeadrePagination;
            /****this variable used for add row config*****/
            $scope.addrowConfig = true;
            /**** sorting field confiration ******/
            $scope.sortingConfig = false;
            $scope.showLoaderTable = true;
            /******This variable used for get all data from server at a time ******/
            $scope.getAllDataFromServerOnce = getAllDataFromServerOnce;
            //enable/disable action buttion(bulk action)
            $scope.action_btn_enable = (tableConfig.bulk_action !==undefined)? tableConfig.bulk_action : false;
            /******* new code sectoon**********/
            $scope.tabActive = true;
            $scope.tabAll = false;
            $scope.tabFilter = false;
            $scope.dragEnable = true;
            $scope.gridOptions = {};
            $scope.selBoxActBtn = false;
            $scope.totalRecords = 0;
            $scope.errorInfoLog = '<div class="no-info-blank"><h3><i class="icon-doc"></i> You have no information </h3></div>';

            var sub_action_type = "";  

            //Table grid option setting
            $scope.gridOptions = {
                columnDefs: columsSetting,
                enableVerticalScrollbar: uiGridConstants.scrollbars.NEVER,
                gridMenuCustomItems: [{
                    title: 'Reset Grid',
                    action: function($event) {
                        angular.forEach($scope.columsSetting, function(item){
                            item.visible = true;
                        });
                        $scope.gridApi.core.notifyDataChange(uiGridConstants.dataChange.COLUMN);
                    }
                }],
                rowTemplate: '<div grid="grid" class="ui-grid-draggable-row" draggable="true"><div ng-repeat="(colRenderIndex, col) in colContainer.renderedColumns track by col.colDef.name" class="ui-grid-cell" ng-class="{ \'ui-grid-row-header-cell\': col.isRowHeader, \'custom\': true }" ui-grid-cell></div></div>',
                onRegisterApi: function(gridApi) {
                    $scope.gridApi = gridApi;
                }
            };

            //This section used for get page view data from gloabl variable(paination setting).
            $scope.gridOptions.paginationPageSizes = getPaginationData("getpagination");
            $scope.gridOptions.paginationPageSize = getPaginationData("per_page_limt");
            $scope.gridOptions.PageSize = $scope.gridOptions.paginationPageSize;
            $scope.viewItemPerPage = $scope.gridOptions.paginationPageSize;
            $scope.gridOptions.minRowsToShow = $scope.gridOptions.paginationPageSize;
            $scope.gridOptions.enableHorizontalScrollbar = uiGridConstants.scrollbars.WHEN_NEEDED;


            /*
             *get product data from sever
             *@param : url {service url},
             *@param : type {product type(ex. related , upsell etc)},
             *@param : page{number },
             *@param : per_page {number 10,20},
             *@param : filter_obj {Object} optional
             */
            $scope._getTableListData = function(url, type, page, per_page, filter_obj){
                var obj = {
                    "page": (page !== undefined && page) ? page : 1,
                    "action_type": (type !== undefined && type != '') ? type : "active_product",
                    "per_page": (per_page !== undefined) ? per_page : 10,
                };

                //In case of serch (merge data set and search attribute)
                if (filter_obj !== undefined && !isEmpty(filter_obj)) {
                    if(sub_action_type!== undefined && sub_action_type === "filter"){
                        obj.sub_action_type = "filter";
                    }

                    angular.extend(obj, filter_obj);

                    //In case obj have any array then convert to string due to get method
                    angular.forEach(obj, function(item, key){
                        if(item!==undefined && angular.isArray(item) && item.length){
                            obj[key] = JSON.stringify(item);
                        }
                    });   
                }

                //Get data from server and set to grid table
                salesfactoryData.getData(url, 'GET', obj)
                    .then(
                        function(resp){
                            var tableData = resp.data;

                            if (angular.isArray(tableData.data) && tableData.data.length > 0 && tableData.status == "success") {
                                $scope.gridOptions.totalItems = tableData.total;
                                $scope.gridOptions.data = tableData.data;
                                $scope.displayTotalNumItems = tableData.total;
                                $scope.showLoaderTable = false;
                                $scope.no_result_found = false;                                
                            } else {
                                $scope.gridOptions.data.length = 0;
                                $scope.gridOptions.totalItems = tableData.total;
                                $scope.gridOptions.data = tableData.data;
                                $scope.displayTotalNumItems = tableData.total;
                                $scope.no_result_found = true;
                                $scope.showLoaderTable = false;

                            }
                        },
                        function(error) {
                            _errorHandler(error);
                        }
                    ).finally(function(){
                        $scope.showLoaderTable = false;
                    });
            };

            //call it self in case of add promation
            $scope._getTableListData(dataJsonUrl, 'active_product', 1, $scope.gridOptions.PageSize, $scope.filterDataObj);



            //Listen on display sequence number of row in table
            $scope.seqNumber = function(row) {
                var rowIndex = -1;
                var hash = row.entity.$$hashKey;
                var data = $scope.gridOptions.data;
                var indx_page = ($scope.gridOptions.paginationCurrentPage - 1) * $scope.gridOptions.PageSize;

                for (var ndx = 0; ndx < data.length; ndx++) {
                    if (data[ndx].$$hashKey == hash) {
                        rowIndex = ndx + indx_page;
                        break;
                    }
                }
                return rowIndex;
            };

            //Listen on Header pagination 
            $scope.HeaderPagination = {
                getTotalPages: function() {
                    return Math.ceil($scope.gridOptions.totalItems / $scope.gridOptions.PageSize);
                },
                nextPage: function() {
                    if ($scope.gridOptions.paginationCurrentPage < this.getTotalPages()) {
                        $scope.gridOptions.paginationCurrentPage++;
                        var filetrObject = getObjectClone($rootScope.filedSetModel);
                        $scope.showLoaderTable = true;
                        $scope._getTableListData(dataJsonUrl, 'active_product', $scope.gridOptions.paginationCurrentPage, $scope.gridOptions.PageSize, filetrObject);
                    }
                },
                previousPage: function() {
                    if ($scope.gridOptions.paginationCurrentPage > 1) {
                        $scope.gridOptions.paginationCurrentPage--;
                        $scope.showLoaderTable = true;
                        var filetrObject = getObjectClone($rootScope.filedSetModel);
                        $scope._getTableListData(dataJsonUrl, 'active_product', $scope.gridOptions.paginationCurrentPage, $scope.gridOptions.PageSize, filetrObject);
                    }
                },
                pageSizeChange: function(num) {
                    $scope.showLoaderTable = true;
                    $scope.gridOptions.paginationCurrentPage = 1;
                    var filetrObject = getObjectClone($rootScope.filedSetModel);
                    $scope._getTableListData(dataJsonUrl, 'active_product', $scope.gridOptions.paginationCurrentPage, num, filetrObject);
                    $scope.viewItemPerPage = num;
                    $scope.gridOptions.minRowsToShow = num;
                    $scope.gridOptions.paginationPageSize = num;
                    $scope.gridOptions.PageSize = num;
                },
                pageChange: function() {
                    if ($scope.gridOptions.paginationCurrentPage < this.getTotalPages()) {
                        $scope.showLoaderTable = true;
                        var filetrObject = getObjectClone($rootScope.filedSetModel);                        
                        $scope._getTableListData(dataJsonUrl, 'active_product', $scope.gridOptions.paginationCurrentPage, $scope.gridOptions.PageSize, filetrObject);
                    }
                }
            };

            /****
             *Listen on click on next footer pagination
             *@url : service url
             *@type : product type(ex. related , upsell etc)
             *@page : page number 
             *@per_page : number 10,20 
             *****/
            $scope.clickOnNext = function(page) {
                $scope.showLoaderTable = true;
                var filetrObject = getObjectClone($rootScope.filedSetModel);               
                $scope._getTableListData(dataJsonUrl, 'active', page, $scope.gridOptions.PageSize, filetrObject);
            };

            /*******
             * This grid Api function to handel All function as per as requirement.
             * This function used for drag row update database table row position.
             * Row selection and batch rwo selection.
             * ********/
            $scope.gridOptions.onRegisterApi = function(gridApi) {
                $scope.gridApi = gridApi;
                gridApi.draggableRows.on.rowDropped($scope, function(info, dropTarget) {
                    var rowOrderInfo = {
                        "fromIndex": info.fromIndex,
                        "dragRowSource": info.draggedRowEntity,
                        "toIndex": info.toIndex,
                        "dragRowTarget": info.targetRowEntity
                    }
                });
                gridApi.selection.on.rowSelectionChanged($scope, function(row) {
                    if (row.isSelected == true) {
                        $scope.product_id.push(row.entity.id);
                    } else {
                        var ind = $scope.product_id.indexOf(row.entity.id)
                        $scope.product_id.splice(ind, 1);
                    }
                    if ($scope.gridApi.selection.getSelectedRows().length > 0) {
                        $scope.selBoxActBtn = true;
                    } else {
                        $scope.selBoxActBtn = false;
                        console.log('fire after selection change');

                    }
                    angular.element('#product_ids').val($scope.product_id.toString());
                });
                gridApi.selection.on.rowSelectionChangedBatch($scope, function(data) {
                    if ($scope.gridApi.selection.getSelectedRows().length > 0) {
                        angular.forEach(data, function(datares, index) {
                            $scope.product_id.push(datares.entity.id);
                        });
                        $scope.selBoxActBtn = true;
                    } else {
                        $scope.product_id = [];
                        $scope.selBoxActBtn = false;
                    }
                    angular.element('#product_ids').val($scope.product_id.toString());

                });
                gridApi.grid.registerDataChangeCallback(function(data) {                   
                    if (!angular.isUndefined($scope.pidEdit) && angular.isArray($scope.pidEdit) && $scope.pidEdit.length > 0) {
                        $scope.pidEdit.map(function(it, k){
                            var ind = $scope.gridOptions.data.findIndex(x => x.id == it.product_id)
                            $scope.gridApi.selection.selectRow($scope.gridOptions.data[ind]);
                        });
                    } else {
                        $scope.gridApi.selection.clearSelectedRows();
                    }
                }, [uiGridConstants.dataChange.ROW]);
            };

            function saveState() {
                var state = $scope.gridApi.saveState.save();
                //localStorageService.set('gridState', state);
            }

            /*******
             * This function used for handel event of status.
             * After click on status section call a AJAX and update database table
             * and update grid data as per as condition like if data is active then set in-active vice versa.
             * *****/
            $scope.updateStatus = function(tempRow, tempCol, $event, actUrl, rowId, actName) {
                var dataObj = {
                    'id': rowId,
                    'action_type': actName
                }

                switch (actName) {
                    case 'delete':
                        if (confirm("Are you sure to delete this record?")) {
                            salesfactoryData.getData(actUrl, 'GET', dataObj).then(function(response) {
                                var index = $scope.gridOptions.data.indexOf(tempRow.entity);
                                $scope.gridOptions.data.splice(index, 1);
                            })
                        }
                        break;
                    case 'change_status':
                        salesfactoryData.getData(actUrl, 'GET', dataObj).then(function(response) {
                            tempRow.entity.status = response;
                        });
                        break;
                }
            }

            $scope.sendVerification = function(tempRow, tempCol, $event, actUrl, rowId, actName) {
                var dataObj = {
                    'id': rowId,
                    'action_type': actName
                }

                salesfactoryData.getData(actUrl, 'GET', dataObj).then(function(response) {
                    var index = $scope.gridOptions.data.indexOf(tempRow.entity);
                    $scope.gridOptions.data.splice(index, 1);
                })
            }



            /******
             * This function removeSelected and removeSelectedRow used for desleced row and remove selected row.
             * Where removeSelectedRow clear selection of rows and removeSelectedRow Reomve selected rowfrom grid as well as database Table.
             * *******/
            $scope.removeSelected = function() {
                $scope.gridApi.selection.clearSelectedRows();
            }
            $scope.removeSelectedRow = function() {
                var idArr = [];
                if (typeof $scope.gridApi.selection.getSelectedRows() != 'undefined' && $scope.gridApi.selection.getSelectedRows().length > 0) {
                    if (confirm("Are you sure you want to delete this?")) {
                        angular.forEach($scope.gridApi.selection.getSelectedRows(), function(data, index) {
                            $scope.gridOptions.data.splice($scope.gridOptions.data.lastIndexOf(data), 1);
                            idArr.push(data.id);
                        });
                        var idObj = {
                            "id": idArr
                        };
                        salesfactoryData.getData(removeRowUrl, idObj);
                    }
                } else
                    alert('Please select at least one check box');
            }
            /** This function used to handel callback function which is declare in celltamplate and do some action
             * and dataAttr.entity is used to get all field value like dataAttr.entity.id.
             **/
            $scope.funEdit = function(dataAttr) {
                console.log(dataAttr.entity);
            }
            /**
             * This both actionOnDataGrid and actionBtnClick function used for Action on Grid Data Like Delete ,Enable and Disable etc 
             * This perform after data is selected if not selected then not perform any action.
             **/
            $scope.actioOptions = [{
                    id: 0,
                    name: '---Action---'
                },
                {
                    id: 1,
                    name: 'Active'
                },
                {
                    id: 2,
                    name: 'Inactive'
                },
                {
                    id: 3,
                    name: 'Delete'
                },
            ];
            $scope.actionOnDataGrid = function() {
                var name = $scope.actionSelectBox.name;
                if (name == '---Action---') {
                    alert('Please any action');
                    $scope.selBoxActBtn = false;
                } else if (name == 'Active' || name == 'Inactive' || name == 'Delete') {
                    if ($scope.gridApi.selection.getSelectedRows().length > 0) {
                        $scope.selBoxActBtn = true;
                    } else {
                        $scope.selBoxActBtn = false;
                        alert('Please select row first');
                    }
                } else {
                    alert('Please select row first');
                }
            }

            $scope.actionBtnClick = function() {
                var name = $scope.actionSelectBox.name;
                var DataSet = $scope.gridApi.selection.getSelectedRows();
                var result = createIdArray(DataSet);
                if ($scope.gridApi.selection.getSelectedRows().length > 0) {
                    switch (name) {
                        case 'Delete':
                            if (confirm("Are you sure you want to delete this?")) {
                                var obj = {
                                    "DataSetID": result,
                                    "actionName": name
                                };
                                salesfactoryData.getData(actionUrl, 'POST', obj).then(function(resp) {
                                    angular.forEach(DataSet, function(data, index) {
                                        $scope.gridOptions.data.splice($scope.gridOptions.data.lastIndexOf(data), 1);
                                    });
                                    $scope.selBoxActBtn = false;
                                });
                            }
                            break;
                        case 'Enable':
                            var obj = {
                                "DataSetID": result,
                                "actionName": name
                            };
                            salesfactoryData.getData(actionUrl, 'POST', obj).then(function(resp) {
                                angular.forEach(DataSet, function(data, index) {
                                    var ind = $scope.gridOptions.data.indexOf(data);
                                    var tempRow = $scope.gridApi.grid.rows[ind];
                                    tempRow.entity.status = "Enabled";
                                    $scope.gridApi.selection.clearSelectedRows();
                                    $scope.gridApi.grid.refresh();
                                });
                            });
                            break;
                        case 'Disable':
                            var obj = {
                                "DataSetID": result,
                                "actionName": name
                            };
                            salesfactoryData.getData(actionUrl, 'POST', obj).then(function(resp) {
                                angular.forEach(DataSet, function(data, index) {
                                    var ind = $scope.gridOptions.data.indexOf(data);
                                    var tempRow = $scope.gridApi.grid.rows[ind];
                                    tempRow.entity.status = "Disabled";
                                    $scope.gridApi.selection.clearSelectedRows();
                                    $scope.gridApi.grid.refresh();
                                });
                            });
                            break;
                        default:
                            alert('select any action first');
                            break;
                    }
                } else {
                    $scope.selBoxActBtn = false;
                    alert('Please select row first');
                }
            }
            /*****This function return all selected row data id*****/
            function createIdArray(dataSet) {
                var idArray = [];
                angular.forEach(dataSet, function(data, index) {
                    idArray.push(data.id);
                });
                return idArray;
            };

            /*****
             * This function used for search data from grid when you are not used searching from server.
             * Where searchDataFromGrid function call on button click
             * and searchDataInGrid function used for searching.
             * ********/
            $scope.searchDataFromGrid = function(resetFlag) {              
                resetFlag =  resetFlag || '';
                if(resetFlag==='resetfilter'){
                    sub_action_type = "";
                    //empty model
                    angular.forEach($rootScope.filedSetModel, function(item,index){
                        if(angular.isObject(item) === true){
                           $rootScope.filedSetModel[index] = {"key": "","value" : "Please Select"};                     
                        }else{
                           $rootScope.filedSetModel[index] = "";                                                    
                        }                   
                    });

                    $scope.showLoaderTable = true;
                    var filetrObject = getObjectClone($rootScope.filedSetModel);
                    $scope._getTableListData(dataJsonUrl, 'active_product', $scope.gridOptions.paginationCurrentPage, $scope.gridOptions.PageSize, filetrObject);
                }else{
                    $scope.searchDataInGrid();
                }
                
                $scope.gridApi.grid.refresh();
            };

            $scope.searchDataInGrid = function(renderableRows) {
                sub_action_type = "filter";
                var filetrObject = getObjectClone($rootScope.filedSetModel);
                //search when search attribute not empty 
                if (!isEmpty(filetrObject)) {
                    $scope.showLoaderTable = true;
                    $scope._getTableListData(dataJsonUrl, '', $scope.gridOptions.paginationCurrentPage, $scope.gridOptions.PageSize, filetrObject);
                }
            };       


            /****** This function used for sort all table  ******/
            $scope.sort = function(keyname, strVal) {
                if (strVal == 'activeSort') {
                    $scope.sortKeyActive = keyname;
                    //set the sortKey to the param passed
                    $scope.reverseActive = !$scope.reverseActive;
                    //if true make it false and vice versa
                } else if (strVal == 'allSort') {
                    $scope.sortKeyAll = keyname;
                    $scope.reverseAll = !$scope.reverseAll;
                } else if (strVal == 'filterSort') {
                    $scope.sortKeyFilter = keyname;
                    $scope.reverseFilter = !$scope.reverseFilter;
                }
            }
            /***this function used for select an unselect all column in table**/
            $scope.selectAllColumnFunAll = function(actionName) {
                if (actionName == 'select') {
                    $scope.gridApi.selection.selectAllRows();
                    $scope.selectItemTotalAll = $scope.gridOptions.data.length;
                } else if (actionName == 'unselect') {
                    $scope.gridApi.selection.clearSelectedRows();
                    $scope.selectItemTotalAll = 0;
                }
            }
            /***** This fnction used for select visible part of table in active section*****/
            $scope.selectVisibleColumnsAll = function(strFlag) {
                if (strFlag == 'visible') {
                    $scope.gridApi.selection.selectAllVisibleRows();
                    console.log($scope.gridApi.selection);
                    $scope.selectItemTotalAll = ($scope.gridApi.core.getVisibleRows($scope.gridApi.grid).length);
                } else if (strFlag == 'unVisible') {
                    $scope.gridApi.selection.clearSelectedRows();
                    $scope.selectItemTotalAll = 0;
                }
            }
            /***this function used for setting panel show hide table columns ****/
            $scope.showHideTableColumn = function(strName) {
                strName = strName.replace(/\s/g, '');
                $scope[strName] = $scope[strName] === true ? false : true;
                return false;
            }
            /**
             * Filter section event handler and server callback config and assign data on table grid.
             **/
            /***** This function used for get input box value from filter section******/
            $scope.textChangeFunction = function(str, $event) {
                var currentVal = $event.target.value;
                if (currentVal !== 'undefined')
                    $scope.filterDataObj[str] = currentVal;
            }
            /*****this function used get select box value from filter section*****/
            $scope.filterSelectBoxChange = function(selectedOption, strName) {
                if (selectedOption.value !== ' ' || typeof selectedOption.value !== 'undefined')
                    $scope.filterDataObj[strName] = selectedOption.value;
            }

            $scope.checkAllActive = function() {
                ($scope.checkedAllActCal) ? $scope.checkedAllActCal = false: $scope.checkedAllActCal = true;
            }
            $scope.checkedAll = function() {
                ($scope.checkedAllCal) ? $scope.checkedAllCal = false: $scope.checkedAllCal = true;
            }
            $scope.checkedAllFilterCls = function() {
                ($scope.checkedAllColFilter) ? $scope.checkedAllColFilter = false: $scope.checkedAllColFilter = true;
            }
            //  console.log($rootScope.filedsSet)
            $scope.$watch("filetrFileldName", function(newVal, oldVal) {
                // return this.filedName;
                // console.log('fdf ffd');
                //console.log(newVal);
            }, true)
            /*** this function used for get data from add row field***/
            $scope.addRowDataGet = function($event) {
                if (!$scope.$$phase)
                    $scope.$apply();
                var currentVal = currentName = '';
                currentVal = $event.target.value;
                currentName = $event.target.name;
                $scope.addtableRowData[currentName] = currentVal;
            }
            /*****This function used for table row add in backend using dynamic url ******/
            $scope.addRowInBackendTable = function(strUrl, strAction) {
                console.log($scope.addtableRowData);
            }
            /****** This function used for get selected value form option box******/
            $scope.getSelectedOptionVal = function() {
                console.log($scope.filterUrlSelectBoxModel);
                console.log('just chil don dhhu');
            }
            $scope.testFunction = function() {
                console.log($scope.test);
            }

            //Listen on calculate table hieight dynamically
            $scope.getTableHeight = function() {
                var rowHeight = 45; // your row height
                var headerHeight = 39; // your header height
                var as = $scope.gridApi.core.getVisibleRows().length;
                return {
                    height: (as * rowHeight + headerHeight) + "px"
                };
            };

            /**********End for product Listing**************/

            //Edit promotion case
            if (pro_id != '' && pro_id !== undefined) {
                /*create coupon code list*/
                $scope.editPromotion = function(pro_id){
                    var objPage = {
                        id: pro_id
                    }
                    salesfactoryData.getData(editUrl, 'GET', objPage).then(function(result){
                       
                        res = result.data;

                        //console.log(res);

                        if (res == undefined) return;

                        $scope.pidEdit = res.promotionfor_seleced_product;
                        $scope._getTableListData(dataJsonUrl, 'active_product', 1, $scope.gridOptions.PageSize, $scope.filterDataObj);

                        $scope.rule_name = res.rule_name;
                        $scope.menu_order = parseInt(res.menu_order);
                        if (res.status == 1) {
                            $scope.status = $scope.statusdropdown.configs[0];
                        } else {
                            $scope.status = $scope.statusdropdown.configs[1];
                        }

                        if (res.product_action_condition == '1') {
                            $scope.product_action_condition = $scope.dynamic.product_action_conditions[0];
                        } else if (res.product_action_condition == '2') {
                            $scope.product_action_condition = $scope.dynamic.product_action_conditions[1];
                        } else {
                            $scope.product_action_condition = $scope.dynamic.product_action_conditions[2];
                        }
                        // $scope.product_action_condition = $scope.dynamic.product_action_conditions[0];
                        $scope.description = res.description;

                        (res.promotion_rule_desc).map(function(val){
                            $scope.label[val.lang_id] = val.label;
                        });

                        jQuery('#date-from').datepicker('update', res.from_date);
                        jQuery('#date-to').datepicker('update', res.to_date);

                        $scope.from_date = res.from_date;
                        $scope.to_date = res.to_date;
                        $scope.uses_per_customer = parseInt(res.uses_per_customer);
                        $scope.maximum_promotion_uses = parseInt(res.maximum_promotion_uses);
                        $scope.coupon_type = res.coupon_type;
                        $scope.dynamic.coupon_code = res.coupon_code;
                        //$scope.dynamic.use_auto_generation = res.use_auto_generation;
                        if (res.use_auto_generation == 1) {
                            $scope.dynamic.use_auto_generation = true;
                        } else {
                            $scope.dynamic.use_auto_generation = false;
                        }

                        $scope.user_per_coupon = res.user_per_coupon;
                        $scope.conditions_serialized = res.conditions_serialized;
                        $scope.dynamic.apply_action = res.apply_action;
                        $scope.discount_amount = res.discount_amount;

                        $scope.dynamic.discount_in_amount_or_percent = res.discount_in_amount_or_percent;
                        $scope.dynamic.apply_to_free_shipping = res.apply_to_free_shipping;
                        $scope.dynamic.stop_rules_processing = res.stop_rules_processing;
                        $scope.dynamic.promotion_rule_coupon = res.promotion_rule_coupon;
                        $scope.customer_group_id = res.selectedpromotionRuleCustomerGroupIds;
                        //set by r date 30/nov/2017
                        $scope.dynamic.maximum_promotion_uses_no_limit = (parseInt(res.maximum_promotion_uses_no_limit) === 1) ? true : false;
                        $scope.dynamic.uses_per_customer_no_limit = (parseInt(res.uses_per_customer_no_limit) === 1) ? true : false;
                        $scope.dynamic.user_per_coupon_no_limit = (parseInt(res.user_per_coupon_no_limit) === 1) ? true : false;
                        $scope.gridOptions.multiSelect = true;
                        //set data chaecked
                        $scope.gridApi.grid.modifyRows($scope.gridOptions.data);
                    });
                };
                $scope.editPromotion(pro_id);
            }

            if (typeof old_inputs != "undefined" && old_inputs != undefined) {
                $scope.PostedPromotion = function(res) {
                    $scope.rule_name = res.rule_name;
                    $scope.menu_order = parseInt(res.menu_order);
                    if (res.status == 1) {
                        $scope.status = $scope.statusdropdown.configs[0];
                    } else {
                        $scope.status = $scope.statusdropdown.configs[1];
                    }

                    if (res.product_action_condition == '1') {
                        $scope.product_action_condition = $scope.dynamic.product_action_conditions[0];
                    } else if (res.product_action_condition == '2') {
                        $scope.product_action_condition = $scope.dynamic.product_action_conditions[1];
                    } else {
                        $scope.product_action_condition = $scope.dynamic.product_action_conditions[2];
                    }
                    // $scope.product_action_condition = $scope.dynamic.product_action_conditions[0];
                    $scope.description = res.description;

                    angular.forEach(res.label, function(value, key) {
                        $scope.label[key] = value;
                    });

                    jQuery('#date-from').datepicker('update', res.from_date);
                    jQuery('#date-to').datepicker('update', res.to_date);
                    $scope.from_date = res.from_date;
                    $scope.to_date = res.to_date;
                    $scope.uses_per_customer = parseInt(res.uses_per_customer);
                    $scope.maximum_promotion_uses = parseInt(res.maximum_promotion_uses);
                    $scope.coupon_type = res.coupon_type;
                    $scope.dynamic.coupon_code = res.coupon_code;
                    //$scope.dynamic.use_auto_generation = res.use_auto_generation;
                    if (res.use_auto_generation == 1) {
                        $scope.dynamic.use_auto_generation = true;
                    } else {
                        $scope.dynamic.use_auto_generation = false;
                    }

                    $scope.user_per_coupon = res.user_per_coupon;
                    $scope.conditions_serialized = res.conditions_serialized;
                    $scope.dynamic.apply_action = res.apply_action;
                    $scope.discount_amount = res.discount_amount;

                    $scope.dynamic.discount_in_amount_or_percent = res.discount_in_amount_or_percent;
                    $scope.dynamic.apply_to_free_shipping = res.apply_to_free_shipping;
                    $scope.dynamic.stop_rules_processing = res.stop_rules_processing;
                    $scope.dynamic.promotion_rule_coupon = res.promotion_rule_coupon;

                    var customer_groups_data = angular.copy($scope.customer_groups);                    
                    $scope.customer_group_id = getDataById(old_inputs.customer_group_id, customer_groups_data);

                    //res.selectedpromotionRuleCustomerGroupIds;
                    //set by r date 30/nov/2017
                    $scope.dynamic.maximum_promotion_uses_no_limit = (parseInt(res.maximum_promotion_uses_no_limit) === 1) ? true : false;
                    $scope.dynamic.uses_per_customer_no_limit = (parseInt(res.uses_per_customer_no_limit) === 1) ? true : false;
                    $scope.dynamic.user_per_coupon_no_limit = (parseInt(res.user_per_coupon_no_limit) === 1) ? true : false;


                }
                $scope.PostedPromotion(old_inputs);

            }
            //Listen on couter increase/decrease        
            $scope.counterHandler = function(modelType, flag){
                switch (modelType) {
                    case 'menu_order':
                        if ($scope.menu_order != undefined && $scope.menu_order != null && flag === "plus") {
                            $scope.menu_order = parseInt($scope.menu_order) + 1;
                        } else if ($scope.menu_order != undefined && $scope.menu_order != null && flag === "minus" && $scope.menu_order !== 0) {
                            $scope.menu_order = parseInt($scope.menu_order) - 1;
                        } else {
                            $scope.menu_order = 0;
                            return false;
                        }
                        break;
                    case 'uses_per_customer':
                        if ($scope.uses_per_customer != undefined && $scope.uses_per_customer != null && flag === "plus") {
                            $scope.uses_per_customer = parseInt($scope.uses_per_customer) + 1;
                        } else if ($scope.uses_per_customer != undefined && $scope.uses_per_customer != null && flag === "minus" && $scope.uses_per_customer !== 0) {
                            $scope.uses_per_customer = parseInt($scope.uses_per_customer) - 1;
                        } else {
                            $scope.uses_per_customer = 0;
                            return false;
                        }
                        break;
                    case 'maximum_promotion_uses':
                        console.log($scope.maximum_promotion_uses);
                        if ($scope.maximum_promotion_uses != undefined && $scope.maximum_promotion_uses != null && flag === "plus") {
                            $scope.maximum_promotion_uses = parseInt($scope.maximum_promotion_uses) + 1;
                        } else if ($scope.maximum_promotion_uses != undefined && $scope.maximum_promotion_uses != null && flag === "minus" && $scope.maximum_promotion_uses !== 0) {
                            $scope.maximum_promotion_uses = parseInt($scope.maximum_promotion_uses) - 1;
                        } else {
                            $scope.maximum_promotion_uses = 0;
                            return false;
                        }
                        break;
                };
            };

            //Listen on change no limit (Reset model on change if user set no limit of coupon)
            $scope.resetCouponModel = function(modelType){
                console.log(modelType);
                if (modelType === "uses_per_customer" && $scope.dynamic.uses_per_customer_no_limit === true) {
                    $scope.uses_per_customer = null;
                } else if (modelType === "maximum_promotion_uses" && $scope.dynamic.maximum_promotion_uses_no_limit) {
                    $scope.maximum_promotion_uses = null;
                }
            };
        }
    ]);//end contoller

    //Listen for using id get object and return
    function getDataById(idArr, objArr){
        var result =[];
        if(idArr!== undefined && idArr.length){
            $.map(objArr, function(item){
                if(item!== undefined && item.id!== undefined){
                    var id = item.id;
                        id = id.toString();
                     
                    if(idArr.indexOf(id)>=0){
                        result.push(item);
                    }
                }
            });
        }
        

        return result;
    }

    //error handler function
    function _errorHandler(errMsg) {
        console.log('hi??');
        try {
            throw new Error("Something went badly wrong!");
        } catch (e) {
            console.log("Opps " + e)
            // swal('Oops...',e,'error');
            console.log("Opps " + e)
        };
    };
    //Listen on check object is empty or not
    function isEmpty(obj) {
        for (var key in obj) {
            if (obj.hasOwnProperty(key))
                return false;
        }
        return true;
    };

    //Listen on get clone of filter object
    function getObjectClone(obj){
        var clone = {};
        angular.forEach(obj, function(item, index){
            if(angular.isObject(item) === true){
               clone[index] = item.key;
            }else{
               clone[index] = item;
            }
        });
        return clone;
    };

})(window.angular);

/**
*@ngdoc
*@description : increment/decrement value 
*@param : action {string} -> increase/decrease
*@param : elem_id {string} -> element id
**/

function increaseDecreaseCounter(evt, action, elem_id){
    evt.stopImmediatePropagation();
    evt.preventDefault();
    if(action!== undefined && elem_id!== undefined){
         var elemVal = $('#'+elem_id).val();
         if(elemVal == '' && action === 'increase'){
            $('#'+elem_id).val(1)
         }else if(!isNaN(elemVal) && elemVal && action === 'increase'){
             elemVal = parseInt(elemVal) + 1;
             $('#'+elem_id).val(elemVal)
         }else if(!isNaN(elemVal) && elemVal &&  action === 'decrease') {  
             if(parseInt(elemVal) == 0) return;

             elemVal = parseInt(elemVal) - 1;
             $('#'+elem_id).val(elemVal);
         }else{
            $('#'+elem_id).val('');
         }  
    }
}