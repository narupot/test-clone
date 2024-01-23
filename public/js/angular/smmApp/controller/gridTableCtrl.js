/*
*@Description : table handler front and back-end both
*@Author : SMOOTHGRAPH CONNECT Pvt Ltd.
**/


(function(){
    "used strict";

    angular.module('smm-app')
    .controller('gridtableCtrl', ['$scope','$timeout','$window','$sce','$rootScope','salesfactoryData','uiGridConstants',tableCtrlHandler]);

    function tableCtrlHandler($scope,$timeout,$window,$sce,$rootScope,salesfactoryData,uiGridConstants){
        //global variable config       
        $scope.addtableRowData = {};
        $scope.displayTotalNumItems = 0,$scope.selectItemTotal=0;
        $scope.filterDataObj = {};
        //configration of filter button table
        $scope.tableFilterConfig = (tableConfig.filter !==undefined)? tableConfig.filter : false;
        //hide show table filter container
        $scope.tableFilterContainer = false;
        //This variable used for select button config section 
        $scope.tableSelectBtnConfig =(tableConfig.chk_action !==undefined)? tableConfig.chk_action : false;
        //This variable used for headre section pagination config
        $scope.tableHeaderPaginationConfig = showHeadrePagination;
        //this variable used for add row config
        $scope.addrowConfig = true;
        $scope.showLoaderTable = true;
        //enable/disable action buttion(bulk action)
        $scope.action_btn_enable = (tableConfig.bulk_action !==undefined)? tableConfig.bulk_action : false;
        
        /*This is used to manag structure
        *@reset_btn_enable ->used for enable reset table button
        *@save_table_btn_enable -> used for enable table structure(means table columns width/order will save)
        *@after_save_state -> used for store after save by user table structure
        */
        $scope.structure={ 
            reset_btn_enable : (tableConfig.save_table_structure !== undefined)? tableConfig.save_table_structure : false,
            save_table_btn_enable : (tableConfig.save_table_structure !== undefined)? tableConfig.save_table_structure : false,
            after_save_state :{},           
        };

        /******This variable used for get all data from server at a time ******/
        $scope.getAllDataFromServerOnce = true;//getAllDataFromServerOnce;
        $scope.tabActive = true;
        $scope.tabAll = false;
        $scope.tabFilter = false;
        $scope.dragEnable = false;
        $scope.gridOptions = {}; 
        $scope.no_result_found=false;
        $scope.errorInfoLog ='<div class="no-info-blank"><h3><i class="icon-doc"></i> You have no information </h3></div>';
        $scope.tableLoaderImgUrl = tableLoaderImgUrl;


        //used for filter tab section
        $scope.filterTabEnable = false;
        var filterId ='';
        
        //this variabled used for tab section in table 
        //This variable used for which type of data get from server like active, all ,filter(some specific data approve)
        var current_active_tab = (typeof currentActiveTab!= "undefined") ? currentActiveTab :  "active",
            sub_action_type = "",
            current_action_id = '',
            user_action_selection ="",
            tableSessionManage = function(flag, curt_tab, action){},
            customRowSelection = function(action, row){},
            addRemoveProductId = function(strFlag, data){},
            _setRowSelected = function(prd_id_arr, matchFlag){}; 
        var sortData = {'field' : '', 'order' : ''};
        var PAGE_LOAD = true;

        /**
        *@ngdoc
        *@description : This object used for session management for table like (current tab filter, 
        * current tab page size, current page and current tab selection action).
        *@param : tableSession { object}     
        **/
        var tableSession = { 
            all : {
                page_sizes : 0,
                page : 1,
                select_all : false,
                select_visible : false,
                filter_data :  {},
                custom_selection : [],
            },
            active : {
                page_sizes : 0,
                page : 1,
                select_all : false,
                select_visible : false,
                filter_data :  {},
                custom_selection : [],
            },
            waiting : {
                page_sizes : 0,
                page : 1,
                select_all : false,
                select_visible : false,
                filter_data :  {},
                custom_selection : [],
            },
            approved : {
                page_sizes : 0,
                page : 1,
                select_all : false,
                select_visible : false,
                filter_data :  {},
                custom_selection : [],
            },
            reject : {
                page_sizes : 0,
                page : 1,
                select_all : false,
                select_visible : false,
                filter_data :  {},
                custom_selection : [],
            },
            cancel : {
                page_sizes : 0,
                page : 1,
                select_all : false,
                select_visible : false,
                filter_data :  {},
                custom_selection : [],
            },         
        };

        //grid table initialization
        //@gridOptions  : type object
        $scope.gridOptions = {
            columnDefs : columsSetting,
            enableVerticalScrollbar: uiGridConstants.scrollbars.NEVER,
            enableHorizontalScrollbar : uiGridConstants.scrollbars.WHEN_NEEDED,
            rowTemplate : '<div grid="grid" class="ui-grid-draggable-row" draggable="false"><div ng-repeat="(colRenderIndex, col) in colContainer.renderedColumns track by col.colDef.name" class="ui-grid-cell" ng-class="{ \'ui-grid-row-header-cell\': col.isRowHeader, \'custom\': true }" ui-grid-cell></div></div>',
            onRegisterApi : function(gridApi) {
                $scope.gridApi = gridApi;
            }
        };

        //This section used for get page view data from gloabl variable(paination setting).
        $scope.gridOptions.paginationPageSizes = getPaginationData("getpagination");
        $scope.gridOptions.paginationPageSize = getPaginationData("per_page_limt");
        $scope.gridOptions.PageSize = $scope.gridOptions.paginationPageSize;
        $scope.viewItemPerPage = $scope.gridOptions.paginationPageSize;
        $scope.gridOptions.minRowsToShow = $scope.gridOptions.paginationPageSize;   

        //Enable scroll bar of table in case of mobile device view
        if($(window).width()>=320 && $(window).width()<=768)
            $scope.gridOptions.enableHorizontalScrollbar = 1;

        /*
        *This function used for get data from server and assign in table creator variable
        * @param url : action url
        * @param type : active | all | filter data 
        * @param page : number (1,2)
        * @param per_page : number (10,20)
        * @param : object (filter data)
        */
        $scope.getTableListData = function(url, type, page, per_page, filter_obj){
            var obj ={
                "page"  : (page!==undefined && page) ? page : 1,
                "action_type" :  (type!==undefined && type!='') ? type : current_active_tab ,
                "per_page" : (per_page!==undefined && per_page) ?  per_page : $scope.gridOptions.PageSize,
                "action_id" : current_action_id,
                "sorting_order" : (sortData.field!='' && sortData.order!='') ? sortData : "",
            };

            /********new feature for session filter on page load 18 apr 2019*******/
            let sessionData = sessionStorage.getItem(current_active_tab);
            if(sessionData!=null && PAGE_LOAD){
                sessionData = JSON.parse(sessionData);
                filter_obj = getObjectClone(sessionData);
                sub_action_type = 'filter';
                $rootScope.filedSetModel = sessionData;
                tableSession[current_active_tab].filter_data = sessionData;
                // sessionStorage.clear();                
            }
            
            PAGE_LOAD = false;
            /******end*****/

            //In case filter 
            if(filter_obj!==undefined && !isEmpty(filter_obj)){

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
            //In case of customer report section if have custermor email
            if(typeof order_email!="undefined"){
                obj["email"] = order_email;
            }

            if(typeof search_text!="undefined"){
                obj["search_text"] = search_text;
            }
            
            
            dataJsonUrl = (angular.isUndefined(url) || url ==='')? dataJsonUrl : url;
          
            salesfactoryData.getData(dataJsonUrl,'GET',obj).then(function(rs){
                if(typeof rs !== "undefined" && rs.data!= null && rs.xhrStatus !== "error"){
                    var d = rs.data;
                    if(!angular.isUndefined(d.data) && angular.isArray(d.data) && d.data.length){
                        $scope.no_result_found=false;
                        $scope.gridOptions.data = [];
                        $scope.gridOptions.totalItems = (d.total);
                        $scope.gridOptions.data = d.data;
                        $scope.displayTotalNumItems = d.total;
                        $scope.showLoaderTable = false;   
                        tableSessionManage("user_action", current_active_tab); 
                        $('#totpendingitems').html(d.total);
                    }else{                        
                        $scope.no_result_found=true;  
                        // In case response data is empty string
                        $scope.gridOptions.totalItems = 0;
                        $scope.gridOptions.data = [];
                        $scope.displayTotalNumItems = 0;
                        $scope.no_result_found=true;
                        $scope.showLoaderTable = false;
                    }
                }                                      
            },function(error){
                $scope.showLoaderTable=false;
                $scope.no_result_found=false;
                _errorHandler();
            }).finally(function(){
                $scope.showLoaderTable = false;
            }); 
        };

         //Listen on controller load 
        $scope.getTableListData('','',1,$scope.gridOptions.PageSize);

        //This function used for Header pagination control
        $scope.HeaderPagination = {
            getTotalPages : function() {
                return Math.ceil($scope.gridOptions.totalItems / $scope.gridOptions.PageSize);
            },
            nextPage : function() {
                if ($scope.gridOptions.paginationCurrentPage < this.getTotalPages()) {
                    $scope.gridOptions.paginationCurrentPage++;
                    $scope.showLoaderTable = true;                    
                    //in case of normal tab section means not cutstom filter tab                   
                    if(current_active_tab!== '' && current_active_tab!=="filter_tab"){
                        var filetrObject = tableSessionManage("get", current_active_tab, "filter_restore");
                         //getObjectClone($rootScope.filedSetModel);
                         $scope.getTableListData('','',$scope.gridOptions.paginationCurrentPage,$scope.gridOptions.PageSize, filetrObject);
                    }else if(current_active_tab ==="filter_tab" && filterId!==undefined && filterId!==''){
                        //In case filter tab model
                        var fobj = angular.copy($scope.filterObj.filterAtrModel[filterId]);
                        $scope.getTableListData('','',$scope.gridOptions.paginationCurrentPage,$scope.gridOptions.PageSize, fobj);
                    }                  
                }
            },
            previousPage : function() {
                if ($scope.gridOptions.paginationCurrentPage > 1) {
                    $scope.gridOptions.paginationCurrentPage--;
                    $scope.showLoaderTable = true;
                    //in case of normal tab section means not cutstom filter tab                   
                    if(current_active_tab!== '' && current_active_tab!=="filter_tab"){
                        var filetrObject = tableSessionManage("get", current_active_tab, "filter_restore");
                         //getObjectClone($rootScope.filedSetModel);
                         $scope.getTableListData('','',$scope.gridOptions.paginationCurrentPage,$scope.gridOptions.PageSize, filetrObject);
                    }else if(current_active_tab ==="filter_tab" && filterId!==undefined && filterId!==''){
                        //In case filter tab model
                        var fobj = angular.copy($scope.filterObj.filterAtrModel[filterId]);
                        $scope.getTableListData('','',$scope.gridOptions.paginationCurrentPage,$scope.gridOptions.PageSize, fobj);
                    }
                }
            },
            pageSizeChange : function(num) {
                $scope.showLoaderTable = true;
                $scope.gridOptions.paginationCurrentPage = 1;
                $scope.gridOptions.minRowsToShow = num;
                $scope.viewItemPerPage = num;
                $scope.gridOptions.paginationPageSize = num;
                $scope.gridOptions.PageSize = num;

                //in case of normal tab section means not cutstom filter tab                   
                if(current_active_tab!== '' && current_active_tab!=="filter_tab"){
                    var filetrObject = tableSessionManage("get", current_active_tab, "filter_restore");
                     //getObjectClone($rootScope.filedSetModel);
                     $scope.getTableListData('','',$scope.gridOptions.paginationCurrentPage, num, filetrObject);
                }else if(current_active_tab ==="filter_tab" && filterId!==undefined && filterId!==''){
                    //In case filter tab model
                    var fobj = angular.copy($scope.filterObj.filterAtrModel[filterId]);
                    $scope.getTableListData('','',$scope.gridOptions.paginationCurrentPage, num, fobj);
                }             
            },
            pageChange : function(){
                if ($scope.gridOptions.paginationCurrentPage <= this.getTotalPages()) {
                    $scope.showLoaderTable = true;                    
                    //in case of normal tab section means not cutstom filter tab                   
                    if(current_active_tab!== '' && current_active_tab!=="filter_tab"){
                        var filetrObject = tableSessionManage("get", current_active_tab, "filter_restore");
                         //getObjectClone($rootScope.filedSetModel);
                         $scope.getTableListData('','',$scope.gridOptions.paginationCurrentPage,$scope.gridOptions.PageSize, filetrObject);
                    }else if(current_active_tab ==="filter_tab" && filterId!==undefined && filterId!==''){
                        //In case filter tab model
                        var fobj = angular.copy($scope.filterObj.filterAtrModel[filterId]);
                        $scope.getTableListData('','',$scope.gridOptions.paginationCurrentPage,$scope.gridOptions.PageSize, fobj);
                    }                    
                }
            },
        };

        /****
        *Listen on click on next footer pagination
        *@url : service url
        *@type : product type(ex. related , upsell etc)
        *@page : page number 
        *@prd_type_flag : which tab is enable like simple,config and related product 
        *****/
        $scope.clickOnNext = function(page){
            $scope.showLoaderTable=true;           
            //in case of normal tab section means not cutstom filter tab                   
            if(current_active_tab!== '' && current_active_tab!=="filter_tab"){
                var filetrObject = tableSessionManage("get", current_active_tab, "filter_restore");
                 //getObjectClone($rootScope.filedSetModel);
                 $scope.getTableListData('', '', page,$scope.gridOptions.PageSize, filetrObject);
            }else if(current_active_tab ==="filter_tab" && filterId!==undefined && filterId!==''){
                //In case filter tab model
                var fobj = angular.copy($scope.filterObj.filterAtrModel[filterId]);
                //call to server
                $scope.getTableListData('', '', page,$scope.gridOptions.PageSize, fobj);
            }                          
        };

        //This function used to display sequence number of row in table
        $scope.seqNumber = function(row) {
            var rowIndex = -1;
            var hash = row.entity.$$hashKey;
            var data = $scope.gridOptions.data; 
            var indx_page = ($scope.gridOptions.paginationCurrentPage -1)*$scope.gridOptions.PageSize;
            
            for (var ndx = 0; ndx < data.length; ndx++) {
                if (data[ndx].$$hashKey == hash) {
                    rowIndex = ndx+indx_page;
                    break;
                }
            }
            return rowIndex;
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
                tableSessionManage("reset", current_active_tab, 'reset_filter');
                $scope.gridOptions.paginationCurrentPage = 1;
                $scope.getTableListData(dataJsonUrl, '', $scope.gridOptions.paginationCurrentPage, $scope.gridOptions.PageSize, filetrObject);               
            }else{
                $scope.searchDataInGrid();
            }

            $scope.gridApi.grid.refresh();
        };

        $scope.searchDataInGrid = function(renderableRows) {           
            sub_action_type = "filter";
            var filetrObject = getObjectClone($rootScope.filedSetModel);           
            tableSessionManage("set", current_active_tab, 'set_filter');
            //search when search attribute not empty
            if (!isEmpty(filetrObject)) {
                $scope.showLoaderTable = true;
                $scope.gridOptions.paginationCurrentPage = 1;
                $scope.getTableListData(dataJsonUrl, '', $scope.gridOptions.paginationCurrentPage, $scope.gridOptions.PageSize, filetrObject);
            }
        };

         /*****
         * This function used for enable tab section.
         * This function call when you are using tab in table section.
         * *******/
        $scope.enableTab = function(str,ftInfo) {
            sub_action_type = "";
            current_active_tab = "";
            current_action_id = "";
            //for active tab click or page have mutiple tab with diffrent data set like filter
            if (typeof str!== "undefined" && str!== 'filter_tab') {                
                $scope.tabActive = true;
                $scope.tabAll = false;
                $scope.tabFilter = false;
                $scope.filterTabEnable = false;
                $scope.no_result_found = false;
                $scope.gridOptions.data =[];
                $scope.displayTotalNumItems = 0;
                current_active_tab  = str;
                current_action_id = (typeof ftInfo!== "undefined" && ftInfo!=='')? ftInfo : '';
                var filetrObject = tableSessionManage("get", current_active_tab, "filter_restore");
                $scope.getTableListData('',current_active_tab ,$scope.gridOptions.paginationCurrentPage,$scope.gridOptions.PageSize, filetrObject);              
            }
            //for filter tab dynamic created in filter section
            else if (typeof str!== "undefined" && str == 'filter_tab') {                
                //In case filter tab is enable                  
                $scope.filterTabEnable = true
                $scope.tabActive = false;
                $scope.tabAll = false;
                $scope.tabFilter = true;
                $scope.gridOptions.data =[];
                $scope.no_result_found = false;
                $scope.displayTotalNumItems = 0;
                current_active_tab = str;
                if(typeof ftInfo!=="undefined"){
                    if(ftInfo.flag!== undefined && ftInfo.flag === "add_new_filter"){
                        $scope.enableDeleteBtn=false;
                    }else{
                        $scope.enableDeleteBtn=true;
                        //action to get filter data from server
                        $scope.filterActionHendler.filter(ftInfo);
                    }                                   
                }
            }           
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
                    "fromIndex" : info.fromIndex,
                    "dragRowSource" : info.draggedRowEntity,
                    "toIndex" : info.toIndex,
                    "dragRowTarget" : info.targetRowEntity
                }
            });
            gridApi.selection.on.rowSelectionChanged($scope, function(row) {
                customRowSelection('test', row);
            });
            gridApi.selection.on.rowSelectionChangedBatch($scope, function(data) {

                //in case selection action perform using button click
                if(user_action_selection!== undefined && (user_action_selection === "select_visible_action" || user_action_selection === "select_all_action")){
                   addRemoveProductId("no_table_session_action", data);                        
                }else{
                   addRemoveProductId("yes_table_session_action", data);
                }
            });             
            //used for when table have save/reset structure fetaure enable
            gridApi.core.on.rowsRendered($scope,function(){
                if(!$scope.initialColumnOrder)
                  $scope.initialColumnOrder = $scope.gridApi.grid.columns.slice();
            });

            /*
            *@desc : action on column sorting & get column name & sorting order 
            **/
            gridApi.core.on.sortChanged($scope, function(grid, sortColumns){                
                $scope.showLoaderTable = !0; 
                sortData['field'] = (sortColumns.length && sortColumns[0].colDef!==undefined) ?  sortColumns[0].colDef.field : '';
                sortData['order'] = (sortColumns.length && sortColumns[0].sort!==undefined) ? sortColumns[0].sort.direction : '';
                
                //in case of normal tab section means not cutstom filter tab                   
                if(current_active_tab!== '' && current_active_tab!=="filter_tab"){
                    var filetrObject = tableSessionManage("get", current_active_tab, "filter_restore");
                     //getObjectClone($rootScope.filedSetModel);
                     $scope.getTableListData('','',$scope.gridOptions.paginationCurrentPage, $scope.gridOptions.PageSize, filetrObject);
                }else if(current_active_tab ==="filter_tab" && filterId!==undefined && filterId!==''){
                    //In case filter tab model
                    var fobj = angular.copy($scope.filterObj.filterAtrModel[filterId]);
                    $scope.getTableListData('','',$scope.gridOptions.paginationCurrentPage, $scope.gridOptions.PageSize, fobj);
                }
            });     
        };

        //Listen on clear selection of rows
        $scope.removeSelected = function() {
            $scope.gridApi.selection.clearSelectedRows();
        };

        /***this function used for select an unselect all column in table**/
        $scope.rowSelectionFun = function(actionname) {
            user_action_selection = "select_all_action";

            if (actionname == 'select') {
                $scope.gridApi.selection.selectAllRows();
                $scope.selectItemTotal = $scope.displayTotalNumItems;
                tableSessionManage("set", current_active_tab, "select");
            } else if (actionname == 'unselect') {
                $scope.gridApi.selection.clearSelectedRows();                
                $scope.selectItemTotal = 0;
                tableSessionManage("reset", current_active_tab, "unselect");
            }
        };
        /***** This fnction used for select visible part of table in active section*****/
        $scope.rowVisibleSelectionFun = function(strFlag) {
            //if()
            console.log(user_action_selection);
            user_action_selection = "select_visible_action";
            if (strFlag == 'visible') {
                $scope.gridApi.selection.selectAllVisibleRows();
                $scope.selectItemTotal = ($scope.gridApi.core.getVisibleRows($scope.gridApi.grid).length);
                tableSessionManage("set", current_active_tab, "visible");
            } else if (strFlag == 'unVisible') {
                $scope.gridApi.selection.clearSelectedRows();
                $scope.selectItemTotal = 0;
                tableSessionManage("reset", current_active_tab, "unVisible");
            }
        };

        /*
        *@description : removeSelectedRow Reomve row from grid as well as database Table.
        *@param : row (object | optional)
        *@param : row_id (int)
        *@param :  row_action_url
        *method(DELETE)
        */
        $scope.removeSelectedRow = function(row, row_id, row_action_url) {           
            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this imaginary file!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: '#DD6B55',
                confirmButtonText: 'Yes, I am sure!',
                cancelButtonText: "No, cancel it!",
                closeOnConfirm: false,
                closeOnCancel: false
            }).then(function(isConfirm){
                if (isConfirm){
                    $scope.showLoaderTable = true;
                    var data = {};

                    try{
                        data['product_id'] = row_id;
                        //in case product section
                        if(typeof page_type!== "undefined" && (page_type=== "attr_set" || page_type === "attr")){
                           data['page_type'] = page_type;
                        }
                        if(row_action_url== undefined && typeof removeRowUrl!== "undefined"){
                            row_action_url = removeRowUrl;
                        }
                    }catch(e){
                        console.log;
                    }
                    
                    salesfactoryData.getData(row_action_url,'DELETE',data)
                    .then(function(response){
                      if(checkResponse(response) === "yes"){
                        swal(response.data.mesg_title, response.data.mesg, response.data.status);
                        $scope.gridOptions.data.splice($scope.gridOptions.data.indexOf(row.entity), 1);
                      }else{
                        swal(response.data.mesg_title, response.data.mesg, response.data.status);   
                      }
                      $scope.showLoaderTable = false;                     
                    },function(error){
                        $scope.showLoaderTable = false;
                        _errorHandler();
                    }).finally(function(){
                        $scope.showLoaderTable = false;
                        console.log;
                    });
                } else{
                    swal("Not Removed", "OOps have some delition error !", "error");
                }               
            }); 
        };

        $scope.deleteMenuRow = function(url,obj){
            salesfactoryData.getData(url,'POST',obj)
            .then(function(resp){
                if(checkResponse(resp) === "yes"){
                    // wihout_selection
                    $scope.showLoaderTable = false;
                }else{
                    $scope.showLoaderTable = false;
                }
            },function(error){
                $scope.showLoaderTable = false;
                _errorHandler();
            })
            .finally(function(){
                console.log;
            })
        };

        //grid.appScope.removeSelectedRow(row, row.entity.id, row.entity.delete)
        //Listen on delete data from table 
        function deleteRow(url,obj){
            salesfactoryData.getData(url,'POST',obj)
            .then(function(resp){
                if(checkResponse(resp) === "yes"){
                    // wihout_selection
                    $scope.showLoaderTable = false;
                }else{
                    $scope.showLoaderTable = false;
                }
            },function(error){
                $scope.showLoaderTable = false;
                _errorHandler();
            })
            .finally(function(){
                console.log;
            })
        };
    
        //Listen on calculate table hieight dynamically
        $scope.getTableHeight = function() {
             var rowHeight = 81; // your row height
             var headerHeight = 39; // your header height
             var as = $scope.gridApi.core.getVisibleRows().length;
             return {
                height: (as * rowHeight + headerHeight) + "px"
             };
        };

        /*******
         * This function used for handel event of status.
         * After click on status section call a AJAX and update database table
         * and update grid data as per as condition like if data is active then set in-active vice versa.
         * *****/
        $scope.updateStatus = function(tempRow, tempCol, $event,actUrl,rowId,actName) {
            var dataObj = {
                'id' : rowId,
                'action_type' : actName
            }

            var rt = false;
            switch(actName){
                case 'delete' :
                    if(confirm("Are you sure you want to delete this?")){
                        rt=true;
                        // salesfactoryData.getData(actUrl,'POST',dataObj).then(function(response) {
                        //      var index = $scope.gridOptions.data.indexOf(tempRow.entity);
                        //   $scope.gridOptions.data.splice(index, 1);
                        // })
                    }else rt= false;
                break;
                case 'change_status' :
                    salesfactoryData.getData(actUrl,'POST',dataObj)
                    .then(function(response) {
                        tempRow.entity.register_step = response.data;
                    });
                break;
                case 'approve_change_status' :
                    salesfactoryData.getData(actUrl,'POST',dataObj)
                    .then(function(response) {
                        if(checkResponse(response) === "yes"){
                            swal("Success", "User Changes approved!", "success").then(function(isConfirm){
                                setTimeout(function(){location.reload();},2000);
                            });
                        }else{
                            swal("Error", "User not approve!", "error");
                        }                        
                        tempRow.entity.register_step = response.data;
                    }, function(error){
                        swal("Error", "User not approve!", "error");
                    });
                break;
                case 'reject_change_status' :
                    salesfactoryData.getData(actUrl,'POST',dataObj)
                    .then(function(response) {
                        if(checkResponse(response) === "yes"){
                            swal("Success", "User Changes Rejected!", "success").then(function(isConfirm){
                                setTimeout(function(){location.reload();},2000);
                            });
                        }else{
                            swal("Error", "somthing Went wrong!", "error");
                        }
                        tempRow.entity.register_step = response.data;
                    }, function(error){
                        swal("Error", "somthing Went wrong!", "error");
                    });
                break;
            }
           return rt;
        };

        /*******
         *@ngdoc
         *@description : This function used for handel event of status.
         * After click on status section call a AJAX and update database table
         * and update grid data as per as condition like if data is active then set in-active vice versa.
         * *****/
        $scope.updateDobStatus = function(tempRow, tempCol, $event,actUrl,rowId,actName) {
            var dataObj = {
                'id' : rowId,
                'action_type' : actName
            }

            var rt = false;
            switch(actName){
                case 'reject' :
                    if(confirm("Are you sure you want to approve this?")){
                        rt=true;
                        salesfactoryData.getData(approveUrl,'POST',dataObj)
                        .then(function(response) {
                            tempRow.entity.register_step = response.data;
                            $('#msg').show();
                            $scope.class='success';
                            $scope.msg=response.data.msg;
                        });
                    }else rt= false;
                break;
                case 'approved' :
                    if(confirm("Are you sure you want to reject this?")){
                        salesfactoryData.getData(rejectUrl,'POST',dataObj)
                        .then(function(response) {
                            tempRow.entity.register_step = response.data;
                            $('#msg').show();
                            $scope.class='success';
                            $scope.msg=response.data.msg;
                        });
                    }
                    
                break;
            }
           return rt;
        };


        /**
        *This section used to handel action of table(status change, approved payment)
        *for update tabel as well as databse on user action click
        **/
        $scope.userActionHandler={
            //Listen on approved any things
            approved : function($event, row, flag, rowIndex){
                _gridTableActionHandler(row, flag, $scope, rowIndex);
            },
            //Listen on change status of any things like active dective 
            status : function($event, rowstatus, rowid, rowIndex){
                _changeStatusHandler($event,rowstatus,rowid, rowIndex, $scope,salesfactoryData, current_active_tab);
            },
            // This function used to export order pdf in order list page           
            exportPdf : function(evt,flag){
                if($scope.gridApi.selection.getSelectedRows().length >0){
                    try{
                        var dataSet = $scope.gridApi.selection.getSelectedRows();
                        switch(flag){
                            case 'export_order_pdf': 
                                var result =  getFormatedOrderId(dataSet);
                                generateOrderPdf(evt, result);
                            break;
                            case 'export_invoice_pdf': 
                                var result =  getFormatedOrderId(dataSet);
                                generateInvoicePdf(evt, result);
                            break;
                            case 'export_shipping_label_pdf':
                                var result =  getFormatedInvoiceId(dataSet);
                                generateShippingLabelPdf(evt, result);
                            break;
                            case 'export_shipment_pdf':
                                var result =  getFormatedInvoiceId(dataSet);
                                generateShipmentPdf(evt, result);
                            break;
                            case 'export_receipt_pdf':
                                var result =  getFormatedInvoiceId(dataSet);
                                generateReceiptPdf(evt, result);
                            break;
                        }
                    }catch(err){console.log};                    
                }else swal("Warning!", 'Please select row first', "warning"); 
            },
            custom : function(evt,row,rowIndex){
                changeNickName(evt,row,rowIndex,$scope);
            }
        };

        /********
        * This both actionOnDataGrid and actionBtnClick function used for Action on Grid Data Like Delete ,Enable and Disable etc 
        * This perform after data is selected if not selected then not perform any action.
        * Action 
        *  Active/Inactive(enable/disable)
        *  Delete
        ********/

        if(typeof actionOptions!== "undefined" && actionOptions){
            $scope.actionOptions = actionOptions;
        }else{
            $scope.actionOptions = [
                {'id': "", 'name': '-- Please select --', 'slug' : 'not_selected'},
                {'id': 1, 'name': 'Enable', 'status_value' : 1, 'slug' : 'enable'},
                {'id': 0, 'name': 'Disable', 'status_value' : 0, 'slug' : 'disable'},
                {'id': 3, 'name': 'Delete', 'status_value' : 'Delete', 'slug' : 'delete'},
            ];
        }
        
        $scope.actionSelectBox = $scope.actionOptions[0];
        
        $scope.actionOnDataGrid = function(){            
            if($scope.actionSelectBox.slug=='not_selected' || $scope.actionSelectBox.id == ""){
                swal("Warning!", 'Please any action', "warning");
                $scope.selBoxActBtn = false;
            }else if(_getIndex($scope.actionOptions, $scope.actionSelectBox.id, "id")!= -1){
                if($scope.gridApi.selection.getSelectedRows().length >0){
                    $scope.selBoxActBtn = true;
                }else{
                    $scope.selBoxActBtn = false;
                    swal("Warning!", 'Please select row first', "warning");                 
                }
            }else{
                swal("Warning!", 'Please select row first', "warning");         
            }
        };

        //Listen on action 
        $scope.actionBtnClick = function(){ 
           var dataSet = $scope.gridApi.selection.getSelectedRows();
           var result =  getId(dataSet);
           var slug = $scope.actionSelectBox.slug.toLowerCase();

            if(dataSet.length){
                var query = {
                    'datasetid' : result,
                    'actionname' : $scope.actionSelectBox['slug'],
                    'select_action' : (tableSession[current_active_tab].select_all === true) ? "all" : "",
                    'product_ids' : result,
                    'collection_id' : $scope.actionSelectBox['id'],
                    'action_obj' : $scope.actionSelectBox,
                };

                if(slug === 'delete'){
                    swal({
                      title: 'Are you sure?',
                      text: "You won't be able to revert this!",
                      type: 'warning',
                      showCancelButton: true,
                      confirmButtonColor: '#3085d6',
                      cancelButtonColor: '#d33',
                      confirmButtonText: 'Yes, delete it!',
                      cancelButtonText: 'No, cancel!',
                      confirmButtonClass: 'btn btn-success',
                      cancelButtonClass: 'btn btn-danger',
                    }).then(function(isConfirm){
                        if(isConfirm) bulkActionHandler(query, $scope.actionSelectBox, dataSet);
                    });                    
                }else{
                    bulkActionHandler(query, $scope.actionSelectBox, dataSet);
                }
            }else{
                $scope.selBoxActBtn = false;
                swal('Please select row first');           
            }
        };

        /*
        *@desc : This function used to handel bulk action
        *@param : obj {object} - query data
        *@param : itemModel {object} - selected option model
        *@param : DataSet {array} - array of selected rows
        */       
        function bulkActionHandler(obj, itemModel, DataSet){
            $scope.showLoaderTable = true;
            salesfactoryData.getData(actionUrl,'POST',obj)
            .then(function(resp){
                if(checkResponse(resp)!="yes") return;

                angular.forEach(DataSet, function(item){
                    var ind = $scope.gridOptions.data.indexOf(item);
                    var tempRow = $scope.gridApi.grid.rows[ind];

                    if(itemModel.slug === "delete"){
                        $scope.gridOptions.data.splice($scope.gridOptions.data.lastIndexOf(item), 1);
                    }else if(itemModel.slug != "delete" && itemModel.slug != "colllection"){
                        tempRow.entity.status = itemModel.status_value;
                    }                                        
                });
                // $scope.gridApi.selection.clearSelectedRows();
                // $scope.gridApi.grid.refresh();
                $scope.selBoxActBtn = false;
                swal("Done", resp.data.mesg, resp.data.status);
                //reload page in case of user perform all delete action
                if(action == "delete") window.location.reload(true);
            }, function(error){
                swal("Opps..", "Something went wrong..", "error");
            })
            .finally(function(){
                $scope.showLoaderTable = false;
            });
        };

        

        /*
        *This function return all selected row data id
        *@param : array (data set)
        */     
        function getId(dataSet){
          var idArray =[];
          angular.forEach(dataSet, function(data, index) {
            idArray.push(parseInt(data.id));
          });
          return idArray;
        }; 

        function getFormatedOrderId(dataSet){
            var idArray =[];
            angular.forEach(dataSet, function(data, index) {                
                idArray.push(data.formatted_order_id);
            });
            return idArray;            
        };
        function getFormatedInvoiceId(dataSet){
            var idArray =[];
            angular.forEach(dataSet, function(data, index) {                
                idArray.push(data.formatted_id);
            });
            return idArray;            
        };


        //date : 29 jan 2018
        //for filter tab section
        $scope.filterTab =[];
        //This variable used for filter tab 
        $scope.filterObj = {
            //model for select box
            filterModel  : {},
            //filter option json
            filterOption : [],
            //[{"label":"Status","type":"checkbox","modelName":"status","id":1,"attr_value" : [{"label" : "Registred","id" : "0"},{"label" : "Guest", "id" : "1" }]},{"label":"Register","type":"checkbox","modelName":"register","id":2,"attr_value" : [{"label" : "Enable","id" : "0"},{"label" : "Disable", "id" : "1" }]},{"label":"Customer Group","type":"mutiselect","id" : 3,"attr_value" :[{"name" : "guest","attr_id" : 10},{"name" : "register","attr_id" : 11}],"modelName" : "userGroup"},{"label":"Email","type":"text","modelName":"email","id":"4"},{"label":"Grand Total","id" :5,"type":"range","from_label":"From","from_modelName":"grand_total_from","to_label":"To","to_modelName":"grand_total_to"},{"label":"Date Subscribe","id":6,"type":"date_range","from_label":"From","from_modelName":"subcribe_from_date","to_label":"To","to_modelName":"subcribe_to_date"}],
            jsonForField : [],
            //[{"id":52,"value":[{"type":"select","label":"Cloth","modelName":"Cloth","id":28,"attr_value":[{"id":125,"label":"XLS"}],"$$hashKey":"object:34"},{"type":"radio","label":"Status","modelName":"Status","id":30,"attr_value":[{"id":123,"label":"Enable","$$hashKey":"object:47"},{"id":124,"label":"Disable","$$hashKey":"object:48"}],"$$hashKey":"object:36"},{"type":"text","label":"First Name","modelName":"First_Name","id":23,"$$hashKey":"object:31"},{"type":"radio","label":"Gender","modelName":"Gender","id":26,"attr_value":[{"id":247,"label":"Male","$$hashKey":"object:2114"},{"id":251,"label":"Female","$$hashKey":"object:2115"},{"id":255,"label":"Trans Gender","$$hashKey":"object:2116"}],"$$hashKey":"object:34"},{"type":"checkbox","label":"Education","modelName":"Education","id":31,"attr_value":[{"id":126,"label":"Graduate"},{"id":127,"label":"Post Graduate"},{"id":128,"label":"Professional"}],"$$hashKey":"object:39"}]}],
            //[],
            //model for filter attribute 
            filterAtrModel : {},
        };

         /*
        *This function used for filter tab in any section of project where implement table with filter tab.
        *To get and set data in filter object.
        */
        function _filterDataHandler(){
            try{
                filter_action_url = filter_action_url;
                filter_delete_url = filter_delete_url;
                $scope.filterObj.filterOption = filterOptionJson;
                //In case filterTab is empty array or have value then add one element for add more
                var temp ={
                    'id' : "0123",
                    "flag" : "add_new_filter",                      
                };
                filterTab.push(temp);
                $scope.filterTab = filterTab;

                //set previous filter data in jsonForField and filterAtrModel
                function _setPrevFilter(dataset){               
                    angular.forEach(dataset, function(item, index){
                        //push in jsonfiled
                        if(item.json_html!== undefined && item.json_html){
                            $scope.filterObj.jsonForField.push({"id" : item.id, "value" : item.json_html}); 
                        }
                        //set model
                        if(item.filter_model!== undefined && item.filter_model){
                            $scope.filterObj.filterAtrModel[item.id] = item.filter_model;
                        } 
                    });
                };
                //if and only iff $scope.filterTab have data then call _setPrvFilter function to set data
                if(angular.isArray($scope.filterTab) && $scope.filterTab.length>0){
                    var ftData =  angular.copy($scope.filterTab);
                    _setPrevFilter(ftData); 
                }           
            }catch(e){
                if(e instanceof ReferenceError){
                    $scope.filterTab = [];          
                    filter_action_url = '';
                    filter_delete_url = '';
                    $scope.filterObj.filterOption = [];
                }
            }
        };

        //if filter_tab_page variable not undefined then call function
        if(typeof filter_tab_page!== "undefined" && filter_tab_page === "filterTabPage"){
            _filterDataHandler();
        }


        //Listen on filter action 
        $scope.filterActionHendler ={
            //Listen on filter and get filter data @param fid
            filter : function(fid){
                $scope.tabActive = false;
                //temp clone of filterAtrModel
                var fobj = $scope.filterObj.filterAtrModel;
                //check model value is empty or not
                var count= _checkPropValEmpty(fobj[fid.id]);
                //in case model is empty then call error function and exit form filter
                if(count<= 1){
                    _createMsgDiv("Please select filter filed and data!" ,"error");
                    return false;
                }
                
                if(fid!== undefined){
                    if(angular.isObject(fobj) && fobj[fid.id]!== undefined){
                        filterId = fid.id;
                        $scope.gridOptions.data=[];
                        $scope.showLoaderTable = false;
                        $scope.getTableListData('','',$scope.gridOptions.paginationCurrentPage,$scope.gridOptions.PageSize, fobj[fid.id]);
                    }
                }               
            },
            /*
            *Listen on save filter for save/update filter in database.
            *@param : btnCheck (Boolean)
            *@param : action (string)
            *@param : Obj (Object)
            */
            save : function(btnCheck, action, obj){
                //In case model is undefined then show error
                if($scope.filterObj.filterAtrModel[obj.id].filter_name === undefined){
                    _createMsgDiv("Please enter filter name!", "error");
                    return false;
                }
                //In case filter model have but filed set not have 
                var count = _checkPropValEmpty($scope.filterObj.filterAtrModel[obj.id]);
                if(count<= 1){
                    _createMsgDiv("Please select field for filter", "error");
                    return false;
                }

                action =  action || '';
                //In case filter already created then action type update
                if(btnCheck!== undefined && btnCheck === true){
                    action = "update";
                }else{
                    //If new created from user side then action type is save
                    action = "create";
                }
                var index = _getIndexOfData($scope.filterObj.jsonForField, obj.id);
                if(obj.id!== undefined && index!== -1){
                    $scope.showLoaderTable = true;
                    var actObj ={
                        "id" : (action === "create")? '' :  obj.id,
                        "filter_model" : $scope.filterObj.filterAtrModel[obj.id],
                        "json_html" : $scope.filterObj.jsonForField[index].value,
                        "action" : action,
                    };
                    
                    salesfactoryData.getData(filter_action_url, 'POST',actObj)
                    .then(function(rsp){
                        if(checkResponse(rsp) === "yes"){
                            //in case create new filter 
                            if((action!== undefined && action === "create") && (angular.isArray(rsp.data.filterArrFinal) && rsp.data.filterArrFinal.length>0)){
                                var copyFilterTab = angular.copy($scope.filterTab);
                                var lastElement =  copyFilterTab.pop();
                                var filterData = rsp.data.filterArrFinal[0];
                                copyFilterTab.push(filterData);
                                copyFilterTab.push(lastElement);
                                $scope.filterTab =[];
                                $scope.filterTab = angular.copy(copyFilterTab); 
                                //delete previous model of add new filter 
                                delete $scope.filterObj.filterAtrModel[obj.id];
                                //set new created filter model in filterAtrModel
                                $scope.filterObj.filterAtrModel[filterData.id] = filterData.filter_model;
                                //set data in jsonForField 
                                $scope.filterObj.jsonForField.pop();
                                $scope.filterObj.jsonForField.push({"id" : filterData.id, "value" : filterData.json_html});                     
                                $scope.getTableListData('','',$scope.gridOptions.paginationCurrentPage,$scope.gridOptions.PageSize, $scope.filterObj.filterAtrModel[filterData.id]);
                                $scope.enableDeleteBtn=true;
                            }
                            _createMsgDiv(rsp.data.message, rsp.data.status);
                        }else{      
                            _errorHandler();                    
                        }
                        $scope.showLoaderTable = false;                     
                    },function(error){
                        $scope.showLoaderTable = false;
                        _errorHandler();
                    }).finally(function(){
                        $scope.showLoaderTable = false;
                    });
                }else{
                    _createMsgDiv("Please select filter field", "error");
                }               
            },
            /*
            *Delte previous created filter from database as well as fornt end
            *@param (obj)  type : Object
            *@param (action) type : string
            */
            deleteFilter : function(obj, action){
                if(obj === undefined) return;

                var actObj = {
                    "id" :  obj.id,
                    "filter_value" : $scope.filterObj.filterAtrModel[obj.id],
                    "action" : action,
                };
                $scope.showLoaderTable = true;
                //Listen for server
                salesfactoryData.getData(filter_delete_url, 'POST', actObj)
                .then(function(rsp){
                    if(checkResponse(rsp) === "yes"){
                        var index =  _getIndexOfData($scope.filterTab, obj.id);
                        //delete from filter list                       
                        if(index>=0){
                            $scope.filterTab.splice(index,1);
                        }
                        //delete from filter model
                        delete $scope.filterObj.filterAtrModel[obj.id];
                        //delete form html json
                        var jsIndex = _getIndexOfData($scope.filterObj.jsonForField, obj.id);
                        if(jsIndex>=0){
                            $scope.filterObj.jsonForField.splice(jsIndex, 1);
                        }

                        $scope.enableDeleteBtn=false;
                        $timeout(function(){
                            angular.element('#list-tab-ative a').click();                            
                        },0);                       
                        _createMsgDiv(rsp.data.message, rsp.data.status);
                    }else{
                        _errorHandler();
                    }
                    $scope.showLoaderTable = false;                 
                },function(error){
                    $scope.showLoaderTable = false;
                    _errorHandler()
                }).finally(function(){
                    $scope.showLoaderTable = false;
                });
            },
            /*
            *Listen on filter option change
            *@param : fId (var)
            */
            optChange : function(fId){
                //temp clone of $scope.filterObj
                var fObj =  $scope.filterObj;
                //in case model is null (means if user select please select)
                //then return
                if(fObj.filterModel[fId] == null) return;

                //@param jsonForField               
                var index = _getIndexOfData(fObj.jsonForField, fId);
                //In case have already id in jsonForField then concat only value 
                if(index!== -1){
                    var valArr =  fObj.filterModel[fId];
                    //In case model value already in jsonForField set
                    var ind =  _getIndexOfData(fObj.jsonForField[index].value, valArr.id);
                    if(ind == -1){
                        fObj.jsonForField[index].value =  fObj.jsonForField[index].value.concat(valArr);    
                    }
                }else{
                    fObj.jsonForField.push({"id" : fId, "value" : [fObj.filterModel[fId]] })
                }                
            },
            /*
            *Listen on remove field from filter template section
            *@param : $event (event)
            *parma  :  _id
            *@param : _modelName (type : string)
            *@param : _modelNameTo (type : sting)           
            */ 
            removeField : function($event, _id, _index, _modelName, _modelNameTo){
                $event.stopPropagation();
                var ftabIndex = _getIndexOfData($scope.filterObj.jsonForField, _id);
                //remove object form value array
                if(ftabIndex>=0){
                    //delete from jsonForField
                    $scope.filterObj.jsonForField[ftabIndex].value.splice(_index, 1);
                    //delete model of json field from filterAtrModel
                    if($scope.filterObj.filterAtrModel[_id][_modelName]!== undefined){
                        delete $scope.filterObj.filterAtrModel[_id][_modelName];
                        if(_modelNameTo!== undefined && $scope.filterObj.filterAtrModel[_id][_modelNameTo]!== undefined){
                            delete $scope.filterObj.filterAtrModel[_id][_modelNameTo];
                        }   
                    }                   
                }               
            },
            /*
            *Listen on mutiselect user search for data then return value
            *@param : query (string)
            *@param : desArr(array)
            */
            autoSugest  : function(query, desArr){
                return desArr;              
            },
        };
        //end filter tab section

        function resetTable(){
            //reset visibility of column
            angular.forEach($scope.gridOptions.columnDefs, function(item, index){
                item.visible = true;               
            });

            //reset order of column
            if($scope.initialColumnOrder) {
                var columnDefsColMov = $scope.gridApi.grid.moveColumns.orderCache;
                columnDefsColMov.length = 0;
                columnDefsColMov.push.apply(columnDefsColMov, $scope.initialColumnOrder)                
            }

            $scope.gridApi.core.notifyDataChange(uiGridConstants.dataChange.COLUMN );           
        };

        //listen on save/reset(in case of reset also reset columns visibility)structure
        $scope.save_table_structure = function(event, flag){
            event.preventDefault();
            
            if(typeof save_table_setting_url == "undefined") return false;

            var table_name = (fieldSetJson.tableName!== undefined) ? fieldSetJson.tableName : "" ;

            $scope.showLoaderTable = true;
            var _st = $scope.structure;
            var _actFlg = false;

            if(flag!== undefined && flag === "save"){                          
                _actFlg = true; 
                _st.after_save_state = $scope.gridApi.saveState.save();              
            }else if(flag!== undefined && flag === "reset"){
                _actFlg = true;                            
            }

            //action to server with table name and data 
            if(typeof save_table_setting_url !== "undefined" &&  _actFlg ===  true){
                var _data = {
                    table_name : table_name,
                    action : flag,
                    data : (flag === "save") ? angular.toJson(_st.after_save_state) : [],
                };

                //for last filter data save
                if(flag === "save"){
                    _data.last_filter = getObjectClone($rootScope.filedSetModel);
                }else{
                    _data.last_filter = [];
                }

                salesfactoryData.getData(save_table_setting_url, "POST", _data)
                .then(function(_r){
                    var result = _r.data;
                    if(checkResponse(_r) === "yes"){
                        if(flag === "reset"){
                            resetTable(); 
                            _st.after_save_state = {};
                        }
                        _actFlg = false;
                        _createMsgDiv(result.msg, result.status);
                    }else{
                        _actFlg = false;
                       _errorHandler(); 
                    }
                   $scope.showLoaderTable = false;
                },function(error){
                    _actFlg = false;
                    $scope.showLoaderTable = false;
                    _errorHandler();
                });
            }  
        };

        // This function wiil get the order excel generation history | Start
        $scope.getCronHistory = function (order_detail){ 
           if(typeof cron_history_url!=='undefined'){
                salesfactoryData.getData(cron_history_url, 'GET', {"id" :order_detail.id})
                .then( function(response){ 
                    swal({title:'',text:response.data.data,footer:'', width: 'auto', customClass: 'sweets2-wide'});
                    // Success
                    },function(){
                        //ERROR
                })
           }
        };




         /*
        *@ngdoc
        *Description : listen to set product id after selection in hidden field
        *@param : strFlag {string}
        *@param : data {array}
        */ 

        addRemoveProductId = function(strFlag, data){
            var session_action;
            //rows selected length
            var seltdLength = $scope.gridApi.selection.getSelectedRows().length;

            if(seltdLength >= 1){
                $scope.selBoxActBtn = true;
                session_action ="set";
            }else{                
                $scope.selBoxActBtn = false;
                session_action = "reset";                
            }

            if(strFlag!== undefined && strFlag === "yes_table_session_action"){
                var act = (typeof session_action!== "undefined" && session_action === "set") ? "select" : "unselect";
                tableSessionManage(session_action, current_active_tab, act);
            }
        };

        /*
        *@ngdoc
        *Description : Listen on custom row selection change
        *@param : action {string}
        *@param : row {array}
        */        
        customRowSelection = function(action, row){
            if(row === undefined || typeof row === "undefined") return;
            
            if(row.isSelected!== undefined && row.isSelected === true){                  
                var index = _getIndex(tableSession[current_active_tab].custom_selection, $scope.gridOptions.paginationCurrentPage, "current_page");                   
                if(index>=0){
                    var rowIndex = _getIndex(tableSession[current_active_tab].custom_selection[index].row_id, row.entity.id);
                    if(rowIndex== -1)
                        tableSession[current_active_tab].custom_selection[index].row_id.push(row.entity.id);
                }else{
                    var temp = {
                        "current_page" : $scope.gridOptions.paginationCurrentPage,
                        "row_selection" : true,
                        "row_id" : [row.entity.id],
                    };
                    tableSession[current_active_tab].custom_selection.push(temp);
                }
                $scope.selBoxActBtn = true; 
            }else{
                $scope.selBoxActBtn = false;
                var index = _getIndex(tableSession[current_active_tab].custom_selection, $scope.gridOptions.paginationCurrentPage, "current_page");
                if(index>=0){
                    var rowIndex = _getIndex(tableSession[current_active_tab].custom_selection[index].row_id, row.entity.id);
                    if(rowIndex>=0){
                       tableSession[current_active_tab].custom_selection[index].row_id.splice(rowIndex, 1); 
                    }                        
                }
            }           
        };

        _setRowSelected = function(prd_id_arr, matchFlag){                                
            if(prd_id_arr == undefined || !angular.isArray(prd_id_arr) || prd_id_arr.length === 0) return;
            prd_id_arr.map(function(elem){
                var el = (matchFlag!== undefined) ? elem[matchFlag] : elem;                    
                var ind = _getIndex($scope.gridOptions.data, el, "id");
                
                if(ind!=-1 && ind>=0){
                    $scope.gridApi.grid.modifyRows($scope.gridOptions.data); 
                    $scope.gridApi.selection.selectRow($scope.gridOptions.data[ind]);
                }
            });
        };

        /**
        *@ngdoc
        *@description : 
        *@param : action {string} -> To perform action like select or unselect
        *@param : curt_tab {string} -> current active tab
        *@param : flag {string} ->Set or reset string for type of action we have to perform on current tableSession object
        **/
        
        tableSessionManage =  function(flag, curt_tab, action){
            if(flag!== undefined && flag === "set" && curt_tab!==undefined && action!== undefined){
                switch(action){
                    case 'select': 
                        tableSession[curt_tab].page =  $scope.gridOptions.paginationCurrentPage;
                        tableSession[curt_tab].page_sizes =  $scope.gridOptions.paginationPageSize;
                        tableSession[curt_tab].select_all =  true;
                        tableSession[curt_tab].select_visible = false;
                        tableSession[curt_tab].filter_data =  angular.copy($rootScope.filedSetModel);
                        //new feature for filter
                        sessionStorage.setItem(curt_tab, angular.toJson(tableSession[curt_tab].filter_data));
                        break;                   
                    case 'set_filter' :
                        tableSession[curt_tab].filter_data = angular.copy($rootScope.filedSetModel);
                        tableSession[curt_tab].page =  $scope.gridOptions.paginationCurrentPage;
                        tableSession[curt_tab].page_sizes =  $scope.gridOptions.paginationPageSize;
                        //new feature for filter
                        sessionStorage.setItem(curt_tab, angular.toJson(tableSession[curt_tab].filter_data));
                        break;
                    case 'visible':
                        tableSession[curt_tab].page = $scope.gridOptions.paginationCurrentPage;
                        tableSession[curt_tab].page_sizes = $scope.gridOptions.paginationPageSize;
                        tableSession[curt_tab].select_visible = true;
                        tableSession[curt_tab].select_all =  false;
                        tableSession[curt_tab].filter_data = angular.copy($rootScope.filedSetModel);
                        //new feature for filter
                        sessionStorage.setItem(curt_tab, angular.toJson(tableSession[curt_tab].filter_data));
                        break; 

                    default :
                        break;
                }
            }else if(flag!== undefined && flag === "reset" && curt_tab!==undefined && action!== undefined){
                switch(action){
                    case 'unselect' :
                        tableSession[curt_tab].page =  $scope.gridOptions.paginationCurrentPage;
                        tableSession[curt_tab].page_sizes =  $scope.gridOptions.paginationPageSize;
                        tableSession[curt_tab].select_all =  false;
                        tableSession[curt_tab].filter_data =  angular.copy($rootScope.filedSetModel);
                        //new feature for filter
                        sessionStorage.setItem(curt_tab, angular.toJson(tableSession[curt_tab].filter_data));
                        break;
                    case 'reset_filter' : 
                        tableSession[curt_tab].filter_data = {};
                        tableSession[curt_tab].page =  $scope.gridOptions.paginationCurrentPage;
                        tableSession[curt_tab].page_sizes =  $scope.gridOptions.paginationPageSize;
                        //new feature for filter
                        sessionStorage.setItem(curt_tab, angular.toJson(tableSession[curt_tab].filter_data));
                        break;
                    case 'unVisible':
                        tableSession[curt_tab].page = $scope.gridOptions.paginationCurrentPage;
                        tableSession[curt_tab].page_sizes = $scope.gridOptions.paginationPageSize;
                        tableSession[curt_tab].select_visible = false;
                        tableSession[curt_tab].filter_data = angular.copy($rootScope.filedSetModel);
                        //new feature for filter
                        sessionStorage.setItem(curt_tab, angular.toJson(tableSession[curt_tab].filter_data));
                        break;
                };
            }else if(flag!== undefined && flag === 'user_action' && curt_tab!==undefined){
               //in case of user action like page change, pagination change and select all is true
               if(tableSession[curt_tab].select_all === true){
                    //first clear selection                     
                    $timeout(function(){
                        $scope.gridApi.selection.clearSelectedRows();
                        $scope.gridApi.grid.modifyRows($scope.gridOptions.data);
                        $scope.gridApi.grid.selection.selectAll= true; 
                        $scope.gridApi.selection.selectAllRows();
                        $scope.selectItemTotal = $scope.displayTotalNumItems;
                    });
               }else if (tableSession[curt_tab].select_visible === true) {                   
                    //in case of user action like page change, pagination change and select visible is true
                    if (tableSession[curt_tab].page === $scope.gridOptions.paginationCurrentPage) {
                       $timeout(function(){
                            $scope.gridApi.selection.clearSelectedRows();
                            $scope.gridApi.grid.modifyRows($scope.gridOptions.data);
                            $scope.gridApi.selection.selectAllVisibleRows();
                            $scope.selectItemTotal = ($scope.gridApi.core.getVisibleRows($scope.gridApi.grid).length);
                       });                        
                    }
                }else if(tableSession[curt_tab].custom_selection.length>=1){ 
                    //in case of custom selection 
                    var index = _getIndex(tableSession[current_active_tab].custom_selection, $scope.gridOptions.paginationCurrentPage, "current_page");

                    if(index>=0){
                        if(tableSession[current_active_tab].custom_selection[index].row_id.length){
                            $timeout(function(){
                                _setRowSelected(tableSession[current_active_tab].custom_selection[index].row_id, undefined);
                            });
                        }
                    }
                }
            }else if(flag!== undefined && flag === "get" && curt_tab!==undefined && action!== undefined){
                //in case user have data for specific tab and they move to other tab then as per as 
                //tab data will appear so
                switch(action){
                    case "filter_restore" : 
                        //used for restore filter, pagination data.
                        $scope.$evalAsync(function(){
                            var ftdata = tableSession[curt_tab].filter_data;

                            angular.forEach($rootScope.filedSetModel, function(elem, index) {
                                if(angular.isObject(elem) == true){                                         
                                    if(ftdata[index]!== undefined){
                                        $rootScope.filedSetModel[index] = ftdata[index];
                                    }
                                    else{
                                        $rootScope.filedSetModel[index] = {"key": "","value" : "Please Select"};
                                    }
                                }else{
                                    if(ftdata[index]!== undefined && ftdata[index]){
                                        $rootScope.filedSetModel[index] = ftdata[index];
                                    }else{
                                        $rootScope.filedSetModel[index] = "";                       
                                    }
                                }
                            });
                        });
                        //new feature for filter
                        sessionStorage.setItem(curt_tab, angular.toJson(tableSession[curt_tab].filter_data));
                        return getObjectClone(tableSession[curt_tab].filter_data);

                        break;

                }
            }
        };

    };

    //error handler function
    function _errorHandler(errMsg) {
        try {
            throw new Error("Something went badly wrong!");
        } catch (e) {
            console.log("Opps " + e);
            _createMsgDiv(e, "error");             
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
    //Listen on get index of object
    //@param sourceObj (type : Object)
    //@param destObj (type number)
    function _getIndexOfData(sourceObj, destObj){
       /* var index = sourceObj.findIndex(function(o){
            return (o.id!== undefined && o.id == destObj);
        });
        return index;*/
        var index = -1;

        sourceObj.forEach(function(o, ind){
            if(o.id!== undefined && o.id == destObj){
                index = ind;            
            }
        });

        return index;
    };

    /*Listen for get index 
    *@param : destObj (oject/array)
    *@param : matchEle (string)
    *@param : matchType (string -optional)
    */
    function _getIndex(destObj, matchEle, matchType){
        var index = -1;       

        destObj.forEach(function(item, indx){
            if(matchType!== undefined && matchType){
                if(item[matchType] == matchEle)
                    index = indx;
            }else{
                if(item == matchEle)
                   index = indx; 
            }
        });

        return index;
    };

    //for toastr like message display using bootsrap alert
    function _createMsgDiv(mesg, classType){
        var _div =  document.createElement('div');
        var _class = "alert custom-message";
        //conditional class
        if(classType === "success"){
            _class += " alert-success";
        }else{
            _class += " alert-danger";  
        }
        _div.className =  _class;
            
        var text = document.createTextNode(mesg);
        _div.appendChild(text);
        document.body.appendChild(_div);
        jQuery(_div).fadeOut(4000,function(){
            jQuery(this).remove();
        }); 
    };

    //Listen on check object is empty and object property value is empty or not
    function _checkPropValEmpty(obj){
        var count =0;
        for(var k in obj){
            if(obj.hasOwnProperty(k) && obj[k]!==undefined){
                count++;
            }
        }
        return count;
    };

    //check valid response and return yes | no
    //checkDataLength check also data length (Boolean)
    function checkResponse(resp){
        var result = "no";

        if(typeof resp!== "undefined" && (typeof resp.data!== "undefined" ||
         resp.data!== undefined) && (resp.data.status!== undefined && resp.data.status === "success" || resp.data.status === true)){
            result = "yes";
        }else{
          result = "no";
        }

        return result;
    };
})(window.angular);