/*
 *@Name : sellerCateCtrl.js
 *@Description : This controller used for admin product categories listing.
 *@Author : Smoothgrapg Connect pvt. Ltd.
 */
(function() {

   "use strict";

   function categoryCtrl($scope, salesfactoryData, uiGridConstants, $rootScope, $timeout) {
      $scope.page = 0;
      $scope.tree = [];
      $scope.loadingMore = false;
      $scope.id = 0;
      $scope.edit = false;
      $scope.method = '';
      $scope.deleteUrl = '';
      $scope.previewUrl = '';
      $scope.data = [];
      $scope.parent_cat = true;
      $scope.parent_id;
      $scope.icon_name = '';
      $scope.comment = '';
      $scope.icon_type = 'image';
      $scope.category_name = [];
      $scope.cat_description = [];
      $scope.cat_footer_seo = [];
      $scope.meta_title = [];
      $scope.meta_keyword = [];
      $scope.meta_description = [];
      $scope.status = 1;

      $scope.showcatprod = false;
      $scope.statusdropdown = [];
      $scope.displaydropdown=[];
      $scope.allcategorylist = [];
      $scope.hidecategorytable = false;
      $scope.tabActive = false;
      $scope.no_result_found = false;
      $scope.errorInfoLog = '<div class="no-info-blank"><h3><i class="icon-doc"></i> You have no information </h3></div>';
      $scope.statusdropdown.configs = [{
            'name': 'Active',
            'value': '1'
         },
         {
            'name': 'Inactive',
            'value': '0'
         }
      ];

      $scope.displaydropdown.configs = [{
            'name': 'Product List',
            'value': '1'
         },
         {
            'name': 'Subcategory Image List',
            'value': '2'
         },
         {
            'name': 'Show Both',
            'value': '1,2'
         }
      ];

      //$scope.display_mode = '1';
      //$scope.cat_comment = $scope.
      $scope.display_mode = $scope.displaydropdown.configs[0];
      $scope.status = $scope.statusdropdown.configs[0];
      //configration of filter button table
      $scope.tableFilterConfig = (tableConfig.filter !== undefined) ? tableConfig.filter : false;
      /*****hide show table filter container******/
      $scope.tableFilterContainer = false;
      /******* This variable used for select button config section ********/
      $scope.tableSelectBtnConfig = (tableConfig.chk_action !== undefined) ? tableConfig.chk_action : false;
      /**** This variable used for headre section pagination config***********/
      $scope.tableHeaderPaginationConfig = showHeadrePagination;
      /****this variable used for add row config*****/
      $scope.addrowConfig = true;
      $scope.showLoaderTable = false;    
      $scope.root_category = false;
      $scope.catmoveerror = false;
      $scope.tableLoaderImgUrl = (typeof tableLoaderImgUrl != "undefined") ? tableLoaderImgUrl : "";

      var scat_id = [],
         rcat_id = [],
         cat_idInd = 0,
         sub_action_type = "";

      $scope.selectionFlag = {
         select_all : false,
         select_visible : false,
      };

/*** Table section start here ***/
    
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
             "action_type" :  (type!==undefined && type!='') ? type : "all_cate" ,
             "per_page" : (per_page!==undefined && per_page) ?  per_page : $scope.gridOptions.PageSize,
         };
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
         //console.log(typeof order_email);
         if(typeof order_email!="undefined"){
             //console.log(order_email);
             obj["email"] = order_email;
         }

         dataJsonUrl = (angular.isUndefined(url) || url ==='')? dataJsonUrl : url;

         salesfactoryData.getData(dataJsonUrl,'GET',obj).then(function(rs){
             var d = rs.data;

             //In case if response data not undefined and data is array
             if(!angular.isUndefined(d.data) && angular.isArray(d.data)){
                 if(d.data.length<=0){
                     $scope.no_result_found=true; 
                 }else{
                     $scope.no_result_found=false;
                 }

                 $scope.gridOptions.totalItems = (d.total);
                 $scope.gridOptions.data = d.data;
                 $scope.displayTotalNumItems = d.total;
                 $scope.showLoaderTable = false;                    
             }else{
                 //In case response data is empty string
                 $scope.gridOptions.totalItems = 0;
                 $scope.gridOptions.data = [];
                 $scope.displayTotalNumItems = 0;
                 $scope.no_result_found=true;
                 $scope.showLoaderTable = false;
             }                           
         },function(error){
             $scope.showLoaderTable=false;
             $scope.no_result_found=false;
             _errorHandler();
         }).finally(function(){
             $scope.showLoaderTable = false;
         }); 
      };



      /*******
       * This grid Api function to handel All function as per as requirement.
       * This function used for drag row update database table row position.
       * Row selection and batch rwo selection.
       * ********/


      /******* This function used for Header pagination control********/
      $scope.HeaderPagination = {
         getTotalPages: function() {
            return Math.ceil($scope.gridOptions.totalItems / $scope.gridOptions.PageSize);
         },
         nextPage: function() {
            if ($scope.gridOptions.paginationCurrentPage < this.getTotalPages()) {
               $scope.gridOptions.paginationCurrentPage++;
            }
         },
         previousPage: function() {
            if ($scope.gridOptions.paginationCurrentPage > 1) {
               $scope.gridOptions.paginationCurrentPage--;
            }
         },
         pageSizeChange: function(num) {
            $scope.viewItemPerPage = num;
            $scope.gridOptions.minRowsToShow = num;
            $scope.gridOptions.paginationPageSize = num;
            $scope.gridOptions.PageSize = num;
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

      /*****
       * This function used for search data from grid when you are not used searching from server.
       * Where searchDataFromGrid function call on button click
       * and searchDataInGrid function used for searching.
       * ********/
      $scope.searchDataFromGrid = function(resetFlag) {
         resetFlag = resetFlag || '';
         sub_action_type = "filter";

         if (resetFlag === 'resetfilter') {
            sub_action_type = "";
            //empty model
            angular.forEach($rootScope.filedSetModel, function(item, index) {
               if (angular.isObject(item) === true) {
                  $rootScope.filedSetModel[index] = item;
               } else {
                  $rootScope.filedSetModel[index] = "";
               }
            });
         }

         $scope.searchDataInGrid();

      };

      $scope.searchDataInGrid = function() {
         var filetrObject = getObjectClone($rootScope.filedSetModel);
         filetrObject.cat_id = $scope.id;
         filetrObject.sub_action_type = sub_action_type;

         //get filter from server
         //$scope.showLoaderTable = true;

         salesfactoryData.getData(catesearchUrl, 'GET', filetrObject)
            .then(function(r) {
               $scope.gridOptions.data = [];
               cat_idInd = [];
               var rsd = r.data;

               if (angular.isDefined(rsd.catproductlist) && rsd.catproductlist.length > 0) {
                  rsd.catproductlist.map(function(t, k) {
                     if (t.cat_id == $scope.id) cat_idInd.push(k);
                     delete rsd.catproductlist[k].cat_id;
                  });
                  $scope.gridOptions.data = rsd.catproductlist;
                  $scope.displayTotalNumItems = $scope.gridOptions.data.length;
                  $scope.no_result_found = false;
                  $scope.gridApi.grid.refresh();
               } else {
                  $scope.no_result_found = true;
                  $scope.displayTotalNumItems = 0;
               }
               $scope.showLoaderTable = false;
            }, function(error) {
               $scope.showLoaderTable = false;
               _errorHandler();
            }).finally(function() {
               $scope.showLoaderTable = false;
            });
      };

      //enable table section
      $scope.enableTab = function(str) {
         if (str == 'deactive') {
            $scope.tabActive = false;
         } else {
            $scope.tabActive = true;
         }
      };

      $scope.getTableHeight = function() {
         var rowHeight = 45; // your row height
         var headerHeight = 39; // your header height
         //var footerRowHeight = 32; // pre-calculated
         var as = $scope.gridApi.core.getVisibleRows().length;

         return {
            height: (as * rowHeight + headerHeight) + "px"
         };
      };

      /****
       *Listen on click on next footer pagination
       *@url : service url
       *@type : product type(ex. related , upsell etc)
       *@page : page number 
       *@prd_type_flag : which tab is enable like simple,config and related product 
       *****/
      $scope.clickOnNext = function(page) {
         //$scope.showLoaderTable=true;
         //_getTableListData('', '', page,$scope.gridOptions.PageSize, $scope.filterDataObj);            
      };


      $scope.save_category = function() {
         var incat = angular.lowercase($rootScope.filedSetModel["id"].value);
         var o = {
            'rm_cat_pid': rcat_id,
            'add_cat_pid': scat_id,
            'cat_id': $scope.id,
            'in_cat': incat
         };

         salesfactoryData.getData(savecateUrl, 'POST', o).then(function(resp) {
            if (incat == 'yes') {
               rcat_id = [];
               $scope.gridOptions.data.reduceRight(function(acc, obj, idx) {
                  if (rcat_id.indexOf(obj.product_id) > -1)
                     $scope.gridOptions.data.splice(idx, 1);
               }, 0);
               $scope.gridApi.grid.selection.selectAll = true;
            }
            if (incat == 'no') {
               console.log('mkjadd');
               $scope.gridOptions.data.reduceRight(function(acc, obj, idx) {
                  if (scat_id.indexOf(obj.product_id) > -1)
                     $scope.gridOptions.data.splice(idx, 1);
               }, 0);
            }
         }, function(error) {
            try {
               throw new Error("Something went badly wrong!");
            } catch (e) {
               console.log('Log Info ' + e)
            };
         }).finally(function() {
            //in all case
            console.log('fjksgf sd gshdfgjsdf');
         });
      };
    
/*****category tree action section*****/

      //this function used to get catory data (self excutive function on page load)
      function getCategoryList() {
         salesfactoryData.getData(categoryList, 'GET', '').then(function(res) {
            if (res) {
                $scope.tree = res.data;
               // Stop the pending timeout
               //$timeout.cancel(cate_time_out);          
            }
         }, function(error) {
            _errorHandler();
         });
      };

      getCategoryList();

      $scope.catTreeOpt = {
         beforeDrag : function(sourceNodeScope){
            //case if category is default category then can't perform action drag & drop 
            if(sourceNodeScope.$modelValue && (sourceNodeScope.$modelValue.is_default).toString() === '1') return false;
           
            return true;
         },
         dragMove : function(event){
            //console.log(event);
         },
         //beforeDrop dropped
         beforeDrop: function(event) {
            if (angular.isUndefined(event.pos.moving) || event.pos.moving === false) return;
            
            var source_data_type = (event.source.nodeScope.$element) ? event.source.nodeScope.$element.attr("data-type") : '',
                dest_data_type = (event.dest.nodesScope.$element) ? event.dest.nodesScope.$element.attr("data-type") : '';
            //case one if source element is child level element & drop at top-level-element then not accept
            //if((source_data_type && dest_data_type) && source_data_type === 'child-level-elem' && dest_data_type === 'top-level-element') return !1;
            //case two if source element is child and dest elemet is undefined then not accept(means if its become child and again assign as a parent level it no accept)
            //if(source_data_type && (dest_data_type == undefined || dest_data_type == '') && source_data_type === 'child-level-elem') return false;

            //console.log("source", event.source.nodeScope);
            //console.log("dest", event.dest);
            // console.log("position", event.pos);
            // console.log("attribute", event.source.nodeScope.$element.attr("data-type"))
            //  console.log("attribute", event.dest.nodesScope.$element.attr("data-type"))
            // console.log("attribute dest", event.dest.nodesScope.$element)
            // console.log(event);
            // console.log("destination ", event.dest.index)           
            
            //category data object 
            var $dest_node = event.dest;
            var $source_node = event.source;
            var current_dest_index  = $dest_node.index,
                below_node_index = '',
                above_node_index = '',
                dest_node_length  = ($dest_node.nodesScope.$modelValue).length;
            var cateObj ={
                source_id : ($source_node.nodeScope.$modelValue) ? ($source_node.nodeScope.$modelValue.id) : '',
                dest_id : ($dest_node.nodesScope.$nodeScope !== null && $dest_node.nodesScope.$nodeScope.$modelValue) ? ($dest_node.nodesScope.$nodeScope.$modelValue.id) : '',
                order_by : {
                  current_node_id  : ($source_node.nodeScope.$modelValue)? ($source_node.nodeScope.$modelValue.id) : '',
                  current_node_sequence : ($source_node.nodeScope.$modelValue)? ($source_node.nodeScope.$modelValue.sequence) : '',
                  parent_id : ($source_node.nodeScope.$modelValue)? ($source_node.nodeScope.$modelValue.parent_id) : '',
                  above_node_id : '',
                  above_node_sequence : '',
                  below_node_id : '',
                  below_node_sequence : '',
                },
            };

            // in case of node drop at first position
            if(current_dest_index === 0){
              cateObj.order_by.below_node_id = ($dest_node.nodesScope.$modelValue && $dest_node.nodesScope.$modelValue[current_dest_index]) ? parseInt($dest_node.nodesScope.$modelValue[current_dest_index].id) : '';
              cateObj.order_by.below_node_sequence = ($dest_node.nodesScope.$modelValue && $dest_node.nodesScope.$modelValue[current_dest_index]) ? parseInt($dest_node.nodesScope.$modelValue[current_dest_index].sequence) : '';
            }else if(current_dest_index === dest_node_length){
              //in case node drop at last position
              above_node_index = current_dest_index -1;
              cateObj.order_by.above_node_id = ($dest_node.nodesScope.$modelValue && $dest_node.nodesScope.$modelValue[above_node_index])? parseInt($dest_node.nodesScope.$modelValue[above_node_index].id) : '';
              cateObj.order_by.above_node_sequence = ($dest_node.nodesScope.$modelValue && $dest_node.nodesScope.$modelValue[above_node_index])? parseInt($dest_node.nodesScope.$modelValue[above_node_index].sequence) : '';
            }else if(current_dest_index>0 && current_dest_index<(dest_node_length -1)){
              //in case node drop in mid
              below_node_index = current_dest_index;
              above_node_index = current_dest_index - 1;
              cateObj.order_by.below_node_id = ($dest_node.nodesScope.$modelValue && $dest_node.nodesScope.$modelValue[below_node_index]) ? parseInt($dest_node.nodesScope.$modelValue[below_node_index].id) : '';
              cateObj.order_by.below_node_sequence = ($dest_node.nodesScope.$modelValue && $dest_node.nodesScope.$modelValue[below_node_index]) ? parseInt($dest_node.nodesScope.$modelValue[below_node_index].sequence) : '';
              cateObj.order_by.above_node_id = ($dest_node.nodesScope.$modelValue && $dest_node.nodesScope.$modelValue[above_node_index])? parseInt($dest_node.nodesScope.$modelValue[above_node_index].id) : '';
              cateObj.order_by.above_node_sequence = ($dest_node.nodesScope.$modelValue && $dest_node.nodesScope.$modelValue[above_node_index])? parseInt($dest_node.nodesScope.$modelValue[above_node_index].sequence) : '';
            }

            //Request to move category 
            $scope.showLoaderTable = false;
            return salesfactoryData.getData(catdragdropurl, 'POST', cateObj)
            .then(function(resp) {
              if(resp && resp.data){
                if(resp.data.status === 'unsuccess'){
                  _createMsgDiv(resp.data.mesg, "error");
                  return !1; 
                }else{
                  _createMsgDiv(resp.data.mesg, resp.data.status);
                  return !0;
                } 
              }else return !1;
            }, function(e) {
               return !1;
            }).finally(function(){
               $scope.showLoaderTable = false;
            });  
         },

         cateOpen: function(that) {
            var curentNodeId = '';
            sub_action_type = "";            

            if (that.$nodeScope !== null) {
               that.$nodeScope.$modelValue.checked = !that.$nodeScope.$modelValue.checked;
               curentNodeId = that.$nodeScope.$modelValue.id;
               //remove active class from all remaining node except current node
               $.map($scope.tree, function(item) {
                  loopUtilLeafNode(item);
               });

               $scope.categoriesopen(that.$nodeScope.$modelValue.id);
               //set status of category
               if(that.$nodeScope.$modelValue.status!== undefined){
                  var st_index = _getIndex($scope.statusdropdown.configs, that.$nodeScope.$modelValue.status, "value");
                  $scope.status = (!isNaN(st_index) && st_index >= 0) ? $scope.statusdropdown.configs[st_index] : $scope.statusdropdown.configs[0];
               }
            }

            function loopUtilLeafNode(node) {
               if (!node) return;

               if (!isNaN(curentNodeId) && node.id != curentNodeId) {
                  node.checked = false;
               }
               //In case node have children
               if (node.children) {
                  node.children.forEach(function(childNode) {
                     loopUtilLeafNode(childNode);
                  })
               }
            }
         }
      };

      /*
       *This section used to expand node using id in case of edit page.
       *private method(getRootNodesScope - > to get parent scope of current node).
       *private method (expandNode -> to expand current node by id).
       *private method (getScopePath -> get path of scope).
       *private method (getScopePathIter -> loop until leanf node and return scope of node).
       */
      var cate_time_out = 0;

      function getRootNodesScope() {
         var parent_cat_index = 0;

         if (typeof top_cat_id != "undefined") {
            parent_cat_index = _getIndex($scope.tree, top_cat_id, "id");
         }

         parent_cat_index = (parent_cat_index != -1) ? parent_cat_index : 0;

         //return parent node scope of tree
         return angular.element(document.getElementById("treeMenuContainer")).scope().$nodesScope.childNodes()[parent_cat_index];
      }

      function expandNode(nodeId) {
         // We need to get the whole path to the node to open all the nodes on the path
         var parentScopes = getScopePath(nodeId);

         if (parentScopes == null) return;

         for (var i = 0; i < parentScopes.length; i++) {
            //for open category if model value is eqal to cat id
            var mid = parentScopes[i].$modelValue.id;

            if (mid !== undefined && mid == cat_id) {
               parentScopes[i].$modelValue.checked = true;

               //set status of category
               if(parentScopes[i].$modelValue.status!== undefined){
                  var st_index = _getIndex($scope.statusdropdown.configs, parentScopes[i].$modelValue.status, "value");
                  $scope.status = (!isNaN(st_index) && st_index >= 0) ? $scope.statusdropdown.configs[st_index] : $scope.statusdropdown.configs[0];
               }
            }

            parentScopes[i].expand();
         }

         $scope.categoriesopen(cat_id);
      };

      function getScopePath(nodeId) {
         return getScopePathIter(nodeId, getRootNodesScope(), []);
      };

      function getScopePathIter(nodeId, scope, parentScopeList) {
         if (!scope) return null;

         var newParentScopeList = parentScopeList.slice();
         newParentScopeList.push(scope);

         if (scope.$modelValue && scope.$modelValue.id === nodeId) return newParentScopeList;

         var foundScopesPath = null;
         // var childNodes = scope.childNodes();
         var childNodes = scope.$element[0].children[1].children;
         //console.log(childNodes);          

         for (var i = 0; foundScopesPath === null && i < childNodes.length; i++) {
            var childNode = angular.element(childNodes[i]).scope();
            foundScopesPath = getScopePathIter(nodeId, childNode, newParentScopeList);
            //foundScopesPath = getScopePathIter(nodeId, childNodes[i], newParentScopeList);
         }

         return foundScopesPath;
      }; 

      $scope.getCategoryPosition = function() {
         var objPage = {
            'cat_id': $scope.id,
            'parent_cat_id': $scope.parent_category.cat_id
         };
         salesfactoryData.getData(checkcatmovepossilbe_url, 'GET', objPage).then(function(res) {

            if (res.type == 'success') {
               $scope.catmoveerror = false;
            }
            if (res.type == "error") {
               $scope.catmoveerror = true;
            }
         }, function(error) {
            try {
               throw new Error("Something went badly wrong!");
            } catch (e) {
               console.log('Log Info ' + e)
            };
         });
      };

      $scope.categoryopen = function() {
         // $scope.showLoaderTable = true;
         // $scope.loadingMore = true;
         $scope.id = cat_id;
         
         var objPage = {
            id: $scope.id
         }
         //get category by id
         salesfactoryData.getData(catediturl, 'GET', objPage).then(function(res1) {
            var res = res1.data;
            console.log(res);
            if (res.status == 1) {              
               $scope.allcategorylist = res.allcategorylist;
               //$scope.status = $scope.statusdropdown.configs[0];
               $scope.edit = true;

            } else {
                //$scope.status = $scope.statusdropdown.configs[1];
            }
            //$scope.comment = res.comment;
            angular.element('#select_' + cat_id).addClass('skyblue');
            
            angular.forEach(res.categorydesces, function(val, key) {
               $scope.category_name[val.lang_id] = val.category_name;
               $scope.cat_description[val.lang_id] = val.cat_description;
               $scope.cat_footer_seo[val.lang_id] = val.cat_footer_seo;
               // console.log(val);
               $scope.meta_title[val.lang_id] = val.meta_title;
               $scope.meta_keyword[val.lang_id] = val.meta_keyword;
               $scope.meta_description[val.lang_id] = val.meta_description;
            });

            if(res.img!== undefined && res.img)
                $scope.display_mode.image = res.img;

            //for category icon 
            if (res.icon_type !== undefined && res.icon_type == "image") {
               $scope.icon_type = res.icon_type;
               $scope.icon_name = res.icon_name;
            } else if (res.icon_type !== undefined && res.icon_type == "bootstrap_code") {
               $scope.icon_type = res.icon_type;
               $scope.icon_name = res.icon_name;
            } 

            $scope.comment = res.comment; 

            //$scope.display_mode = res.dis_mode; 
            /*if(res.dis_mode == '1') {  
              $scope.display_mode = $scope.displaydropdown.configs[0];
            }else if(res.dis_mode == '2'){
              $scope.display_mode = $scope.displaydropdown.configs[1];
            }else{
              $scope.display_mode = $scope.displaydropdown.configs[2];
            } */ 
            /***copy***/
            //$scope.gridOptions.data = [], cat_idInd = []; 
            /*res.catproductlist.map(function(t, k) {
               if (t.cat_id == $scope.id)
                  cat_idInd.push(k);
               delete res.catproductlist[k].cat_id;
            });*/
            $scope.showLoaderTable = false;
            //console.log($scope.showLoaderTable);
            //$scope.gridOptions.data = res.catproductlist;
            $scope.showcatprod = true;
            /*********/

            if(res.is_default=='1'){
              // default category will remove delete button 
              $scope.deleteUrl = false;
              $scope.previewUrl = false;
            }else{
              // other category have url
              $scope.deleteUrl = res.deleteUrl; 
              $scope.previewUrl = res.previewLink; 
            }
            
            $scope.loadingMore = false;

         }, function(error) {
            $scope.showLoaderTable = false;
            _errorHandler();
         }).finally(()=>{
          $scope.showLoaderTable = false;
         });

      };

      //In case of edit page 
      if (cat_id != '') {
         $scope.categoryopen();
      }

      $scope.categoriesopen = function(catid) {
         //$scope.parent_category = $scope.allcategorylist[0];
         //$scope.showLoaderTable = true;
         $scope.loadingMore = true;
         $scope.id = catid;
         $scope.edit = true;
         $scope.parent_cat = false;
         
         angular.element('input[name="_method"]').val('PUT');
         angular.element('#category_id').val(catid);
         angular.element('.listName').removeClass('skyblue');
         angular.element('#select_' + catid).addClass('skyblue');
         var formaction = angular.element('form#sellerCategoryForm').attr('action');
         formaction = action + '/' + catid;
         angular.element('form#sellerCategoryForm').attr('action', formaction);
         var objPage = {
            id: $scope.id
         }
         //console.log($scope.icon_type);
         //defaultVal
         salesfactoryData.getData(cateEditurl, 'GET', objPage).then(function(res) {
            $scope.gridOptions.data = [], cat_idInd = [];
            var rsd = res.data;
            rsd.catproductlist.map(function(t, k) {
               if (t.cat_id == $scope.id)
                  cat_idInd.push(k);
               delete rsd.catproductlist[k].cat_id;
            });
            console.log(rsd.img);
            
            /******for display mode********/
              $scope.display_mode.prd_list = false;
              $scope.display_mode.sub_cat_list = false;
              $scope.display_mode.image = '';   
              if(rsd.dis_mode!== undefined && rsd.dis_mode!==""){
                var dis_mode = rsd.dis_mode.split(',');
                if(dis_mode.indexOf("1")>=0)
                  $scope.display_mode.prd_list = true;
                if(dis_mode.indexOf("2") >=0)
                  $scope.display_mode.sub_cat_list = true;               
              }

              if(rsd.img!== undefined && rsd.img)
                $scope.display_mode.image = rsd.img;   

            /********end******************/


            //for category icon 
            if (rsd.icon_type !== undefined && rsd.icon_type == "image") {
               $scope.icon_type = rsd.icon_type;
               $scope.icon_name = rsd.icon_name;
            } else if (rsd.icon_type !== undefined && rsd.icon_type == "bootstrap_code") {
               $scope.icon_type = rsd.icon_type;
               $scope.icon_name = rsd.icon_name;
            }

            $scope.showLoaderTable = false;
            $scope.gridOptions.data = rsd.catproductlist;

            $scope.gridOptions.totalItems = $scope.gridOptions.data.length;
            $scope.displayTotalNumItems = $scope.gridOptions.data.length;
            $scope.parent_id = rsd.parent_id;

            if (angular.isDefined(rsd.catproductlist) && rsd.catproductlist.length > 0) {
               //$scope.gridOptions.data=res.catproductlist;
               //$scope.displayTotalNumItems = $scope.gridOptions.data.length;
               $scope.no_result_found = false;
            } else {
               $scope.no_result_found = true;
               //$scope.displayTotalNumItems =0;
            }

            if (($scope.gridOptions.data).length > 0) {
               $scope.hidecategorytable = true;
            } else {
               $scope.hidecategorytable = false;
            }



            $scope.loadingMore = false;
            if (rsd.status == 1) {
               $scope.showcatprod = true;
               //$scope.status = $scope.statusdropdown.configs[0];
               $scope.allcategorylist = rsd.allcategorylist;

            } else {
               $scope.showcatprod = true;
               //$scope.status = $scope.statusdropdown.configs[1];
            }

            

            if(rsd.is_default=='1'){
              // default category will remove delete button 
              $scope.deleteUrl = false;
              $scope.previewUrl = false;
            }else{
              // other category have url
              $scope.deleteUrl = rsd.deleteUrl; 
              $scope.previewUrl = rsd.previewLink; 
            }
            $scope.comment = rsd.comment;
            angular.element('#parent_id').val(rsd.parent_id);
            (rsd.categorydesces).map(function(val) {
               $scope.category_name[val.lang_id] = val.category_name;
               $scope.cat_description[val.lang_id] = val.cat_description;
               $scope.cat_footer_seo[val.lang_id] = val.cat_footer_seo;

               $scope.meta_title[val.lang_id] = val.meta_title;
               $scope.meta_keyword[val.lang_id] = val.meta_keyword;
               $scope.meta_description[val.lang_id] = val.meta_description;
               $scope.edit = true;
               $scope.cat_mesg = val.category_name;
            });
         }, function(error) {
            $scope.showLoaderTable = false;
            _errorHandler();
         });
      };

      //Invoke on finish node tree repeat 
      $scope.$on('ngRepeatFinished', function(ngRepeatFinishedEvent) {        
         //in case of edit open category
         //console.log(cat_id);
         if(typeof cat_id!= "undefined" && cat_id){
             var rootScope = getRootNodesScope();
             if (rootScope != undefined) {
                var c = parseInt(cat_id);
                expandNode(c);
            }
         }         
      });

   };

   angular.module('smm-app').controller('sellerCateCtrl', ['$scope', 'salesfactoryData', 'uiGridConstants', '$rootScope', '$timeout',categoryCtrl]);

   //directive to check render complet of data
   angular.module("smm-app").directive("onFinishRender", function($timeout) {
      return {
         link: function(scope, element, attr) {
            if (scope.$last === true) {
               $timeout(function() {
                  scope.$emit(attr.onFinishRender);
               });
            }
         }
      }
   });

   /*Listen for get index 
    *@param : destObj (oject/array)
    *@param : matchEle (string)
    *@param : matchType (string -optional)
    */
   function _getIndex(destObj, matchEle, matchType){
        var index = -1;
        // index = destObj.findIndex(function(item){
        //     if(matchType!== undefined && matchType){
        //         return (item[matchType] == matchEle);
        //     }else{
        //         return (item == matchEle);
        //     }
        // });
        // return index;

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

   //Listen on get clone of filter object
   function getObjectClone(obj) {
      var clone = {};
      angular.forEach(obj, function(item, index) {
         if (angular.isObject(item) === true) {
            clone["f_type"] = item.value;
         } else {
            clone[index] = item;
         }
      });
      return clone;
   };

   //error handler function
   function _errorHandler(errMsg) {
      console.log('hi??');
      try {
         throw new Error("Something went badly wrong!");
      } catch (e) {
         console.log("Opps " + e);
         _createMsgDiv(e, "error");
      };
   };

   //for toastr like message display using bootsrap alert
   function _createMsgDiv(mesg, classType) {
      var _div = document.createElement('div');
      var _class = "alert custom-message";
      //conditional class
      if (classType === "success") {
         _class += " alert-success";
      } else {
         _class += " alert-danger";
      }
      _div.className = _class;

      var text = document.createTextNode(mesg);
      _div.appendChild(text);
      document.body.appendChild(_div);
      jQuery(_div).fadeOut(4000, function() {
         jQuery(this).remove();
      });
   };

})(window.angular);

//handles clicks and keydowns on the link
var sap = {ui:{keycodes:{SPACE:32, ENTER:13 }}};

function navigateLink(evt, x) {
    if (evt.type=="click" ||
        evt.keyCode == sap.ui.keycodes.SPACE ||
        evt.keyCode == sap.ui.keycodes.ENTER) {
        var ref = evt.target != null ? evt.target : evt.srcElement;
        if (ref && (x!==undefined && x)){
          window.location = x;
        } 
    }
};