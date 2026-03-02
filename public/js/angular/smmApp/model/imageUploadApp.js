(function() {
	angular.module('smm-app', ['uploadModule'], function($httpProvider, $interpolateProvider) {				
		$interpolateProvider.startSymbol('<%');
		$interpolateProvider.endSymbol('%>');
	})

})(window.angular);
