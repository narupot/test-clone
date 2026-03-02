/*****
 *This controller used to handel all attribute related function 
 *Authour : Smoothgraph Connect Pvt Ltd.
 ******/

(function () {
  "use strict";

  function _attrbuteSetCtrlHandler($scope, $filter, $http) {
    //data set and setting config
    $scope.prdData = {
      specslist: JSON.parse(attributes),
      attributesets: JSON.parse(attributesets),
      currentPage: 0,
      pageSize: 10,
      query: '',
      product_usage:0,
      specs_create_list: [],
      spesattrset: [],
      pagearr: [10, 20, 30, 40, 50],
      loading: {
        "save_and_continue": false,
        "disableBtn": false,
        "btnloaderpath": tableLoaderImgUrl,
      },
      browserImgSrc: 'far fa-file-image',
      //browserImgSrc: browserImgSrc,
      sec_disable: false,
    };
    //model setting 
    $scope.attrset = {};
    $scope.errors = [];
    //edit case handler
    if (typeof action_page !== undefined && action_page == "edit_page") {
      //disable input and select box
      $scope.prdData.sec_disable = true;
      //set  values in all field
      $scope.attrset['name'] = prev_attrset_data.name;
      $scope.attrset['description'] = prev_attrset_data.description;
      $scope.attrset['label_flag'] = prev_attrset_data.label_flag;
      $scope.attrset['font_color'] = prev_attrset_data.font_color;
      $scope.attrset['flag_bg_color'] = prev_attrset_data.flag_bg_color;
      $scope.attrset['id'] = prev_attrset_data.id;
      $scope.prdData.product_usage = prev_attrset_data.product_usage;
      //$scope.remind_icon = prev_attrset_data.remind_icon;
      if (prev_attrset_data.remind_icon)
        $scope.prdData.browserImgSrc = prev_attrset_data.remind_icon;

      $scope.prdData.specs_create_list.length = 0;
      $scope.prdData.specs_create_list = alreadysetAttribute;
      angular.forEach($scope.prdData.specs_create_list, function (item) {
        var indx = $scope.prdData.specslist.findIndex((x) => x.id === item.id);
        if (indx !== -1)
          $scope.prdData.specslist.splice(indx, 1);
      });
      //set attribute set options 
      if (prev_attrset_data.base_on_id) {
        var atr_ind = $scope.prdData.attributesets.findIndex((x) => x.id == prev_attrset_data.base_on_id);
        if (atr_ind != -1)
          $scope.attrset['base_on_id'] = $scope.prdData.attributesets[atr_ind];
      }
    }

    //drag and drop callback
    $scope.dropCallback = function (index, item, external, type, flag) {
      $scope.$evalAsync(function () {
        // $scope.product.specmodel.push({textVal:null,selectVal:null,textareaVal:null,usevaiant:false,attrselect:null,mulattrselect:null,id:item.id, front_input: item.front_input, required: item.required});
        var indx = $scope.prdData.specslist.findIndex((x) => x.id === item.id);
        if (indx !== -1) {
          $scope.prdData.specslist.splice(indx, 1);
        }
        $scope.prdData.specs_create_list.push(item);
      })
      // Return false here to cancel drop. Return true if you insert the item yourself.
      return true;
    };

    //get number of filter data 
    $scope.getFilterData = function(){
      return $filter('filter')($scope.prdData.specslist, $scope.prdData.query);
    };

    //get total number of attribute count
    $scope.getSpecTotalPageCount = (totalFilteredSpecCount) => {
       return Math.ceil($scope.getFilterData().length/$scope.prdData.pageSize);     
    };

    $scope.$watch('prdData.query', function(newValue,oldValue){                      
     if(oldValue!=newValue){
        $scope.prdData.currentPage = 0;
      }
    },true);

    /********Product variants and specification,requirement section*********/
    $scope._removeSep = function ($event, index, strFlag, item) {
      //this function used to remove object from  specs_create_list
      $event.preventDefault();
      if (strFlag === 'specification') {
        var temp = angular.copy($scope.prdData.specslist);
        temp = temp.concat(item);
        $scope.prdData.specslist = temp;
        var ind = $scope.prdData.specs_create_list.findIndex(x => x.id === item.id);
        if (ind != -1)
          $scope.prdData.specs_create_list.splice(ind, 1);

      }
    };
    //Listen on attribute set from submit
    $scope.saveAttribute = function ($event, action_name) {
      $event.stopPropagation();

      if ($scope.prdData.specs_create_list.length == 0) {
        swal('Opss..', 'Please drag and drop attribute set', 'warning');
      }
      if ($scope.attrbuteform.$valid) {
        //enable page loader and disable save,save_continue button
        $scope.prdData.loading.save_and_continue = true;
        $scope.prdData.loading.disableBtn = true;
        $http({
          method: 'POST',
          url: formactionUrl,
          headers: {
            'Content-Type': undefined
          },
          transformRequest: function (data) {
            var formData = new FormData();
            
            var _id = $scope.prdData.specs_create_list.map(function (obj) {
              return obj.id;
            });

            //console.log(data.formData);
            //exit;
            data.formDatas["attr_id"] = _id;
            formData.append("formDatas", angular.toJson(data.formDatas));
            //formData.append("remind_icon", data.files);
            formData.append("remind_icon", data.remind_icon);
            //add action in case of add attribute set
            if (action_name !== undefined) {
              formData.append("submit_type", action_name);
            }
            return formData;
          },
          data: {
            formDatas: $scope.attrset,
            remind_icon : angular.element(document.getElementById('remind_icon')).val(),             
            // files: $scope.remind_icon
          },
        }).then(function (resp) {
          if (resp.data.status !== undefined && resp.data.status == 'success') {
            swal({
              position: 'top-center',
              type: 'success',
              title: 'Good',
              showConfirmButton: false,
              timer: 1500
            });
            window.location.href = resp.data.redirect;
          } else {
            //excute code on error message
            //console.log(resp.data.errors);
            $scope.errors = resp.data.errors;
            //console.log($scope.errors);
          }
          $scope.prdData.loading.save_and_continue = false;
          $scope.prdData.loading.disableBtn = false;
        }, function (error) {
          _error();
        }).finally(function () {
          $scope.prdData.loading.save_and_continue = false;
          $scope.prdData.loading.disableBtn = false;
        });
      }
    };
    //Listen on error 
    $scope.showError = function (inputName) {
      var form = $scope.attrbuteform;
      return (form.$submitted || form[inputName].$touched) && !form[inputName].$valid;
    };
    //File upload handler
    var readURL = function (input) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
          $scope.$evalAsync(function () {
            $scope.prdData.browserImgSrc = e.target.result;
          });
        };
        reader.readAsDataURL(input.files[0]);
      }
    };
    //Listen on file change
    $scope.fileNameChanged = function (ele) {
      var file = ele.files[0];
      var ext = file.name.split('.').pop().toLowerCase();
      if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
        swal('Opps', 'invalid extension!', 'warning');
        return false;
      }
      $scope.$evalAsync(function () {
        $scope.remind_icon = file;
        readURL(ele);
      });
    };
    var prev_attrset = [];
    //Listen on attribute set change 
    $scope._attrSetChange = function () {
      if ($scope.attrset.base_on_id) {
        //enable page loader and disable save,save_continue button
        $scope.prdData.loading.save_and_continue = true;
        $scope.prdData.loading.disableBtn = true;

        $http({
          url: attributeurl,
          method: 'GET',
          params: {
            "attr_set_id": $scope.attrset.base_on_id.id
          },
        }).then(function (resp) {
          if (resp.data.status !== undefined && resp.data.status == "success") {
            if (resp.data.set_attributes && resp.data.set_attributes.length > 0) {
              //pevious attrbute set check and change 
              if (prev_attrset.length > 0) {
                angular.forEach(prev_attrset, function (item) {
                  var atrind = $scope.prdData.specs_create_list.findIndex(x => x.id === item.id);
                  if (atrind != -1) {
                    $scope.prdData.specs_create_list.splice(atrind, 1);
                  }
                });
                prev_attrset.length = 0;
                prev_attrset = resp.data.set_attributes;
              } else {
                prev_attrset = resp.data.set_attributes;
              }
              //remove specifcation from specslist 
              var attr = [];
              angular.forEach(resp.data.set_attributes, function (item) {
                var ind = $scope.prdData.specslist.findIndex(x => x.id === item.id);
                var atrind = $scope.prdData.specs_create_list.findIndex(x => x.id === item.id);
                if (ind != -1)
                  $scope.prdData.specslist.splice(ind, 1);
                if (atrind == -1) {
                  attr.push(item);
                }
              });
              var temp = angular.copy($scope.prdData.specs_create_list);
              temp = temp.concat(attr);
              $scope.prdData.specs_create_list = temp;
            } else {
              //in case there are no attribute in attaribute set then remove all previous attribute list form sepec_create_list
              if (resp.data.set_attributes.length === 0 && prev_attrset.length > 0) {
                angular.forEach(prev_attrset, function (item) {
                  var atrind = $scope.prdData.specs_create_list.findIndex(x => x.id === item.id);
                  if (atrind != -1) {
                    $scope.prdData.specs_create_list.splice(atrind, 1);
                  }
                });
                prev_attrset = [];
              }

            }
            //enable page loader and disable save,save_continue button
            $scope.prdData.loading.save_and_continue = false;
            $scope.prdData.loading.disableBtn = false;
          }
        }, function (error) {
          //enable page loader and disable save,save_continue button
          $scope.prdData.loading.save_and_continue = false;
          $scope.prdData.loading.disableBtn = false;
          _error();
        }).finally(function () {
          //enable page loader and disable save,save_continue button
          $scope.prdData.loading.save_and_continue = false;
          $scope.prdData.loading.disableBtn = false;
          console.log;
        });
      }
    };
  };
  //Listen on error 
  function _error() {
    try {
      throw new Error("Something went badly wrong!");
    } catch (e) {
      console.log("Opps " + e);
      swal(
        'Oops...',
        'Something went wrong!',
        'error'
      );
    };
  };

  angular.module('sabinaAdminApp').controller('attributeSetCtrl', _attrbuteSetCtrlHandler);

  /*
   *@description : This directiv used for enable sortable on attribute set after render finish
   */

  angular.module('sabinaAdminApp').directive('onFinishRender', ['$timeout', '$parse', function ($timeout, $parse) {
    return {
      restrict: 'A',
      link: function (scope, element, attr) {
        if (scope.$last === true) {
          $timeout(function () {
            if (!!attr.onFinishRender) {
              $parse(attr.onFinishRender)(scope);
            }
            angular.element(document.getElementById('attr-sortable')).sortable({
              handle: ".fa-bars",
              start: function (event, ui) {
                ui.item.startIndex = ui.item.index();
              },
              stop: function (event, ui) {
                var arr = angular.copy(scope.$parent.prdData.specs_create_list);
                scope.$parent.prdData.specs_create_list = [];
                var temp = arr[ui.item.startIndex];
                arr[ui.item.startIndex] = arr[ui.item.index()];
                arr[ui.item.index()] = temp;
                scope.$parent.prdData.specs_create_list = arr;
                scope.$parent.$apply();
              }
            });
          });
        }
      }
    }
  }]);
})(window.angular);