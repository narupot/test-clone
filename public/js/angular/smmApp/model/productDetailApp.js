(function(){
	/****
	*This app used for product detail page.
	*Created date 10/11/2017
	***/
	//'LCS.loadingBar', 'ui.bootstrap'
	angular.module('smm-app',['jsonseivice','ngDroplet'],function($interpolateProvider){
		$interpolateProvider.startSymbol('<%');
		$interpolateProvider.endSymbol('%>');
	})
	.config([function ($locationProvider,cfpLoadingBarProvider) {
		// $locationProvider.html5Mode(true).hashPrefix('!');
		//cfpLoadingBarProvider.includeSpinner = true;
		//cfpLoadingBarProvider.parentSelector  ='body';
	}])
	.filter('trustAsResourceUrl',['$sce',function($sce) {return function(val) {
		return $sce.trustAsResourceUrl(val);};
	}])
	.filter('unsafe', function($sce) {
		return $sce.trustAsHtml;
	});
}).call(this);