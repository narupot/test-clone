/*
*@desc : This controller used to handel all product detail's functionality.
*@author : SMOOTHGRAPH CONNECT PVT LTD.
*@Created At : 06/011/2018
*@version : sg.0.1
*/

(function (angular, undefined){
    
    "use strict";
    var atc_action;

    var productDetailController = function ($scope, salesfactoryData, $filter, $window, $timeout, $location, $anchorScroll, $rootScope, $sce, dataManipulation,  $interval, productService){
        //scope variable section 
        var rvCtrl = this;
        //product image's slider setting
        // rvCtrl.prd_view ={
        //     "images" : productData.productImage,
        //     "videos" : productData.productVideo,
        //     "thumb_type" : (productData.oneProductInfo.prdType === 'normal' || productData.oneProductInfo.prdType === 'configrable') ? pageSetting.image.thumb_type : pageSetting.main_image.thumb_type,
        //     "badges" : prd_badge,
        // };
        rvCtrl.productInfo = productData || {};
        //.productInfo; 
        // rvCtrl.attrRes = productData.attrres;
        // rvCtrl.orderInfo = (Object.keys(productData.orderInfo).length !== 0) ? productData.orderInfo : '';
        // rvCtrl.attrValRes = productData.attrValRes; 
        // rvCtrl.selAttrVal = productData.selectedAttrVal;
        // rvCtrl.productReview = productData.userProductReview;
        // rvCtrl.optionInfo = productData.optionInfo;
        // rvCtrl.optnameArr = productData.optnameArr;
        // rvCtrl.ptag = productData.productTags;
        // rvCtrl.spcfAttr = productData.sattrRes;
        // rvCtrl.spcfAttrVal = productData.sattrValRes;
        // rvCtrl.contactProduct = productData.contactProduct;
        // rvCtrl.starModal= rvCtrl.productInfo.userProductRating;
        // rvCtrl.feedbackForm = (rvCtrl.starModal) ? false : true;   
        // rvCtrl.friends = [];
        // rvCtrl.contactMessage ='';
        // rvCtrl.activeimgprevtab=true;
        rvCtrl.totalQtyVal = [];
        rvCtrl.file_interface={};
        rvCtrl.loading = {
            "disableBtn" : false,
            "btnloaderpath" : "",/*btnloaderpath,*/
            "addTocart_and_bynow" : false,
        };
        rvCtrl.totalPrice = 0; 
        rvCtrl.soldoutFlag = false; 
        rvCtrl.productLayoutView = "";
        // rvCtrl.prdBlogUrl = prdBlogUrl;     
        // rvCtrl.curCode = curCode;
        // rvCtrl.colorPath = colorPath;
        // rvCtrl.oneProductInfo = productData.oneProductInfo;
        // rvCtrl.selAttrValDetails = productData.selectedAttrValDetails;
        // rvCtrl.varientType = productData.varientType; 
        // rvCtrl.pageSetting = pageSetting;
        // $scope.pageSetting = pageSetting;
        /*************
        *@desc : related & recent product config 
        */       
        // rvCtrl.relatedProductConfig = {
        //     itemPerPage : 10,
        //     currentPage : 1,
        //     totalItems : 0,
        //     data : false,
        //     slidesToShow : 4,
        //     autoplay: true,            
        //     responsive: [                                           
        //         {
        //           breakpoint: 991,
        //           settings: {
        //               slidesToShow: 3,
        //           }
        //         },
        //         {
        //           breakpoint: 767,
        //           settings: {
        //               slidesToShow: 2,
        //           }
        //         },
        //         {
        //           breakpoint: 480,
        //           settings: {
        //               slidesToShow: 1,
        //           }
        //         }
        //     ],
        // };

        // rvCtrl.recentProductConfig = {
        //     itemPerPage : 10,
        //     currentPage : 1,
        //     totalItems : 0,
        //     data : false,
        //     slidesToShow : 4,
        //     autoplay: true,            
        //     responsive: [                                           
        //         {
        //           breakpoint: 991,
        //           settings: {
        //               slidesToShow: 3,
        //           }
        //         },
        //         {
        //           breakpoint: 767,
        //           settings: {
        //               slidesToShow: 2,
        //           }
        //         },
        //         {
        //           breakpoint: 480,
        //           settings: {
        //               slidesToShow: 1,
        //           }
        //         }
        //     ],
        // };      

        //private variable section        
        var prd_info = [];
        //for product selction array on selection
        var matrixPrdSelection = [];        
        var customData = null;
        // var secData =[{"name" : "relatedProductConfig", "url" : related_prd_url, "data" : {product_id : rvCtrl.oneProductInfo.id} }];
        //{"name" : "recentProductConfig", "url" : recent_viewed_prd_url, "data" : {product_id : rvCtrl.oneProductInfo.id}}
        var currentOptionValue =[]; 
        /************************ function section ******************/

        /*************** private function section *******/
        /*
        *@desc : enable/disable loader/button
        *@param : strflag {string (enable/diable)}
        *@param : btnFlag {boolean} 
        */
        var _enbdsbLodBtn = function (strflag,btnFlag){
            rvCtrl.loading['addTocart_and_bynow'] = (strflag && strflag==='enable')? true : false;
            rvCtrl.loading['disableBtn'] = btnFlag;
        };

        /*
        *@desc : add tooltip html for all variant style type color image
        *   but if have value(means text) then not generate  
        */
        var getTooltipContent = function(){
            _.forEach(rvCtrl.attrValRes, function(item){                
                _.forEach(item, function(elem){                 
                    if(elem && angular.isArray(elem)){
                        _.map(elem, function(el) {
                            if(el.color_image){
                              el.tooltip = $sce.trustAsHtml('<div class="varient-tooltip"><image src="'+rvCtrl.colorPath+el.color_image+'" alt="'+el.color_image+'" width="50" height="50"></div>');
                            }else if(el.color_code){
                              el.tooltip = $sce.trustAsHtml('<div class="varient-tooltip"><span style="background :'+el.color_code+'; width: 50px; height: 50px; display:inline-block"></span></div>');
                            }                                               
                        });
                    }           
                });
            });     
        };

        /*
        *@desc : invoke to get related & recent product data 
        *@param : secName {string}
        *@param : url {string}
        *@param : data {object}
        */  
        var getRelatedRecentProduct = function (secName, url, data){
            salesfactoryData.getData(url, 'POST', data)
            .then(function (res){
                if(typeof res === "undefined" || res.data === null || res.xhrStatus === "error") return;
                var result = res.data;
                // rvCtrl.relatedPrdData = result;  
                if(result.status!== undefined && result.status === "success"){
                    if(result.data!== undefined && angular.isArray(result.data) && result.data.length){
                        rvCtrl[secName].data = true;
                        rvCtrl[secName].itemPerPage =   result.no_items;
                        rvCtrl[secName].totalItems =    result.data.length;
                        $scope.product_Items = getBadgeStyle(result.data);
                    }           
                }else{
                    rvCtrl[secName].data = false;
                    rvCtrl[secName].itemPerPage =   10;
                    rvCtrl[secName].totalItems =    0;
                }
            }, function (err){
                console.log;
            });
        };

        /**
        *@desc : get product review
        **/
        var getProductReview = function(){
            let query = {"product_id" : rvCtrl.productInfo[0]['id']};
            
            salesfactoryData.getData(PRODUCT_REVIEW_URL, 'POST', angular.toJson(query))
            .then((response)=>{
                if(typeof response === "undefined" || response.data === null || response.xhrStatus === "error") return;
                if(response.data.status && response.data.status ==="success" && response.data['data'].length){
                    rvCtrl.productReview = response.data['data'];
                }else rvCtrl.productReview = [];
            }, (err)=>{
                console.log;
            });            
        };


        /****
        *@desc : This function used to init data in related variable on controller init
        ****/
        var init = function (){
            //view setting (0 - for list & 1 for grid(means matrix or table) view)
            // productLayoutView = (!isNaN(productLayoutView)) ? parseInt(productLayoutView) : 0;

            // if(productLayoutView === 0){
                // rvCtrl.productLayoutView = "list";
                customData = dataManipulation.constructDataList(productData, 'normal', productData.attrres);
                rvCtrl.soldoutFlag = customData.total_soldOut;
                prd_info = customData.product_info;
                rvCtrl.totalQtyVal = customData.total_qty;  
                //add product info in product services
                productService.setProduct(rvCtrl.productInfo);
                // console.log(customData);
                // if(rvCtrl.oneProductInfo.prdType === "bundle")
                //     totalPriceCalculation(customData.products, rvCtrl.oneProductInfo.prdType);                
                //garbage variable need to check used 
                var attr_json = customData.attr_json; 
            // }else if(productLayoutView === 1){
            //     rvCtrl.productLayoutView = "grid";
            //     customData = dataManipulation.costructDataTabular(productData.tableView, productData.oneProductInfo.prdType, productData.productInfo, productData.varientType);
            //     matrixPrdSelection = customData.tabularData;
            //     rvCtrl.soldoutFlag = customData.total_soldOut;                
            //     prd_info = customData.product_info;
            //     rvCtrl.totalQtyVal = customData.total_qty;   
            //     if(rvCtrl.oneProductInfo.prdType === "bundle")
            //         totalPriceCalculation(customData.products, rvCtrl.oneProductInfo.prdType);            
            //     //construct column setting (replace id field to selection)
            //     _.forEach(productData.tableView, function (item){
            //         if(!_.isArray(item) && item.data!==undefined){
            //            item['header'][0]['field'] = "selection";
            //             item['header'][0]['displayName'] = ""; 
            //         }                    
            //     });
            //     rvCtrl.tableViewData = productData.tableView;               
            // }

            //check if any product have default select attribute then set 
            // $timeout(function(){ 
            //     var attr_res_arr = _.flatMap(rvCtrl.attrRes),
            //         atrLen = attr_res_arr.length,
            //         prdLen = prd_info.length;
            //     var selectdAtr = _.flatMap(productData.attributeArr);

            //     if(atrLen > 0 && prdLen > 0 && selectdAtr.length){               
            //         _.forEach(prd_info, function(elem){
            //             rvCtrl.cmobsArr = productData.attributeArr[elem.mainprdid];                   
            //             var atrkeyArr = Object.keys(rvCtrl.cmobsArr);
            //             var attrValArr = _.flatMap(rvCtrl.cmobsArr);
            //             dataManipulation.combinationMap(rvCtrl.attrValRes[elem.mainprdid], atrkeyArr, attrValArr);
            //         });
            //     }
            // });

            //get related & recent product
            // for(var s of secData){
            //     getRelatedRecentProduct.apply(rvCtrl, [s.name, s.url, s.data]);
            // };

            //in case have attrValRes 
            if(rvCtrl.attrValRes) getTooltipContent();

            //set error message 
            if(typeof errorMessageJson!="undefined" && typeof errorMessageJson == "object")
                errorMessageService.setMessage(errorMessageJson);

            //get poduct review 
            //if(productData.oneProductInfo.prdType == 'normal' || productData.oneProductInfo.prdType == 'configrable') getProductReview();
        };
        //call init on controller init
        init();

        /**This function used for convert file into base64
        * @fileObj : (object)
        **/
        var getBase64Url = function (fileObj) {
             return new Promise(function(res, rej){
                 try{
                    reader = new FileReader();
                    reader.readAsDataURL(fileObj);
                    reader.onloadend = function(){
                        res({
                            filename: fileObj.name,
                            filecontent: reader.result,
                            base64 : true,
                        });
                    };
                 }
                 catch(err){
                     rej(err);
                 }
             });
        };

        /*
        *@desc : This function used for get all attribute field id
        *@param : @arr :(type [])
        *@param :  @prdid : (type int)
        ***/
        var getFieldId = function (arr,mainPrdId,flag) {
            var tempArr = [],
                tempObj ={};
            _.forEach(arr, function(value,key){
                if(!_.isUndefined(value[mainPrdId]) && value[mainPrdId]!=''){
                    if(!_.isUndefined(flag) && flag==="optDetailchange"){
                        tempObj[key]= value[mainPrdId];
                    }
                    else{
                        tempArr.push(parseInt(key));
                    }
                }
            });
            /***get all uploaded file id fro droplet file 
            * @optionId      :(int option  id)
            * @productId     :(int product id) 
            * @productindex  :(int product index)
            ****/
            if(!_.isUndefined(flag) && (flag==='optchange' || "optDetailchange") && !_.isEmpty(rvCtrl.file_interface)){
                var uploadimgId = [];
                var uplodimgprdid =0;
                _.map(rvCtrl.file_interface.getFiles(), function(fileObj){
                    var optid = parseInt(fileObj.optionId),
                        prodid = parseInt(fileObj.mainPrdId);
                    if(fileObj.file.type && fileObj.file.type!==4){
                        if(prodid == mainPrdId && uploadimgId.indexOf(optid)<0){
                            if(flag==="optchange"){
                                uplodimgprdid = prodid;
                                uploadimgId.push(optid);
                            }else if(flag==="optDetailchange"){
                                tempObj[optid] = fileObj.file.name;
                            }
                        }
                    }
                });
                if(uplodimgprdid == mainPrdId){
                    tempArr= tempArr.concat(uploadimgId);
                }
            }
            return (!_.isUndefined(flag) && flag==="optDetailchange") ? tempObj : tempArr;
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
            // console.log(response);     
            if(response.qtcheck){
                swal(error_msg.quantity_error, 'warning');
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
            return {'productId' : cartData.id,'quantity' : cartData.quantity};
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
                // console.log('function addToCart called with:', strflag, response);
                let $instock =  document.getElementById('instock');
                angular.element($instock).html(response.data.product_quantity);
                if(typeof response === "undefined" || response.data === null || response.xhrStatus === "error") return;
                if(response.data.status && response.data.status ==="success"){
                    if(strflag === "buynow"){
                        window.location.href = cartUrl;
                        return;
                    } 
                    // angular.element(document.getElementById('totalCartProduct')).html(response.data.cart_quantity);
                    // angular.element(document.getElementById('addToCartdiv')).modal('show');
                    let $add_to_cart_modal = document.getElementById('addToCartdiv'),
                        $cart_quantity= document.getElementsByClassName('tot_prd_noti'),
                        $cart_price = document.getElementById('totalCartPrice'),
                        /*$user_cart_a = angular.element($cart_quantity).parent(),
                        $user_cart_icon = angular.element($cart_quantity).prev(),*/
                        $bargaining =  document.getElementById('tot_bar_noti'),
                        $wating_for_payment = document.getElementsByClassName('tot_prd_noti'),
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

                     swal({
                        type: "success",
                        title: "ทำรายการสำเร็จแล้ว",
                        text: "ท่านได้เพิ่มสินค้าในหน้าตะกร้าสินค้าเรียบร้อยแล้ว"
                    }).then(() => {
                        // window.location.reload();
                    });

                }
                else if(response.data.status && response.data.status ==="check_qty_stock"){
                     swal('', response.data.msg,'warning'); 
                }
                else if(response.data.status && response.data.status ==="stock_zero"){
                     swal('', response.data.msg,'error'); 
                } 
                else if(response.data.status && response.data.status ==="zero"){
                     swal('', response.data.msg,'error'); 
                } 
                else if (response.data.status && response.data.status ==="fail"){
                     swal('', response.data.msg,'error');
                }
                else if (response.data.status && response.data.status ==="price_changed"){
                     swal('', response.data.msg,'error');  
                     window.location.reload();
                }else{
                    swal('Opps', error_msg.server_error,'error');                               
                }

            }, function (err){
                swal('Opps', error_msg.server_error,'error'); 
            })
            .finally(function () {_enbdsbLodBtn('disabled',false)});
        };

        //this function used for get array combination
        var getCombArr = function(cmbarr){
            var cmbArr =[];
            _.forEach(cmbarr, function(item){
                if(item.length!==undefined && item.length>1){
                    for(var i=0;i<item.length;i++){ 
                        var v = parseInt(item[i]); 
                        if(cmbArr.indexOf(v)<0) 
                            cmbArr.push(v);
                    }
                }else if(item.length!==undefined && item.length===1){
                 var v = parseInt(item);
                 if(cmbArr.indexOf(v)<0)
                    cmbArr.push(v);
                }
                if(item.valId!==undefined){
                 var v = parseInt(item.valId);
                 if(cmbArr.indexOf(v)<0)
                    cmbArr.push(v);
                }
            });
            return cmbArr;
        };

        /************** end ******************/

        //this function used for get price by option
        rvCtrl.getPriceByOption = function($event, str, prdId, index, qty_zero){ 
            var cqty = (qty_zero !== undefined || typeof qty_zero!= "undefined") ? qty_zero : rvCtrl.totalQtyVal[index];
            var mainPrdId = _.filter(rvCtrl.productInfo, function(o){return o.id == prdId})[0].mainProductId; 
            //get all option and option value id 
            var valId = (rvCtrl.optionFieldId!= undefined) ? getFieldId(rvCtrl.optionFieldId,mainPrdId,'optchange') : [];
            var chkVal = (rvCtrl.optionValueCheck!= undefined) ? getFieldId(rvCtrl.optionValueCheck,mainPrdId,'valchange') : [];        
            var query = {
                'productId' : prdId,
                "quantity" : cqty,
                "optionValueId" : chkVal,
                "optionId" : valId
            };

            salesfactoryData.getData(productPriceByOption, 'POST', query)
            .then(function(resp) { 
                if(typeof resp === "undefined" || resp.data === null || resp.xhrStatus === "error") return;
                rvCtrl.productInfo[index].productPrice = resp.data;                              
            },function(error){
               swal('Opps', error_msg.server_error,'error');                               
            })
            .finally(function (){ _enbdsbLodBtn('disabled',false);});
        };

        /*********** image attribute upload section ******/
        // Listen for when the file interface has been configured.
        $scope.$on('$dropletReady', function whenDropletReady() {
            if(rvCtrl.file_interface.allowedExtensions){
                rvCtrl.file_interface.allowedExtensions(['png','jpeg','jpg', 'bmp', 'gif', 'svg']);
                rvCtrl.file_interface.useArray(true);
                rvCtrl.file_interface.setRequestUrl(uploadAction);
            }           
        });
        // Listen for when the files have been successfully uploaded.
        $scope.$on('$dropletSuccess', function onDropletSuccess(event, response, files) {
            // $scope.uploadCount = files.length;
           //  $scope.success     = true;
           //  $timeout(function timeout() {
           //      $scope.success = false;
           //  }, 5000);
        });

        //Listen to add image in droplet
        $scope.$on('$dropletFileAdded', function onDropletSuccess(event,fileObje) {
            if(fileObje.type & rvCtrl.file_interface.FILE_TYPES.INVALID){
                swal('opps', errorMessageService.getMessage('file_extension_not_valid'), 'error')
            }else if(rvCtrl.file_interface.FILE_TYPES.VALID){
                //Listen when file is valid then upload to server
                getBase64Url(fileObje.file)
                .then(function(obj){
                    salesfactoryData.getData(uploadAction,'POST',{'upload_path':upload_path,'uploadfile': obj})
                    .then(function(resp){
                        if(typeof resp === "undefined" || resp.data === null || resp.xhrStatus === "error") return;
                        if(resp.data.status==="success")
                            rvCtrl.getPriceByOption(event,'tqc',parseInt(fileObje.productId),parseInt(fileObje.productIndex));
                    }, function(error){console.log;});
                }).catch(function(){console.log;})
            }
        });

        /*************** scope function **********/
        $scope.scrollToReview = function(event){
            // set the location.hash to the id of
            // the element you wish to scroll to.
            $location.hash('reviewBox');
            // call $anchorScroll()
            $anchorScroll();
        };

        rvCtrl.addToShoppinglistHandler = function($event, item){
             $event.stopImmediatePropagation();
            _enbdsbLodBtn('disabled',true);
            //console.log(item);
            salesfactoryData.getData(item.shopping_url, 'POST', {
                "cat_id": item.cat_id,'item_id':item._id,'badge_id':item.badge_id
            })
            .then(function(response) {
                //console.log(response);
                if(response.data.status=="no_shopping_list"){
                    swal({
                      input: 'text',
                      title:'+ Create shopping list',
                      text: 'Shopping list name',
                      confirmButtonText: 'Save',
                      showCancelButton: true,
                      inputValidator: (value) => {
                        return new Promise((resolve, reject)=>{
                            if(!value) reject('You need to write shopping list name!');
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
        *@desc : this function used for change attribute
        *@param : index {int} -> product index
        *@param : attrId {int} -> attribute id
        *@param : mainPrdId {int} -> main product id
        *@param : prdDetail {object} -> current product prdId
        *@param : pIndex {int} -> current product id
        *@param : atr_id {int} -> current product id
        */
        rvCtrl.changeContent = function(index, attrId, mainPrdId, prdDetail, pIndex, atr_id){ 
            _enbdsbLodBtn('enable',true);
            currentOptionValue = [];
            currentOptionValue = getCombArr(rvCtrl.attrValRes[mainPrdId][atr_id]);
            var prd_type = prd_info[0].product_type;
            var query ={
                'mainProductId' : mainPrdId,
                'attrVal' : rvCtrl.selAttrVal[mainPrdId],
                'currentAttrId' : attrId,
                'currentAttrValId' : rvCtrl.selAttrVal[mainPrdId][index].valId,
                'product_type' : prd_type,              
            };

            salesfactoryData.getData(getVarientProduct, 'POST', query) 
            .then(function (response) {
                if(typeof response === "undefined" || response.data === null || response.xhrStatus === "error") return;
                var data = response.data;
                if(data.status && data.status === "success"){
                    rvCtrl.cmobsArr = data.attributeArr;
                    /*product data will change after all variant become selected */
                    if(data.prd_data_status!== undefined && data.prd_data_status === "change"){
                        var qtm = 0;
                        if(prd_type==='bundle'){
                           qtm = 0;                     
                        }else if(prd_type === "configrable"){
                           qtm = 1;
                           rvCtrl.prd_view['images'] = data.productImage;
                        }
                        //add custom props
                        var d =  dataManipulation.onePrdGetterSetter(data.productInfo, prd_type, qtm);                
                        rvCtrl.productInfo[pIndex] = data.productInfo
                        rvCtrl.productInfo[pIndex] = d;                     
                        rvCtrl.totalQtyVal[pIndex] = qtm;
                    }

                    //test code  for variant combination
                    var atrkeyArr = Object.keys(rvCtrl.cmobsArr);
                    var attrValArr = _.flatMap(rvCtrl.cmobsArr);
                    dataManipulation.combinationMap(rvCtrl.attrValRes[mainPrdId], atrkeyArr, attrValArr);
                    //check all product sold out
                    rvCtrl.soldoutFlag = (dataManipulation.soldout(productData.productInfo) === (productData.productInfo).length) ? true : false;
                }else {
                    swal('Opps', error_msg.server_error,'error');
                    rvCtrl.selAttrVal[mainPrdId] = null; 
                }
            }, function (err){
                swal('Opps', error_msg.server_error,'error');                               
            })
            .finally( function (){
                _enbdsbLodBtn('disabled',true);
            });

        }

        /****
        *@desc : This function call on click on add to cart button and check cases if valid the call addtocart function
        * all main product id and check before add to cart all product have selected attribue
        *@param :  @event : (event)
        *@parm :  @strflag :(string)
        * ******/
        rvCtrl.addToCartHandler = function($event, strflag) {
            $event.stopPropagation();
            //console.log('in roemal add handler', prd_info);            
            var cartObj = beforeCartCheck(rvCtrl.productLayoutView, strflag, prd_info, rvCtrl.productInfo, rvCtrl.selAttrVal, rvCtrl.tableViewData);
            
            if(cartObj.gotocart === "no") return;

            var cartData = getCartData(cartObj.query);
            _enbdsbLodBtn('enable',true);
            //send data to server 
            addToCart(strflag, cartData);
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

            let $add_to_cart_modal = document.getElementById('addToCartdiv');
            setTimeout(function() {
                angular.element($add_to_cart_modal).modal('hide');
            }, 2000);
        };

        //this function used to set rating of product
        $scope.setReview = function(event, data) {
            rvCtrl.starModal = data.rating;
        };

        /**
        *@desc : Listen to add wishlist
        *@param : $event 
        *@param : item {object}
        **/
        rvCtrl.addToWishlist = function($event, item){           
            $event.stopImmediatePropagation();                      
            salesfactoryData.getData(addIntoWishlist,'GET', {"product_id" : item.id})
            .then(function(response){ 
                if(typeof response === "undefined" || response.data === null || response.xhrStatus === "error") return;
                if(response.data.status && response.data.status == "success"){                    
                    item['wish'] = item._id;
                    item['in_wishlist'] = !0;
                    rvCtrl.productInfo.in_wishlist = !0;
                    _toastrMessage(response.data.status, response.data.message);
                    if('redirect_url' in response.data){
                        window.setTimeout(function(){
                            // Move to a new location or you can do something else
                            return window.location.href = response.data.redirect_url;
                        }, 2000);
                    }
                }                       
            },function(error){
                swal('Opps', error_msg.server_error,'error');                               
            });
        };       

        /**
        *@desc : Listen to remove wishlist
        *@param : $event 
        *@param : item {object}
        **/        
        rvCtrl.removeFromWishlist = function($event, item) {
            $event.stopImmediatePropagation();

            salesfactoryData.getData(removeFromWishlist, 'GET', {"product_id" : item.id})
            .then(function(response){
              if(typeof response === "undefined" || response.data === null || response.xhrStatus === "error") return;
              if(response.data.status && response.data.status == "success"){
                item['wish'] = null;
                item['in_wishlist'] = !1;
                rvCtrl.productInfo.in_wishlist = !1;
                _toastrMessage(response.data.status, response.data.message);
              } 
            },function(error){
                swal('Opps', error_msg.server_error,'error');                               
            });
        };

        /*
        *@desc : this function used to handle add product for compare 
        *@param : prdData {object}
        **/

        rvCtrl.addToCompare = function($evt, prdData){            
            if(!prdData) return;           
            $evt.preventDefault();

            salesfactoryData.getData(PRODUCT_COMPARE_URL, 'POST', {"product_id" : prdData.id})
            .then(function(response){
                if(typeof response === "undefined" || response.data === null || response.xhrStatus === "error") return;
                if(response.data.status){
                   _toastrMessage(response.data.status, response.data.mesg);
                } 
            }, function(err){
                //error handle here
            })
            .finally(function(){
                //
            });
        };        

        /***
        *@desc  : quantiy increase decrease
        *@param : $event
        *@param : valMax
        *@param : str
        *@param : prdDetail 
        *@param : index
        ***/
        rvCtrl.incDcrQuntity = function($event, valMax, str, prdDetail, index){
            
            $event.stopPropagation();     
            /*in case of min_order_qty user can't decrease less than min_order_qty*/
            /*if(parseInt(prdDetail.min_order_qty)>=0 && parseInt(prdDetail.quantity)<=parseInt(prdDetail.min_order_qty)){
                prdDetail.quantity = prdDetail.min_order_qty;
            }
            */  
       
            var res = dataManipulation.quantityHandler(valMax, str, prdDetail, prd_info[0].product_type);
            if(res && res.status === "success"){
                
                var qty_zero;
                if(prdDetail.quantity == 0) qty_zero = 1;
                //update price model array and call price function 
                rvCtrl.totalQtyVal[index] = prdDetail.quantity;
                //in case of matrix view (grid view)
                // if(rvCtrl.productLayoutView === 'grid'){
                //     var index = _.findIndex(matrixPrdSelection, {"id" : prdDetail.id});                
                //     if(index>=0){                    
                //         matrixPrdSelection[index]['quantity'] = prdDetail.quantity;
                //     }
                // }                
                // _enbdsbLodBtn('enable',true);
                // rvCtrl.getPriceByOption($event, str, prdDetail.id, index, qty_zero);

                //update price calculation model for total price               
                var cpindx = _.findIndex(customData.products, {"product_id" : prdDetail.id});
                if(cpindx >= 0 && prdDetail.quantity === '0'){                 
                    customData.products[cpindx].quantity = prdDetail.quantity;
                     swal('', "คุณไม่สามารถเพิ่มสินค้าในตะกร้าได้เนื่องจากสต๊อกไม่เพียงพอ" , 'warning'); 
                    // if(rvCtrl.oneProductInfo.prdType === "bundle")
                    //    totalPriceCalculation(customData.products, rvCtrl.oneProductInfo.prdType);
                }   

            }else{
                prdDetail.quantity = parseInt(prdDetail.quantity ?? 0);
                valMax = parseInt(valMax ?? 0);
                prdDetail.stock = String(prdDetail.stock ?? '1');

                if (!prdDetail.quantity || prdDetail.quantity === 0) {
                    swal('', res.msg, 'warning');
                    prdDetail.quantity = 1;
                }
                else if (prdDetail.stock == '0' && prdDetail.quantity >= valMax) {
                    swal('', res.msg, 'warning');
                }
                else {
                    swal('', res.msg, 'warning');
                }
     
            }
        };

        /*****
        *@desc : related product section
        ******/
        $scope.removeFromWishlist = function($event, item){
            rvCtrl.removeFromWishlist($event, item);
        };
        $scope.addToWishlist = function($event, item){
            rvCtrl.addToWishlist($event, item);
        };
        $scope.addToCompare = function($evt, prdData){
            rvCtrl.addToCompare($evt, prdData);
        };
        //Listen to change product image on click of product thumb
        $scope.changeProductImage = function($event, item, pitem){
            $event.preventDefault();
            pitem.thumbnail_image = item;           
        };

        let clearTimeOut,
            imgArray = [];
        $scope.count = 0;

        $scope.changeHoverImage = function($event, item) {
            $event.stopImmediatePropagation();
            $scope.over_img_src = '';

            if (item.images && item.images.length) {               
                imgArray = [];
                imgArray = angular.copy(item.images);
                imgArray.push(item.thumbnail_image);
                item["list_slide"] = angular.copy(imgArray);
                let imgLen = imgArray.length;                
                clearTimeOut = $interval(function() {
                    if (angular.isUndefined(clearTimeOut)) {                       
                        $interval.cancel(clearTimeOut);
                    }

                    $scope.over_img_src = '';
                    $scope.over_img_src = imgArray[$scope.count];                     
                    $scope.count++;
                    //In case count reach to image length
                    if ($scope.count === imgLen) {
                        $scope.count = 0;
                    }
                }, 1500);
            }
        };
        //Listen on mouse leave
        $scope.mouseLeaveHandler = function($event) {
            $event.preventDefault();
            if (angular.isDefined(clearTimeOut)) {
                $interval.cancel(clearTimeOut);
                $scope.over_img_src = '';
                imgArray = [];
                $scope.count = 0;
                clearTimeOut = undefined;
            }
        };
        //Listen on scope destory and clear time out
        $scope.$on('$destroy', function() {
            // Make sure that the interval is destroyed too
            $interval.cancel(clearTimeOut);
            $scope.over_img_src = '';
            imgArray = [];
            $scope.count = 0;
            clearTimeOut = undefined;
        });

        /*listen to redirect product to product detail page */
        $scope.redirectToProductPage = function(itemUrl){
            window.location.href = itemUrl;
        };

        /*
        *@desc : get inline style for product badges
        */        
        function getBadgeStyle(productItems){ 
            //get image height & width 
            let regpx = /(px|%)/i;         
            let cpImage = new Image();
                //cpImage.src = scope.defaultImg.large;
            angular.forEach(productItems, (prdItem)=>{
                angular.forEach(prdItem.prd_badge, (item)=>{
                    let cpW = (399/3).toFixed(2),
                        cpH = (360/3).toFixed(2),
                        percent = (item.category_lavel_size) ? parseInt(item.category_lavel_size) : 0;
                        // console.log(item.category_lavel_size);
                    if(percent){
                        cpW = ((cpW*percent)/100).toFixed(2);
                        cpH = ((cpH*percent)/100).toFixed(2);
                    }

                    item['badge_style']={
                        'width' : cpW+'px',
                        'height' :cpH+'px',
                        'background' : 'url('+item.image+') 0px 0px no-repeat',
                    };
                    //for text
                    if(item.badge_desc && item.badge_desc.category_lavel_text){
                        let fts = (regpx.test(item.category_text_size)) ? item.category_text_size : item.category_text_size+'px';
                        item['text_style']= item.category_text_style+'color:'+item.category_text_color+'; font-size:'+fts+';';                       
                    }
                });
            });
            return productItems;
        };

        /********* end related product section ***********/


        /*****
        *@desc : Product total price calulation (only bundle & with pre enavle setting)
        *@param : product_type {string} (only for bundle)
        ******/
        function totalPriceCalculation(productData, product_type){
            if(product_type!='bundle') return 0;
            _enbdsbLodBtn('enable', true);
            let query = _.filter(productData, (obj)=> {
                obj['currency_id'] = rvCtrl.oneProductInfo.currency_id;
                return (obj.quantity>0 && !obj.sold_out); 
            });

            salesfactoryData.getData(productPriceByQuantity, 'POST', angular.toJson(query))
            .then((response)=>{
                if(typeof response === "undefined" || response.data === null || response.xhrStatus === "error") return;
                if(response.data.status && response.data.status == "success")  rvCtrl.totalPrice = response.data.amount || 0;
            }, (err)=>{
                console.log;
            }).finally(()=>{
               _enbdsbLodBtn('enable', false);
            });            
        };       
    };

    /*******
    *@desc : This controller used to handle product review 
    *
    ********/
    var reviewController = function($scope, salesfactoryData, $filter, $timeout, $sce, productService, dataManipulation, $rootScope){
        let rv = this;
        rv.pagination = {
            totalItems: 0,
            itemsPerPage: 10,
            currentPage: 1,
            maxPageSize : 10,
            no_more : false,
        };        
        rv.review_data = [];
        rv.order_history = [];
        //Loader setting 
        $scope.loader = {
           /* loadingMore: !1,*/
            /*loaderImg: btnloaderpath,*/
            addtocart: !1,
            disableBtn: !1,
            img_load : 'data:image/gif;base64,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',
        };
        let prdInfo = productService.getProduct();/* get product info from services */    

        //controller on init function    
        (function init(){
            angular.element('.product-info-detail a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
              /*  e.target // newly activated tab
                e.relatedTarget // previous active tab*/
                if(e.target.getAttribute('href') === '#review') !rv.review_data.length && getReviewData();
                //get order history
                if(e.target.getAttribute('href') === '#ord-history') !rv.order_history.length && getOrderHistoryData();
            });
            //related product
            relatedProduct(); 
        })();
        //Listen to get product review data 
        function getReviewData(flag){
            salesfactoryData.getData(getAllReviews, 'POST', {
                'product_id' : prdInfo && prdInfo['id'] || null,
                'page' : rv.pagination.currentPage,
                'item_per_page' : rv.pagination.itemsPerPage,
            })
            .then(resp=>{
                if(typeof resp === "undefined" || resp.data === null || resp.xhrStatus === "error") return;
                let res = resp.data;                
                if(res.status === 'success' && res.data['data'].length){
                    res.data.total && parseInt(res.data.total)>rv.pagination.currentPage && rv.pagination.currentPage++;
                    // rv.review_data = _.concat(rv.review_data, res.data['data']);
                    if(flag && flag === 'load_more') rv.review_data = _.concat(rv.review_data, res.data['data']);
                    else rv.review_data = res.data['data'];
                    rv.pagination.no_more = (res.data.total && parseInt(res.data.total)>rv.review_data.length) ? !0 : !1;
                    return;
                }
                rv.review_data = [];
                rv.pagination.no_more = !1;
            }, err=>{
                console.log();
            }).finally(()=>{
                //end ajax
            });
        };

        rv.loadMore = (evt)=>{
            evt.preventDefault();
            getReviewData('load_more');
        };

        /******** Related product sections *********/
        rv.related_product_config = {
            data : false,
            slidesToShow : 4,
            autoplay: true,            
            responsive: [                                           
                {
                  breakpoint: 991,
                  settings: {
                      slidesToShow: 3,
                  }
                },
                {
                  breakpoint: 767,
                  settings: {
                      slidesToShow: 2,
                  }
                },
                {
                  breakpoint: 480,
                  settings: {
                      slidesToShow: 1,
                  }
                }
            ],
        };
        function relatedProduct(){
            salesfactoryData.getData(getRelatedProducts, 'GET', {'product_id' : prdInfo && prdInfo['id'] || null})
            .then(resp=>{
                if(typeof resp === "undefined" || resp.data === null || resp.xhrStatus === "error") return;
                var result = resp.data;
                if(result.status!== undefined && result.status === "success"){
                    if(result.detail!== undefined && angular.isArray(result.detail.data) && result.detail.data.length){
                        rv.related_product_config.data = true;                        
                        $scope.product_Items = result.detail.data;
                    }           
                }else{
                    rv.related_product_config.data = false;
                };
            }, err=>{
                console.log;
            });
        };/************* end related **************/
        /*
        *@desc : get cart data 
        *@cartData {object}
        */
        var getCartData = function getCartData(cartData){ 
            return {'productId' : cartData.id || cartData._id,'quantity' : cartData.quantity};
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
            // console.log(response);     
            if(response.qtcheck){
                swal("Opps..", error_msg.quantity_error, 'warning');
                response.gotocart = "no";
            }
            // else if(response.vtcheck){
            //     swal("Opps..", errorMessageService.getMessage('please_select_attribute_in_all_product'), 'warning');
            //     response.gotocart = "no";
            // }
            return response;
        };

        /************ get order history *************/
        function getOrderHistoryData(){
            salesfactoryData.getData(getOrderHistory, 'GET', {'product_id' : prdInfo && prdInfo['id'] || null})
            .then(resp=>{
                if(typeof resp === "undefined" || resp.data === null || resp.xhrStatus === "error") return;
                let res = resp.data; 
                if(res.status === 'success' && res.data.length){
                    rv.order_history = _.concat(rv.order_history, res.data);
                    return;
                }
                rv.order_history = [];
            }, err=>{
                console.log;
            });
        };
        /********** end ************/
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
                        $wating_for_payment = document.getElementsByClassName('tot_prd_noti'),
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
                     swal('', response.data.msg,'error'); 
                }else{
                    swal('Opps', error_msg.server_error,'error');                               
                }
            }, function (err){
                swal('Opps', error_msg.server_error,'error'); 
            })
            .finally(function () {_enbdsbLodBtn('disabled',false)});
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
            $elem.find('.product-name').text(item.category.category_name);
            $elem.find('.price-box .price').text(item.unit_price+' Baht/Box');
            $elem.find('.spiner .spinNum').val(1);   
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
            //prd.quantity = 1; 
            atc_action = strflag;    
            if(!action){
                addToCartModalHandler(angular.element(document.getElementById('add_to_cart_modal')), prd, strflag);
                return;
            }         
            var cartObj = beforeCartCheck($scope.productLayoutView, strflag, prd_info, prd, null, null);
            
            if(cartObj.gotocart === "no") return;

            var cartData = getCartData(cartObj.query);
            _enbdsbLodBtn('enable',true);
            //send data to server 
            addToCart(strflag, cartData);
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
        };   

        $scope.addToShoppinglistHandler = function($event, item){
             $event.stopImmediatePropagation();
            _enbdsbLodBtn('disabled',true);
            //console.log(item);
            salesfactoryData.getData(item.shopping_url, 'POST', {
                "cat_id": item.cat_id,'item_id':item._id,'badge_id':item.badge_id
            })
            .then(function(response) {
                //console.log(response);
                if(response.data.status=="no_shopping_list"){
                    swal({
                      input: 'text',
                      title:'+ Create shopping list',
                      text: 'Shopping list name',
                      confirmButtonText: 'Save',
                      showCancelButton: true,
                      inputValidator: (value) => {
                        return new Promise((resolve, reject)=>{
                            if(!value) reject('You need to write shopping list name!');
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
    };

    /******
    *@desc : This services used to share data b/w both controller 
    *******/
    var productService = function(){
        let prd = {};
        //add product information in prd    
        let setProduct = (prdData)=>{
            angular.extend(prd, prdData);
        };
        //return product infromation 
        let getProduct = ()=>{
            return prd;
        };
        return {
            setProduct : setProduct,
            getProduct : getProduct, 
        };
    };

    // Product Listing Thumbnails slider
    function applySlickSlider($timeout){
        return {
            restrict : 'A',
            link : (scope, element, attrs)=>{
                $timeout(()=>{
                    if($(element).hasClass('slick-initialized'))
                        $(element).slick('unslick');  
                    $(element).slick(scope.rv.related_product_config);
                });
            },
        };
    };

    //Listen to add addModalDirective 
    function addModalDirective($timeout, $rootScope){
        return{
            restrict : 'A',
            link : function(scope, elem, attrs){
                $(elem).find('.addtocart').bind('click', function(evt){
                    let d = $rootScope.temp_prd;
                        /*$(elem).data('product');*/
                        d['quantity'] = parseInt($(elem).find('.spinNum').val());
                    scope.$evalAsync(()=>{
                        scope.addToCartHandler(evt, atc_action, d, 'action');
                    });
                    $(elem).parents('#add_to_cart_modal').modal('hide');
                });
                //increase/decrease 
                $(elem).find('.increase').bind('click', function(){
                    let v = parseInt($(elem).find('.spinNum').val());
                    $(elem).find('.spinNum').val(v+1);
                });
                $(elem).find('.decrease').bind('click', function(){
                    let v = parseInt($(elem).find('.spinNum').val());
                    if(v==0) return
                    $(elem).find('.spinNum').val(v-1);
                });
            },
        };
    };

    angular.module('smm-app')
        .factory('productService', [productService])
        .controller('productDetailCtrl', ['$scope', 'salesfactoryData', '$filter', '$window', '$timeout', '$location', '$anchorScroll', '$rootScope', '$sce', 'dataManipulation', '$interval', 'productService', productDetailController])
        .controller('reviewController', ['$scope', 'salesfactoryData', '$filter', '$timeout', '$sce', 'productService', 'dataManipulation', '$rootScope', reviewController])
        .directive('slickSliderDir', ['$timeout', applySlickSlider])
        .directive('addModalDir', ['$timeout', '$rootScope', addModalDirective]);
})(angular);

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
    "positionClass": "toast-top-center",
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