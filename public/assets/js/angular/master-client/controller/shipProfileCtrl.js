(function() {
    /*****
     *This controller used for shipping profile 
     ****/
    "used strict";

    function shipProfileCtrl($scope, salesfactoryData) {
        $scope.countryListData = countryListData;
        $scope.selectedCountry = (typeof selectedCountry !== 'undefined' && selectedCountry.length > 0) ? selectedCountry : [];
        $scope.provinceData = (typeof provinceData !== 'undefined' && provinceData.length > 0) ? provinceData : [];
        $scope.countrySelected = [];
        $scope.country_manage = {
            country_selection: null,
            show_state: false,
            country_province : (typeof angular_state !="undefined" && angular_state.length) ? angular_state: [],
        };
        var myArr = [];

        if ($scope.selectedCountry.length >= 1) {
            selectedCountry.map(function(val, key) {
                myArr.push(val.id);
            });
        }
        $scope.countryArr = myArr;
        $scope.selectedProvince = [];

        if ($scope.provinceData.length >= 1) {
            provinceData.map(function(val, key) {
                if (selProvinceInd.indexOf(val.id) != -1) {
                    $scope.selectedProvince.push(val);
                }
            })
        }

        /*
        *@desc : This function used for add and remove one by one
        *@param : item {object} - countryListData
        *@param : from {object} 
        *@param : to {object}
		*@param : tempStr {string-addItems | removeItems}
        */
        /******  ******/
        $scope.moveItem = function(item, from, to, tempStr) {
        	if(typeof from == "undefined" || typeof to == "undefined") return;  

            var cps = [];
                    	
            if(item.length > 1) {
            console.log('remove1', tempStr);            	
                item.map(function(val, key) {
                    let idx = from.indexOf(val);
                    if (idx != -1) {
                        if(tempStr == 'removeItems'){
                           cps.push(from[idx]);   
                        }
                        from.splice(idx, 1);
                        to.push(val);                        
                    }
                }); 

                if(tempStr == 'removeItems' && $("#shippingType option:selected" ).val() != 'free-shipping') removeCountryProvince(cps);               
            }else {
                let idx = from.indexOf(item[0]);
                if (idx != -1) {
                    if(tempStr == 'removeItems'){
                       cps.push(from[idx]);   
                    }
                    from.splice(idx, 1);
                    to.push(item[0]);                   
                }
                
                if(tempStr == 'removeItems' && $("#shippingType option:selected" ).val() != 'free-shipping') removeCountryProvince(cps);
            }
            addCountryValue();
        };

        /**** This function used to add all and remove all country on click arrow****/
        $scope.moveAll = function(from, to, strAction) {
            var countryId = [];
            angular.forEach(from, function(item) {
                to.push(item);
                countryId.push(item.id);
            });

            from.length = 0;
            switch (strAction) {                
                case 'removeAllList':                    
                    $scope.countryArr = [];
                    $scope.provinceData =[];
                    $scope.selectedProvince =[];
                    $scope.country_manage.country_selection = null;
                    $scope.country_manage.country_province =[];
                    break;
            };
            addCountryValue();
        };

        function addCountryValue(){
            if($("#shippingType option:selected" ).val() == 'free-shipping'){
                $scope.countryArr = [];
                if($scope.selectedCountry.length){
                    angular.forEach($scope.selectedCountry, function(item){
                        $scope.countryArr.push(item.id)
                    });
                }else {
                    $scope.countryArr = [];
                }
            }
        };

        function removeCountryProvince(ctData){
            //remove all country province 
            angular.forEach(ctData, function(item){
                let cind = _getIndex($scope.country_manage.country_province, item.id, 'id');                
                let csind = _getIndex($scope.countryArr, item.id, 'id');

                if(cind>=0){
                   $scope.country_manage.country_province.splice(cind, 1);
                }

                if(csind>=0){
                   $scope.countryArr.splice(csind, 1);
                }
            });           
        };

        shippingTypeChange = function(){
            $scope.$evalAsync(function(){
                $scope.countryArr = [];
                $scope.selectedCountry = [];
                $scope.country_manage.country_selection = null;
                $scope.country_manage.country_province =[];
                $scope.selectedProvince =[];
            });
        };

        /*
        *@desc : Listen to handle country change & get province 
        */
        $scope.countryChangeHandler = function() {
            if ($scope.country_manage.country_selection != null) {
                $scope.country_manage.show_state = true;
                let ct = {
                    "countryId": JSON.stringify([$scope.country_manage.country_selection])
                };
                //get province/state list
                salesfactoryData.getData(getShippingState, 'GET', ct)
                .then(function(resp) {
                    $scope.provinceData = resp.data;
                }, function(error) {
                    console.log;
                });
            } else {
                $scope.country_manage.show_state = false;
                $scope.provinceData = [];
            }
        };

        $scope.provinceChange = function(){            
            //selected country country_manage.country_selection
            if($scope.country_manage.country_selection!=null){
               let cindex = _getIndex($scope.country_manage.country_province, $scope.country_manage.country_selection, 'id');

               if(cindex>=0 && $scope.selectedProvince.length){
                    $scope.country_manage.country_province[cindex].province = $scope.country_manage.country_province[cindex].province.concat(filterProvince($scope.provinceData, $scope.selectedProvince, $scope.country_manage.country_province[cindex].province));
                    
                    if($scope.countryArr.indexOf($scope.country_manage.country_selection)== -1){
                        $scope.countryArr= $scope.countryArr.concat($scope.country_manage.country_selection);
                    }
               }else if(cindex == -1 && $scope.selectedProvince.length){                   
                    let cind = _getIndex($scope.selectedCountry, $scope.country_manage.country_selection, 'id');
                    let obj ={
                       "id" :  $scope.country_manage.country_selection,
                       "name" : (cind>=0) ? $scope.selectedCountry[cind].name : "",
                       "province" : filterProvince($scope.provinceData, $scope.selectedProvince, [])
                    };
                    $scope.country_manage.country_province.push(obj);
                    if($scope.countryArr.indexOf($scope.country_manage.country_selection)== -1){
                        $scope.countryArr= $scope.countryArr.concat($scope.country_manage.country_selection);
                    }
               }
            } 
        };

        $scope.removeCp = function(cp, item, index){
            let cpind = _getIndex($scope.country_manage.country_province, cp.id, "id");
            let spind = _getIndex($scope.selectedProvince, item.id);
            
            $scope.$evalAsync(function(){
                if(cpind>=0){
                    $scope.country_manage.country_province[cpind].province.splice(index, 1);
                }

                if(spind>=0){
                    $scope.selectedProvince.splice(spind, 1);
                    let $opt = Array.prototype.slice.call($('.select:first option:selected'));                   
                    for(let i=0; i<$opt.length;i++){
                        //&& $($opt[i]).text() == item.name                        
                        if($($opt[i]).val() == item.id){                                                       
                            $($opt[i]).prop('selected', false);
                            $('.select').trigger('chosen:updated');
                            break;
                        }
                    }
                } 
            });         
        };

    };
    angular.module("sabinaAdminApp").controller('shipProfileCtrl', shipProfileCtrl);

    /*Listen for get index 
    *@param : destObj (oject/array)
    *@param : matchEle (string)
    *@param : matchType (string -optional)
    */
    function _getIndex(destObj, matchEle, matchType){
        var index = -1;       

        destObj.forEach(function(item, indx){
            if(matchType!== undefined && matchType){
                if(item[matchType] == matchEle)
                    index = indx;
            }else{
                if(item == matchEle)
                   index = indx; 
            }
        });

        return index;
    };

    function filterProvince(provinceArr, selectedId, ps){
        let result = [];
        
        angular.forEach(provinceArr, function(item){
            if(selectedId.indexOf(item['id'].toString())>=0 && _getIndex(ps, item.id, "id") == -1){                
                result.push(item);
            }
        });

        return result;
    };  

}).call(this);

//directive section
(function(_ng) {
    _ng.module("sabinaAdminApp").directive("onFinishRender", finishRender);
    //enable choose after render finish
    function finishRender($parse, $timeout) {
        var linkHandler = function(scope, element, attr) {
            if (scope.$last === true) {
                $timeout(function() {
                    //scope.$emit('ngRepeatFinished');
                    $(element).parent().val('').trigger('chosen:updated');
                    $('.select').chosen();

                    if (!!attr.onFinishRender) {
                        $parse(attr.onFinishRender)(scope);
                        $(element).chosen();
                    }
                });
            }
        };
        return {
            restrict: 'A',
            link: linkHandler,
        }
    };
})(window.angular);