(function() {
	angular.module('sabinaAdminApp', ['uploadModule'], function($httpProvider, $interpolateProvider) {				
		$interpolateProvider.startSymbol('<%');
		$interpolateProvider.endSymbol('%>');
	})

})(window.angular);
