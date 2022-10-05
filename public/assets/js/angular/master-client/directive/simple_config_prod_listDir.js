(function(){
	/*
	*This directive used to handel simple and config product listing in case of bundel product
	*Author : Smmoothgraph Connect pvt ltd.
	*/
	//simpleProductList directive
	function simpleProductList($compile,$rootScope){
		return {
			restrict: 'E',
			scope :{
				productData : '=',
			},
			templateUrl : simple_bundel_prd_HtmlUrl,
			link: function (scope, iElement, iAttrs) {
				scope.removeSimpleProd =function($event,item,index){
					$event.stopPropagation();
					var ind = _.indexOf($rootScope.prd_data.simple_product_id,item.id);
					if(ind!= -1){
						$rootScope.prd_data.simple_product_id.splice(ind,1);
						scope.productData.splice(index,1);
					}					
				};

				scope.sortingOrderHandler = function(prdFlag, item){
					if(prdFlag == undefined) return;
					sortingData(prdFlag, item, $rootScope);				
				};
			}
		};
	};
	//bundel product directive function
	function configProductList($rootScope){
		return{
			restrict : 'E',
			scope :{
				productData:'=',
			},
			templateUrl:config_bundel_prd_HtmlUrl,
			link: function(scope,iElement,iAttrs){
				//
			},
			controller : function ($scope) {
				$scope.product_detail_url = product_detail_url;
				$scope.removeConfigProd=function($event,prd_val,prd_key){
					var main_prd_id = _.map(prd_val.main_product_detail,'id');
					if(!_.isUndefined(main_prd_id) && main_prd_id.length>0){
						for(i in main_prd_id){
							var ind = _.indexOf($rootScope.prd_data.config_product_id,main_prd_id[i]);
							$rootScope.prd_data.config_product_id.splice(ind,1);
						}
					}
					$scope.productData.splice(prd_key,1);
					$event.stopPropagation();
				}

				$scope.sortingOrderHandler = function(prdFlag, item){
					if(prdFlag == undefined) return;
					sortingData(prdFlag, item, $rootScope);				
				};
			},	
		};
	};

	function sortingData(prd_flag, item, $rootScope){
		var data = $rootScope.prd_data.simple_config_prd_order[prd_flag];
		var t = {
			"id" : parseInt(item.id),
			"sorting_order" : parseInt(item.prd_sorting_order),
		};
	
		var order_val = item.prd_sorting_order;
		//in case sorting order value is empty or null then check its exist
		if(order_val == ""){
			var indx = _.findIndex(data, {"id" : item.id});

			if(indx>=0){				
				data.splice(indx, 1);
			}
		}else{
			var indxe = _.findIndex(data, {"id": item.id});
			
			if(indxe == -1){
				data.push(t);
			}else{
				data[indxe].sorting_order = parseInt(item.prd_sorting_order);							
			}
		}		
	};

	angular.module('sabinaAdminApp').directive('simpleProductDir',simpleProductList);
	angular.module('sabinaAdminApp').directive('configProductDir',configProductList);
})(window.angular);