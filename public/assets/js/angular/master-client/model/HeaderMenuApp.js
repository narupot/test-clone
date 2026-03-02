(function(){
  /*******This module used for megamenu in sabina 
  *Created at 10/11/2017
  *Author : Smoothgraph Connect PVT. LTD
  *******/
  "use strict"
  //'ui.bootstrap',
  angular.module('megaMenuApp',['ui.tree','jsonseivice'],function($interpolateProvider){
      $interpolateProvider.startSymbol('<%');
      $interpolateProvider.endSymbol('%>');
  }).filter('trustAsResourceUrl',['$sce',function($sce) {return function(val) {return $sce.trustAsResourceUrl(val);};}])
.filter('to_trusted', ['$sce', function($sce){
            return function(text) {
                return $sce.trustAsHtml(text);
            };
        }]);
}).call(this);
