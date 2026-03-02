"use strict";
(function() {
	angular.module('sabinaAdminApp').controller('sellerCateCtrl', ['$scope', 'salesfactoryData','uiGridConstants','$rootScope',
	function($scope, salesfactoryData ,uiGridConstants,$rootScope) {
		$scope.page = 0;
		$scope.tree = [];
		$scope.loadingMore = false;
		$scope.id = 0;
	    $scope.edit = false;
	    $scope.method = '';
	    $scope.data = [];
	    $scope.parent_cat = true;
	    $scope.parent_id;
		$scope.category_name = [];
	    $scope.cat_description = [];
	    $scope.cat_footer_seo = [];
	    $scope.meta_title =[];
	    $scope.meta_keyword = [];
	    $scope.meta_description = [];
	    $scope.status = 1;
	    $scope.showcatprod=false;
	    $scope.statusdropdown= [];
	    $scope.allcategorylist=[];
	    $scope.hidecategorytable=false;
	    $scope.tabActive=false;
	    $scope.no_result_found = false;
	    $scope.errorInfoLog ='<div class="no-info-blank"><h3><i class="icon-doc"></i> You have no information </h3></div>';
	    $scope.statusdropdown.configs = [{'name': 'Yes','value': '1'},
	    {'name': 'No', 'value': '0'}];
	    $scope.status = $scope.statusdropdown.configs[0]; 
	    //configration of filter button table
		$scope.tableFilterConfig = (tableConfig.filter !==undefined)? tableConfig.filter : false;
		/*****hide show table filter container******/
		$scope.tableFilterContainer = false;
		/******* This variable used for select button config section ********/
		$scope.tableSelectBtnConfig =(tableConfig.chk_action !==undefined)? tableConfig.chk_action : false;
		/**** This variable used for headre section pagination config***********/
		$scope.tableHeaderPaginationConfig = showHeadrePagination;
		/****this variable used for add row config*****/
		$scope.addrowConfig = true;
		$scope.showLoaderTable = true;
		/******This variable used for get all data from server at a time ******/
		//$scope.getAllDataFromServerOnce = getAllDataFromServerOnce;

		$scope.filterDataObj = {};
		$scope.tableloader = false;
		$scope.root_category = false;
		$scope.catmoveerror = false;
		let scat_id =[],rcat_id=[],cat_idInd=0;
		//$scope.tableFilterContainer=true;

	    // $scope.tinymceOptions = {
	    //     plugins: 'link image code',
	    //     toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | code',
	    //     width: 700
	    // };

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
		// $scope.columsSetting = [
		// 	{field : 'product_id',displayName : 'Id'},
		// 	{field : 'name',displayName : 'Product Name'},
		// 	{field : 'sku',displayName : 'SKU'},
		// 	{field : 'initial_price',displayName : 'Initial Price'},
		// 	{field : 'special_price',displayName : 'Special Price'}
		// ];

		salesfactoryData.getData(tableHeadurl, 'GET','').then((res)=>{
			if(res)
			  $scope.columsSetting = res.data;
		},(error)=>{
			try{throw new Error("Something went badly wrong!");}
			catch(e){console.log('Log Info '+e)};
		});
		$scope.gridOptions = {
			columnDefs : $scope.columsSetting,
			enableVerticalScrollbar: uiGridConstants.scrollbars.NEVER,
			rowTemplate : '<div grid="grid" class="ui-grid-draggable-row" draggable="true"><div ng-repeat="(colRenderIndex, col) in colContainer.renderedColumns track by col.colDef.name" class="ui-grid-cell" ng-class="{ \'ui-grid-row-header-cell\': col.isRowHeader, \'custom\': true }" ui-grid-cell></div></div>',
			onRegisterApi : function(gridApi) {
				$scope.gridApi = gridApi;
			}
		};
		$scope.gridOptions.enableHorizontalScrollbar = uiGridConstants.scrollbars.WHEN_NEEDED;
		/*******
		 * This grid Api function to handel All function as per as requirement.
		 * This function used for drag row update database table row position.
		 * Row selection and batch rwo selection.
		 * ********/
		$scope.gridOptions.onRegisterApi = function(gridApi) {
			$scope.gridApi = gridApi;
			let rowdragdrop=(tableConfig.row_rearrange !==undefined)? tableConfig.row_rearrange : false;
			gridApi.dragndrop.setDragDisabled(!rowdragdrop);
			gridApi.selection.on.rowSelectionChanged($scope, function(row) {
				scat_id=[];
				if(angular.lowercase($scope.filedSetModel[0].value) == 'no'){ 
					if(gridApi.selection.getSelectedGridRows().length>0){
						gridApi.selection.getSelectedGridRows().map((t)=>{
							scat_id.push(t.entity.product_id);
						})
					}
				}else if(angular.lowercase($scope.filedSetModel[0].value) == 'yes'){
					rcat_id=[];
					if(gridApi.grid.rows.length>0){
						(gridApi.grid.rows).map((it)=>{ 
							if(angular.isUndefined(it.isSelected) || it.isSelected === false)
								rcat_id.push(it.entity.product_id)
						})
					}
				}else if(angular.lowercase($scope.filedSetModel[0].value) == 'any'){
					rcat_id=[],scat_id=[];
					if(gridApi.grid.rows.length>0){
						(gridApi.grid.rows).map((it)=>{ 
							if(angular.isUndefined(it.isSelected) || it.isSelected === false)
								rcat_id.push(it.entity.product_id);
							else if(!angular.isUndefined(it.isSelected) || it.isSelected === true)
								scat_id.push(it.entity.product_id);
						})
					}
				}
			});
			gridApi.selection.on.rowSelectionChangedBatch($scope, function() {
				scat_id =[];console.log('rowSelectionChangedBatch');
				if(gridApi.selection.getSelectedGridRows().length>0){
					gridApi.selection.getSelectedGridRows().map((t)=>{
			    		scat_id.push(t.entity.product_id);
			    	})
			    }
			});
			gridApi.grid.registerDataChangeCallback(function(data){
			  if(cat_idInd.length>0){cat_idInd.map((it,k)=>{$scope.gridApi.selection.selectRow($scope.gridOptions.data[it]);});}
			  else $scope.gridApi.selection.clearSelectedRows();
	        },[uiGridConstants.dataChange.ROW]);
	        gridApi.pagination.on.paginationChanged($scope, function (pageNumber, pageSize){
	        	//$scope.gridOptions.minRowsToShow =3;
	        	//$scope.gridOptions.minimumColumnSize =3;
	        	$scope.gridApi.grid.refresh();
	        	console.log($scope.gridOptions);
	        	console.log(gridApi.core.getVisibleRows($scope.gridApi.grid))                             
	            //pageNumberOrPageSizeChanged(pageNumber, pageSize);
	            console.log('oh me joy' + pageNumber +'===='+pageSize);
	        }); 
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
		};
		/***this function used for select an unselect all column in table**/
		$scope.selectAllColumnFunActive = function(actionName) {
			if (actionName == 'select') {
				$scope.gridApi.selection.selectAllRows();
				$scope.selectItemTotalActive = $scope.gridOptions.data.length;
			} else if (actionName == 'unselect') {
				$scope.gridApi.selection.clearSelectedRows();
				$scope.selectItemTotalActive = 0;
			}
		};
		$scope.selectAllColumnFunFilter = function(actionName) {
			if (actionName == 'select') {
				$scope.gridApi.selection.selectAllRows();
				$scope.selectItemTotalFilter = $scope.gridOptions.data.length;
			} else if (actionName == 'unselect') {
				$scope.gridApi.selection.clearSelectedRows();
				$scope.selectItemTotalFilter = 0;
			}
		};
		$scope.selectAllColumnFunAll = function(actionName) {
			if (actionName == 'select') {
				$scope.gridApi.selection.selectAllRows();
				$scope.selectItemTotalAll = $scope.gridOptions.data.length;
			} else if (actionName == 'unselect') {
				$scope.gridApi.selection.clearSelectedRows();
				$scope.selectItemTotalAll = 0;
			}
		};
		/***** This fnction used for select visible part of table in active section*****/
		$scope.selectVisibleColumnsActive = function(strFlag) {
			$scope.selectVisibleItem = $scope.selectVisibleItemAll = $scope.selectVisibleItemFilter = 0;
			if (strFlag == 'visible') {
				$scope.gridApi.selection.selectAllVisibleRows();
				$scope.selectItemTotalActive = ($scope.gridApi.core.getVisibleRows($scope.gridApi.grid).length);
			} else if (strFlag == 'unVisible') {
				$scope.gridApi.selection.clearSelectedRows();
				$scope.selectItemTotalActive = 0;
			}
		};
		$scope.selectVisibleColumnsAll = function(strFlag) {
			if (strFlag == 'visible') {
				$scope.gridApi.selection.selectAllVisibleRows();
				$scope.selectItemTotalAll = ($scope.gridApi.core.getVisibleRows($scope.gridApi.grid).length);
			} else if (strFlag == 'unVisible') {
				$scope.gridApi.selection.clearSelectedRows();
				$scope.selectItemTotalAll = 0;
			}
		};
		$scope.selectVisibleColumnsFilter = function(strFlag) {
			if (strFlag == 'visible') {
				$scope.gridApi.selection.selectAllVisibleRows();
				$scope.selectItemTotalFilter = ($scope.gridApi.core.getVisibleRows($scope.gridApi.grid).length);
			} else if (strFlag == 'unVisible') {
				$scope.gridApi.selection.clearSelectedRows();
				$scope.selectItemTotalFilter = 0;
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
				$scope.filterDataObj['f_type'] = selectedOption.value;
		}
		/*****
		 * This function used for search data from grid when you are not used searching from server.
		 * Where searchDataFromGrid function call on button click
		 * and searchDataInGrid function used for searching.
		 * ********/
		$scope.searchDataFromGrid = function() {
			//console.log($scope.filterDataObj);
			$scope.gridApi.grid.registerRowsProcessor($scope.searchDataInGrid, 200);
			$scope.gridApi.grid.refresh();
			//$scope.displayTotalNumItems = $scope.gridOptions.data.length;
		};
		$scope.searchDataInGrid = function(renderableRows,resetFlag) {
			resetFlag = resetFlag || '';
			let ind = ($rootScope.filedsSet).findIndex((o)=>{return o.fieldName=='id'});
			$scope.filterDataObj['f_type'] = $scope.filedSetModel[0].value;
			$scope.filterDataObj['cat_id'] = $scope.id;
			if(angular.isDefined(resetFlag) && resetFlag !==''){
				angular.forEach($scope.filterDataObj,(item,key)=>{delete $scope.filterDataObj[key];})
				angular.forEach($scope.filedSetModel, (item,key)=>{$scope.filedSetModel[key] =''});
				$scope.filedSetModel[0]=$rootScope.filedsSet[ind].optionArr[0];
				$scope.filterDataObj['cat_id'] = $scope.id;
				$scope.filterDataObj['f_type'] = $scope.filedSetModel[0].value;
			}
			$scope.tableloader =true;
			salesfactoryData.getData(catesearchUrl,'GET',$scope.filterDataObj).then((r)=>{
				$scope.gridOptions.data=[],cat_idInd=[];
				let rsd = r.data;
				//console.log(r);return;
				if(angular.isDefined(rsd.catproductlist) && rsd.catproductlist.length>0){
					rsd.catproductlist.map((t,k)=>{
						if(t.cat_id==$scope.id) cat_idInd.push(k);
						delete rsd.catproductlist[k].cat_id;
					});
					$scope.gridOptions.data=rsd.catproductlist;
					$scope.displayTotalNumItems = $scope.gridOptions.data.length;
					$scope.no_result_found = false;
				}else{
					$scope.no_result_found = true;
					$scope.displayTotalNumItems =0;
				}
				$scope.tableloader =false;
				//$scope.gridApi.grid.refresh();
			},(error)=>{
				try{}catch(e){$log.log.info('info'); $scope.tableloader =true;}
			}).finally(()=>{$scope.tableloader =false;});
		};
		$scope.enableTab = function(str) {
			if (str == 'deactive')$scope.tabActive = false;
			else $scope.tabActive = true; 
		};
		$scope.save_category=()=>{
			let incat = angular.lowercase($scope.filedSetModel[0].value);
			let o ={'rm_cat_pid' : rcat_id,'add_cat_pid' :scat_id,'cat_id':$scope.id,'in_cat': incat };
			salesfactoryData.getData(savecateUrl,'POST',o).then((resp)=>{
			if(incat =='yes'){
				rcat_id=[];
				$scope.gridOptions.data.reduceRight(function(acc, obj, idx) {
			    if (rcat_id.indexOf(obj.product_id) > -1)
			       $scope.gridOptions.data.splice(idx,1);
				}, 0);
				$scope.gridApi.grid.selection.selectAll = true;
			}
			if(incat =='no'){
				console.log('mkjadd');
				$scope.gridOptions.data.reduceRight(function(acc, obj, idx) {
			    if (scat_id.indexOf(obj.product_id) > -1)
			       $scope.gridOptions.data.splice(idx,1);
				}, 0); 
			}

			console.log('mithilesh');
			console.log($scope.gridOptions.data);

			},(error)=>{
				try{throw new Error("Something went badly wrong!");}
				catch(e){console.log('Log Info '+e)};
			}).finally(()=>{
				//in all case
				console.log('fjksgf sd gshdfgjsdf');
			});
		};
		//catdragdropurl
		/*****category tree action section*****/
		$scope.catTreeOpt={
			// accept:(sourceNodeScope, destNodesScope, destIndex)=> {
			//  	 return $scope.catemove;
			// },
			//beforeDrop dropped
			beforeDrop: (event)=>{
			 	if(angular.isUndefined(event.pos.moving) || event.pos.moving === false) return;
			 	let o={};
			  	if(event.source.nodeScope.$modelValue)
			 		o["source_id"]=(event.source.nodeScope.$modelValue.id);
			 	o["dest_id"] =(event.dest.nodesScope.$nodeScope!==null)?(event.dest.nodesScope.$nodeScope.$modelValue.id) : false; 
			 	return salesfactoryData.getData(catdragdropurl,'POST',o).then((r)=>{
			 		console.log(r);
			 		// if(r.data.status==='unsuccess') $scope.catemove=false;
			 		// else $scope.catemove= true;
			 		if(r.data.status==='unsuccess') return false;
			 		else return true;
			 	},(e)=>{

			 	});
			 	// console.log(event.source.nodeScope.$modelValue);
			 	// console.log(event.dest.nodesScope.$nodeScope.$modelValue);
			 },
			cateOpen:(that)=>{
				if(that.$nodeScope!==null)
					$scope.categoriesopen(that.$nodeScope.$modelValue.id);
			 	console.log(that.$nodeScope.$modelValue);
			}
		};
	
		/*** end code******/
		$scope.getCategoryPosition=()=>{
			var objPage={'cat_id':$scope.id,'parent_cat_id':$scope.parent_category.cat_id};
	        salesfactoryData.getData(checkcatmovepossilbe_url, 'GET', objPage).then((res)=>{ 
	        	console.log(res);
	        	if(res.type=='success'){
	        		$scope.catmoveerror = false; 
	        	}
	        	if(res.type=="error"){
	        		$scope.catmoveerror = true;
	        	}
	        },(error)=>{
				try{throw new Error("Something went badly wrong!");}
				catch(e){console.log('Log Info '+e)};
			}); 
		};
		/*****Category data get and set section****/
		salesfactoryData.getData(categoryList, 'GET','').then((res)=>{
			if(res){
				$scope.tree = res.data;
				//console.log(res.data);
			}
		},(error)=>{
			try{throw new Error("Something went badly wrong!");}
			catch(e){console.log('Log Info '+e)};
		});

	   if(cat_id != ''){
		$scope.categoryopen = function(){
			console.log('open categoryopen');
	                $scope.loadingMore = true;
	                $scope.id = cat_id;
	                var objPage={id:$scope.id}
	                salesfactoryData.getData(catediturl, 'GET', objPage).then((res)=>{ 

	                  if(res.status == 1){
			         	//console.log('mithilesh');
			        	console.log(res.allcategorylist);
			        	$scope.allcategorylist = res.allcategorylist;
			        	$scope.status = $scope.statusdropdown.configs[0];
			        	$scope.edit = true;
			         }else{
			          	$scope.status = $scope.statusdropdown.configs[1];
			         } 
			         angular.element('#select_'+cat_id).addClass('skyblue');
			         console.log(res.data.categorydesces);
			         angular.forEach(res.data.categorydesces, function(val,key){
						     $scope.category_name[val.lang_id] = val.category_name;
						     $scope.cat_description[val.lang_id] = val.cat_description;
						     $scope.cat_footer_seo[val.lang_id] = val.cat_footer_seo;
	                         // console.log(val);
						    $scope.meta_title[val.lang_id] = val.meta_title;
						    $scope.meta_keyword[val.lang_id] = val.meta_keyword;
						    $scope.meta_description[val.lang_id] = val.meta_description;
						    $scope.icon_type = val.icon_type;
						    $scope.icon_name = "Hello";
						    

	                    });

			           $scope.loadingMore = false;


		            },(error)=>{
						try{throw new Error("Something went badly wrong!");}
						catch(e){console.log('Log Info '+e)};
					});

	              ///alert(cat_id);

	           };

	         $scope.categoryopen();

	       }

	        $scope.categoriesopen = (catid)=>{
	       	    //$scope.parent_category = $scope.allcategorylist[0];
	       		$scope.loadingMore = true;
				$scope.id = catid;
				$scope.edit = true;
				$scope.parent_cat = false;
				
				angular.element('input[name="_method"]').val('PUT');
				angular.element('#category_id').val(catid);
				angular.element('.listName').removeClass('skyblue');
				angular.element('#select_'+catid).addClass('skyblue');
				var formaction = angular.element('form#sellerCategoryForm').attr('action');
				formaction = action + '/' + catid;
				angular.element('form#sellerCategoryForm').attr('action', formaction);
				var objPage={id:$scope.id}
				// let indOpt = ($rootScope.filedsSet).findIndex((o)=>{return o.fieldName=='id'});
				// let defaultOpt = $rootScope.filedsSet[indOpt].defaultVal;
				// angular.forEach($rootScope.filedsSet[indOpt].optionArr, (item,key)=>{
				// 	if(item.id==defaultOpt) $scope.filedSetModel[indOpt]= item;
				// });
				//defaultVal
				salesfactoryData.getData(cateEditurl, 'GET', objPage).then((res)=>{
					$scope.gridOptions.data=[],cat_idInd=[];
					let rsd = res.data;
					rsd.catproductlist.map((t,k)=>{
						if(t.cat_id==$scope.id) cat_idInd.push(k);
						delete rsd.catproductlist[k].cat_id;
					});
					$scope.showLoaderTable = false;
					$scope.gridOptions.data = rsd.catproductlist;
					$scope.gridOptions.totalItems = (rsd.catproductlist.length);
					$scope.displayTotalNumItems = $scope.gridOptions.data.length;
	                $scope.parent_id = rsd.parent_id;
	                if(angular.isDefined(rsd.catproductlist) && rsd.catproductlist.length>0){
							//$scope.gridOptions.data=res.catproductlist;
							//$scope.displayTotalNumItems = $scope.gridOptions.data.length;
							$scope.no_result_found = false;
					}else{
							$scope.no_result_found = true;
							//$scope.displayTotalNumItems =0;
					}


					// if($scope.gridOptions.data.length>0){
					// 	$scope.hidecategorytable=true;
					// }else{
					// 	$scope.hidecategorytable=false;
					// }
					// console.log('mmm');
					// console.log(res.catproductlist);
					if(($scope.gridOptions.data).length>0){
						$scope.hidecategorytable=true;
					}else{
						$scope.hidecategorytable=false;
					}	

					$scope.loadingMore = false;
					if(res.status == 1){ 
						$scope.showcatprod = true;					
						$scope.status = $scope.statusdropdown.configs[0];
						$scope.allcategorylist = rsd.allcategorylist;

					}else{
						$scope.showcatprod = true;
						$scope.status = $scope.statusdropdown.configs[1];
					} 

					angular.element('#parent_id').val(rsd.parent_id);   
					(rsd.blogcategorydesces).map((val)=>{
						$scope.category_name[val.lang_id] = val.category_name;
						$scope.cat_description[val.lang_id] = val.cat_description;
						$scope.cat_footer_seo[val.lang_id] = val.cat_footer_seo;
						
						$scope.meta_title[val.lang_id] = val.meta_title;
						$scope.meta_keyword[val.lang_id] = val.meta_keyword;
						$scope.meta_description[val.lang_id] = val.meta_description;
						$scope.edit = true;
					});
				},(error)=>{
					try{throw new Error("Something went badly wrong!");}
					catch(e){console.log('Log Info '+e)};
				});
				console.log($scope.gridOptions);

	        };
			$scope.getTableHeight = function() {
				 var rowHeight = 45; // your row height
				 var headerHeight = 39; // your header height
				 //var footerRowHeight = 32; // pre-calculated
				 let as = $scope.gridApi.core.getVisibleRows().length;
				 console.log();
				 return {
				    height: (as * rowHeight + headerHeight) + "px"
				 };
			};
	}]);
})();