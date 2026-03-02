(function () {
    "used strict";

    //find index cahnge using method
    function findIndexMethod(list, matchEle) {
        var index = -1;
        for (var i = 0; i < list.length; ++i) {
            if (list[i].fieldName !== undefined && list[i].fieldName === matchEle) {
                index = i;
                break;
            }
        }

        return index;
    };

    _getInfo = function (fName, fType) {
        // var ind = fieldset.findIndex(function(x){
        //    return(x.fieldName!== undefined && x.fieldName===fName);
        // });
        var ind = findIndexMethod(fieldset, fName);

        if (ind >= 0) {
            var r = false;
            if (fType === 'sortable') {
                r = (fieldset[ind].sortable !== undefined) ? fieldset[ind].sortable : false;
            } else if (fType === 'width') {
                r = (fieldset[ind].width !== undefined) ? parseInt(fieldset[ind].width) : 100;
            } else if (fType === 'align') {
                r = (fieldset[ind].align !== undefined) ? 'text-' + fieldset[ind].align : 'text-left';
            }
            return r;
        } else {
            if (fType === 'width') {
                return 100;
            } else if (fType === 'align') {
                return 'text-left';
            } else if (fType === 'sortable') {
                return false;
            }
        }
        return false;
    };
    // var fieldset = fieldSetJson.fieldSets;
    /**** This code used for columns setting of table where field is field name of database filed.*****/
    var columsSetting = [{
            field: 'id',
            displayName: 'Id',
            cellTemplate: '<span><%grid.appScope.seqNumber(row)+1%></span>',
            enableSorting: _getInfo('id', 'sortable'),
            width: _getInfo('id', 'width'),
            cellClass: _getInfo('id', 'align'),
        },
        {
            field: 'sku',
            displayName: 'SKU',
            cellTooltip: true,
            enableSorting: _getInfo('sku', 'sortable'),
            width: _getInfo('sku', 'width'),
            cellClass: _getInfo('sku', 'align'),
        },
        {
            field: 'thumbnail_image',
            displayName: 'Image',
            cellTemplate: '<span><img src="<%row.entity.thumbnail_image%>"></span>',
            width: _getInfo('thumbnail_image', 'width'),
        },
        {
            field: 'name',
            displayName: 'Name',
            cellTooltip: true,
            enableSorting: _getInfo('name', 'sortable'),
            width: _getInfo('name', 'width'),
            cellClass: _getInfo('name', 'align'),
        },
        {
            field: 'product_type',
            displayName: 'Product Type',
            enableSorting: _getInfo('product_type', 'sortable'),
            width: _getInfo('product_type', 'width'),
            cellClass: _getInfo('product_type', 'align'),
        },

        {
            field: 'initial_price',
            displayName: 'Price',
            cellTemplate: '<span><%row.entity.currency_symbol + row.entity.initial_price%></span>',
            enableSorting: _getInfo('initial_price', 'sortable'),
            minWidth: _getInfo('initial_price', 'width'),
            cellClass: _getInfo('initial_price', 'align'),
        }
    ];


    /*****
     *@This section used to manage related product in both case product create and bundel product create
     *@Controller name : relatedPrdCtrl
     *@Template :  product_related_product (blade file)
     *@Date : 02 dec 2017
     *@Description : Manage all up sale,cross sale and related sale product
     *****/
    function relatedPrdGridHandler($scope, $rootScope, uiGridConstants, salesfactoryData) {
        //set default selected value in case of edit page (product)
        if (typeof selected_rel_bundle !== "undefined" && selected_rel_bundle.related !== undefined)
            $rootScope.prd_data.related_product_id_id = angular.copy(selected_rel_bundle.related);
        if (typeof selected_rel_bundle !== "undefined" && selected_rel_bundle.crosssel !== undefined) {
            $rootScope.prd_data.cross_sale_product_id = angular.copy(selected_rel_bundle.crosssel);
        }
        if (typeof selected_rel_bundle !== "undefined" && selected_rel_bundle.upsel !== undefined) {
            $rootScope.prd_data.up_sale_product_id = angular.copy(selected_rel_bundle.upsel);
        }

        //this variabled used for tab section in table 
        var current_active_tab = "related_product",
            sub_action_type = "";


        //All table related setting section and bundel product section
        //@related crossell upsell
        //@gridOptions : (object)
        //Controller used as vm;
        var vm = this;
        vm.displayTotalNumItems = 0, vm.selectItemTotal = 0;
        vm.filterDataObj = {};

        //used for filter. which is uesed to  store filter of current tab enable 
        vm.ftModel = {
            "related_product": {},
            "crossell_product": {},
            "upsell_product": {},
        };

        // active used for  related , all uesd for crossell and  tab_filter for  upsell
        vm.tab = {
            'active': true,
            'all': false,
            'filter': false,
        };
        vm.enableSimple = false;
        vm.enableConfig = false;
        vm.tableLoaderImgUrl = (typeof tableLoaderImgUrl !== "undefined" && tableLoaderImgUrl) ? tableLoaderImgUrl : '';
        /*******
         *Default config for all comman setting 
         *@tableFilterConfig :Filter button table,@tableFilterContainer : table filter container @tableSelectBtnConfig : select button
         *@tableHeaderPaginationConfig  : headre section pagination, @showLoaderTable :enable disable loader
         *@no_result_found :no result found,@errorInfoLog
         ********/
        vm.tableFilterConfig = (tableConfig.filter !== undefined) ? tableConfig.filter : false;
        vm.tableFilterContainer = false;
        vm.tableSelectBtnConfig = (tableConfig.chk_action !== undefined) ? tableConfig.chk_action : false;
        vm.tableHeaderPaginationConfig = showHeadrePagination;
        vm.showLoaderTable = false;
        vm.no_result_found = false;
        vm.errorInfoLog = '<div class="no-info-blank"><h3><i class="icon-doc"></i> You have no information </h3></div>';
        vm.displayTotalNumItems = 0;
        //enable/disable action buttion
        vm.action_btn_enable = (tableConfig.bulk_action !== undefined) ? tableConfig.bulk_action : false;

        //grid table initialization
        //@gridOptions  : type object
        vm.gridOptions = {};
        vm.gridOptions = {
            columnDefs: columsSetting,
            enableVerticalScrollbar: uiGridConstants.scrollbars.NEVER,
            enableHorizontalScrollbar: uiGridConstants.scrollbars.WHEN_NEEDED,
            rowTemplate: '<div grid="grid" class="ui-grid-draggable-row" draggable="true"><div ng-repeat="(colRenderIndex, col) in colContainer.renderedColumns track by col.colDef.name" class="ui-grid-cell" ng-class="{ \'ui-grid-row-header-cell\': col.isRowHeader, \'custom\': true }" ui-grid-cell></div></div>',
            onRegisterApi: function (gridApi) {
                vm.gridApi = gridApi;
            }
        };


        vm.gridOptions.paginationPageSizes = [];
        vm.gridOptions.paginationPageSizes = pagination;
        vm.gridOptions.paginationPageSize = pagination[0];
        vm.gridOptions.PageSize = vm.gridOptions.paginationPageSize;
        vm.viewItemPerPage = vm.gridOptions.paginationPageSize;
        vm.gridOptions.minRowsToShow = vm.gridOptions.paginationPageSize;


        /******
         *@get product data from sever
         *@param  url : service url
         *@param type : product type(ex. related , upsell etc)
         *@param page : page number 
         *@param prd_type_flag : which tab is enable like simple,config and related product.
         *@param per_page (number of data per page)
         *@param  filter_obj (filter data)
         ******/
        vm._getTableListData = function (url, type, page, prd_type_flag, per_page, filter_obj) {
            var obj = {
                "page": (page !== undefined && page) ? page : 1,
                "action_type": (type !== undefined && type != '') ? type : "active_product",
                "per_page": (per_page !== undefined && per_page) ? per_page : vm.gridOptions.PageSize,
            };
            //In case filter 
            if (filter_obj !== undefined && !isEmpty(filter_obj)) {
                if (sub_action_type !== undefined && sub_action_type === "filter") {
                    obj.sub_action_type = "filter";
                }

                angular.extend(obj, filter_obj);
                //In case obj have any array then convert to string due to get method
                angular.forEach(obj, function (item, key) {
                    if (item !== undefined && angular.isArray(item) && item.length) {
                        obj[key] = JSON.stringify(item);
                    }
                });
            }

            salesfactoryData.getData(url, 'GET', obj)
                .then(
                    function (resp) {
                        var tableData = resp.data;
                        if (angular.isArray(tableData.data) && tableData.data.length > 0) {
                            vm.gridOptions.totalItems = tableData.total;
                            vm.gridOptions.data = tableData.data;
                            vm.showLoaderTable = false;
                            vm.displayTotalNumItems = tableData.total;
                            vm.no_result_found = false;
                        } else {
                            vm.gridOptions.totalItems = 0;
                            vm.displayTotalNumItems = 0;
                            vm.gridOptions.data = [];
                            vm.no_result_found = true;
                            vm.showLoaderTable = false;
                        }
                    },
                    function (error) {
                        _errorHandler(error);
                    }
                ).finally(function () {
                    vm.showLoaderTable = false;
                });
        };

        /*
         *private method to get and set current filter in related model
         *@param actionFlag {string flag -> get/set/reset}
         *return {object}
         */
        vm.getSetCurrentActiveTab = function (actionFlag, active_tab, obj) {
            if (actionFlag !== undefined && active_tab !== undefined && actionFlag === "set") {
                vm.ftModel[active_tab] = angular.copy(obj);
            } else if (actionFlag !== undefined && actionFlag === "reset") {
                vm.ftModel[active_tab] = obj;
            } else if (actionFlag !== undefined && active_tab !== undefined && actionFlag === "get") {
                //$rootScope.filedSetModel = angular.copy(vm.ftModel[active_tab]);
                changeFilterModel("ds", active_tab);
                return getObjectClone(vm.ftModel[active_tab]);
            }
        }

        //This function used for enable tab section.This function call when you are using tab in table section.
        vm.enableTab = function (str, action_type) {
            sub_action_type = "";

            if (action_type !== undefined && action_type)
                current_active_tab = action_type;

            angular.forEach(vm.tab, function (item, key) {
                if (key === str) {
                    vm.tab[key] = true;
                } else {
                    vm.tab[key] = false;
                }
            });

            //on enable tab get data as per as filter if have otherwise
            if ($rootScope.prd_tab.enable_related_tab === true) {
                var current_filter = vm.getSetCurrentActiveTab("get", current_active_tab);
                //check current tab have filter or not then set sub_action_type
                sub_action_type = (!isEmpty(current_filter)) ? "filter" : "";
                vm._getTableListData(dataJsonUrl, current_active_tab, vm.gridOptions.paginationCurrentPage, current_active_tab, vm.viewItemPerPage, current_filter);
            }
        };
        // This grid Api function to handel All function as per as requirement. This function used for drag row update database table row position.Row selection and batch rwo selection.
        vm.gridOptions.onRegisterApi = function (gridApi) {
            vm.gridApi = gridApi;
            gridApi.draggableRows.on.rowDropped($scope, function (info, dropTarget) {
                //exe
            });
            gridApi.selection.on.rowSelectionChanged($scope, function (row) {
                //for simple and config product in case of bundel product tab
                if ($rootScope.prd_tab.enable_bundel_tab === true && (vm.enableSimple === true || vm.enableConfig === true)) return;
                //for related product tab
                if ($rootScope.prd_tab.enable_related_tab === true && row.isSelected === true) {
                    if (vm.tab['active'] === true) {
                        if (_exitsPrevId($rootScope.prd_data.related_product_id_id, row.entity.id) == -1)
                            $rootScope.prd_data.related_product_id_id.push(row.entity.id);
                    } else if (vm.tab['all'] === true) {
                        if (_exitsPrevId($rootScope.prd_data.cross_sale_product_id, row.entity.id) == -1)
                            $rootScope.prd_data.cross_sale_product_id.push(row.entity.id);
                    } else if (vm.tab['filter'] === true) {
                        if (_exitsPrevId($rootScope.prd_data.up_sale_product_id, row.entity.id) == -1)
                            $rootScope.prd_data.up_sale_product_id.push(row.entity.id);
                    }
                } else {
                    if (vm.tab['active'] === true) {
                        $rootScope.prd_data.related_product_id_id.splice($rootScope.prd_data.related_product_id_id.findIndex(x => x === row.entity.id), 1);
                    } else if (vm.tab['all'] === true) {
                        $rootScope.prd_data.cross_sale_product_id.splice($rootScope.prd_data.cross_sale_product_id.findIndex(x => x === row.entity.id), 1);
                    } else if (vm.tab['filter'] === true) {
                        $rootScope.prd_data.up_sale_product_id.splice($rootScope.prd_data.up_sale_product_id.findIndex(x => x === row.entity.id), 1);
                    }
                }
            });
            gridApi.selection.on.rowSelectionChangedBatch($scope, function () {
                //for simple and config product in case of bundel product tab
                if ($rootScope.prd_tab.enable_bundel_tab === true && (vm.enableSimple === true || vm.enableConfig === true)) return;
                //for related product tab
                if ($rootScope.prd_tab.enable_related_tab === true && vm.gridApi.selection.getSelectedRows().length > 0) {
                    if (vm.tab['active'] === true) {
                        $rootScope.prd_data.related_product_id_id = _.map(vm.gridApi.selection.getSelectedRows(), 'id');
                    } else if (vm.tab['all'] === true) {
                        $rootScope.prd_data.cross_sale_product_id = _.map(vm.gridApi.selection.getSelectedRows(), 'id');
                    } else if (vm.tab['filter'] === true) {
                        $rootScope.prd_data.up_sale_product_id = _.map(vm.gridApi.selection.getSelectedRows(), 'id');
                    }
                } else {
                    if (vm.tab['active'] === true)
                        $rootScope.prd_data.related_product_id_id.length = 0;
                    else if (vm.tab['all'] === true)
                        $rootScope.prd_data.cross_sale_product_id.length = 0;
                    else if (vm.tab['filter'] === true)
                        $rootScope.prd_data.up_sale_product_id.length = 0;
                }
            });
            gridApi.pagination.on.paginationChanged($scope, function (pageNumber, pageSize) {
                //pageNumberOrPageSizeChanged(pageNumber, pageSize);
            });
            gridApi.grid.registerDataChangeCallback(function (data) {
                //for simple product in cse of bundel product and for config product in case of bundel product
                //for related product change on grid if data is come form server then set checked if true
                // active used for  related , all uesd for crossell and  tab_filter for  upsell
                if ($rootScope.prd_tab.enable_related_tab === true && ($rootScope.prd_data.related_product_id_id.length > 0 || $rootScope.prd_data.cross_sale_product_id.length > 0 || $rootScope.prd_data.up_sale_product_id.length > 0)) {
                    if (vm.tab['active'] === true && $rootScope.prd_data.related_product_id_id.length > 0) {
                        for (value of $rootScope.prd_data.related_product_id_id) {
                            var index = vm.gridOptions.data.findIndex(x => x.id === value);
                            vm.gridApi.selection.selectRow(vm.gridOptions.data[index]);
                        }
                    } else if (vm.tab['all'] === true && $rootScope.prd_data.cross_sale_product_id) {
                        for (var value of $rootScope.prd_data.cross_sale_product_id) {
                            var index = vm.gridOptions.data.findIndex(x => x.id == value)
                            vm.gridApi.selection.selectRow(vm.gridOptions.data[index]);
                        }
                    } else if (vm.tab['filter'] === true && $rootScope.prd_data.up_sale_product_id) {
                        for (var value of $rootScope.prd_data.up_sale_product_id) {
                            var index = vm.gridOptions.data.findIndex(x => x.id == value)
                            vm.gridApi.selection.selectRow(vm.gridOptions.data[index]);
                        }
                    } else {
                        vm.gridApi.selection.clearSelectedRows();
                    }
                } else if ($rootScope.prd_tab.enable_bundel_tab === true && ($rootScope.prd_data.simple_product_id.length > 0 || $rootScope.prd_data.config_product_id.length > 0)) {
                    if (vm.enableSimple === true && $rootScope.prd_data.simple_product_id.length > 0) {
                        for (var value of $rootScope.prd_data.simple_product_id) {
                            var index = vm.gridOptions.data.findIndex(x => x.id == value)
                            vm.gridApi.selection.selectRow(vm.gridOptions.data[index]);
                        }
                    } else if (vm.enableConfig === true && $rootScope.prd_data.config_product_id.length > 0) {
                        for (var value of $rootScope.prd_data.config_product_id) {
                            var index = vm.gridOptions.data.findIndex(x => x.id == value)
                            vm.gridApi.selection.selectRow(vm.gridOptions.data[index]);
                        }
                    } else {
                        vm.gridApi.selection.clearSelectedRows();
                    }
                } else {
                    vm.gridApi.selection.clearSelectedRows();
                }
            }, [uiGridConstants.dataChange.ROW]);
        };

        //Grid layout manage function
        //This function used for Header pagination control********/
        vm.HeaderPagination = {
            getTotalPages: function () {
                return Math.ceil(vm.gridOptions.totalItems / vm.gridOptions.PageSize);
            },
            nextPage: function () {
                if (vm.gridOptions.paginationCurrentPage < this.getTotalPages()) {
                    vm.gridOptions.paginationCurrentPage++;
                    vm.showLoaderTable = true;
                    var filetrObject = getObjectClone($rootScope.filedSetModel);

                    if ($rootScope.prd_tab.enable_related_tab === true) {
                        var _ctf = vm.getSetCurrentActiveTab("get", current_active_tab);
                        vm._getTableListData(dataJsonUrl, current_active_tab, vm.gridOptions.paginationCurrentPage, current_active_tab, vm.viewItemPerPage, _ctf);
                    } else if ($rootScope.prd_tab.enable_bundel_tab === true) {
                        if (vm.enableSimple === true)
                            vm._getTableListData(simple_product_url, 'simple_product', vm.gridOptions.paginationCurrentPage, 'simple', vm.gridOptions.PageSize, filetrObject);
                        else if (vm.enableConfig === true)
                            vm._getTableListData(config_product_url, 'config_product', vm.gridOptions.paginationCurrentPage, 'config', vm.gridOptions.PageSize, filetrObject);
                    }
                }
            },
            previousPage: function () {
                if (vm.gridOptions.paginationCurrentPage > 1) {
                    vm.gridOptions.paginationCurrentPage--;
                    vm.showLoaderTable = true;
                    var filetrObject = getObjectClone($rootScope.filedSetModel);

                    if ($rootScope.prd_tab.enable_related_tab === true) {
                        var _ctf = vm.getSetCurrentActiveTab("get", current_active_tab);
                        vm._getTableListData(dataJsonUrl, current_active_tab, vm.gridOptions.paginationCurrentPage, current_active_tab, vm.viewItemPerPage, _ctf);
                    } else if ($rootScope.prd_tab.enable_bundel_tab === true) {
                        if (vm.enableSimple === true)
                            vm._getTableListData(simple_product_url, 'simple_product', vm.gridOptions.paginationCurrentPage, 'simple', vm.gridOptions.PageSize, filetrObject);
                        else if (vm.enableConfig === true)
                            vm._getTableListData(config_product_url, 'config_product', vm.gridOptions.paginationCurrentPage, 'config', vm.gridOptions.PageSize, filetrObject);
                    }
                }
            },
            pageSizeChange: function (num) {
                vm.showLoaderTable = true;
                vm.gridOptions.paginationCurrentPage = 1;
                vm.gridOptions.minRowsToShow = num;
                vm.viewItemPerPage = num;
                vm.gridOptions.paginationPageSize = num;
                vm.gridOptions.PageSize = num;

                var filetrObject = getObjectClone($rootScope.filedSetModel);

                if ($rootScope.prd_tab.enable_related_tab === true) {
                    var _ctf = vm.getSetCurrentActiveTab("get", current_active_tab);
                    vm._getTableListData(dataJsonUrl, current_active_tab, vm.gridOptions.paginationCurrentPage, current_active_tab, num, _ctf);
                } else if ($rootScope.prd_tab.enable_bundel_tab === true) {
                    if (vm.enableSimple === true)
                        vm._getTableListData(simple_product_url, 'simple_product', vm.gridOptions.paginationCurrentPage, 'simple', num, filetrObject);
                    else if (vm.enableConfig === true)
                        vm._getTableListData(config_product_url, 'config_product', vm.gridOptions.paginationCurrentPage, 'config', num, filetrObject);
                }


            },
            pageChange: function () {
                if (vm.gridOptions.paginationCurrentPage <= this.getTotalPages()) {
                    vm.showLoaderTable = true;
                    var filetrObject = getObjectClone($rootScope.filedSetModel);

                    if ($rootScope.prd_tab.enable_related_tab === true) {
                        var _ctf = vm.getSetCurrentActiveTab("get", current_active_tab);
                        vm._getTableListData(dataJsonUrl, current_active_tab, vm.gridOptions.paginationCurrentPage, current_active_tab, vm.viewItemPerPage, _ctf);
                    } else if ($rootScope.prd_tab.enable_bundel_tab === true) {
                        if (vm.enableSimple === true)
                            vm._getTableListData(simple_product_url, 'simple_product', vm.gridOptions.paginationCurrentPage, 'simple', vm.gridOptions.PageSize, filetrObject);
                        else if (vm.enableConfig === true)
                            vm._getTableListData(config_product_url, 'config_product', vm.gridOptions.paginationCurrentPage, 'config', vm.gridOptions.PageSize, filetrObject);
                    }
                }
            },
        };
        /******
         * This function removeSelected and removeSelectedRow used for desleced row and remove selected row.
         * Where removeSelectedRow clear selection of rows and removeSelectedRow Reomve selected rowfrom grid as well as database Table.
         * *******/
        $scope.removeSelected = function () {
            $scope.gridApi.selection.clearSelectedRows();
        };
        $scope.removeSelectedRow = function () {
            var idArr = [];
            if (typeof $scope.gridApi.selection.getSelectedRows() != undefined && $scope.gridApi.selection.getSelectedRows().length > 0) {
                if (confirm("Are you sure you want to delete this?")) {
                    angular.forEach($scope.gridApi.selection.getSelectedRows(), function (data, index) {
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
        };
        /*****This function return all selected row data id*****/
        function createIdArray(dataSet) {
            var idArray = [];
            angular.forEach(dataSet, function (data, index) {
                idArray.push(data.id);
            });
            return idArray;
        }

        /*
         *Privete method to change filter accodring to current tab
         *@param {string->crt_active_tab}
         *@param {strflag}
         */
        function changeFilterModel(strflag, crt_active_tab) {
            $scope.$evalAsync(function () {
                if (crt_active_tab !== undefined && crt_active_tab) {
                    angular.forEach($rootScope.filedSetModel, function (elem, index) {
                        if (angular.isObject(elem) == true) {
                            if (vm.ftModel[crt_active_tab][index] !== undefined) {
                                $rootScope.filedSetModel[index] = vm.ftModel[crt_active_tab][index];
                            } else {
                                $rootScope.filedSetModel[index] = {
                                    "key": "",
                                    "value": "Please Select"
                                };
                            }
                        } else {
                            if (vm.ftModel[crt_active_tab][index] !== undefined && vm.ftModel[crt_active_tab][index])
                                $rootScope.filedSetModel[index] = vm.ftModel[crt_active_tab][index];
                            else
                                $rootScope.filedSetModel[index] = "";
                        }
                    });
                }
            });
        }

        /*****
         * This function used for search data from grid when you are not used searching from server.
         * Where searchDataFromGrid function call on button click
         * and searchDataInGrid function used for searching.
         * ********/
        vm.searchDataFromGrid = function (resetFlag) {
            resetFlag = resetFlag || '';
            if (resetFlag === 'resetfilter') {
                sub_action_type = "";
                //empty model
                angular.forEach($rootScope.filedSetModel, function (item, index) {
                    if (angular.isObject(item) === true) {
                        $rootScope.filedSetModel[index] = {
                            "key": "",
                            "value": "Please Select"
                        };
                    } else {
                        $rootScope.filedSetModel[index] = "";
                    }
                });

                vm.showLoaderTable = true;
                var filetrObject = getObjectClone($rootScope.filedSetModel);
                vm.gridOptions.paginationCurrentPage = 1;
                if ($rootScope.prd_tab.enable_related_tab === true) {
                    vm.getSetCurrentActiveTab("reset", current_active_tab, {});
                    vm._getTableListData(dataJsonUrl, current_active_tab, vm.gridOptions.paginationCurrentPage, current_active_tab, vm.viewItemPerPage, filetrObject);
                } else if ($rootScope.prd_tab.enable_bundel_tab === true) {
                    if (vm.enableSimple === true)
                        vm._getTableListData(simple_product_url, 'simple_product', vm.gridOptions.paginationCurrentPage, 'simple', vm.gridOptions.PageSize, filetrObject);
                    else if (vm.enableConfig === true)
                        vm._getTableListData(config_product_url, 'config_product', vm.gridOptions.paginationCurrentPage, 'config', vm.gridOptions.PageSize, filetrObject);
                }
            } else {
                vm.searchDataInGrid();
            }

            vm.gridApi.grid.refresh();
        };

        vm.searchDataInGrid = function (renderableRows) {
            sub_action_type = "filter";
            vm.showLoaderTable = true;
            var filetrObject = getObjectClone($rootScope.filedSetModel);
            vm.gridOptions.paginationCurrentPage = 1;

            if ($rootScope.prd_tab.enable_related_tab === true) {
                vm.getSetCurrentActiveTab("set", current_active_tab, $rootScope.filedSetModel);
                vm._getTableListData(dataJsonUrl, current_active_tab, vm.gridOptions.paginationCurrentPage, current_active_tab, vm.viewItemPerPage, filetrObject);
            } else if ($rootScope.prd_tab.enable_bundel_tab === true) {
                if (vm.enableSimple === true)
                    vm._getTableListData(simple_product_url, 'simple_product', vm.gridOptions.paginationCurrentPage, 'simple', vm.gridOptions.PageSize, filetrObject);
                else if (vm.enableConfig === true)
                    vm._getTableListData(config_product_url, 'config_product', vm.gridOptions.paginationCurrentPage, 'config', vm.gridOptions.PageSize, filetrObject);
            }

        };

        /***this function used for select an unselect all column in table**/
        vm.rowSelectionFun = function (actionname) {
            if (actionname == 'select') {
                vm.gridApi.selection.selectAllRows();
                vm.selectItemTotal = vm.displayTotalNumItems;
            } else if (actionname == 'unselect') {
                vm.gridApi.selection.clearSelectedRows();
                vm.selectItemTotal = 0;
            }
        }
        /***** This fnction used for select visible part of table in active section*****/
        vm.rowVisibleSelectionFun = function (strFlag) {
            if (strFlag == 'visible') {
                vm.gridApi.selection.selectAllVisibleRows();
                vm.selectItemTotal = (vm.gridApi.core.getVisibleRows(vm.gridApi.grid).length);
            } else if (strFlag == 'unVisible') {
                vm.gridApi.selection.clearSelectedRows();
                vm.selectItemTotal = 0;
            }
        };
        /***
         * Filter section event handler and server callback config and assign data on table grid.
         * *****/
        /***** This function used for get input box value from filter section******/
        $scope.textChangeFunction = function (str, $event) {
            var currentVal = $event.target.value;
            if (currentVal !== undefined)
                vm.filterDataObj[str] = currentVal;
        }

        /*****this function used get select box value from filter section*****/
        vm.filterSelectBoxChange = function (selectedOption, strName) {
            if (selectedOption.value !== ' ' || selectedOption.value !== undefined)
                $scope.filterDataObj[strName] = selectedOption.value;
        };

        //This function used to display sequence number of row in table
        $scope.seqNumber = function (row) {
            var rowIndex = -1;
            var hash = row.entity.$$hashKey;
            var data = vm.gridOptions.data;
            var indx_page = (vm.gridOptions.paginationCurrentPage - 1) * vm.gridOptions.PageSize;

            for (var ndx = 0; ndx < data.length; ndx++) {
                if (data[ndx].$$hashKey == hash) {
                    rowIndex = ndx + indx_page;
                    break;
                }
            }
            return rowIndex;
        };
        //Manage table height
        vm.getTableHeight = function () {
            var rowHeight = 45; // your row height
            var headerHeight = 39; // your header height
            var as = vm.gridApi.core.getVisibleRows().length;
            return {
                height: (as * rowHeight + headerHeight) + "px"
            };
        };

        //check prev exits id 
        function _exitsPrevId(item, id) {
            var exits = _.findIndex(item, function (o) {
                return o == id;
            });
            return exits;
        };

        // $broadcast getRelatedPrdData on enable related product from main controller
        $scope.$on('getRelatedPrdData', function (e) {
            e.preventDefault();
            vm.showLoaderTable = true;
            vm.enableSimple = false;
            vm.enableConfig = false;
            $rootScope.filedSetModel.type = "";
            var filetrObject = vm.getSetCurrentActiveTab("get", current_active_tab);
            vm._getTableListData(dataJsonUrl, current_active_tab, 1, current_active_tab, vm.viewItemPerPage, filetrObject);
        });

        /****
         *Listen on click on next footer pagination
         *@url : service url
         *@type : product type(ex. related , upsell etc)
         *@page : page number 
         *@prd_type_flag : which tab is enable like simple,config and related product 
         *****/
        vm.clickOnNext = function (page) {
            vm.showLoaderTable = true;
            var filetrObject = getObjectClone($rootScope.filedSetModel);

            if ($rootScope.prd_tab.enable_related_tab === true) {
                var _ctf = vm.getSetCurrentActiveTab("get", current_active_tab);
                vm._getTableListData(dataJsonUrl, current_active_tab, page, current_active_tab, vm.viewItemPerPage, _ctf);
            } else if ($rootScope.prd_tab.enable_bundel_tab === true) {
                if (vm.enableSimple === true)
                    vm._getTableListData(simple_product_url, 'simple_product', page, 'simple', vm.gridOptions.PageSize, filetrObject);
                else if (vm.enableConfig === true)
                    vm._getTableListData(config_product_url, 'config_product', page, 'config', vm.gridOptions.PageSize, filetrObject);
            }
        };

        //Listen after click on simple and config bundel product button
        vm._simpleBundelProduct = function ($event, strflag) {
            if ($rootScope.prd_tab.enable_bundel_tab === true) {
                switch (strflag) {
                    case "simple":
                        vm.enableSimple = true;
                        vm.enableConfig = false;
                        vm.tab['active'] = true;
                        $rootScope.filedSetModel.name = "";
                        $rootScope.filedSetModel.sku = "";
                        $rootScope.filedSetModel.type = "normal";
                        var filetrObject = getObjectClone($rootScope.filedSetModel);
                        vm._getTableListData(simple_product_url, 'simple_product', 1, 'simple', vm.gridOptions.PageSize, filetrObject);
                        break;
                    case "config":
                        vm.enableConfig = true;
                        vm.enableSimple = false;
                        vm.tab['active'] = true;
                        $rootScope.filedSetModel.name = "";
                        $rootScope.filedSetModel.sku = "";
                        $rootScope.filedSetModel.type = "configrable";
                        var filetrObject = getObjectClone($rootScope.filedSetModel);
                        vm._getTableListData(config_product_url, 'config_product', 1, 'config', vm.gridOptions.PageSize, filetrObject);
                        break;
                    default:
                        break;
                }
            }
            $event.preventDefault();
        };
        //Listen after select any product from table then get selected and set to list for display
        vm._saveSelectedPrd_List = function ($event, strflag) {
            $event.stopPropagation();
            var selectedRow_id = _.map(vm.gridApi.selection.getSelectedRows(), 'id');
            if (selectedRow_id.length > 0) {
                switch (strflag) {
                    case "simple":
                        if ($rootScope.prd_data.simple_product_id.length > 0) {
                            selectedRow_id = _.difference(selectedRow_id, $rootScope.prd_data.simple_product_id);
                            $rootScope.prd_data.simple_product_id = $rootScope.prd_data.simple_product_id.concat(selectedRow_id);
                        } else {
                            $rootScope.prd_data.simple_product_id = $rootScope.prd_data.simple_product_id.concat(selectedRow_id);
                        }
                        //get seleted product data from server 
                        //
                        
                        salesfactoryData.getData(get_simple_prd_url, 'POST', $rootScope.prd_data.simple_product_id)
                            .then(function (r) {
                                if (!_.isUndefined(r.data) && r.data.length > 0) {
                                    vm.simplePrdData = r.data;
                                    $('#addsimpleproduct').modal('hide');
                                }
                            }, function (error) {
                                _errorHandler(error);
                                $('#addsimpleproduct').modal('hide');
                            });
                        break;
                    case "config":
                        if ($rootScope.prd_data.config_product_id.length > 0) {
                            selectedRow_id = _.difference(selectedRow_id, $rootScope.prd_data.config_product_id);
                            $rootScope.prd_data.config_product_id = $rootScope.prd_data.config_product_id.concat(selectedRow_id);
                        } else {
                            $rootScope.prd_data.config_product_id = $rootScope.prd_data.config_product_id.concat(selectedRow_id);
                        }
                        //get seleted product data from server
                        salesfactoryData.getData(get_config_prd_url, 'POST', $rootScope.prd_data.config_product_id)
                            .then(function (r) {
                                if (!_.isUndefined(r.data) && r.data.length > 0) {
                                    vm.configPrdData = r.data;
                                    $('#addconfigproduct').modal('hide');
                                }
                            }, function (error) {
                                _errorHandler(error);
                                $('#addconfigproduct').modal('hide');
                            });
                        break;
                };
            } else {
                swal('Opps..', 'Please select row first', 'warning');
            }
        };
        //bundel product edit case handel
        try {
            if (typeof execution_count != "undefined" && execution_count === 0) {
                execution_count = 1;

                if (typeof simple_prd_details != "undefined" && simple_prd_details.length > 0) {
                    vm.simplePrdData = simple_prd_details;
                    $rootScope.prd_data.simple_product_id.length = 0;
                    $rootScope.prd_data.simple_product_id = _.map(simple_prd_details, 'id');
                    //for product sorting order
                    $rootScope.prd_data.simple_config_prd_order["simple"].length = 0;
                    _.forEach(simple_prd_details, function (elem) {
                        if (elem.prd_sorting_order !== undefined && elem.prd_sorting_order != "") {
                            $rootScope.prd_data.simple_config_prd_order["simple"].push({
                                "id": elem.id,
                                "sorting_order": elem.prd_sorting_order
                            });
                        }
                    })
                }

                if (typeof config_prd_details != "undefined" && config_prd_details.length > 0) {
                    vm.configPrdData = config_prd_details;
                    $rootScope.prd_data.config_product_id.length = 0;
                    var idarr = [];
                    _.forEach(config_prd_details, function (item) {
                        idarr = idarr.concat(_.map(item.main_product_detail, "id"));

                        var _ind = _.findIndex($rootScope.prd_data.simple_config_prd_order["config"], {
                            "id": item.main_product_detail[0].id
                        });
                        if (_ind == -1) {
                            $rootScope.prd_data.simple_config_prd_order["config"].push({
                                "id": item.main_product_detail[0].id,
                                "sorting_order": item.main_product_detail[0].prd_sorting_order,
                            })
                        }

                    });
                    $rootScope.prd_data.config_product_id = angular.copy(idarr);
                }
            }
        } catch (e) {
            console.log(e);
        };

    }; //end controller
    //error handler function
    function _errorHandler(errMsg) {
        try {
            throw new Error("Something went badly wrong!");
        } catch (e) {
            console.log("Opps " + e)
            swal('Oops...', e, 'error');
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
    function getObjectClone(obj) {
        var clone = {};
        angular.forEach(obj, function (item, index) {
            if (angular.isObject(item) === true) {
                clone[index] = item.key;
            } else {
                clone[index] = item;
            }
        });
        return clone;
    };

    angular.module('sabinaAdminApp').controller('relatedPrdCtrl', ['$scope', '$rootScope', 'uiGridConstants', 'salesfactoryData', relatedPrdGridHandler]);
}).call(this);