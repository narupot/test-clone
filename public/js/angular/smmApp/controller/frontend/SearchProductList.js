 app.controller('ProductListController', ['$scope', 'getJsonData','$window','$timeout','$rootScope',
function($scope, getJsonData,$window,$timeout,$rootScope) {
  $scope.page = 0,
  $scope.product_Items = [],
  $scope.loadingMore = false,
  $scope.messageShow = false,
  $scope.senddropdown = true;
  $scope.message = '',
  $scope.totalpages = 1;
  $scope.totalpagesArray = [];
  $scope.attributeNames = {};
  $scope.fillterAttributes = '';
  $scope.orderBy = 'name';
  $scope.order = 'ASC';
  $scope.productsView = 'grid';
  $scope.totalproducts = 0;
  /*seame use for search product*/
  $scope.search = name;
  $scope.attrbute_results = JSON.parse(attrbute_results);
  $scope.selectedAttributes = [];


  //filterByAttribute('ASC')
  
  // $scope.cat_id = cat_id;
  /** fetch data at the time of load function**/
 
  $scope.loadData = function() {
    if ($scope.totalpages == $scope.page) return;
     $scope.page++;
     $scope.loadingMore = true;
     objPage={page:$scope.page, search: $scope.search, orderBy:$scope.orderBy, order:$scope.order}; 
     getJsonData.getDataFromServer(getproductURL, 'GET', objPage).then(function(res) { 
     if(res.status=='success'){

            $scope.product_Items = res.data;
            $scope.totalpages = res.totalPages;
            $scope.totalproducts = res.totalproducts;
            for (var i=1; i<=res.totalPages; i++) {
               $scope.totalpagesArray.push(i);
            }
            $scope.loadingMore=false;

     }else{
          
          $scope.loadingMore= false; 
          // $scope.loadingMore = false;

     }
      }
    );
  };


 $scope.loadData();
 $scope.loadpagedata = function($currpage) {
    
    $scope.page = $currpage;
    $scope.loadingMore = true;

    objPage={page:$scope.page, search: $scope.search, fillterAttributes: $scope.attributeNames, orderBy:$scope.orderBy, order:$scope.order}; 

    //if($scope.attributeNames){
     // objPage.fillterAttributes= $scope.attributeNames;
   // }

    //console.log(objPage);

     getJsonData.getDataFromServer(getproductURL, 'GET', objPage).then(function(res) { 
        if(res.status=='success'){
            $scope.product_Items = res.data;
            //$scope.totalpages = res.totalPages;
           /* for (var i=1; i<=res.totalPages; i++) {
              $scope.totalpagesArray.push(i);

            }*/
            $scope.loadingMore=false;

        }else{
             $scope.loadingMore= false; 
        }
      }
    )
  };

 $scope.filterByAttribute = function($attributeName, $removeID = null){
  
  if($attributeName == 'DESC' || $attributeName == 'ASC'){
       $scope.order =  $attributeName;
  }  

  if($attributeName == 'reset-all'){
      $scope.selectedAttributes = [];
      $scope.attributeNames = {};
  }
  //console.log($scope.attributeNames[$attributeName]);

  if($removeID != null){
     $scope.attributeNames[$attributeName][$removeID] = false;
  }


  var objPage = { page:1, search: $scope.search, fillterAttributes: $scope.attributeNames, orderBy:$scope.orderBy, order:$scope.order};

  //console.log(obj);
  //$scope.attributeNames.page = 1;
  //$scope.attributeNames.cat_id = cat_id;
  /*when select category from tab*/
  $scope.loadingMore = true;
  if($attributeName == 'category' && $scope.attributeNames[$attributeName] !== undefined){
    //categoryPageUrl
      window.location.href = categoryPageUrl +'/'+ $scope.attributeNames[$attributeName];

  }



  //console.log(obj);

  getJsonData.getDataFromServer(getproductURL, 'GET', objPage).then(function(res) { 
        if(res.status=='success'){
               $scope.totalpagesArray = [];
              $scope.product_Items = res.data;
              $scope.totalpages = res.totalPages;
              $scope.totalproducts = res.totalproducts;
              $scope.attrbute_results =  res.attrbute_results;
              $scope.selectedAttributes = res.selectedAttributes;

              for (var i=1; i<=res.totalPages; i++) {
                  $scope.totalpagesArray.push(i);

              }
              $scope.loadingMore=false;

          }else{
             $scope.loadingMore= false; 
          }
        }
   )

 };


  $scope.productViews = function($value){
  
     $scope.productsView = $value;

  };


  $scope.addIntoWishlist = function(product_id, $event, $index) {
    $event.stopImmediatePropagation();
    var obj = {"product_id" : product_id};
    getJsonData.getDataFromServer(addIntoWishlist,'GET', obj).then((response)=>{
            $scope.product_Items[$index].wish = product_id;

            // console.log($index);           
           //$scope.product_Items[$index].total_likes_fun++;
           
          
    });
  }

  /****this function is used for unlike***/
  $scope.removeFromWishlist = function(product_id, $event, $index) {
    $event.stopImmediatePropagation();
    var obj = {"product_id" : product_id};
    getJsonData.getDataFromServer(removeFromWishlist, 'GET', obj).then((response)=>{
           $scope.product_Items[$index].wish = null;
          //  console.log($index); 
           //$scope.product_Items[$index].total_likes_fun--;
           
    });
  }
 
}]);