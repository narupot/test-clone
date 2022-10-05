(function(){
	/******product category node tree directive handler****/
	function catetreeDir(treeNodeService,$timeout){
		return {
	        restrict:'E',
	       	templateUrl : nodehtml, 
	        scope: {
				//dataset:'=',
				tree:'=dataset',
				selected:'='
	        },
	        controller:function($scope) {
				$scope.selected = $scope.selected || [];
				//$scope.tree = angular.copy($scope.dataset);
	            //$scope.tree = angular.copy($scope.tree);
	            $scope.expandNode = function(n,$event) {
	                $event.stopPropagation();
	                console.log(n);
	                n.toggle();
	            };
	          	$scope.itemSelect = function(item) {
	          		item.checked = !item.checked;
					// var rootVal = !item.checked;
					// treeNodeService.selectChildren(item,rootVal)
					// treeNodeService.findParent($scope.tree[0],null,item,selectParent);
					// var s = _.compact(treeNodeService.getAllChildren($scope.tree[0],[]).map(function(c){ return c.checked && !c.children;}));
					// $scope.numSelected = s.length;
				};   
	        	function selectParent(parent){
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
	/*****node checkbox action directive*****/
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
	/*******Node tree services for get,set ,check,find ect.******/
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
		    //if(!node) return [];
		    //if this node is selected add to array
		    if(node.checked) {
		        arr.push(node);
		        // return arr;
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
			//console.log(node);
		};	
        return {
	       getAllChildren:getAllChildren,
	       getSelected:getSelected,
	       selectChildren:selectChildren,
	       findParent:findParent,
	       setSelected:setSelected
	    };
   	};
   	/*****This directive used for increment and decrement like spiner
   	*property u can set 
   	*min,max,step,and editable,where editable used for editable
   	***/
   	function counterHendlar(){
   		return{
			restrict: 'E',
			scope: { value: '=value' },
			template :'<span class="decrease glyphicon glyphicon-minus" data-ng-click="minus()"></span>'+
					  '<input type="number" data-ng-model="value" data-ng-change="changed()" data-ng-readonly="readonly">'+
					  '<span class="increase icon-add5" data-ng-click="plus()"></span>',
			link:function(scope,element,attributes){
				if(_.isUndefined(scope.value))
					throw "Missing the value attribute on the counter directive.";

				var min = _.isUndefined(attributes.min) ? null : parseInt(attributes.min);
				var max = _.isUndefined(attributes.max) ? null : parseInt(attributes.max);
				var step = _.isUndefined(attributes.step) ? 1 : parseInt(attributes.step);
				element.addClass('counter-container');

				// If the 'editable' attribute is set, we will make the field editable.
				scope.readonly = _.isUndefined(attributes.editable) ? true : false;
				/*** Sets the value as an integer.*/
				function setValue( val ) {
					scope.value = parseInt( val );
				}
				// Set the value initially, as an integer.
				setValue( scope.value );
				scope.minus = function() {
					if ( min && (scope.value <= min || scope.value - step <= min) || min === 0 && scope.value < 1 ) {
					    setValue( min );
					    return false;
					}
					setValue( scope.value - step );
				};
				scope.plus = function() {
					if ( max && (scope.value >= max || scope.value + step >= max) ) {
					    setValue( max );
					    return false;
					}
					setValue( scope.value + step );
				};
				/**
				* This is only triggered when the field is manually edited by the user.
				* Where we can perform some validation and make sure that they enter the
				* correct values from within the restrictions.
				*/
				scope.changed = function() {
					// If the user decides to devare the number, we will set it to 0.
					if ( !scope.value ) setValue( 0 );
					// Check if what's typed is numeric or if it has any varters.
					if ( /[0-9]/.test(scope.value) ) {
					    setValue( scope.value );
					}else {
					    setValue( scope.min );
					}
					// If a minimum is set, var's make sure we're within the limit.
					if ( min && (scope.value <= min || scope.value - step <= min) ) {
					    setValue( min );
					    return false;
					}
					// If a maximum is set, var's make sure we're within the limit.
					if ( max && (scope.value >= max || scope.value + step >= max) ) {
					    setValue( max );
					    return false;
					}
					// Re-set the value as an integer.
					setValue( scope.value );
				};
			}
		};
   	};

   	//this directive used for number input with format 
   	String.prototype.splice = function(idx, rem, s) {
    	return (this.slice(0, idx) + s + this.slice(idx + Math.abs(rem)));
	};

   	function numberInputHendlar(){
   		return {
	        restrict: 'E',
	        scope: {
	            field  : '=field',
	            symbol : '<?',
	            nameAttr :'<?',
	            requiredAttr : '<?',
	        },
	        template:'<div class="input-group">'+
        			 '<span class="input-group-addon" ng-if="symbol">'+currency_symbol+'</span>'+
       			 	 '<input type="text" ng-model="field" name="<%nameAttr%>" ng-required="<%requiredAttr%>" />'+
      				 '</div>',
	        link: function(scope, element, attrs) {
	        	scope.symbol = (attrs.symbol!==undefined) ? false : true;
	        	scope.nameAttr = (attrs.nameAttr!==undefined) ? attrs.nameAttr : "";
	        	scope.requiredAttr = (attrs.requiredAttr!==undefined && attrs.requiredAttr == true) ? attrs.requiredAttr : false;

	            $(element).bind('keyup', function(e) {
	                var input = element.find('input');
	                var inputVal = input.val();

	                //clearing left side zeros
	                while (scope.field.charAt(0) == '0') {
	                    scope.field = scope.field.substr(1);
	                }

	                scope.field = scope.field.replace(/[^\d.\',']/g, '');

	                var point = scope.field.indexOf(".");
	                if (point >= 0) {
	                    scope.field = scope.field.slice(0, point + 3);
	                }

	                var decimalSplit = scope.field.split(".");
	                var intPart = decimalSplit[0];
	                var decPart = decimalSplit[1];

	                intPart = intPart.replace(/[^\d]/g, '');
	                // if (intPart.length > 3) {
	                //     var intDiv = Math.floor(intPart.length / 3);
	                //     while (intDiv > 0) {
	                //         var lastComma = intPart.indexOf(",");
	                //         if (lastComma < 0) {
	                //             lastComma = intPart.length;
	                //         }

	                //         if (lastComma - 3 > 0) {
	                //             intPart = intPart.splice(lastComma - 3, 0, ",");
	                //         }
	                //         intDiv--;
	                //     }
	                // }

	                if (decPart === undefined) {
	                    decPart = "";
	                }
	                else {
	                    decPart = "." + decPart;
	                }
	                var res = intPart + decPart;

	                scope.$apply(function() {scope.field = res});

	            });
	        }
	    };
   	};


	angular.module("smm-app").service('treeNodeService',treeNodeService);
	angular.module("smm-app").directive('indeterminateCheckbox',nodeCheckHendler);
	angular.module("smm-app").directive('prodCateTree',catetreeDir);
	angular.module("smm-app").directive('counterDir',counterHendlar);
	angular.module("smm-app").directive('numberInput',numberInputHendlar);
}).call(this);