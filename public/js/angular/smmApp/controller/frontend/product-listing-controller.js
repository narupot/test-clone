/*****
 *@Author : Smoothgraph Connect pvt Ltd.
 *@Created date : 11/12/2017
 *@description : This controller used for handel all front product listing
 *****/
(function() {
    "use strict";
    var atc_action;
    /*Listen for get index 
     *@param : destObj (oject/array)
     *@param : matchEle (string)
     *@param : matchType (string -optional)
     */
    var _getIndex = function(destObj, matchEle, matchType) {
        var index;
        index = destObj.findIndex(function(item) {
            if (matchType !== undefined && matchType) {
                return (item[matchType] == matchEle);
            } else {
                return (item == matchEle);
            }
        });
        return index;
    };

    //Listen on error 
    var _error = function() {
        try {
            throw new Error("Something went badly wrong!");
        } catch (e) {
            messageHandler(lang_oops, "error");
            console.log("Opps " + e);
        };
    };

    /*
     *@desc : for toastr like message display using bootsrap alert
     *@param : mesg {string}
     *@param : classType {string} ->error/success
     */
    var messageHandler = function(mesg, classType) {
        var _div = document.createElement('div');
        var _class = "alert custom-message";
        //conditional class
        if (classType === "success") {
            _class += " alert-success";
        } else {
            _class += " alert-danger";
        }
        _div.className = _class;

        var text = document.createTextNode(mesg);
        _div.appendChild(text);
        document.body.appendChild(_div);
        jQuery(_div).fadeOut(4000, function() {
            jQuery(this).remove();
        });
    };

    /*
     *@Description : function to animate scrollbar to top after page change
     */
    var animate_top = function() {
        var body = jQuery("html, body");
        body.stop().animate({
            scrollTop: 0
        }, 500, 'swing');
    };


    var controllerFunction = function($scope, salesfactoryData, $window, $timeout, $rootScope, $state, $interval, dataManipulation) {
        var pageLoad = true,
            order = (typeof short_data != "undefined") ? short_data[0]['by'] : 'asc'; 
        /************
         *@desc : this section used to config pagination setting
         *config from admin if not then used local config
         *************/
        /** scope variable ***/
        $scope.page = 0;
        $scope.product_Items = [];
        $scope.cate_data = cate_id;
        $scope.pagination = {
            show_hide_pagination: false,
            label: (typeof paginations !== "undefined") ? paginations[0] : '10',
            totalItems: 0,
            itemsPerPage: 12,
            currentPage: 1,
            item_option_arr: (typeof paginations !== "undefined") ? paginations : [],
            grid_class : "grid-4",
            maxPageSize : 10,
        };
        //Loader setting 
        $scope.loader = {
           /* loadingMore: !1,*/
            /*loaderImg: btnloaderpath,*/
            addtocart: !1,
            disableBtn: !1,
            img_load : 'data:image/gif;base64,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',
        };
        //object used for variable mainupluation
        $scope.varModel = {
            no_result_found: !0,
        };
        $scope.shortData = (typeof short_data != "undefined") ? short_data : [];
        $scope.orderBy = (typeof short_data != "undefined") ? short_data[0]['name'] : 'name';
        $scope.orderLabel = (typeof short_data != "undefined") ? short_data[0]['value'] : 'new';
        $scope.productLayoutView = (typeof page_type!="undefined" && page_type === "user_wishlist") ? 'list' : 'grid';
        $scope.attributeNames = (typeof selectedAttributes !== "undefined" && selectedAttributes != "") ? JSON.parse(selectedAttributes) : {};
        $scope.fillterAttributes = '';
        $scope.search = name;        
        $scope.selectedAttributes = [];
        $scope.list_class=(typeof shop_id!='undefined')? 'col-lg-4':'col-lg-3';
        $scope.show_layout = (typeof page_type!="undefined" && page_type === "user_wishlist") ? false : true;
        var cateshopid = (typeof cat_data!="undefined" && cat_data!==null) ? cat_data.id : null;
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
                if(mobileAndTabletcheck()) $scope.pagination.maxPageSize = 5;
            }catch(er){
                console.log;
            };            
        })($scope);
        
        var getShopFilter = function(){
            let query = {
                'cat_id': $scope.cate_data && $scope.cate_data['_id'] || null,
                'shop_id' : (typeof shop_id!="undefined" && shop_id) || null,
            };
            let shop_filter_url;
            if(typeof shopFilter!='undefined'){
                shop_filter_url = shopFilter;
            }else{
                return;
            }
            
            salesfactoryData.getData(shop_filter_url, 'GET', query)
            .then(function(response) {
                if (typeof response === "undefined" || response.data === null || response.xhrStatus === "error") return;
                var result = response.data;
                if (result) {
                    $scope.shop_filter_data = result;
                    if(cat_data && $scope.shop_filter_data.category){
                        try{
                            $scope.shop_filter_data.category.map(function(o){
                                if(o.id == cat_data.id){
                                    o.checked = true;
                                    $scope.filter_action.category.push(o.id);
                                }
                            })
                        }catch(err){
                            //
                        }
                    }
                } else {
                    $scope.shop_filter_data = {};
                }
            }, function(error) {
                //error handler here
            })
            .finally(function() {
                //showHideLoader('hideLoader'); /*$scope.loader.loadingMore = !1;*/
            });
        };
        getShopFilter();
        var setPreviousSelectedAttribute = function() { 
            pageLoad = !1;
            try{
                //In case have attributes
                if (typeof selectedAttributes !== "undefined" && selectedAttributes) {
                    var atrData = $scope.filter_action.attrbute_results;
                    var ftId = [];

                    angular.forEach($scope.attributeNames, function(o, index) {
                        /*var t = Object.keys(o);
                        t = t.map(Number);*/
                        ftId = ftId.concat(o.map(Number));
                    });
                    //for attribute
                    angular.forEach(atrData, function(o) {
                        // if (o.attribute_values !== undefined && o.attribute_values.values !== undefined && o.attribute_values.values.length) {
                            // o.attribute_values.values.map(function(item) {
                                if (o._id != undefined && ftId.indexOf(o._id) != -1) {
                                    $scope.filter_action.filter_list.push(o);
                                }
                            // })
                        // }
                    });
                    if(atrData && atrData.length){
                        $scope.filter_action.attrbute_results = updateBadgesAndReview($scope.filter_action.attrbute_results);
                    }
                } else {
                    $scope.attributeNames = {};
                }  
            }catch(er){
                console.log;
            }                      
        };

        /*
        *@desc : enable/disable loader/button
        *@param : strflag {string (enable/diable)}
        *@param : btnFlag {boolean} 
        */
        var _enbdsbLodBtn = function (strflag,btnFlag){
            // $scope.loader['addTocart_and_bynow'] = (strflag && strflag==='enable')? true : false;
            $scope.loader['disableBtn'] = btnFlag;
            btnFlag && showHideLoader('showLoader') ||  !btnFlag && showHideLoader('hideLoader');
        };

        /************
        *@desc : check data condition before cart
            1. Attribute is selected or not
            2. Product quantity >0
        *@product  : (normal, configrable, bundle) 
        ************/ 
        var beforeCartCheck = function beforeCartCheck(viewType, action, prdInfo, prdData, prdSelectedAttrs, matrixData) {            
            //call services to check beforeCart(viewType, action, prdInfo, prdData, prdSelectedAttrs, matrixData)
            var response = dataManipulation.beforeCart(viewType, action, prdInfo, prdData, prdSelectedAttrs, matrixData);
            if(response.qtcheck){
                swal({
                    type: 'warning',
                    text: error_msg.quantity_error,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: text_ok_btn,
                });
                response.gotocart = "no";
            }
            // else if(response.vtcheck){
            //     swal("Opps..", errorMessageService.getMessage('please_select_attribute_in_all_product'), 'warning');
            //     response.gotocart = "no";
            // }
            return response;
        }; 
        /*
        *@desc : get cart data 
        *@cartData {object}
        */
        var getCartData = function getCartData(cartData){            
            // var result = [];                       
            // var tmpSelAtt = angular.copy(rvCtrl.selAttrVal);
            
            // _.forEach(cartData, function(item){
            //     var obj={'productId' :  "",'mainproductid' : "",'quantity' : "",'attrDetail' : [], 'optionId' : [],'optionValueId' : [],'optionIdDetail' : []};
            //     obj.productId = item.id;
            //     obj.mainproductid = item.mainProductId || "";
            //     obj.quantity = item.quantity;
            //     obj.optionId = (rvCtrl.optionFieldId) ? getFieldId(rvCtrl.optionFieldId, item.mainProductId, 'optchange') : []; 
            //     obj.optionValueId  = (rvCtrl.optionValueCheck) ? getFieldId(rvCtrl.optionValueCheck, item.mainProductId, 'valchange') : [];
            //     obj.optionIdDetail = (rvCtrl.optionFieldId) ? getFieldId(rvCtrl.optionFieldId, item.mainProductId, 'optDetailchange') : [];

            //     if(rvCtrl.productLayoutView === 'grid' && item.attr_val){
            //         _.forEach(item.attr_val, function(o){
            //             obj.attrDetail.push({"attribute_id" : o.attribute_id, "valId" : o.attribute_value_id});                            
            //         });
            //     }else if(rvCtrl.productLayoutView === 'list'){
            //         var tempRes =[],
            //             mprd = item.mainProductId;
            //         _.forEach(rvCtrl.attrRes,function(currentValue, currentIndex) {
            //             var temp = _.flatMap(tmpSelAtt[mprd]);
            //             if(!_.isUndefined(temp) && temp.length>0 && currentIndex==mprd){
            //                 _.forEach(currentValue, function(cVal,cInd){
            //                     var t = temp[cInd];
            //                     t["attribute_id"] = cVal.attribute_id;
            //                 });
            //               tempRes = tempRes.concat(temp);
            //             }
            //         });
            //         obj.attrDetail = tempRes;
            //     }

            //     result.push(obj);                
            // });
            // return result;
            return {'productId' : cartData.id || cartData._id,'quantity' : cartData.quantity || 1 };
        };


        /****
        *@desc : this function used to check quantity availabel in store
        *@param : strflag {string}
        *@param : cartData {object}
        *****/
        var addToCart = function (strflag, cartData){
            cartData['cart_action'] =  strflag;            
            //send data to server 
            salesfactoryData.getData (addProductToCart, 'POST', cartData)
            .then(function (response){ 
                if(typeof response === "undefined" || response.data === null || response.xhrStatus === "error") return;
                if(response.data.status && response.data.status ==="success"){
                    if(strflag === "buynow"){
                        window.location.href = cartUrl;
                        return;
                    } 
                    // angular.element(document.getElementById('totalCartProduct')).html(response.data.cart_quantity);
                    // angular.element(document.getElementById('addToCartdiv')).modal('show');
                    let $add_to_cart_modal = document.getElementById('addToCartdiv'),
                        $cart_quantity= document.getElementById('tot_cart_noti'),
                        $cart_price = document.getElementById('totalCartPrice'),
                        /*$user_cart_a = angular.element($cart_quantity).parent(),
                        $user_cart_icon = angular.element($cart_quantity).prev(),*/
                        $bargaining =  document.getElementById('tot_bar_noti'),
                        $wating_for_payment = document.getElementById('tot_prd_noti'),
                        $paid_product = document.getElementById('tot_paid_noti'),                        
                        cd = response.data.cart_quantity || "";
                        cd && cd['bargain_prd'] && parseInt(cd['bargain_prd'])>0 && angular.element($bargaining).show().text(cd['bargain_prd']);
                        cd && cd['cart_prd'] && parseInt(cd['cart_prd'])>0 && angular.element($wating_for_payment).show().text(cd['cart_prd']);
                        cd && cd['paid_prd'] && parseInt(cd['paid_prd'])>0 && angular.element($paid_product).show().text(cd['paid_prd'] );
                        cd && cd['tot'] && parseInt(cd['tot'])>0 && angular.element($cart_quantity).show().text(cd['tot'] );
                   
                    angular.element($cart_price).html(response.data.cart_price);
                    angular.element($add_to_cart_modal).modal('show');
                    // angular.element($user_cart_icon).addClass('shake');
                    angular.element($cart_quantity).addClass('cart-run');
                    // angular.element($user_cart_a).addClass('glow');                   
                    $timeout(function(){
                        // angular.element($user_cart_icon).removeClass('shake');
                        angular.element($cart_quantity).removeClass('cart-run');
                        // angular.element($user_cart_a).removeClass('glow');
                    }, 800);
                }else if (response.data.status && response.data.status ==="fail"){
                    swal({
                        type: 'error',
                        text: response.data.msg,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: text_ok_btn,
                    });
                }else{
                    swal({
                        type: 'error',
                        text: error_msg.server_error,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: text_ok_btn,
                    });                              
                }
            }, function (err){
                swal({
                    type: 'error',
                    text: error_msg.server_error,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: text_ok_btn,
                }); 
            })
            .finally(function () {_enbdsbLodBtn('disabled',false)});
        };


        /*
         *@desc : Listen on push Query string in url then state will change
         *@type : private
         */
        var pushQueryString = function() {
            let idArr = $scope.filter_action.getId($scope.filter_action.filter_list);            
            $state.go('query_state', {
                // page: $scope.pagination.currentPage,
                // filter_by: (!angular.isUndefined(idArr) && idArr.length > 1 ? idArr.join(',') : idArr),
                // order_by: $scope.orderBy,
                // search: $scope.search,
                // price: $scope.filter_action.filter_price_range.min +'-'+$scope.filter_action.filter_price_range.max,
                // item_page: $scope.pagination.itemsPerPage,
            }, {
                reload: true
            });
        };

        /*
         *@desc : this method used to handel route param for filter
         *@type : private 
         *@param : toParams {Object}
         */
        var routeParamFilterHandler = function(toParams) {
            var ft_attr = (toParams.filter_by !== undefined && toParams.filter_by.length) ? toParams.filter_by[0].split(",") : [];
            var ft_cls = (toParams.cid !== undefined && toParams.cid.length) ? toParams.cid[0].split(",") : [];
            var ft = $scope.filter_action;
            //check filter and handel        
            if (ft.filter_list.length > 0) {
                for (var i in ft.filter_list) {
                    //in case of attribute & collections 
                    var str_type = (ft.filter_list[i].cid !== undefined && ft.filter_list[i].cid != '') ? "cid" : "id";
                    var ftdata = (str_type === "cid") ? ft_cls[i] : ft_attr[i];
                    var index = _getIndex(ft.filter_list, ftdata, str_type);

                    if (index == -1) {
                        //ft.removeFilterHandler(ft.filter_list[i]);
                    }
                }
            }
        };

        /*****
         *@desc : This object used to manage all filter related functionality
         ******/
        $scope.filter_action = {
            attrbute_results: [],           
            filter_list: [],
            review_rating : typeof rating!="undefined" && angular.copy(rating) || [],
            price_flag : false, 
            filter_price_range: {
                min : '',
                max : '',
            }, 
            badges : [],  
            category : [cateshopid],         
            filter_list_handler: function(item) {
                let _self = this;               
                if (item.checked) _self.filter_list.push(item); 
                else {
                    let ind = _self.filter_list.findIndex(function(o) {
                        return o._id == item._id && item.f_type == o.f_type;
                    });
                    //remove exsting elemnt from filter_list array
                    if (ind >= 0) _self.filter_list.splice(ind, 1);
                }

                $scope.pagination.currentPage = 1;
                pushQueryString();
            },
            apply_filter : function($evt){
                $scope.pagination.currentPage = 1;
                $evt.preventDefault();
                pushQueryString();
            },
            removeFilterHandler: function(item) {
                let _self = this;
                let ind = _self.filter_list.findIndex(function(o) {
                    return o._id == item._id && item.f_type == o.f_type;
                });
                //remove exsting elemnt from filter_list array
                if (ind >= 0) {
                    item.checked = false;
                    _self.filter_list.splice(ind, 1);
                    //in case of price type 
                    if (item.price_type !== undefined && item.price_type === "price_type") {
                        _self.filter_price_range.min ="";
                        _self.filter_price_range.max ="";
                    }
                }

                //reset model on remove
                angular.forEach($scope.attributeNames, function(_key, _val) {
                    //reset model if its true and not undefined
                    if (_key[item.id] !== undefined && _key[item.id] == true) {
                        $scope.attributeNames[_val][item.id] = false;
                    }
                });

                pushQueryString();
            },
            clearAllFilter: function(flag) {
                let self = this;
                self.filter_list = [];
                //reset badge model
                angular.forEach(self.attrbute_results, o=>{
                    o.checked = false;
                });
                //reset rating & review model 
                angular.forEach(self.review_rating, o=>{
                    o.checked= false;
                });
                //reset price model
                self.filter_price_range.min = "";
                self.filter_price_range.max = "";
                //in case of search page then reset cate model
                if(typeof name!="undefined" && name){
                    angular.forEach($scope.cat_data, o=>{
                        o.checked = false;
                    });
                }
                pushQueryString();
            },
            badgeHandler : function(item){
                let _self = this;
                if (item.checked) _self.badges.push(item.id);
                else {
                    let ind = _self.badges.findIndex(function(o) {
                        return o == item.id;
                    });
                    //remove exsting elemnt from filter_list array
                    if (ind >= 0) _self.badges.splice(ind, 1);
                }

                $scope.pagination.currentPage = 1;
                pushQueryString();
            },
            categoryHandler : function(item){
                let _self = this;
                if (item.checked) _self.category.push(item.id);
                else {
                    let ind = _self.category.findIndex(function(o) {
                        return o == item.id;
                    });
                    //remove exsting elemnt from filter_list array
                    if (ind >= 0) _self.category.splice(ind, 1);
                }

                $scope.pagination.currentPage = 1;
                pushQueryString();
            },
            getId: function(data) {
                let d = [];
                data.map(function(o) {
                    if(o._id) d.push(o._id);
                });
                return d;
            },          
            //filter by price
            filter_by_price: function(item, index) {
                let _self = this;                
                let item_index = _getIndex(_self.filter_list, "price_type", "price_type");

                if (item_index >= 0) _self.filter_list[item_index].value = item.min +'-'+ item.max;
                else {
                    //fo price value 
                    let p;
                    if(item.min && item.max) p = item.min +'-'+ item.max;
                    else if(item.min && !item.max) p = '>='+item.min;
                    else if(!item.min && item.max) p = '<='+item.max;
                    _self.filter_list.push({
                        id: 'price_1',
                        value: p/*item.min +'-'+ item.max*/,
                        type: item.type || 'price',
                        price_type: "price_type",
                    });
                }
                (item.min || item.max) && pushQueryString();
            },
            getFilterData : function(){
                let self = this;
                let rs = null;
                let badge = [];
                let review = [];
                let cat_ids = [];
                angular.forEach(self.filter_list, item=>{
                    //in case of badge
                    if(item.badge_name && item.checked) badge.push(item._id);
                    // in case of review
                    else if(item.type && item.type === "rating" && item.checked) review.push(item.rating);
                    //in case search page add category ids
                    else if(typeof name!="undefined" && item.f_type == 'cate' && item.checked) cat_ids.push(item._id);
                });

                if(badge.length || review.length || self.filter_price_range.min || self.filter_price_range.max || cat_ids.length || typeof name!="undefined"){
                    rs = {};
                    rs['badge'] = badge.length && badge || null;
                    rs['review'] = review.length && review || null;
                    //for search page category id 
                    if(typeof name!="undefined" && cat_ids.length == 0){
                        rs['cat_ids'] = queryData().cat_ids;
                    }else{
                        rs['cat_ids'] = cat_ids.length && cat_ids || null;
                    }
                    
                    if(self.filter_price_range.min || self.filter_price_range.max) rs['price'] = self.filter_price_range;
                    else if(!self.filter_price_range.min && !self.filter_price_range.max) rs['price'] = null;
                }               
                return rs;
            },
        };

        function queryData() {
            if(typeof name!="undefined" && name && typeof cat_data!="undefined"){   
                let cat_ids = [];
                let rs = {};                         
                angular.forEach(cat_data, c=>{
                    cat_ids.push(c._id);
                });
                rs['cat_ids'] = cat_ids.length && cat_ids || null;
                return rs;
            }else{
                return $scope.attributeNames;
            }
        };

        /*****
         *@desc : This function used to load data from server
         ******/
        $scope.loadData = function() {
            showHideLoader('showLoader'); /*$scope.loader.loadingMore = !0;           */
            $scope.product_Items = []; 
            
            let query = {
                'page': parseInt($scope.pagination.currentPage),
                'cat_id': ($scope.cate_data && $scope.cate_data['_id'])  || $scope.filter_action.category || null,
                'orderBy': $scope.orderBy,
                'order' : order,
                'itemsPerPage': $scope.pagination.itemsPerPage,
                'fillterAttributes': (pageLoad)? queryData() : $scope.filter_action.getFilterData(),                
                'search': $scope.search,
                'shop_id' : (typeof shop_id!="undefined" && shop_id) || null,
                'badge_id': $scope.filter_action.badges,
                // 'cat_id': $scope.filter_action.category, 
            };

            salesfactoryData.getData(getproductURL, 'POST', query)
                .then(function(response) {
                    if (typeof response === "undefined" || response.data === null || response.xhrStatus === "error") return;
                    var result = response.data;
                    if (result.status === 'success' && angular.isArray(result.detail.data) && result.detail.data.length > 0) {
                        try{
                            //in case of search page 
                            if(typeof name!="undefined" && name && typeof cat_data!="undefined"){                            
                                angular.forEach(cat_data, c=>{
                                    c['f_type'] = 'cate';
                                });
                                $scope.cate_data = updateBadgesAndReview(cat_data)
                            }else{
                                $scope.cate_data = updateBadgesAndReview(result.cat_data);
                            }
                            //add type in bage
                            if(typeof badges!="undefined"){
                                angular.forEach(badges, c=>{
                                    c['f_type'] = 'badge';
                                });
                            }
                        }catch(er){
                            console.log;
                        }
                                         
                        $scope.product_Items = result.detail.data;
                        /*$scope.cate_data = result.cat_data;*/
                        $scope.pagination.totalItems = result.detail.total;
                        //$scope.filter_action.attrbute_results = result.badges && updateBadgesAndReview(result.badges) || [];

                        $scope.filter_action.attrbute_results = (typeof badges!="undefined")? updateBadgesAndReview(badges) : [];

                        $scope.filter_action.price_flag =  result.price_flag || false,
                        $scope.varModel.no_result_found = !1;
                        animate_top();
                       
                        //show hide pagination (item selection, sorting by , order by and bottom pagination)
                        $scope.pagination.show_hide_pagination = ($scope.product_Items.length <= $scope.pagination.itemsPerPage) ? !0 : !1;
                        //in case page is load then check and set pre selected attributes
                        if (pageLoad) setPreviousSelectedAttribute();
                    } else {
                        $scope.product_Items = [];
                        $scope.pagination.totalItems = 0;
                        $scope.varModel.no_result_found = !0;
                        $scope.pagination.show_hide_pagination = !0;
                    }
                }, function(error) {
                    //error handler here
                })
                .finally(function() {
                    showHideLoader('hideLoader'); /*$scope.loader.loadingMore = !1;*/
                });
        };

        //listen to update data of badge && review && getBadges(result.badges)
        function updateBadgesAndReview(data, ftype){
            angular.forEach(data, item=>{
                item['checked'] = false;
                let indx = $scope.filter_action.filter_list.findIndex(o=> o._id == item._id && item.f_type == o.f_type);
                // getIndex($scope.filter_action.filter_list, item._id, '_id')
                if(indx!=-1) item.checked = true;
            });
            return data;
        };

        /*
        *@desc : add popup for add to cart
        */
        function addToCartModalHandler($elem, item, flag){
            $elem.modal('show');
            //show hide element 
            if(atc_action === 'addtocart'){
                $elem.find('.modalcartadd').show();
                $elem.find('.modalcartbuy').hide();
            }else if(atc_action === 'buynow'){
                $elem.find('.modalcartadd').hide();
                $elem.find('.modalcartbuy').show();
            }
            let min_qty = 1;
            if(item.min_order_qty > 0){
                min_qty = item.min_order_qty;
            }
            $elem.find('.product-name').text(item.category.category_name);
            $elem.find('.price-box .price').text(item.unit_price+' '+lang_baht+'/'+item.package_name);
            $elem.find('.spiner .spinNum').val(min_qty); 
            //add attribute 
            $elem.find('.spiner .spinNum').attr('minqty', item.min_order_qty);  

            $elem.find('.show-unit').text(item.package_name); 
            $elem.find('.prd-image').attr('src', item.thumbnail_image); 
            $elem.find('.filled-stars').css('width', parseInt(item.avg_star)*20+'%'); 
            $rootScope.temp_prd = angular.copy(item);
            // $elem.find('.modal-dialog').attr('data-product', angular.toJson(item));
            $elem.find('.addtocart').attr('data-actiontype', flag);
            $elem.find('.prod-standard .size label').text(' : '+item.badge.size);
            $elem.find('.prod-standard .quality label').text(' : '+item.badge.grade);
            $elem.find('.prod-standard .la img').attr('src',  $elem.find('.prod-standard .la img').data('basepath')+item.badge.icon);
        };

        /****
        *@desc : This function call on click on add to cart button and check cases if valid the call addtocart function
        * all main product id and check before add to cart all product have selected attribue
        *@param :  @event : (event)
        *@parm :  @strflag :(string)
        * ******/
        $scope.addToCartHandler = function($event, strflag, prd, action) {
            $event.stopPropagation();
            prd['product_type'] = 'normal';          
            let prd_info=[prd]; 
            //prd.quantity = 100; 
            atc_action = strflag;   
            if(!action){
                addToCartModalHandler(angular.element(document.getElementById('add_to_cart_modal')), prd, strflag);
                return;
            }
            
            var cartObj = beforeCartCheck($scope.productLayoutView, strflag, prd_info, prd, null, null);
            
            if(cartObj.gotocart === "no") return;

            var cartData = getCartData(cartObj.query);
            _enbdsbLodBtn('enable',true);


            ////////////////////////////////////////
            salesfactoryData.getData(prd.shopping_url, 'POST', {
                "cat_id": prd.cat_id,'item_id':prd._id,'badge_id':prd.badge_id
            })
            .then(function(response) {
                addToCart(strflag, cartData);
                // if(response.data.status=="no_shopping_list"){
                //     swal({
                //       input: 'text',
                //       title:text_create_shopping_list,
                //       text: text_shopping_list_name,
                //       confirmButtonColor: '#3085d6',
                //       cancelButtonColor: '#d33',
                //       confirmButtonText: text_save_btn,
                //       cancelButtonText: txt_no,
                //       showCancelButton: true,
                //       inputValidator: (value) => {
                //         return new Promise((resolve, reject)=>{
                //             if(!value) reject(text_you_need_to_write_shopping_list_name);
                //             else resolve(value);
                //         });
                //       },
                //     }).then((result) => {
                //         salesfactoryData.getData(prd.shopping_url, 'POST', {
                //         "cat_id": prd.cat_id,'item_id':prd._id,'badge_id':prd.badge_id,"shopping_list_name":result
                //         }).then(function(resp) {
                //             addToCart(strflag, cartData);
                //             // if(resp.data.redirect_url!=''){
                //             //     location.href = resp.data.redirect_url;
                //             // }else{
                //             //     _toastrMessage(resp.data.status, resp.data.message);
                //             // }
                //         },err=>{
                //             console.log;
                //         });                         
                //     },err=>{
                //             console.log;
                //     });
                // }else{
                //     addToCart(strflag, cartData);    
                // }
            }, function(error) {
                _error();
            }).finally(()=>{
                _enbdsbLodBtn('disabled',false);
            });
            ////////////////////////////////////////




            //send data to server 
            ///////////     addToCart(strflag, cartData);  ///////////////////
            // salesfactoryData.getData(checkProductBeforeCart, 'POST', cartData)
            // .then(function (response){
            //     if(typeof response === "undefined" || response.data === null || response.xhrStatus === "error") return;
            //     if(response.data.status === 'success')
            //         addToCart(strflag, cartData);
            //     else swal("", response.data.msg, "error");                
            // }, function (){
            //     swal('Opps', error_msg.server_error,'error');                                
            // })
            // .finally(function (){
            //    _enbdsbLodBtn('disabled',false);
            // });
        };


        $scope.addToShoppinglistHandler = function($event, item){
            $event.stopImmediatePropagation();
            _enbdsbLodBtn('disabled',true);
            salesfactoryData.getData(item.shopping_url, 'POST', {
                "cat_id": item.cat_id,'item_id':item._id,'badge_id':item.badge_id
            })
            .then(function(response) {
                if(response.data.status=="no_shopping_list"){
                    swal({
                      input: 'text',
                      title: text_create_shopping_list,
                      text: text_shopping_list_name,
                      confirmButtonText: text_save_btn,
                      confirmButtonColor: '#3085d6',
                      cancelButtonColor: '#d33',
                      cancelButtonText: txt_no,
                      showCancelButton: true,
                      inputValidator: (value) => {
                        return new Promise((resolve, reject)=>{
                            if(!value) reject(text_you_need_to_write_shopping_list_name);
                            else resolve(value);
                        });
                      },
                    }).then((result) => {
                        salesfactoryData.getData(item.shopping_url, 'POST', {
                        "cat_id": item.cat_id,'item_id':item._id,'badge_id':item.badge_id,"shopping_list_name":result
                        }).then(function(resp) {
                            if(resp.data.redirect_url!=''){
                                location.href = resp.data.redirect_url;
                            }else{
                                _toastrMessage(resp.data.status, resp.data.message);
                            }
                        },err=>{
                            console.log;
                        });                         
                    },err=>{
                            console.log;
                    });
                }
                _toastrMessage(response.data.status, response.data.message);
            }, function(error) {
                _error();
            }).finally(()=>{
                _enbdsbLodBtn('disabled',false);
            });
        };

        /*
         *@desc : Listen on add wishlist
         *@param : $event {event}
         *@param : item {object}
         */
        $scope.addToWishlist = function($event, item) {
            $event.stopImmediatePropagation();
            _enbdsbLodBtn('disabled',true);
            salesfactoryData.getData(addIntoWishlist, 'GET', {
                "product_id": item._id
            })
            .then(function(response) {
                if (response.data.status !== undefined && response.data.status == "success") {
                    item['wish'] = item._id;
                    item['in_wishlist'] = !0;
                    _toastrMessage(response.data.status, response.data.message);
                }
                if(response.data.status == "unsuccess"){
                	swal({
                        type: 'error',
                        text: response.data.message,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: text_ok_btn,
                    });
                }
            }, function(error) {
                _error();
            }).finally(()=>{
                _enbdsbLodBtn('disabled',false);
            });
        };

        /*
         *@desc : Listen on remove wishlist
         *@param : $event {event}
         *@param : item {object}
         */
        $scope.removeFromWishlist = function($event, item, p_index) {
            $event.stopImmediatePropagation();
            swal({
                title: are_you_sure,
                text: text_want_to_remove_product_from_wishlist,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: txt_no,
                confirmButtonText: text_confirm_btn
            }).then(function(){
                _enbdsbLodBtn('disabled',true);
                salesfactoryData.getData(removeFromWishlist, 'GET', {
                    "product_id": item._id
                })
                .then(function(response) {
                    if (response.data.status !== undefined && response.data.status == "success") {
                        item['wish'] = null;
                        item['in_wishlist'] = !1;
                        _toastrMessage(response.data.status, response.data.message);
                        //in case of wishlist page remove product also from user list
                        if(typeof page_type!="undefined" && page_type === 'user_wishlist'){
                            $scope.product_Items.splice(p_index, 1);
                            $scope.varModel.no_result_found = $scope.product_Items.length === 0 && true || false;
                        }
                    }
                }, function(error) {
                    _error();
                }).finally(()=>{
                    _enbdsbLodBtn('disabled',false);
                });
            }).catch(function(reason){
                //alert("The alert was dismissed by the user: "+reason);
            });
            return ;

            
        };
        
        //Listen on pagination change 
        $scope.loadNext = function(page) {
            //call get data function with query string
            pushQueryString();
        };
        //Listen on item per page change by user
        $scope.changeItemPerPage = function($evt, item) {
            $scope.pagination.itemsPerPage = item;
            $scope.pagination.label = item;
            $scope.pagination.currentPage = 1;
            //call get data function with query string
            pushQueryString();
        };

        //Listen on layout change (mean grid to list & list to grid)
        $scope.prdLayoutManage = function(value) {
            $scope.productLayoutView = value;
        };

        //Listen to change order
        $scope.changeOrder = function($evt, item) {
            $evt.preventDefault();
            $scope.orderBy = item.name;
            $scope.orderLabel = item.value;
            order = item.by;
            pushQueryString();
        };

        //Listen to change product image on click of product thumb
        $scope.changeProductImage = function($event, item, pitem){
            $event.preventDefault();
            pitem.thumbnail_image = item;           
        };

        /*listen to redirect product to product detail page */
        $scope.redirectToProductPage = function(itemUrl){
            window.location.href = itemUrl;
        };

        /*
        *@ngdoc
        *@desc :Listen on url query string change
            there are two case 
            1. when page is load (In case fromState is empty)
            2. when route will navigate (means browser forward & backward)            
        **/
        $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams) {           
            // Use $state.params to update as needed
            var ft = $scope.filter_action;            
            try {
                //in case toParams have page 
                if (toParams.page) {
                    $scope.pagination.currentPage = toParams.page;
                }
                //in case toParams have item_page (means item per page)
                if (toParams.item_page) {
                    var p = _getIndex($scope.pagination.item_option_arr, toParams.item_page);
                    if (p != -1) {
                        $scope.pagination.itemsPerPage = $scope.pagination.item_option_arr[p];
                        $scope.pagination.label = $scope.pagination.item_option_arr[p];
                    }
                }
                //in case toParams have order by & order 
                if (toParams.order_by && toParams.order) {
                    $scope.orderBy = toParams.order_by;                    
                    var odr = _getIndex($scope.shortData, $scope.orderBy, "name");
                    if (odr != -1)
                        $scope.orderLabel = $scope.shortData[ord]['value'];
                }
                //in case toParams have filter attribute or collection id
                if ((toParams.filter_by || toParams.cid) && (toParams.filter_by.length || toParams.cid.length)) {
                    routeParamFilterHandler(toParams);
                }
            } catch (e) {
                console.log;
            }          
            //get filter data
            $scope.loadData();
        });
    };

    //This directive used to lazy load image
    function  jqLazyLoad($timeout){
        return {
            restict : 'AC',
            link : function(scope, element, attrs){
                $timeout(function() {
                    jQuery(element).lazyload({
                        effect: "fadeIn",
                        effectspeed: 500,
                        skip_invisible: false
                    });
                }, 500);
            },
        };
    };

    //Listen to add addModalDirective 
    function addModalDirective($timeout, $rootScope){
        return{
            restrict : 'A',
            link : function(scope, elem, attrs){
                jQuery(elem).find('.addtocart').bind('click', function(evt){
                    let d = $rootScope.temp_prd;
                        d['quantity'] = parseInt(jQuery(elem).find('.spinNum').val());
                    scope.$evalAsync(()=>{
                        scope.addToCartHandler(evt, atc_action, d, 'action');
                    });
                    jQuery(elem).parents('#add_to_cart_modal').modal('hide');
                });
                //increase/decrease 
                jQuery(elem).find('.increase').bind('click', function(){
                    let v = parseInt(jQuery(elem).find('.spinNum').val());
                    jQuery(elem).find('.spinNum').val(v+1);
                });
                jQuery(elem).find('.decrease').bind('click', function(){
                    let v = parseInt($(elem).find('.spinNum').val());
                    let min_qt = parseInt(elem.find('.spinNum').attr('minqty'))
                    if(v==0 && min_qt == 0){
                      jQuery(elem).find('.spinNum').val('1');
                      return  
                    }else if(v==0 && min_qt>0){
                      jQuery(elem).find('.spinNum').val(min_qt);
                      return  
                    }else if(v<=min_qt && min_qt>0){
                        jQuery(elem).find('.spinNum').val(min_qt);
                        return;
                    }
                    jQuery(elem).find('.spinNum').val(v-1);
                });
                //change 
                jQuery(elem).find('.spinNum').bind('change', function(){
                    let v = parseInt(jQuery(elem).find('.spinNum').val());
                    let min_qt = parseInt(elem.find('.spinNum').attr('minqty'))
                    if(!v && min_qt == 0){
                      jQuery(elem).find('.spinNum').val('1');
                      return  
                    }else if(!v && min_qt>0){
                      jQuery(elem).find('.spinNum').val(min_qt);
                      return  
                    }
                });

            },
        };
    };

    angular.module('smm-app')
        .controller('ProductListController', ['$scope', 'salesfactoryData', '$window', '$timeout', '$rootScope', '$state', '$interval', 'dataManipulation', controllerFunction])
        /*.controller('ProductListController', ['$scope', 'salesfactoryData', '$window', '$timeout', '$rootScope', '$interval', controllerFunction])*/
        .directive('jqLazy', ['$timeout', jqLazyLoad])
        .directive('addModalDir', ['$timeout', '$rootScope', addModalDirective]);
})(window.angular);

/*
*@Description : Listen on toastr message display 
*@param : status (string) like - seccuss/error
*@param : message (string)
*/

function _toastrMessage(status, message){
  try{
    Command: toastr[status](message);
  }catch(err){
    console.log;
  };  
};  

//Toaster option setting for message display
try{
    toastr.options = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": false,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "9000",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut",
  };
}catch(e){
  if(e instanceof ReferenceError)
    console.log;
}

/* isNumberKey
    Only allows NUMBERS to be keyed into a text field.
    @environment ALL
    @param evt - The specified EVENT that happens on the element.
    @return True if number, false otherwise.
*/
function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    // Added to allow decimal, period, or delete
    if (charCode == 110 || charCode == 190 || charCode == 46) 
        return true;
    
    if (charCode > 31 && (charCode < 48 || charCode > 57)) 
        return false;
    
    return true;
} // isNumberKey