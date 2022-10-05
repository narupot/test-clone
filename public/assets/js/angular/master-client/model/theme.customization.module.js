/*
*@desc : This module used to theme customization 
*@created date : 18/09/2018
*@author : SMOOTHGRAPH CONNECT PVT LTD
*/

(function(angular, undefined){
	
	// 'ui.router'
	angular.module('sabinaAdminApp', ['colorpicker.module', 'jsonservice'], function($interpolateProvider){
		$interpolateProvider.startSymbol('<%');
		$interpolateProvider.endSymbol('%>');
		console.log('init.........');
	}).config(['$httpProvider', function ($httpProvider) {
		$httpProvider.interceptors.push('httpLoaderInterceptor');
		console.log('config.........');
		/********** route config ***********/
		//, '$stateProvider', '$urlRouterProvider', '$locationProvider', 
		// $stateProvider, $urlRouterProvider, $locationProvider
		//remove # from url 
		// $locationProvider.html5Mode({
		//     enabled: true,
		//     requireBase: true,
		//     rewriteLinks: false
		// });		
		/********** end ************/
	}]).filter('startFrom', function() {
	    return function(input, start) {
	        start = +start; //parse to int
	        return input.slice(start);
		}
	});

})(angular);
