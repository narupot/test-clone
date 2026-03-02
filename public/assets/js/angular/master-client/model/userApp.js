(function(angular, undefined) {
  "used strict";

    /*
     *@Name : userApp.js
     *@Description : This app used to handel all user related module
     *@Author : Smoothgraph Connect Pvt. Ltd
     *@Created at : 15/01/2018
     */

    angular.module("userApp", ['jsonseivice','paginationDir'], function($interpolateProvider) {
      $interpolateProvider.startSymbol('<%');
      $interpolateProvider.endSymbol('%>');
    });

  //filter for add dynamic html
  angular.module("userApp").filter("unsafe", ["$sce", function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }]);

  //filter for dynamic url add in ifrem 
  angular.module("userApp").filter("trustAsResourceUrl", ['$sce', function($sce) {
    return function(val) {
      return $sce.trustAsResourceUrl(val);
    }
  }]);


})(window.angular);