/*
 *@Name : userMainCtrl.js
 *@Description : This controller used to handel user profile like (whislist)
 *@Author : Smoothgraph Connect Pvt Ltd.
 *@created At : 15/001/2018
 */

(function(angular, undefined) {
  "used strict";


  function userProfileCtrl($scope, salesfactoryData) {
    $scope.product_Items = [];
    //Pagination setting
    $scope.pagination = {
      totalItems: 0,
      itemsPerPage: 10,
      currentPage: 1,
    };
    //Loader setting 
    $scope.loader = {
      loadingMore: false,
      loaderImg: btnloaderpath,
      disableBtn: false,
    };
    $scope.no_result_found = false;
    $scope.errorInfoLog = '<div class="no-info-blank"><h3><i class="icon-doc"></i> You have no whislist </h3></div>';

    //Listen on load more data from server (mean load product on click on pagination)
    $scope.loadData = function() {
      //if ($scope.pagination.totalItems == $scope.pagination.currentPage) return;
      $scope.loader.loadingMore = true;
      var _obj = {
          page: $scope.pagination.currentPage,
          per_page: $scope.pagination.itemsPerPage,
      };
    
      //fetch data from server https://api.myjson.com/bins/a0afb  getproductURL
      salesfactoryData.getData(getproductURL, 'GET', _obj)
        .then(function(res) {
          var result = res.data;
          if (result.status == 'success' && angular.isArray(result.data) && result.data.length > 0) {
            $scope.product_Items = result.data;
            $scope.pagination.totalItems = parseInt(result.total);
            $scope.pagination.itemsPerPage = parseInt(result.itemsPerPage);
            $scope.loader.loadingMore = false;
            $scope.no_result_found = false;
          } else {
            $scope.no_result_found = true;
            $scope.loader.loadingMore = false;
          }
        }, function(error) {
          $scope.loader.loadingMore = false;
          _error();
        });
    };
    //self exeucte on load controller
    $scope.loadData();

    /*
     *@Description : Listen on remove wishlist
     *@param : $event 
     *@param : wishlist_id (string)
     *@param : index (number)
     */
    $scope.removeFromWishlist = function($event, wishlist_id, index) {
      $event.stopImmediatePropagation();
      salesfactoryData.getData(removeFromWishlist, 'POST', {
          "wishlist_id": wishlist_id
        })
        .then(function(response){
          if (response.data.status !== undefined && response.data.status == "success") {
          	_toastrMessage(response.data.status, response.data.message);
            $scope.product_Items.splice(index,1);	         
          }
        }, function(error) {
          _error();
        });
    };

    /****
    *Listen on click on next footer pagination
    *@url : service url
    *@type : product type(ex. related , upsell etc)
    *@page : page number 
    *@prd_type_flag : which tab is enable like simple,config and related product 
    *****/
    $scope.clickOnNext = function(page){
      $scope.loadData();        
    };



  }; //end controller
  
  userProfileCtrl.$inject = ['$scope', 'salesfactoryData']
  angular.module("userApp").controller("manageUserProfile", userProfileCtrl);

  //Listen on error 
  function _error() {
    try {
      throw new Error("Something went badly wrong!");
    } catch (e) {
      //_messageHandler('error','Something went wrong!','Oops...')
      // console.log("Opps " + e);
    };
  };

  //Toaster setting for message
  function _toastrMessage(status, message){
  	Command: toastr[status](message)
  }
  
  //Toaster setting for message
  toastr.options = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": false,
    "positionClass": "toast-top-center",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "9000000000000",
    
  };
  
})(window.angular);