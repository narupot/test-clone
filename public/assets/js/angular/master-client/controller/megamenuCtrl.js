/****
*@desc : Megamenu controller
*@created date : 18-apr-2019
*@ahthor : Smoothgraph Connect Pvt Ltd.
*****/

(function(){
    "use strict";

    function megamenuController($scope, salesfactoryData, $timeout, $rootScope){
        $scope.menu = {
            menu_design_selection : " ",
            name : "",
        };

        $scope.menuData = {
            left_menu : globalTree, 
            right_menu : [],
            category_tree : [],
            pages : [],
            // menu_design_json : menu_design_json || [],
            default_lang_id : default_lang_id,
        };
        $rootScope.publishStatus = "0";
        /*
        *@desc : get category & page data 
        *@param : type - 1 for category & 4 for page
        */
        var getCategoryAndPages = (query)=>{
        	salesfactoryData.getData(CATEGORY_PAGE_DATA_URL, 'GET', query)
        	.then((response)=>{        		
        		if(response && response.data){
        			let result = response.data;
        			if(result.status == 'success'){
        				switch(query.type){
        					case 1:
        						$scope.menuData.category_tree = result.list;
        						break;
        					case 4:
        						$scope.menuData.pages = result.list;
        						break;
        				};
        			}        			        		
        		}
        	}, (err)=>{
        		//
        	});        	
        };

        /*
        *@desc : This function used to set previous data in case of edit page (manu edit)
        */
        var setPreviousData = function(){
            //set right menu data 
            $scope.menuData['right_menu'] = angular.copy(menu_json['menu_json']) || []; 
            //set menu design selected
            // $scope.menu['menu_design_selection'] = menu_json['menu_design']['menu_design_id'].toString();
            //set menu name 
            $scope.menu['name'] = menu_json['menu_name'];
            //set staust 
            $rootScope.publishStatus = menu_json['status'].toString();
        };


        var init = (function(){
        	getCategoryAndPages({'type' : 1});
        	getCategoryAndPages({'type' : 4});
            //in case of menu edit
            try{
                if(pagetype === 'edit_menu'){
                    /*
                    *@desc : In case of edit add temp_id in node 
                    */
                    (function addTempId(){
                        function loopUtilLeafNode(rows){
                            if(!rows) return;
                            //for root nodes
                            if(rows.nodes){
                                angular.forEach(rows.nodes, chNode=>{
                                    chNode['temp_id'] = Math.floor(Math.random(1111,9999) * 20000);
                                    loopUtilLeafNode(chNode);
                                });
                            }
                        };

                        angular.forEach(menu_json['menu_json'], rows=>{
                            rows['temp_id'] = Math.floor(Math.random(1111,9999) * 20000);
                            loopUtilLeafNode(rows);
                        });
                    })();
                    setPreviousData();
                } 
            }catch(err){
                console.log;                
            }
        })();

        $scope.menuFunction = {
			getLangTitle : (node)=>{
				let txt = "";
                if(node.lang){    
    				node.lang.map((o)=>{
    					if(typeof default_lang_id!="undefined" && o.id == default_lang_id) txt = o.input;
    				});	
                }			
				return txt;
			},		
        };


        /*
        *@desc :Mega menu tree config
        */

        $scope.megaMenuTreeOption = {
            // dropped : function(event){
            // 	//
            // },
            beforeDrop : function(event){
            	//add one unique id with random number
            	try{
            		event.source.cloneModel['temp_id'] = Math.floor(Math.random(1111,9999) * 20000);
            	}catch(er){console.log}            	
            },
            // dragMove: function(event){}
            // dragMove: function(event){}
        };

        /****** collapsed & expand *****/
        $scope.collapseAll = function () {
	       $scope.$broadcast('angular-ui-tree:expand-all');
	    };

	    $scope.expandAll = function () {
           $scope.$broadcast('angular-ui-tree:collapse-all');	       
	    };

	    /*
	    *@desc : Listen to select parent id in case of category node
	    *@param : currentNode {current active node}	    
	    *@param : nodeType {current active node type} ->category | page    
	    */
	    let activeNode = "",
	    	activeNodeType = "";
	   	$scope.selectNodeModal = function(currentNode, nodeType){
			activeNode = currentNode;
			activeNodeType = nodeType;
	   	};

		/*
		*@desc : get category id & add category || pages in active node
		**/		
		$scope.getParentId = function($event, node){
			$event.preventDefault();
			$scope.$evalAsync(()=>{
				if(activeNodeType && activeNodeType === 'category'){
					activeNode['category_id'] = node.id;
					//hide modal 
					angular.element(document.getElementById('categoryList')).modal('hide');
				}else if(activeNodeType && activeNodeType === 'page'){
					activeNode['page_id'] = node.id;	
					//hide modal 
					angular.element(document.getElementById('pagesList')).modal('hide');
				} 
			});
		};

        //brodcast from header menu directive
        $scope.$on('savemenu', function(evt, data) {
            $scope.saveMenu(data, $scope.megamenuForm);
        });

	    /*
	    *@desc : Listen to save menu 
	    */
	    $scope.saveMenu = ($evt, megamenuForm)=>{
	    	$evt.preventDefault();
	    	if(!$scope.menuData.right_menu.length){
	    		swal('Oops.!', 'Please add menu', 'warning');
	    		return;
	    	} 
	    	            
	    	//form validation for required field	link-title   link-url
            let preg = 'page-title-'+LANG_CODE,
                creg = 'category-title-'+LANG_CODE,
                lreg = 'link-title-'+LANG_CODE,
                ptRegx = new RegExp(preg, 'i'),
                ctRegx = new RegExp(creg, 'i'),
                psRegx = /page-selection/i,
                csRegx = /category-selection/i,
                ltRegx = new RegExp(lreg, 'i'),
                //change for attr_input_link
                atu =  'link-url-'+LANG_CODE,
                luRegx = new RegExp(atu, 'i'); 
           
	    	let errorHtml="";
            // console.log(megamenuForm);
	    	if(megamenuForm.$invalid){
	    		angular.forEach(megamenuForm, (item, keys)=>{
	    			//in case of page title
	    			if(ptRegx.test(keys) && item.$error && item.$error.required && item.$invalid)
	    				errorHtml+='<div class="alert alert-danger" role="alert"><span class="far fa-exclamation-circle text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>'+formFieldName[0].mesg+'</div>';
	    			//in case of category title
	    			else if(ctRegx.test(keys) && item.$error && item.$error.required && item.$invalid)
	    				errorHtml+='<div class="alert alert-danger" role="alert"><span class="far fa-exclamation-circle text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>'+formFieldName[1].mesg+'</div>';
	    			//in case of page selection
	    			else if(psRegx.test(keys) && item.$error && item.$error.required && item.$invalid)
	    				errorHtml+='<div class="alert alert-danger" role="alert"><span class="far fa-exclamation-circle text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>'+formFieldName[2].mesg+'</div>';
	    			//in case of category selection
	    			else if(csRegx.test(keys) && item.$error && item.$error.required && item.$invalid)
	    				errorHtml+='<div class="alert alert-danger" role="alert"><span class="far fa-exclamation-circle text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>'+formFieldName[3].mesg+'</div>';	    			
	    			//in case of menu name
	    			else if(keys =='menu-name' && item.$error && item.$error.required && item.$invalid)
	    				errorHtml+='<div class="alert alert-danger" role="alert"><span class="far fa-exclamation-circle text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>'+formFieldName[4].mesg+'</div>';
                    //in case of menu name
                    else if(ltRegx.test(keys) && item.$error && item.$error.required && item.$invalid)
                        errorHtml+='<div class="alert alert-danger" role="alert"><span class="far fa-exclamation-circle text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>'+formFieldName[6].mesg+'</div>';
                    //in case of menu name
                    else if(luRegx.test(keys) && item.$error && item.$error.required && item.$invalid)
                        errorHtml+='<div class="alert alert-danger" role="alert"><span class="far fa-exclamation-circle text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>'+formFieldName[7].mesg+'</div>';
	    		});
	    	}

	    	//in case of design selection	    	
	    	// if($scope.menu.menu_design_selection == " ")
	    	// 	errorHtml+='<div class="alert alert-danger" role="alert"><span class="far fa-exclamation-circle text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>'+formFieldName[5].mesg+'</div>';	    			
	    	
	    	if(errorHtml){
	    		swal('Oops..!', errorHtml, 'error');
	    		return;
	    	}
            showHideLoader('showLoader');
            //save data            
	    	let md = {
                    "menu_json" : angular.copy($scope.menuData.right_menu),
                    "menu_name" :$scope.menu['name'],
                    // "menu_design" : $scope.menuData.menu_design_json.find((o)=> o.menu_design_id == $scope.menu.menu_design_selection),
                    "status" : $rootScope.publishStatus,
                };
                md = angular.toJson(md);
            // console.log(md); return;
	    	salesfactoryData.getData(SAVE_URL, 'POST', md)
	    	.then((response)=>{
                try{
                    let resp = response.data;
                    //actionUrl
                    swal({
                        title : 'Done',
                        text : resp.mesg,
                        type : resp.status,
                        timer : 1500,
                        showConfirmButton : false,
                    });

                    if(resp.status == 'success'){
                        $timeout(()=>{
                            window.location.href = resp.actionUrl;
                        }, 1500);
                    }
                }catch(err){console.log};
	    	}, (err)=>{
	    		swal('Opps..!', 'something went wrong', 'error');
	    	}).finally(()=>{
                showHideLoader('hideLoader');
            });	    
	    };
    };

    function nodeTreeDirective(){
        return {
            template : '<node ng-repeat="node in cattree"></node>',
            replace : true,
            restrict : 'E',
            scope : {
                cattree : '=children',
            },
        };
    };

    function nodeDirective($compile){
        function nodeDirLinkFunction(scope, element){
            /*
            * Here we are checking that if current node has children then compiling/rendering children.
            * */
            if (scope.node && scope.node.children && scope.node.children.length > 0) {
                // scope.node.childrenVisibility = true;
                let childNode = $compile('<ul class="tree listName" ng-if="!node.childrenVisibility"><node-tree children="node.children"></node-tree></ul>')(scope);
                element.append(childNode);
            } else scope.node.childrenVisibility = false;
        };

        function nodeDirController($scope){
            // This function is for just toggle the visibility of children
            $scope.toggleVisibility = function(node) {
                if (node.children) node.childrenVisibility = !node.childrenVisibility;                
            };

            // Here We are marking check/un-check all the nodes.
            $scope.checkNode = function(node, $event) {              
                $event.stopImmediatePropagation();
                node.checked = !node.checked;
                function checkChildren(c) {             
                  angular.forEach(c.children, function(c) {
                    c.checked = node.checked;
                    checkChildren(c);
                  });
                }
                checkChildren(node);
            };
        };

        return {
            restrict : 'E',
            replace : true,
            template : '<li class="tree-list"><span class="listName" ng-click="toggleVisibility(node)" ng-if="node.children.length"><i class="fas fa-plus-square" ng-if="node.childrenVisibility && node.children.length"></i><i class="fas fa-minus-square" ng-if="(!node.childrenVisibility && node.children.length)"></i></span><i class="fas fa-folder"></i><span ng-click="getParentId($event, node)" ng-class="{\'category-node-active\' : node.checked }">{{ node.name }}</span></li>',
            link : nodeDirLinkFunction,
            controller : nodeDirController,
        };
    };

    /*
	*@desc : This directive used to handel icon picker
	*/
	var iconPickerOptions = {
    	//hide iconpicker automatically when a value is picked. it is ignored if mustAccept is not false and the accept button is visible
    	hideOnSelect: true,
    	templates : {
    		iconpickerItem: '<a role="button" href="javascript:void(0)" class="iconpicker-item"><i></i></a>'
    	},
    };

    function iconPickerDirective(){
    	return {
    		restrict : 'A',
    		link : function (scope, element, attrs){
    			//enable color picker    			
    			$(element).iconpicker(iconPickerOptions);
    			//Listen to icon select 
    			$(element).on('iconpickerSelected', function(event){
    				try{
    					scope.$evalAsync(()=>{
    						scope.$parent.$nodeScope.$modelValue.atr_menu_icon = event.iconpickerValue;
    						// scope.$emit("iconpickerSelected", {"prop" : scope.$parent.prop, "icon_class" : event.iconpickerValue});	
    					});    					
    				}catch(err){
    					console.log;
    				}
    			});
    		},
    	}
    };

    function menuRightDirective($rootScope, $timeout){
        return {
            restrict : 'A',
            link : function(scope, element){
                if(pagetype == 'edit_menu' || pagetype == 'create_menu') $(element).removeClass('d-none');
                
                $(element).find('.btn-save').bind('click', function($evt){
                    $rootScope.$broadcast('savemenu', $evt);
                });

                /*
                *@desc : Listen to change publish status by user
                */
                $(element).find('.publish input[type=\'checkbox\']').bind('change', function(evt){
                    $rootScope.publishStatus = ($(this).prop('checked')) ? "1" : "0";
                });

                //update status          
                $timeout(()=>{
                    $(element).find('.publish input[type=\'checkbox\']').prop('checked', (($rootScope.publishStatus == "1")? true : false));                   
                });
            },
        };
    };

    /**controller & directive init***/
    angular.module("smm-app")
        .controller('megamenuCtrl', ['$scope', 'salesfactoryData', '$timeout', '$rootScope', megamenuController])
        .directive('nodeTree', [nodeTreeDirective])
        .directive('node', ['$compile', nodeDirective])
        .directive('iconPicker', [iconPickerDirective])
        .directive('menuRightDir', ['$rootScope', '$timeout', menuRightDirective]);
  // angular.module('megaMenuApp').directive('selectpickerConfig',_selectpickerHandler);

})(window.angular);