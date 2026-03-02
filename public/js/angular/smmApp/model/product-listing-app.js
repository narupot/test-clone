/**
*This is product listing app used for display product listing in front(user view)
*Created at : 11/12/2017
*Author: Smoothgraph Connect Pvt. Ltd
*App Name : smm-app
*File Name : frontPrdListApp.js
**/
(function(){
	'use strict';
	//@ct.ui.router.extras : for ui routing
	//@jsonseivice : factory for get data from server(REST)
	//@directives.dirPagination for pagination 'ct.ui.router.extras',
	angular.module('smm-app',['jsonseivice', 'ct.ui.router.extras', 'paginationDir'], function($interpolateProvider){
		$interpolateProvider.startSymbol('<%');
		$interpolateProvider.endSymbol('%>');
	});
	//config blok used for config al route related functionality
	angular.module('smm-app').config(['$stateProvider','$urlRouterProvider', '$locationProvider','$stickyStateProvider','$provide', '$compileProvider',function($stateProvider,$urlRouterProvider, $locationProvider,$stickyStateProvider,$provide, $compileProvider) {
		  	$stickyStateProvider.enableDebug(false);		  	
			$locationProvider.html5Mode({
			    enabled: true,
			    requireBase: true,
			    rewriteLinks: false
			});			
		   
			$urlRouterProvider.rule(function ($injector, $location) {
		       //what this function returns will be set as the $location.url
		        var path = $location.path(), normalized = path.toLowerCase();
		        if (path != normalized) {
		            //instead of returning a new url string, I'll just change the $location.path directly so I don't have to worry about constructing a new url string and so a new state change is not triggered
		            $location.replace().path(normalized);
		        }
		        // because we've returned nothing, no state change occurs
		    });
		    
			//page filter_by order_by order				   
		 	$stateProvider.state('query_state', { 
				sticky: true,
				url :  decodeURIComponent(browser_url) + '?{page:(?:/[^/]+)?} & {filter_by:(?:/[^/]+)?} & {cid:(?:/[^/]+)?} & {order_by:(?:/[^/]+)?} & {order:(?:/[^/]+)?} & {search:(?:/[^/]+)?} & {price:(?:/[^/]+)?}',
		        params: {
					page: {value: null,squash: true,dynamic: true},
					filter_by: {array: true,value: [],squash: true,dynamic: true},
					cid: {array: true,value: [],squash: true,dynamic: true},
					order_by : {value: null,squash: true,dynamic: true},
					order : {value: null,squash: true,dynamic: true},
					search : {value: null,squash: true,dynamic: true},	
					price : {value: null,squash: true,dynamic: true},				
				},
				reloadOnSearch: true,
				resolve: { 
					resolvedData: function($stateParams){
						//return $stateParams
					}					
				},
			});
	}]);
	//This filter used for access cross domain url in iframe
	angular.module('smm-app').filter('trustAsResourceUrl',function($sce) {
		return $sce.trustAsResourceUrl(val);
	});
	//This filter used for add dynamic JSON html in angular template
	angular.module('smm-app').filter('unsafe', function($sce) {
		return $sce.trustAsHtml;
	});
})(window.angular);