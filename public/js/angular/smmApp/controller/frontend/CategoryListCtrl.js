 /*****
  *Author : Smoothgraph Connect pvt Ltd.
  *Created date : 11/12/2017
  *This controller used for handel category list
  *****/  
 (function() {
     function catListHandler($scope, salesfactoryData, $window, $timeout, $rootScope, $filter, $interval) {
         /************
         *@desc : this section used to config pagination setting
         *config from admin if not then used local config
         *************/
        /** scope variable ***/
        $scope.page = 0;
        $scope.product_Items = sub_cats || [];
        $scope.pagination = {
            show_hide_pagination: false,
            label: (typeof paginations !== "undefined") ? paginations[0]['value'] : '10',
            totalItems: 0,
            itemsPerPage: 10,
            currentPage: 1,
            item_option_arr: (typeof paginations !== "undefined") ? paginations : [],
            // grid_class : "grid-4",
            maxPageSize : 10,
        };
        //Loader setting 
        $scope.loader = {
            loadingMore: !1,
            //loaderImg: btnloaderpath,
            addtocart: !1,
            disableBtn: !1,
        };
        //object used for variable mainupluation
        $scope.varModel = {
            no_result_found: !0,
        };
        $scope.productLayoutView = 'grid-view'; 
        /************ function section ***************/
        /****
        *@desc : check mobile and set page size (means number page show in pagination)
        *****/
        window.mobileAndTabletcheck = function() {
          var check = false;
          (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
          return check;
        };

        var init = (function($scope){
            //set product view layout as per as theme customization setting
            try{
                if(mobileAndTabletcheck()){                   
                    $scope.pagination.maxPageSize = 5;
                }               
            }catch(er){
                console.log;
            };            
        })($scope);

        //Listen on pagination change 
        $scope.loadNext = function(page) {
            //call get data function with query string
            pushQueryString();
        };
        //Listen on item per page change by user
        $scope.changeItemPerPage = function($evt, item) {
            $scope.pagination.itemsPerPage = item.key;
            $scope.pagination.label = item.value;
            $scope.pagination.currentPage = 1;
            //call get data function with query string
            pushQueryString();
        };

        //Listen on layout change (mean grid to list & list to grid)
        $scope.prdLayoutManage = function(value) {
            $scope.productLayoutView = value;
        };

        /*listen to redirect product to product detail page */
        $scope.redirectToProductPage = function(itemUrl){
            window.location.href = itemUrl;
        };       
     }; //end controller

     
     angular.module('smm-app', [], function($interpolateProvider){
        $interpolateProvider.startSymbol('<%');
        $interpolateProvider.endSymbol('%>');
     });
     catListHandler.$inject = ['$scope', '$window', '$timeout', '$rootScope', '$filter', '$interval'];
     angular.module('smm-app').controller('CategoryList', catListHandler);

     /*Listen for get index 
      *@param : destObj (oject/array)
      *@param : matchEle (string)
      *@param : matchType (string -optional)
      */
     function _getIndex(destObj, matchEle, matchType) {
         var index;
         index = destObj.findIndex(function(item) {
             if (matchType !== undefined && matchType) {
                 return (item[matchType] == matchEle);
             } else {
                 return (item == matchEle);
             }
         });
         return index;
     }

     //Listen on error 
     function _error() {
         try {
             throw new Error("Something went badly wrong!");
         } catch (e) {
             //_messageHandler('error','Something went wrong!','Oops...')
             console.log("Opps " + e);
         };
     };

     //function to animate scrollbar to top after page change 
     function animate_top() {
         var body = $("html, body");
         body.stop().animate({
             scrollTop: 0
         }, 500, 'swing');
     };

 })(window.angular);