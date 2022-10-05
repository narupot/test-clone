/**
*@desc : Mega Menu Module
*@Created at: 18/14/2019
*@Author : Smoothgraph Connect PVT. LTD
**/

(function(){
    "use strict";
    angular.module('smm-app',['ui.tree','jsonseivice'],function($interpolateProvider){
        $interpolateProvider.startSymbol('<%');
        $interpolateProvider.endSymbol('%>');
    }).filter('trustAsResourceUrl',['$sce',function($sce) {
        return function(val) {
            return $sce.trustAsResourceUrl(val);
        }
    }]).filter('to_trusted', ['$sce', function($sce){
        return function(text) {
            return $sce.trustAsHtml(text);
        };
    }]);
})(window.angular);