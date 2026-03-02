/*
*This directived used for create tree like structure
*Author :Smoothgraph Connect PVT. Ltd.
*directive name : <tree-list-dir dataset="data" selected="model"> </tree-list-dir>
*html url : nodehtml (template url)
*Dependency :  underscore.js /angular.ui.tree.min.js
*/

(function(angular){

	//config
	var moduleName = "tree.structure.Dir";
	var module;
	try{
		module =  angulae.module(moduleName);
	}catch(e){
		//in case module does't exits then create one
		module = angular.module(moduleName, []);
	}

	module
		.service('treeNodeService',treeNodeService)
		.directive('indeterminateCheckbox',nodeCheckHendler)
		.directive('treeListDir',catetreeDir)
		.provider('treeTemplate', treeTemplateProvider)
		.run(['$templateCache',dirTreeTemplateInstaller]);


   	/*
   	*product category node tree directive handler
   	*@param : treeNodeService (services)
   	@param {
			scope : tree (mandotry)	
   		}
   	*/
	function catetreeDir(treeNodeService,$timeout,treeTemplate, $sce){
		return {
	        restrict:'E',
	       	templateUrl : (typeof nodehtml!= "undefined") ? $sce.trustAsResourceUrl(nodehtml) : treeTemplate.getPath(), 
	        scope: {
				//dataset:'=',
				tree:'=dataset',
				selected:'='
	        },
	        controller:function($scope) {
				$scope.selected = $scope.selected || [];
				//$scope.tree = angular.copy($scope.dataset);
	           // $scope.tree = angular.copy($scope.tree);
	            $scope.expandNode = function(n,$event) {
	                $event.stopPropagation();
	                n.toggle();
	            };
	          	$scope.itemSelect = function(item) {
					var rootVal = !item.checked;
					treeNodeService.selectChildren(item,rootVal)
					treeNodeService.findParent($scope.tree[0],null,item,selectParent);
					var s = _.compact(treeNodeService.getAllChildren($scope.tree[0],[]).map(function(c){ return c.checked && !c.children;}));
					$scope.numSelected = s.length;
				};   
	        	function selectParent(parent) {
					var chNode = treeNodeService.getAllChildren(parent,[]);
					if(!chNode) return;
					chNode = chNode.slice(1).map(function(c){ return c.checked;});
					parent.checked =  chNode.length === _.compact(chNode).length;
					treeNodeService.findParent($scope.tree[0],null,parent,selectParent)
				};       
	            $scope.nodeStatus = function(node) {
	                var flattenedTree = getAllChildren(node,[]);
	                flattenedTree = flattenedTree.map(function(n){ return n.checked });
	    
	                return flattenedTree.length === _.compact(flattenedTree);
	            };
	 
	        },
	        link:function(scope,el,attr) {
	        	scope.$watch('tree',function(nv,ov) {
	         		//if(nv) return;
					if(nv && !ov) { scope.$apply();}
					//UPDATE SELECTED IDs FOR QUERY
					//get the root node
					var rootNode = nv[0];
					//get all elements where checked == true
					// var a = _.flatten(_.map(nv, function(node){treeNodeService.getSelected(node, [])}));
					// // var a = treeNodeService.getSelected(rootNode, []);
					// //get the ids of each element
					// a = _.pluck(a,'id');
					// scope.selected = a;
					//get all elements where checked == true					
		            var x = _.map(nv,function(node){
		              return treeNodeService.getSelected(node, []);
		            });
		            x = _.flatten(x);
		            x = _.pluck(x,'id');
		            scope.selected = x;
				},true);
	        }
	    };
	};
	
	/*
	*node checkbox action directive
	*@param : treeNodeService(services)
	*/
	function nodeCheckHendler(treeNodeService){
		return {
	        restrict:'A',
	        scope: {
	          node:'='  
	        },
	        link: function(scope, element, attr) {
	                scope.$watch('node',function(nv) {
		                var flattenedTree = treeNodeService.getAllChildren(scope.node,[]);
		                flattenedTree = flattenedTree.map(function(n){ return n.checked });
		                var initalLength = flattenedTree.length;
		                var compactedTree = _.compact(flattenedTree);
		                var r = compactedTree.length > 0 && compactedTree.length < flattenedTree.length;
		                element.prop('indeterminate', r);
	            },true);
	        }
    	};
	};

		/*
	*Node tree services for get,set ,check,find ect.
	*/
	function treeNodeService(){
		function getAllChildren(node,arr) {
		   if(!node) return;
		    arr.push(node);

		    if(node.children) {
		        //if the node has children call getSelected for each and concat to array
		        node.children.forEach(function(childNode) {
		            arr = arr.concat(getAllChildren(childNode,[]))  
		        })
		    }
		    return arr;   
		};    
		function findParent(node,parent,targetNode,cb) {
		    if(_.isEqual(node,targetNode)) {
		        cb(parent);
		        return;
		    }
		    
		    if(node.children) {
		        node.children.forEach(function(item){
		            findParent(item,node,targetNode,cb);
		        });
		    }
		};           
		function getSelected(node,arr) {
		    if(!node) return [];
		    //if this node is selected add to array
		    if(node.checked) {
		        arr.push(node);
		    }
		   
		    if(node.checked && node.children && (node.children.length > 0)) {
		        //if the node has children call getSelected for each and concat to array
		        node.children.forEach(function(childNode) {
		            arr = arr.concat(getSelected(childNode,[]))  
		        })
			}else if(node.children && (node.children.length > 0)){
				//if the node has children call getSelected for each and concat to array
		        node.children.forEach(function(childNode) {
		            arr = arr.concat(getSelected(childNode,[]))  
		        })
			}

		    return arr;
		};
		function selectChildren(chNode,val) {
		    //set as selected
		    chNode.checked = val;
		    if(chNode.children) {
		        //recursve to set all children as selected
		        chNode.children.forEach(function(el) {
		            selectChildren(el,val);  
		        })
		    }
		};
		function setSelected(node,arr){
		};	
        return {
	       getAllChildren:getAllChildren,
	       getSelected:getSelected,
	       selectChildren:selectChildren,
	       findParent:findParent,
	       setSelected:setSelected
	    };
   	};

	//template of directive

	function dirTreeTemplateInstaller($templateCache) {
        $templateCache.put('tree.structure.Dir.template', '<div class="root-box tree-menu" ui-tree="" id="productCateTree" data-drag-enabled="false"> <ol id="productCateTree" ui-tree-nodes="" class="tree" data-ng-model="tree"> <li ng-repeat="item in tree" ui-tree-node ng-include="\'items_renderer.html\'"></li></ol> <script type="text/ng-template" id="items_renderer.html"> <div class="tree-node tree-node-content" ui-tree-handle ng-click="itemSelect(item,this)" ng-class="{nodeActive:item.checked,matched:item.match}"> <a class="btn-xs" data-ng-if="item.children && item.children.length > 0" data-ng-click="expandNode(this,$event)"> <span class="expandCollapse" data-ng-class="{\'glyphicon-plus plus\': collapsed,\'glyphicon-minus minus\': !collapsed}"></span> </a> <input style="position: relative; visibility: visible !important;" type="checkbox" ng-checked="item.checked" indeterminate-checkbox node="item"> <span class="listName"><%item.name%> (<%item.total_products%>)</span> </div> <ol ui-tree-nodes="" ng-model="item.children" ng-class="{hidden: collapsed}"> <li ng-repeat="item in item.children" ui-tree-node ng-include="\'items_renderer.html\'">  </li> </ol> </script>');
    }

	/**
     * This provider allows global configuration of the template path used by the tree.structure.Dir directive.
     */
    function treeTemplateProvider() {
        var templatePath = 'tree.structure.Dir.template';
        this.setPath = function(path) {
            templatePath = path;
        };

        this.$get = function() {
            return {
                getPath: function() {
                    return templatePath;
                }
            };
        };
    }

})(window.angular);