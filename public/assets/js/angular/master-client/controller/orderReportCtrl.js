/*
*@Name : orderReportCtrl.js
*@Description : This controller used for order list manage
*@Author : Smoothgraph Connect Pvt. Ltd.
*/

(function(angular,undefined){
	//THIS CONTYROLLER UDEF OR PRODUCT LISTING 
	"used strict";

	//function for export csv from server
	var exportDataAsCsv = function(){};

	//In case of page is oder report then initilzed google chart
	if(typeof page_type!="undefined" && page_type === "order_report"){
		google.charts.load('current', {'packages':['corechart',report_chart_setting.chart_type]});
	}


	function ctrlHendler($scope,$timeout,$window,$sce,$rootScope,salesfactoryData,uiGridConstants){
		//$scope.pageName=pageName;
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
		//enable/disable action buttion
		$scope.action_btn_enable = false;
		/******This variable used for get all data from server at a time ******/
		$scope.getAllDataFromServerOnce = true;//getAllDataFromServerOnce;
	    $scope.tabActive = true;
		$scope.tabAll = false;
		$scope.tabFilter = false;
		$scope.dragEnable = true;
	 	$scope.gridOptions = {}; 
	 	$scope.no_result_found=false;
		$scope.errorInfoLog ='<div class="no-info-blank"><h3><i class="icon-doc"></i> You have no information </h3></div>';
		$scope.tableLoaderImgUrl = tableLoaderImgUrl;

		//this variabled used for tab section in table 
        //This variable used for which type of data get from server like active, all ,filter(some specific data approve)
        var current_active_tab = "active",
            sub_action_type = ""; 
         var sortData = {'field' : '', 'order' : ''};
		//grid table initialization
		//@gridOptions  : type object
		$scope.gridOptions = {
			columnDefs : columsSetting,
			enableVerticalScrollbar: uiGridConstants.scrollbars.NEVER,
			enableHorizontalScrollbar : uiGridConstants.scrollbars.WHEN_NEEDED,
			gridMenuCustomItems:[{
				title : 'Reset Grid',
				action: function ($event) {
				  angular.forEach($scope.columsSetting,function(item){item.visible = true;});
				  $scope.gridApi.core.notifyDataChange( uiGridConstants.dataChange.COLUMN );
				}
			}],
			rowTemplate : '<div grid="grid" class="ui-grid-draggable-row" draggable="true"><div ng-repeat="(colRenderIndex, col) in colContainer.renderedColumns track by col.colDef.name" class="ui-grid-cell" ng-class="{ \'ui-grid-row-header-cell\': col.isRowHeader, \'custom\': true }" ui-grid-cell></div></div>',
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
		function _getTableListData(url,type,page,per_page,filter_obj){
			var obj ={
				"page"  : (page!==undefined && page) ? page : 1,
				"action_type" :  (type!==undefined && type!='') ? type : "active_product",
				"per_page" : (per_page!==undefined && per_page) ?  per_page : $scope.gridOptions.PageSize,
				"sorting_order" : (sortData.field!='' && sortData.order!='') ? sortData : "",
			};

			//In case filter object not empty
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

			//In case of customer report section if have query string
			var qs =getAllUrlParams();	
			
			if(!isEmpty(qs)){
				var qst =  JSON.stringify(qs);
				qst =  JSON.parse(qst);
				angular.extend(obj,qst);
			}

		

			dataJsonUrl = (angular.isUndefined(url) || url ==='')? dataJsonUrl : url;

			salesfactoryData.getData(dataJsonUrl,'POST',obj).then(function(rs){
				var d = rs.data;
				if(d.data.length<=0){
					$scope.no_result_found=true; 
				}else{
					$scope.no_result_found=false;
				}
				
				$scope.gridOptions.totalItems = (d.total);
				$scope.gridOptions.data = d.data;
				$scope.displayTotalNumItems = d.total;
				$scope.showLoaderTable = false;	

				//In case of order report page for draw chart
				if(typeof page_type!="undefined" && page_type === "order_report" ){
					if(angular.isArray(d.data) && d.data.length>1){
						if($scope.creatChartData(d.data)){
							 google.charts.setOnLoadCallback(drawChart);
						}
					}
				}		
			},function(error){
				try{throw new Error("Something went badly wrong!");}
		    	catch(e){console.log("error: " + e); $scope.showLoaderTable=false;$scope.no_result_found=false;}
			}).finally(function(){
				$scope.showLoaderTable = false;
			}); 
		};
		//Listen on controller load 
		_getTableListData('','',1,$scope.gridOptions.PageSize);
		
		//This function used for Header pagination control
		$scope.HeaderPagination = {
			getTotalPages : function() {
				return Math.ceil($scope.gridOptions.totalItems / $scope.gridOptions.PageSize);
			},
			nextPage : function() {
				if ($scope.gridOptions.paginationCurrentPage < this.getTotalPages()) {
					$scope.gridOptions.paginationCurrentPage++;
					$scope.showLoaderTable = true;
					var filetrObject = getObjectClone($rootScope.filedSetModel);
					_getTableListData('','',$scope.gridOptions.paginationCurrentPage,$scope.gridOptions.PageSize, filetrObject);
				}
			},
			previousPage : function() {
				if ($scope.gridOptions.paginationCurrentPage > 1) {
					$scope.gridOptions.paginationCurrentPage--;
					$scope.showLoaderTable = true;
					var filetrObject = getObjectClone($rootScope.filedSetModel);
					_getTableListData('','',$scope.gridOptions.paginationCurrentPage,$scope.gridOptions.PageSize, filetrObject);
				}
			},
			pageSizeChange : function(num) {
				$scope.showLoaderTable = true;
				$scope.gridOptions.paginationCurrentPage = 1;
				var filetrObject = getObjectClone($rootScope.filedSetModel);
				_getTableListData('','',$scope.gridOptions.paginationCurrentPage, num, filetrObject);
				$scope.gridOptions.minRowsToShow = num;
				$scope.viewItemPerPage = num;
				$scope.gridOptions.paginationPageSize = num;
				$scope.gridOptions.PageSize = num;				
			},
			pageChange : function(){
				if ($scope.gridOptions.paginationCurrentPage <= this.getTotalPages()) {                	
                    $scope.showLoaderTable = true;
                    var filetrObject = getObjectClone($rootScope.filedSetModel);
                    
                    _getTableListData('','',$scope.gridOptions.paginationCurrentPage,$scope.gridOptions.PageSize, filetrObject);
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
			var filetrObject = getObjectClone($rootScope.filedSetModel);
			_getTableListData('', '', page,$scope.gridOptions.PageSize, filetrObject);				
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
				//console.log($rootScope.filedSetModel)
				/*//empty filter object
				for (var prop in $scope.filterDataObj) {
                    if ($scope.filterDataObj.hasOwnProperty(prop)) {
                        delete $scope.filterDataObj[prop];
                    }
                } */
                var filetrObject = getObjectClone($rootScope.filedSetModel);
                $scope.showLoaderTable = true;
                _getTableListData(dataJsonUrl, '', $scope.gridOptions.paginationCurrentPage, $scope.gridOptions.PageSize, filetrObject);               
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
                _getTableListData(dataJsonUrl, '', $scope.gridOptions.paginationCurrentPage, $scope.gridOptions.PageSize, filetrObject);
            }
		};

		/*****
		 * This function used for enable tab section.
		 * This function call when you are using tab in table section.
		 * *******/
		$scope.enableTab = function(str,ftInfo) {
			sub_action_type = "";

			if (str == 'active') {
				$scope.tabActive = true;
				$scope.tabAll = false;
				$scope.tabFilter = false;				
			} else if (str == 'all') {
				$scope.tabActive = false;
				$scope.tabAll = true;
				$scope.tabFilter = false;				
			} else if (str == 'filter') {				
				if(ftInfo!==undefined){
					var o = {'filter_id' :  ftInfo.id};
					$scope.enableDeleteBtn=true;
					$scope.deleteFtabId = ftInfo.id;
					$scope.filterActionHendler(getsavefilter,o,'act_ftTabClick')
				}else{
					$scope.enableDeleteBtn=false,$scope.ftModel.tgclass = true;
					$scope.ftModel.f_field=[],$scope.ftModel.optModel=[];
					$scope.deleteFtabId=0;$scope.filter_name='';
					$scope.txtAutoSugstModel={},$scope.ftModel.rangeModel={};
					$scope.attrAutoSugstmodel ={};
				} 
				$scope.tabActive = false;
				$scope.tabAll = false;
				$scope.tabFilter = true;
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
				if($scope.gridApi.selection.getSelectedRows().length >0){
				 	$scope.selBoxActBtn = true;
				 	console.log('dshj       fghsdf');
				}
				else
					$scope.selBoxActBtn = false;

				if(row.isSelected===true){
					//
				}else{
					//
				}
			});
			gridApi.selection.on.rowSelectionChangedBatch($scope, function() {
				console.log('batch selection change');
				if($scope.gridApi.selection.getSelectedRows().length >0){
					$scope.selBoxActBtn = true;
					var temp = $scope.gridApi.selection.getSelectedRows();
					var temparr =[];
					temp.map(function(item){
						temparr.push(item.id)
					});
				}
				else
					$scope.selBoxActBtn = false;

			});
			gridApi.pagination.on.paginationChanged($scope, function (pageNumber, pageSize){                             
	            //pageNumberOrPageSizeChanged(pageNumber, pageSize);
	            //console.log('oh me joy');
	        }); 
			/*
	        *@desc : action on column sorting & get column name & sorting order 
            **/
            gridApi.core.on.sortChanged($scope, function(grid, sortColumns){
            	$scope.showLoaderTable = !0; 
                sortData['field'] = (sortColumns.length && sortColumns[0].colDef!==undefined) ?  sortColumns[0].colDef.field : '';
                sortData['order'] = (sortColumns.length && sortColumns[0].sort!==undefined) ? sortColumns[0].sort.direction : '';
                var filetrObject = getObjectClone($rootScope.filedSetModel);
				_getTableListData('','',$scope.gridOptions.paginationCurrentPage,$scope.gridOptions.PageSize, filetrObject);
            });
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
						console.log('fg g h dfd');
						rt=true;
						// salesfactoryData.getData(actUrl,'POST',dataObj).then(function(response) {
						//      var index = $scope.gridOptions.data.indexOf(tempRow.entity);
						// 	 $scope.gridOptions.data.splice(index, 1);
						// })
					}else rt= false;
				break;
				case 'change_status' :
					salesfactoryData.getData(actUrl,'POST',dataObj).then(function(response) {
						console.log(response);
						tempRow.entity.register_step = response.data;
					});
				break;
			}
		   return rt;
		};


		//Listen on clear selection of rows
		$scope.removeSelected = function() {
			$scope.gridApi.selection.clearSelectedRows();
		};

		/*
		*removeSelectedRow Reomve selected rowfrom grid as well as database Table.
		*@param : row (object | optional)
		*/
		$scope.removeSelectedRow = function(row) {
			var idArr = [];

			//In case if row selection check box is enable
			if (typeof $scope.gridApi.selection.getSelectedRows() != 'undefined' && $scope.gridApi.selection.getSelectedRows().length > 0) {
				if (confirm("Are you sure you want to delete this?")) {
					var selectedRowsData = $scope.gridApi.selection.getSelectedRows();
					if(angular.isArray(selectedRowsData) && selectedRowsData.length>0){
						//console.log(getId(selectedRowsData));
					}
					angular.forEach($scope.gridApi.selection.getSelectedRows(), function(data, index) {
						$scope.gridOptions.data.splice($scope.gridOptions.data.lastIndexOf(data), 1);
						idArr.push(data.id);
					});
					var idObj = {
						"id" : idArr,
					};
					salesfactoryData.getData(removeRowUrl,"POST", idObj);
				}
			}else if(!$scope.tableSelectBtnConfig){
				// In case row selection check box is not enable but delete action id define
				if(confirm("Are you sure you want to delete this?")){
					var obj ={
						id  : row.entity.delete_id, 
					};
					$scope.showLoaderTable = true;
					deleteRow(removeRowUrl,obj,'wihout_selection');
				}
				

				
			}else {
				alert('Please select at least one check box');	
			}			
		};
		//Listen on delete data from table 
		function deleteRow(url,obj){
			salesfactoryData.getData(url,'POST',obj)
			.then(function(resp){
				if(resp.data.status!= undefined && (resp.data.status==true || resp.data.status =="success")){
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
		}

		/********
		* This both actionOnDataGrid and actionBtnClick function used for Action on Grid Data Like Delete ,Enable and Disable etc 
		* This perform after data is selected if not selected then not perform any action.
		********/
		 $scope.actioOptions = [
	        {id: 0, name: '---Action---'},
	        {id: 1, name: 'Enable'},
	        {id: 2, name: 'Disable'},
	        // {id: 3, name: 'Delete'},
	    ];
	    $scope.actionSelectBox = $scope.actioOptions[0];
	    $scope.actionOnDataGrid = function(){
	    	var name = $scope.actionSelectBox.name;
	    	if(name=='---Action---'){
	    		alert('Please any action');
	    		$scope.selBoxActBtn = false;
	    	}else if(name == 'Enable' || name=='Disable' || name=='Delete'){
	    		if($scope.gridApi.selection.getSelectedRows().length >0){
	    			$scope.selBoxActBtn = true;
	    		}else{
	    			$scope.selBoxActBtn = false;
	    			alert('Please select row first');
	    		}
			}else{
				alert('Please select row first');
			}
	    }
	 	$scope.actionBtnClick = function(){
	 	   var name = $scope.actionSelectBox.name;
	       var DataSet = $scope.gridApi.selection.getSelectedRows();
	       var result =  getId(DataSet);
	       if($scope.gridApi.selection.getSelectedRows().length >0){
		       switch(name){
					case 'Delete' :
						if (confirm("Are you sure you want to delete this?")) {
							var obj = {
							 "datasetid" : result,
							 "actionname" : name
							};
							$scope.showLoaderTable = true;
							salesfactoryData.getData(actionUrl,'POST',obj).then(function(resp){
								angular.forEach(DataSet, function(data, index) {
									$scope.gridOptions.data.splice($scope.gridOptions.data.lastIndexOf(data), 1);
								});
							   $scope.selBoxActBtn = false;  
							}).finally(function(){$scope.showLoaderTable = false;});
						}
						break;
					case 'Enable' : 
						var obj = {
						 "datasetid" : result,
						 "actionname" : name
						};
						$scope.showLoaderTable = true;
						salesfactoryData.getData(actionUrl,'POST',obj).then(function(resp){
							angular.forEach(DataSet, function(data, index) {
								var ind = $scope.gridOptions.data.indexOf(data);
								var tempRow = $scope.gridApi.grid.rows[ind];
								tempRow.entity.status = 1;//"Enabled";
								$scope.gridApi.selection.clearSelectedRows();
								$scope.gridApi.grid.refresh();
							});
							$scope.showLoaderTable = false;
						}).finally(function(){$scope.showLoaderTable = false;});
					   break;
					case  'Disable' :
						var obj = {
						 "datasetid" : result,
						 "actionname" : name
						};
						$scope.showLoaderTable = true;
						salesfactoryData.getData(actionUrl,'POST',obj).then(function(resp){
							angular.forEach(DataSet, function(data, index) {
								var ind = $scope.gridOptions.data.indexOf(data);
								var tempRow = $scope.gridApi.grid.rows[ind];
								tempRow.entity.status = 0;//"Disabled";
								$scope.gridApi.selection.clearSelectedRows();
								$scope.gridApi.grid.refresh();
							});
							$scope.showLoaderTable = false;
						}).finally(function(){$scope.showLoaderTable = false;});
					 break;
					default :
						alert('select any action first');
						break;
				}
	       }else{
	       	$scope.selBoxActBtn = false;
	    	alert('Please select row first');
	       }
	    };

	    /*
	    *This function return all selected row data id
	    *@param : array (data set)
	    */	   
	    function getId(dataSet){
	      var idArray =[];
	      console.log(dataSet);
	      angular.forEach(dataSet, function(data, index) {
	      	console.log(data);
			idArray.push(data.id);
		  });
		  return idArray;
	    }

	    
	 	
		/***this function used for select an unselect all column in table**/
		$scope.rowSelectionFun = function(actionname) {
			if (actionname == 'select') {
				$scope.gridApi.selection.selectAllRows();
				$scope.selectItemTotal = $scope.gridOptions.data.length;
			} else if (actionname == 'unselect') {
				$scope.gridApi.selection.clearSelectedRows();
				$scope.selectItemTotal = 0;
			}
		}
		/***** This fnction used for select visible part of table in active section*****/
		$scope.rowVisibleSelectionFun = function(strFlag) {
			if (strFlag == 'visible') {
				$scope.gridApi.selection.selectAllVisibleRows();
				$scope.selectItemTotal = ($scope.gridApi.core.getVisibleRows($scope.gridApi.grid).length);
			} else if (strFlag == 'unVisible') {
				$scope.gridApi.selection.clearSelectedRows();
				$scope.selectItemTotal = 0;
			}
		};
		/***
		 * Filter section event handler and server callback config and assign data on table grid.
		 * *****/
		/***** This function used for get input box value from filter section******/
		$scope.textChangeFunction = function(str, $event) {
			var currentVal = $event.target.value;
			if (currentVal !== 'undefined')
				$scope.filterDataObj[str] = currentVal;
		}
		
		/*****this function used get select box value from filter section*****/
		$scope.filterSelectBoxChange = function(selectedOption, strName) {
			if (selectedOption.value !== ' ' ||  selectedOption.value !== undefined)
				$scope.filterDataObj[strName] = selectedOption.key;
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

		//Listen on create data for report chart
		$scope.creatChartData= function(data){
			data.map(function(d,i){
				if(i!==0){
					var a = d.totAmt.split(' ')[0];
					a = numberFormat(a);
					//report_chart_setting.columns_array[i]= new Array(d.period,a,d.totShip);
					report_chart_setting.columns_array.push([d.period,a,d.totShip]);
				}
			});
			return true;
		};

		//convert expected format in number
		function numberFormat(num){
			var output=0;
			//In case num have ,
			if(num.indexOf(',') !== -1){
				var n = num.split(',');
				//In case n have decimal point			
				var result ='';
				for(var i in n){
					result += n[i];
				}
				output = filterFormat(result);
			}else {
				output = filterFormat(num);
			}
			// clouser function to convert in exect number 
			function filterFormat(value){
				if (/^(\-|\+)?([0-9]+(\.[0-9]+)?|Infinity)$/.test(value)){
					return Number(value);
				}
				return NaN;
			}
			return output;
		}

		/**
		*This function used to export cutsomer data as per as search 
		*@param {object -> filter data }
		*@param  {string ->flag }
		**/
		exportDataAsCsv =  function(action_url, action_flag){

			if(action_url == undefined || action_url == "") return;

			//In case of customer report section if have query string
			var qs =getAllUrlParams();	
			var customer_report_section = false;
			var qst ='';

			if(!isEmpty(qs)){
				qst =  JSON.stringify(qs);
				qst =  JSON.parse(qst);
				customer_report_section = true;
			}	

			$scope.$evalAsync(function(){
				var data = getObjectClone($scope.filedSetModel);

				if(typeof customer_report_section!=="undefined" && customer_report_section){
					angular.extend(data,qst);
				}	

				window.location = action_url +'?'+ $.param(data);			
			});
		};

	};//end controller 
	angular.module('sabinaAdminApp').controller('orderReportCtrl', ctrlHendler);
  
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

    /******this function used for get query params from url******/
	function getAllUrlParams(url) {
	  var queryString = url ? url.split('?')[1] : window.location.search.slice(1);	

	  var obj = {};
	  var parm={};
	  if (queryString) {
		  queryString = queryString.split('#')[0];
	      var arr = queryString.split('&');
		  for (var i=0; i<arr.length; i++) {
			var a = arr[i].split('=');
			// in case params look like: list[]=thing1&list[]=thing2
			var paramNum = undefined;
			var paramName = a[0].replace(/\[\d*\]/, function(v) {
				paramNum = v.slice(1,-1);
				return '';
			});
			var paramValue = typeof(a[1])==='undefined' ? true : a[1];
			if(decodeURIComponent(paramName).match(/\[\d*\]/)){
				var x =1,str = paramName;
	    		str= decodeURIComponent(str).replace(/\[\d*\]/,'');
	    		if(typeof parm[str]!=='undefined'){var a = parm[str];parm[str] = ++a;}else parm[str] =x; 
	        }
			paramName = decodeURIComponent(paramName).replace(/\[\d*\]/,'');
			paramValue = decodeURIComponent(paramValue);
			if (obj[paramName]) {
				if (typeof obj[paramName] === 'string') {obj[paramName] = [obj[paramName]];}
				if (typeof paramNum === 'undefined') {obj[paramName].push(paramValue);}else {obj[paramName][paramNum] = paramValue;}
	        }else {
	          	obj[paramName] = paramValue;
	        }
	      }
	   }
	   for(var key in obj){if(typeof parm[key]!=='undefined'){if(parm[key]===1){obj[key] =[obj[key]];}}};
	   return obj;
	};
    
    //Listen on google chart drwaw
    function drawChart() {
       var data = google.visualization.arrayToDataTable(report_chart_setting.columns_array);
	   //var formatter = new google.visualization.NumberFormat({pattren : '$'});
	   // formatter.format(data, 1); // Apply formatter to second column
	  var options = {
	    width: '100%',
	    height: '70%',
	    bar: {groupWidth: '95%'},
	    bars: 'vertical',
	    colors: ["#3da5a0","#37cc00"],
	    legend: { position: "bottom" },	
	  };
	  var chart = new google.charts.Bar(document.getElementById('salerReoprtchart'));
	  chart.draw(data, google.charts.Bar.convertOptions(options));
	  //chart.getImageURI();
	  // google.visualization.events.addListener(chart, 'ready', function () {
		 //       // export_link.href = chart.getImageURI();
		 //       // id="export_link" target="_blank"
		 //       $('#export_link').click(function(){
		 //       	PrintElem('salerReoprtchart');
		 //       	//console.log(chart.getChart().getImageURI());
		 //       });
		 //       // console.log(chart.getImageURI());
	  // });	
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
  
    //Listen on click on export customer data(button)
    $(document).on("click", "#expcuscsv", function(event){
    	event.preventDefault();
    	var url = $(this).attr("data-attr");

    	if(url){
    		exportDataAsCsv(url, "customerReportCsv");
    	}
    });
})(window.angular);