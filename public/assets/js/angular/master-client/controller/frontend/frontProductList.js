 /*****
  *Author : Smoothgraph Connect pvt Ltd.
  *Created date : 11/12/2017
  *This controller used for handel all front product listing
  *****/
 (function() {
   function prdCtrlHandler($scope, salesfactoryData, $window, $timeout, $rootScope, $state,$filter) {
     /****
      *Page load config Object : page_config
      *@page : number 0,
      *@product_Items : array [],
      *@loadingMore : Blooean false,
      *@totalItems : number  for pagination
      ****/
     //$scope.page=0;
     $scope.product_Items = [];
     //Pagination setting
     $scope.pagination = {
       totalItems: 0,
       itemsPerPage: 12,
       currentPage: 1,
     };
     //Loader setting 
     $scope.loader = {
       loadingMore: false,
       loaderImg: btnloaderpath,
       addtocart: false,
       disableBtn: false,
     };
     //Layout management
     $scope.orderBy = 'name';
     $scope.order = 'ASC';
     $scope.productLayoutView = 'grid';
     $scope.attributeNames = {};
     $scope.fillterAttributes = '';
     $scope.messageShow = false,
     $scope.senddropdown = true;
     $scope.message = '',

       $scope.cats_id = (typeof cat_id !== "undefined" && cat_id) ? cat_id : null;

     $scope.selectedAttributes = [];



     //Listen on load more data from server (mean load product on click on pagination)
     $scope.loadData = function(_obj, strFlag) {
       // console.log(_obj)
       //console.log(strFlag);

       // if ($scope.pagination.totalItems == $scope.pagination.currentPage) return;

       //  console.log("strFlag");

       $scope.loader.loadingMore = true;

       if (_obj == undefined && strFlag == undefined && strFlag != "layout_action") {
         _obj = {
           page: $scope.pagination.currentPage,
           cat_id: $scope.cats_id,
           orderBy: $scope.orderBy,
           order: $scope.order
         };
       }
       //fetch data from server https://api.myjson.com/bins/a0afb  getproductURL
       salesfactoryData.getData(getproductURL, 'POST', _obj)
         .then((res) => {
           let result = res.data;
           if (result.status == 'success') {
             $scope.product_Items = result.data;
             $scope.pagination.totalItems = result.totalproducts;
             $scope.loader.loadingMore = false;
           } else {
             $scope.loader.loadingMore = false;
           }
         }, (error) => {
           $scope.loader.loadingMore = false;
           _error();
         });
     };
     //self exeucte on load controller
     $scope.loadData();
     //Listen on layout managenet 
     $scope.filterByAttribute = function(attributeName, removeID = null) {

       if (attributeName == 'DESC' || attributeName == 'ASC') {
         $scope.order = attributeName;
       }
       if (attributeName == 'reset-all') {
         $scope.selectedAttributes = [];
         $scope.attributeNames = {};
       }
       //console.log($scope.attributeNames[attributeName]);
       if (removeID != null) {
         $scope.attributeNames[attributeName][removeID] = false;
       }
       let obj = {
         page: $scope.pagination.currentPage,
         cat_id: $scope.cats_id,
         fillterAttributes: $scope.attributeNames,
         orderBy: $scope.orderBy,
         order: $scope.order
       };
       /*when select category from tab*/
       $scope.loadData(obj, 'layout_action');
     };
     //Listen on layout change (mean grid to list list to grid)
     $scope.prdLayoutManage = function(value) {
       $scope.productLayoutView = value;
     };
     //Listen on add wishlist
     $scope.addIntoWishlist = function(product_id, $event, index) {
       $event.stopImmediatePropagation();
       salesfactoryData.getData(addIntoWishlist, 'GET', {
           "product_id": product_id
         })
         .then((response) => {
           if (response.data.status !== undefined && response.data.status == "success") {
             $scope.product_Items[index].wish = product_id;
             _toastrMessage(response.data.status, response.data.message);
           }
         }, (error) => {
           _error();
         });
     };
     //Listen on remove wishlist
     $scope.removeFromWishlist = function(product_id, $event, index) {
       $event.stopImmediatePropagation();
       salesfactoryData.getData(removeFromWishlist, 'GET', {
           "product_id": product_id
         })
         .then((response) => {
           if (response.data.status !== undefined && response.data.status == "success") {
             $scope.product_Items[index].wish = null;
             _toastrMessage(response.data.status, response.data.message);
           }
         }, (error) => {
           _error();
         });
     };
     //Listen on pagination change 
     $scope.loadNext = (page) => {
       $scope.loadData();
       //changeUrl('page',page);
     };
     //quantiy model and function
     $scope.quantity = {
       totalQtyVal: [],
       incDcrQuntity: function($event, valMax, str, prdId, index) {
         //this function used for handel quantiy increase decrease
         $event.stopPropagation();

         if (str == "up" && this.totalQtyVal[index] < valMax) {
           this.totalQtyVal[index]++;
           this.getPriceByOption($event, str, prdId, index);
         } else if (str == "down" && this.totalQtyVal[index] > 1) {
           this.totalQtyVal[index]--;
           this.getPriceByOption($event, str, prdId, index);
         }
       },
       getPriceByOption: function($event, str, prdId, index) {
         //this function used for get price by option

         //In case totalQtyVal is undefined or 0
         if (str === "tqchange" && ((this.totalQtyVal[index] == undefined) || this.totalQtyVal[index] == 0)) return;
         //get main product id
         let mainPrdId = filter($scope.product_Items, (o) => {
           return o.id == prdId
         })[0].mainProductId;
         //get all option and option value id 
         /* let valId = (rvCtrl.optionFieldId!= undefined) ? getFieldId(rvCtrl.optionFieldId,mainPrdId,'optchange') : [];
          let chkVal = (rvCtrl.optionValueCheck!= undefined) ? getFieldId(rvCtrl.optionValueCheck,mainPrdId,'valchange') : [];    
          let dataObj = {
            'productId' : prdId,
            "quantity" : this.totalQtyVal[index],
            "optionValueId" : chkVal,
            "optionId" : valId
          };*/
         /*salesfactoryData.getData(productPriceByOption, 'POST', dataObj)
         .then((resp) =>{ 
           $scope.product_Items[index].productPrice = resp.data;
           loaderImage('hide');
         },(error)=> console.log);*/
       }
     };



     /*
      *This object used to manage all filater related function (including data and function)
      */

     $scope.filter_action = {
       attrbute_results: JSON.parse(attrbute_results),
       subcategories: (typeof subcategories !== "undefined" && subcategories) ? JSON.parse(subcategories) : [],
       filter_list: [],
       selected_cat : [],
       filter_list_handler: function(filetr_attribute, _key, attr_code, attr_id, attr_item) {
         /*
          *This function used to handel filter action on attribute click
          *@param : filetr_attribute (object)
          *@param : _key (string)
          *@param : attr_code (string)
          *@param : attr_item (object)
          */

         var _self = this;

         if ($scope.attributeNames[attr_code][attr_id] == true) {
           _self.filter_list.push(attr_item);
           let idArr =  _self.getId(_self.filter_list);
           $state.go('query_state', {page:$scope.pagination.currentPage,filter_by: (!angular.isUndefined(idArr) && idArr.length > 1 ? idArr.join(',') : idArr)}, {reload:true});
          // console.log();
           //filter_by: (!angular.isUndefined(idArr) && idArr.length > 1 ? idArr.join(',') : idArr),
         } else if (!$scope.attributeNames[attr_code][attr_id] == true) {
           let ind = _self.filter_list.findIndex((o) => {
             return o.id == attr_item.id;
           });
           //remove exsting elemnt from filter_list array
           if (ind >= 0) {
             _self.filter_list.splice(ind, 1);
           }
         }
         //get filter data
         let obj = {
           page: $scope.pagination.currentPage,
           cat_id: $scope.cats_id,
           fillterAttributes: $scope.attributeNames,
           orderBy: $scope.orderBy,
           order: $scope.order
         };
         $scope.loadData(obj, 'layout_action');
       },
       removeFilterHandler: function(item) {
         var _self = this;

         let ind = _self.filter_list.findIndex((o) => {
           return o.id == item.id;
         });
         //remove exsting elemnt from filter_list array
         if (ind >= 0) {
           _self.filter_list.splice(ind, 1);
         }

         //reset model on remove
         angular.forEach($scope.attributeNames, (_key, _val) => {
           //reset model if its true and not undefined
           if (_key[item.id] !== undefined && _key[item.id] == true) {
              $scope.attributeNames[_val][item.id] = false;
           }
         });
         //get filter data
         let obj = {
           page: $scope.pagination.currentPage,
           cat_id: $scope.cats_id,
           fillterAttributes: $scope.attributeNames,
           orderBy: $scope.orderBy,
           order: $scope.order
         };
         $scope.loadData(obj, 'layout_action');
       },
       clearAllFilter : function(flag){
          if(flag!== undefined && flag === 'reset_all'){
              this.filter_list= [];
              this.selected_cat = [];
              //reset model on remove
              $scope.attributeNames ={};
          }
          //get filter data
          let obj = {
           page: $scope.pagination.currentPage,
           cat_id: $scope.cats_id,
           fillterAttributes: $scope.attributeNames,
           orderBy: $scope.orderBy,
           order: $scope.order
          };
          $scope.loadData(obj, 'layout_action');
       },
       getId : function(data){
          let result = data.map((o)=>{
              return o.id;
          });
          return result;
       },
       filter_by_cat : function(action_type,cate_item){
          /*
          *This function used to handel filter action on categories click
          *@param : action_type (string)
          *@param : cate_item (Object)
          */
          var _self = this;
          if(action_type === "add_cat"){
            if($scope.attributeNames.category[cate_item.id] === true){
              _self.selected_cat.push(cate_item);
            }else{
              let ind = _self.selected_cat.findIndex((o) => {
                 return o.id == cate_item.id;
              });
              //remove exsting elemnt from filter_list array
              if (ind >= 0) {
                _self.selected_cat.splice(ind, 1);
              }
            }
          }else if(action_type === "remove_cat"){
            let ind = _self.selected_cat.findIndex((o) => {
               return o.id == cate_item.id;
            });
            //remove exsting elemnt from filter_list array
            if (ind >= 0) {
              _self.selected_cat.splice(ind, 1);
            }
            //reset model
            $scope.attributeNames.category[cate_item.id] = false;
          }
          //get filter data
          let obj = {
           page: $scope.pagination.currentPage,
           cat_id: $scope.cats_id,
           fillterAttributes: $scope.attributeNames,
           orderBy: $scope.orderBy,
           order: $scope.order
          };
          $scope.loadData(obj, 'layout_action');          
       },
     };

     $scope.$on('$locationChangeSuccess', function($stateParams, $sate, newState, oldState) {
        // Use $state.params to update as needed
        console.log('ddf');
        console.log($stateParams)
        console.log("state ?? ");
        console.log($state.params);

        console.log("newState \t" +  newState);
        console.log("oldState \t" + oldState);
     })

   }; //end controller

   angular.module('siam-app').controller('ProductListController', prdCtrlHandler); 



   //Listen on error 
   function _error() {
     try {
       throw new Error("Something went badly wrong!");
     } catch (e) {
       //_messageHandler('error','Something went wrong!','Oops...')
       console.log("Opps " + e);
     };
   };
   //Define variable
   var objQueryString = {};

   //Get querystring value
   function getParameterByName(name) {
     name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
     var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
       results = regex.exec(location.search);
     return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
   }

   //Add or modify querystring
   function changeUrl(key, value) {
     console.log(location)
     //Get query string value
     var searchUrl = location.search;
     //Get href from url 
     var urlHref = location.href;

     console.log("searchUrl" + searchUrl)
     if (searchUrl.indexOf("?") == "-1") {
       var urlValue = '?' + key + '=' + value;
       urlHref += urlValue;
       history.pushState({
         state: 1,
         rand: Math.random()
       }, '', urlHref);
     } else {
       //Check for key in query string, if not present
       if (searchUrl.indexOf(key) == "-1") {
         var urlValue = searchUrl + '&' + key + '=' + value;
       } else { //If key present in query string
         oldValue = getParameterByName(key);
         if (searchUrl.indexOf("?" + key + "=") != "-1") {
           urlValue = searchUrl.replace('?' + key + '=' + oldValue, '?' + key + '=' + value);
         } else {
           urlValue = searchUrl.replace('&' + key + '=' + oldValue, '&' + key + '=' + value);
         }
       }
       urlHref += urlValue;
       history.pushState({
         state: 1,
         rand: Math.random()
       }, '', urlHref);
       //history.pushState function is used to add history state.
       //It takes three parameters: a state object, a title (which is currently ignored), and (optionally) a URL.
     }
     objQueryString.key = value;
     //sendAjaxReq(objQueryString);
   }

   //Used to display data in webpage from ajax
   function sendAjaxReq(objQueryString) {
     $.post('test.php', objQueryString, function(data) {
       //alert(data);
     })
   }


   //Function used to remove querystring
   function removeQString(key) {
     var urlValue = document.location.href;

     //Get query string value
     var searchUrl = location.search;

     if (key != "") {
       oldValue = getParameterByName(key);
       removeVal = key + "=" + oldValue;
       if (searchUrl.indexOf('?' + removeVal + '&') != "-1") {
         urlValue = urlValue.replace('?' + removeVal + '&', '?');
       } else if (searchUrl.indexOf('&' + removeVal + '&') != "-1") {
         urlValue = urlValue.replace('&' + removeVal + '&', '&');
       } else if (searchUrl.indexOf('?' + removeVal) != "-1") {
         urlValue = urlValue.replace('?' + removeVal, '');
       } else if (searchUrl.indexOf('&' + removeVal) != "-1") {
         urlValue = urlValue.replace('&' + removeVal, '');
       }
     } else {
       var searchUrl = location.search;
       urlValue = urlValue.replace(searchUrl, '');
     }
     history.pushState({
       state: 1,
       rand: Math.random()
     }, '', urlValue);
   }



 })(window.angular);

