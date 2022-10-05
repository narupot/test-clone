tableApp.controller('customerReportController', ['$scope', 'salesfactoryData', '$rootScope', '$parse', '$timeout', 'uiGridConstants', '$templateCache',
function($scope, salesfactoryData, $rootScope, $parse, $timeout, uiGridConstants, $templateCache) {
	$scope.filetrFileldName = {};
	$scope.addtableRowData = {};
	$scope.salesData = [];
	$scope.settingPanel = [];
	$scope.dataLength = 0;
	$scope.filterDataObj = {};
	$scope.enableTick = true;
	$scope.selectItemTotalActive = 0,$scope.selectItemTotalAll =0, $scope.selectItemTotalFilter =0, $scope.displayTotalNumItems = 0;
	//configration section of setting panel
	$scope.tableSettingConfig = true;
	//configration of filter button table
	$scope.tableFilterConfig = true;
	/***** hhide show table filter container******/
	$scope.tableFilterContainer = false;
	/******* This variable used for select button config section ********/
	$scope.tableSelectBtnConfig = true;
	/**** This variable used for headre section pagination config***********/
	$scope.tableHeaderPaginationConfig = showHeadrePagination;
	/****this variable used for add row config*****/
	$scope.addrowConfig = true;
	/**** sorting field confiration ******/
	$scope.sortingConfig = false;
	$scope.showLoaderTable = true;
	/******This variable used for get all data from server at a time ******/
	$scope.getAllDataFromServerOnce = getAllDataFromServerOnce;
	/******* new code sectoon**********/
	$scope.dragEnable = true;
	$scope.selBoxActBtn = false;
	$scope.totalRecords=0;
	/***
	 * This code used for columns setting
	 * where field is field name of table and displayName is what you want to display name of filed.
	 * cellTemplate used for add template(Means html as per as your requirement).
	 * Assign Action on template(HTML) Like ng-click="grid.appScope.funEdit(row)"
	 * where row is attribute which is use to get entire row attribute with the help of row.entity.
	 * Where ng-click is click event and grid.appScope is scope accessibility that means funEdit belons to grid scope
	 * so if you want to access this scope then you need to write this line grid.appScope.
	 * After that you declare function in controller like $scope.funEdit = function(dataAttr){/ any action here/}.
	 * where dataAttr.entity to get all attribute of this field like name id totla amount etc.
	 ***/
	/******This function used for get data from server and assign in table creator variable*****/
	$scope.columsSetting = [{
		field : 'id',
		displayName : 'S.No',
		cellTemplate : '<span><%grid.appScope.seqNumber(row)+1%></span>',
		//width: '70',
		enableSorting: true,
	},{		
		field : 'email',
		displayName : 'Email',
		cellTemplate: '<a href="javascript:;" class="skyblue"><%row.entity.email%></a>',
		enableSorting: true,
	}, {
		field : 'fname',
		displayName : 'First name',
		enableSorting: true,
	},  {
		field : 'lname',
		displayName : 'Last Name',
		enableSorting: true,
	},  {
		field : 'group',
		displayName : 'Customer Group',
		//width: '150',
		enableSorting: true,
	}, {
		field : 'lastOrder',
		displayName : 'Last Order Date',
		//width: '150',
		enableSorting: true,
	},  {
		field : 'totOrd',
		cellTemplate : '<span><a href="<%row.entity.listUrl%>" class="skyblue"><%row.entity.totOrd%></a></span>',
		displayName : 'Number of Order',
		//width: '150',
		enableSorting: true,
	},  {
		field : 'totAmt',
		displayName : 'LifeTime Sales',
		//width: '150',
		enableSorting: true,
	}, {
		field : 'avgSale',
		displayName : 'Average Sales',
		//minWidth: '150',
		enableSorting: true,
	}];

	$scope.gridOptions = {
		columnDefs : $scope.columsSetting,
		enableVerticalScrollbar: uiGridConstants.scrollbars.NEVER,
       	rowTemplate : '<div grid="grid" class="ui-grid-draggable-row" draggable="true"><div ng-repeat="(colRenderIndex, col) in colContainer.renderedColumns track by col.colDef.name" class="ui-grid-cell" ng-class="{ \'ui-grid-row-header-cell\': col.isRowHeader, \'custom\': true }" ui-grid-cell></div></div>',
		onRegisterApi : function(gridApi) {
			$scope.gridApi = gridApi;
		}
	};
	$scope.gridOptions.enableHorizontalScrollbar = uiGridConstants.scrollbars.WHEN_NEEDED;
	/******This function used for get data from server and assign in table creator variable*****/
	salesfactoryData.getData(dataJsonUrl, '').then(function(response) {
		$scope.salesData = response;
		$scope.settingPanel = $scope.salesData[0];
		$scope.gridOptions.totalItems = (response.length);
		$scope.gridOptions.data = response;
		$scope.displayTotalNumItems = $scope.gridOptions.data.length;
		$scope.showLoaderTable = false;
	});
	/***** This function used for get page view data from server*****/
	salesfactoryData.getData(pagelimit, '').then(function(response) {
		$scope.gridOptions.paginationPageSizes = [];
		angular.forEach(response, function(key, val) {
			$scope.gridOptions.paginationPageSizes[val] = (key.value);
		})
		$scope.gridOptions.paginationPageSize = response[0].value;
		$scope.gridOptions.PageSize = $scope.gridOptions.paginationPageSize;
		$scope.viewItemPerPage = $scope.gridOptions.paginationPageSize;
		$scope.gridOptions.minRowsToShow = $scope.gridOptions.paginationPageSize;
		if (!$scope.$$phase)
			$scope.$apply();
	});
	/*******This function used to display sequence number of row in table******/
	$scope.seqNumber = function(row) {
	   // find real row by comparing $$hashKey with entity in row
	    var rowIndex = -1;
	    var hash = row.entity.$$hashKey;
	    var data = $scope.gridOptions.data;     // original rows of data
	    for (var ndx = 0; ndx < data.length; ndx++) {
	        if (data[ndx].$$hashKey == hash) {
	            rowIndex = ndx;
	            break;
	        }
	    }
	    return rowIndex;
	};
	/******* This function used for Header pagination control********/
	$scope.HeaderPagination = {
		getTotalPages : function() {
			return Math.ceil($scope.gridOptions.totalItems / $scope.gridOptions.PageSize);
		},
		nextPage : function() {
			if ($scope.gridOptions.paginationCurrentPage < this.getTotalPages()) {
				$scope.gridOptions.paginationCurrentPage++;
			}
		},
		previousPage : function() {
			if ($scope.gridOptions.paginationCurrentPage > 1) {
				$scope.gridOptions.paginationCurrentPage--;
			}
		},
		pageSizeChange : function(num) {
			$scope.viewItemPerPage = num;
			$scope.gridOptions.minRowsToShow = num;
			if ($scope.getAllDataFromServerOnce) {
				$scope.gridOptions.paginationPageSize = num;
				$scope.gridOptions.PageSize = num;
				$scope.gridOptions.totalItems = ($scope.salesData.length);
				$scope.displayTotalNumItems = $scope.gridOptions.data.length;
			} else {
				$scope.showLoaderTable = true;
				var obj = {
					'pageLimit' : num
				};
				salesfactoryData.getData('admin/users/json', obj).then(function(response) {
					$scope.gridOptions.data = response;
					$scope.gridOptions.totalItems = (response.length);
					$scope.showLoaderTable = false;
				})
			}
		}
	}
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
			console.log(row);
			if($scope.gridApi.selection.getSelectedRows().length >0)
			 	$scope.selBoxActBtn = true;
			else
				$scope.selBoxActBtn = false;
			console.log('fire after selection change');
		});
		gridApi.selection.on.rowSelectionChangedBatch($scope, function() {
			console.log('batch selection change');
			if($scope.gridApi.selection.getSelectedRows().length >0)
			 	$scope.selBoxActBtn = true;
			else
				$scope.selBoxActBtn = false;
		});
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
	$scope.updateStatus = function(tempRow, tempCol, $event,actUrl,rowId,actName) {
		var dataObj = {
			'id' : rowId,
			'action_type' : actName
		}
	
		switch(actName){
			case 'delete' :
				if(confirm("Are you sure you want to delete this?")){
					salesfactoryData.getData(actUrl,'POST',dataObj).then(function(response) {
					     var index = $scope.gridOptions.data.indexOf(tempRow.entity);
						 $scope.gridOptions.data.splice(index, 1);
					})
				}
			break;
			case 'change_status' :
				salesfactoryData.getData(actUrl,'POST',dataObj).then(function(response) {tempRow.entity.status = response;});
			break;
		}
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
		if ( typeof $scope.gridApi.selection.getSelectedRows() != 'undefined' && $scope.gridApi.selection.getSelectedRows().length > 0) {
			if (confirm("Are you sure you want to delete this?")) {
				angular.forEach($scope.gridApi.selection.getSelectedRows(), function(data, index) {
					$scope.gridOptions.data.splice($scope.gridOptions.data.lastIndexOf(data), 1);
					idArr.push(data.id);
				});
				var idObj = {
					"id" : idArr
				};
				salesfactoryData.getData(removeRowUrl, idObj);
			}
		} else
			alert('Please select at least one check box');
	}
	/***** This function used to handel callback function which is declare in celltamplate and do some action
	 * and dataAttr.entity is used to get all field value like dataAttr.entity.id.
	 * ******/
	$scope.funEdit = function(dataAttr) {
		console.log(dataAttr.entity);
	}
	/********
	* This both actionOnDataGrid and actionBtnClick function used for Action on Grid Data Like Delete ,Enable and Disable etc 
	* This perform after data is selected if not selected then not perform any action.
	********/
	 $scope.actioOptions = [
        {id: 0, name: '---Action---'},
        {id: 1, name: 'Enable'},
        {id: 2, name: 'Disable'},
        {id: 3, name: 'Delete'},
    ];
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
       var result =  createIdArray(DataSet);
       if($scope.gridApi.selection.getSelectedRows().length >0){
	       switch(name){
				case 'Delete' :
					if (confirm("Are you sure you want to delete this?")) {
						var obj = {
						 "DataSetID" : result,
						 "actionName" : name
						};
						salesfactoryData.getData(actionUrl,'POST',obj).then(function(resp){
							angular.forEach(DataSet, function(data, index) {
								$scope.gridOptions.data.splice($scope.gridOptions.data.lastIndexOf(data), 1);
							});
						   $scope.selBoxActBtn = false;  
						});
					}
					break;
				case 'Enable' : 
					var obj = {
					 "DataSetID" : result,
					 "actionName" : name
					};
					salesfactoryData.getData(actionUrl,'POST',obj).then(function(resp){
						angular.forEach(DataSet, function(data, index) {
							var ind = $scope.gridOptions.data.indexOf(data);
							var tempRow = $scope.gridApi.grid.rows[ind];
							tempRow.entity.status = "Enabled";
							$scope.gridApi.selection.clearSelectedRows();
							$scope.gridApi.grid.refresh();
						});
					});
				   break;
				case  'Disable' :
					var obj = {
					 "DataSetID" : result,
					 "actionName" : name
					};
					salesfactoryData.getData(actionUrl,'POST',obj).then(function(resp){
						angular.forEach(DataSet, function(data, index) {
							var ind = $scope.gridOptions.data.indexOf(data);
							var tempRow = $scope.gridApi.grid.rows[ind];
							tempRow.entity.status = "Disabled";
							$scope.gridApi.selection.clearSelectedRows();
							$scope.gridApi.grid.refresh();
						});
					});
				 break;
				default :
					alert('select any action first');
					break;
			}
       }else{
       	$scope.selBoxActBtn = false;
    	alert('Please select row first');
       }
    }
    /*****This function return all selected row data id*****/
    function createIdArray(dataSet){
      var idArray=[];
      angular.forEach(dataSet, function(data, index) {
		idArray.push(data.id);
	  });
	  return idArray;
    }
    
	// $scope.callbackFunctionAbc = function(){
	// alert('fsdgfhgfh');
	// }
	// $scope.salesData.forEach( function( row, index){
	// row.sequence = index;
	// });
	/*****
	 * This function used for search data from grid when you are not used searching from server.
	 * Where searchDataFromGrid function call on button click
	 * and searchDataInGrid function used for searching.
	 * ********/
	$scope.searchDataFromGrid = function() {
		//console.log($scope.filterDataObj);
		$scope.gridApi.grid.registerRowsProcessor($scope.searchDataInGrid, 200);
		$scope.gridApi.grid.refresh();
		// console.log($scope.gridOptions.data.length);
		// console.log($scope.gridApi.core.getVisibleRows().length);
		// console.log($scope.gridOptions.totalItems);
		// //$scope.displayTotalNumItems = $scope.gridOptions.data.length;
		// $scope.displayTotalNumItems = $scope.gridOptions.totalItems;
		// console.log($scope.gridApi.grid.options.totalItems);
		//$scope.lineTotal = _.reduce($scope.gridOptions.data, function(a, b) {return a + b}, 0);
		//console.log($scope.gridApi.grid.renderContainers.body.visibleRowCache);
	    var abc= $scope.gridApi.grid.rows.filter(function(o){
	     	return o.visible;
	     })
	    console.log(abc);
		//console.log($scope.gridApi.grid.renderContainers.body.renderedRows);

	}
	$scope.searchDataInGrid = function(renderableRows) {
		var matcher = [];
		angular.forEach($scope.filterDataObj, function(name, index) {
			if (name != 'Please Select...')
				matcher.push(name);
		});
		//console.log(matcher);
		var matLen = matcher.length,
		    i = 0;
		renderableRows.forEach(function(row) {
			var match = false;
			$rootScope.filedsSet.forEach(function(field, indx) {
				var temp = '';
				if (i >= matLen)
					i = 0;
				if (matcher[i] != '')
					temp = new RegExp('^' + matcher[i], 'i');
				if (row.entity[field.fieldName].match(temp)) {
					i++;
					match = true;
				}

			});
			if (!match)
				row.visible = false;
		});
		return renderableRows;
	};
	/*** end code******/

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
		if (selectedOption.value !== ' ' || typeof selectedOption.value !== 'undefined')
			$scope.filterDataObj[strName] = selectedOption.value;
	}
	/******* This function used for set filter attribute and get data from server *****/
	$scope.getFilterDataFromServer = function() {
		var objData = angular.merge({}, $scope.filterDataObj, $rootScope.dropdownValList)
		//angular.extend($scope.filterDataObj,$rootScope.dropdownValList);
		console.log(objData);
		console.log($scope.filterDataObj);
		console.log($rootScope.drodownValList);
		return false;
		if (angular.isObject($scope.filterDataObj) && !angular.equals({}, $scope.filterDataObj)) {
			var obj = $scope.filterDataObj;
			salesfactoryData.getData('admin/users/json', obj).then(function(res) {
				//$scope.gridOptions.data = res.salesData;
				console.log(res);
				//$scope.salesData = [];
				//$scope.salesData = res.salesData;
				//angular.copy(res.salesData, $scope.salesData);
				//$scope.salesData.push(res.salesData);
				// $scope.$applyAsync()
				// console.log($scope.salesData);
				if (!$scope.$$phase)
					$scope.$apply();
			})
		}
	}

	$scope.checkAllActive = function() {
		($scope.checkedAllActCal) ? $scope.checkedAllActCal = false : $scope.checkedAllActCal = true;
	}
	$scope.checkedAll = function() {
		($scope.checkedAllCal) ? $scope.checkedAllCal = false : $scope.checkedAllCal = true;
	}
	$scope.checkedAllFilterCls = function() {
		($scope.checkedAllColFilter) ? $scope.checkedAllColFilter = false : $scope.checkedAllColFilter = true;
	}
	//	console.log($rootScope.filedsSet)
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
	};
	/*****This function used for table row add in backend using dynamic url ******/
	$scope.addRowInBackendTable = function(strUrl, strAction) {
		console.log($scope.addtableRowData);
	};
	/****** This function used for get selected value form option box******/
	$scope.getSelectedOptionVal = function() {
		console.log($scope.filterUrlSelectBoxModel);
		console.log('just chil don dhhu');
	};
	$scope.testFunction = function() {
		console.log($scope.test);
	};
	$scope.getTableHeight = function() {
		 var rowHeight = 45; // your row height
		 var headerHeight = 39; // your header height
		 //var footerRowHeight = 32; // pre-calculated
		 var as = $scope.gridApi.core.getVisibleRows().length;
		 console.log();
		 return {
		    height: (as * rowHeight + headerHeight) + "px"
		 };
	};
}]);