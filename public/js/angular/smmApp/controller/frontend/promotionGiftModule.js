/*
*@Description : This controller used to handel all product detail function
*@Author :  Smoothgraph Connect PVT LTD.
*@Created At : 06/011/2018
*/

(function(angular){
    function promotionGiftHandler($scope,salesfactoryData,$filter,$window,$timeout,$sce, dataManipulation){
        //variable define section
        var rvCtrl = this;
        rvCtrl.attrRes = [];
        rvCtrl.attrValRes = []; 
        rvCtrl.selAttrVal = [];
        rvCtrl.productVideo=[];
        rvCtrl.friends = [];
        var shareObjInfo ={};
        var file_name ='';
        rvCtrl.messageShow = false;
        rvCtrl.senddropdown = true;
        rvCtrl.sendshare = false;
        rvCtrl.contactMessage ='';
        rvCtrl.activeimgprevtab=true;
        rvCtrl.totalQtyVal = [];
        rvCtrl.file_interface={};
    	rvCtrl.curCode = curCode;
    	rvCtrl.colorPath = colorPath;
        rvCtrl.loading = {
            "disableBtn" : false,
            "btnloaderpath" : btnloaderpath,
            "addTocart_and_bynow" : false,
        };
        
        /*==check view and get and set custom data ==*/     
        //used for store all information of product
        var prd_info =[];
        //In case if all product have quantity zero(0)
        rvCtrl.soldoutFlag = false;
       /* var customData  = dataManipulation.constructDataList(productData.productInfo, productData.oneProductInfo.prdType, productData.attrres);*/
        /*    rvCtrl.soldoutFlag = customData.total_soldOut;
            prd_info = customData.product_info;
            rvCtrl.totalQtyVal = customData.total_qty;       
        var attr_json = customData.attr_json;     */ 
        /*end ==============================*/

    rvCtrl.productData = productData;

    //pagination config 
    rvCtrl.pagination ={
    	currentPage : 0,
    	pageSizes : 2,
    };
       
        
        // rvCtrl.productInfo = productData.productInfo;
        // rvCtrl.attrRes = productData.attrres;
        // rvCtrl.attrValRes = productData.attrValRes;
        // rvCtrl.selAttrVal = productData.selectedAttrVal;
        // rvCtrl.selAttrValDetails = productData.selectedAttrValDetails;
        // rvCtrl.varientType = productData.varientType;
        // rvCtrl.spcfAttrVal = productData.sattrValRes;

        //get total number of product data count
        rvCtrl.getProductTotalPageCount = (totalFilteredPrdCount) => {
          	var count = Math.ceil(totalFilteredPrdCount/rvCtrl.pagination.pageSizes*1.0);
          	return count;
        };

        /*
        *@Description :this function used for change attribute
        *@param : index {int} -> product index
        *@param : attrId {int} -> attribute id
        *@param : mainPrdId {int} -> main product id
        *@param : prdDetail {object} -> current product prdId
        *@param : pIndex {int} -> current product id
        *@param : atr_id {int} -> current product id
        */
        
        rvCtrl.changeContent = function(index, attrId, mainPrdId, prdDetail, pIndex, atr_id, prdData){ 
            _enbdsbLodBtn('enable',true);
            console.log(prdData); 
            // return;           
            var prd_type = prdData.productInfo[0].product_type;

            var dataObj ={
                'mainProductId' : mainPrdId,
                'attrVal' : rvCtrl.selAttrVal[mainPrdId],
                'currentAttrId' : attrId,
                'currentAttrValId' : rvCtrl.selAttrVal[mainPrdId][index].valId,
                'product_type' : prd_type,              
            };           

            salesfactoryData.getData(getVarientProduct,'POST',dataObj)
            .then(function(r){
                var data = r.data;

                if((data.status!== undefined && data.status=== "success") && Object.keys(data).length>0){
                    rvCtrl.cmobsArr = data.attributeArr;
                    /*product data will change after all variant become selected */
                    if(data.prd_data_status!== undefined && data.prd_data_status === "change"){
                        var qtm = 0;
                        // rvCtrl.productImg = data.productImage;                    

                        //add custom props
                        var d =  dataManipulation.onePrdGetterSetter(data.productInfo, prd_type, qtm); 
                        prdData.productInfo[0] = data.productInfo
                        prdData.productInfo[0] = d;                     
                        rvCtrl.totalQtyVal[pIndex] = qtm;
                    }                   
                   
                    //test code  for variant combination
                    var atrkeyArr = Object.keys(rvCtrl.cmobsArr);
                    var attrValArr = _.flatMap(rvCtrl.cmobsArr);
                    console.log("here")
                    dataManipulation.combinationMap(prdData.attrValRes[mainPrdId], atrkeyArr, attrValArr);
                    //end                                   
                }else{
                    //unsucess error case;
                    _enbdsbLodBtn('disabled',false);
                    swal('Opps','Something wrong','error');
                    rvCtrl.selAttrVal[mainPrdId] = null;                    
                }
            }, function(error){
                try{throw new Error("Something went badly wrong!");}
                catch(e){
                    _enbdsbLodBtn('disabled',false);                
                    swal('Opps','Something wrong','error');
                }
            }).finally(function(){
                _enbdsbLodBtn('disabled',false);               
            });
        };

        //this function used for get array combination
        rvCtrl.getCombArr = function(cmbarr){
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

        //this function used to check quantity length in case of add to cart
        function checkQuantityLength(prdLength){
            var count = 0,
                i=0,
                tq ='';

            for(; i<prdLength; i++){
                tq = rvCtrl.totalQtyVal[i];
                if(!isNaN(tq) && tq === 0){
                    count++;
                }               
            }
            return count;
        };

        //check data condition is attribute or quatity selected or not
        var cartValidationCheck = (function(prdData){
        	return new Promise(function(resolve, reject){
        		var product_type = prdData.productInfo[0].prdType,
                    mainprdid = prdData.productInfo[0].mainProductId,
                    prdLength = prdData.productInfo.length;
                //check product validation
				if(product_type!== undefined && product_type === "configrable"){                      
				    if(!_.isUndefined(rvCtrl.selAttrVal[mainprdid])){
				        var cbLength = _.flatMap(rvCtrl.selAttrVal[mainprdid]).length;
				        var attr_count = _.flatMap(prdData.attrres[mainprdid]).length;
				        if(cbLength != attr_count){
				            swal("Opps..",'Please Select Attribute in All Product','warning');
				            reject("fail");
				        }else resolve("success");
				    }else{
						swal("Opps..",'Please Select Attribute First!','warning');
						reject("fail");
				    }                               
				}else if(product_type!== undefined && product_type === "normal"){                   
				    if(checkQuantityLength(prdLength) === prdLength){                        
				        swal("Opps..",'Please Select at least one quantity  ','warning');
				        gotocart = "no";
                        reject("fail");
				    }else resolve("success");
				}
        	});
        });

        /****
        * This function call on click on add to cart button and check cases if valid the call addtocart function
        * all main product id and check before add to cart all product have selected attribue
        * @event : (event)
        * @strflag :(string)
        * ******/
        rvCtrl.addToCartHandler = function($event, prdData, strflag) {
            $event.stopPropagation();  

            cartValidationCheck(prdData).then(function(resolved){
                console.log(prdData); 
            	//sucess csae
            	_enbdsbLodBtn('enable',true);
            	var dataObj=[];
            	dataObj.push({
            		"productId" : prdData.productInfo[0].id,
                    "quantity" : 1,
            		//"quantity" : prdData.quantity,
            		"promotion_id" : prdData.promotion_id,
            		"action_from" : "action_from_promotion",
            	});

                console.log(dataObj);

            	//check quantity in stocke
            	salesfactoryData.getData(checkProductBeforeCart, "POST", dataObj)
	            .then(function(r) {
	                if(r.data==='100'){
	                    addToCart(strflag, prdData);
	                }else{
	                    _enbdsbLodBtn('disabled',false);
	                    swal("", r.msg, "error");
	                    return;
	                }
	            },function(error){console.log;})
	            .finally(function(){
	                _enbdsbLodBtn('disabled',false);
	            });
            }, function(error){
            	//reject case
            	console.log("//reject case");
            	return;
            }).catch(function(err){
            	//
            	console.log("//catch case");
            });           
        };
            
        //Listen after check quantity availabel in store
        function addToCart(strflag, prdData) {
        	var product_type = prdData.productInfo[0].prdType,
                mainprdid = prdData.productInfo[0].mainProductId,
                tmpSelAtt = _.flatMap(rvCtrl.selAttrVal[mainprdid]);
            var optionDataArr = {};
            var attrValIdObj =[];   

            //get all selected attribute as per as main product id
			_.forEach(prdData.attrres,function(currentValue, currentIndex) {			   
			    if(!_.isUndefined(tmpSelAtt) && tmpSelAtt.length>0 && currentIndex==mainprdid){
			        _.forEach(currentValue, function(cVal,cInd){
			            tmpSelAtt[cInd].attribute_id = cVal.attribute_id;
			        });
			    }
			});

            //create data for add to cart
            var dataObj =[{
            	productId : prdData.productInfo[0].id,
            	mainproductid : mainprdid,
                quantity : 1,
                order_id:order_id,
            	//quantity : prdData.quantity,
                promotion_id : prdData.promotion_id,
            	optionId : [],
            	optionValueId :[],
            	optionIdDetail :[],
            	attrDetail : tmpSelAtt,
            }]; 



            
            //go add to cart
            salesfactoryData.getData(addProductToCart, 'POST', dataObj)
            .then(function(response){
                _enbdsbLodBtn('disabled',false);
                //console.log(cartUrl + strflag);

                if (response.data.success === "success" && (_.isUndefined(strflag) ||  strflag=='action_from_promotion')) {
                  var totalCartProduct = $('#totalCartProduct').html();       
                  var totalNew = Number(totalCartProduct)+1;       
                 // jQuery('#totalCartProduct').html(totalNew);
                  //jQuery('#addToCartdiv').modal('show');
                  //console.log(cartUrl);
                  window.parent.location.href=cartUrl;
                }else if(response.data.success === "success" && !_.isUndefined(strflag) && strflag==="buynow"){
                    console.log(cartUrl);
                    window.parent.location.href=cartUrl;
                }

                if(!_.isUndefined(response.data.success) && response.data.success !== "success" )
                    //'Please check your network connection'
                    swal('Opps',response.data.message,'error');
                return false;
            },function(error){
                swal('Opps','Something wrong','error');
            }).finally(function(){
            	_enbdsbLodBtn('disabled',false)
            });
        };      
        
        /*
        *private method to add tooltip html for all variant style type color image
        *but if have value(means text) then not generate  
        */      
        var getTooltipContent = function(){
            $.map(rvCtrl.attrValRes, function(item){                
                $.map(item, function(elem){                 
                    if(elem!== undefined && angular.isArray(elem) && elem.length){
                        $.map(elem, function(el) {
                            if(el.value!== undefined && el.color_code!== undefined && el.color_image!== undefined){
                                if(el.color_image){
                                  el.tooltip = $sce.trustAsHtml('<div class="varient-tooltip"><image src="'+rvCtrl.colorPath+el.color_image+'" alt="'+el.color_image+'" width="50" height="50"></div>');
                                }else if(el.color_code!== undefined && el.color_code){
                                  el.tooltip = $sce.trustAsHtml('<div class="varient-tooltip"><span style="background :'+el.color_code+'; width: 50px; height: 50px; display:inline-block"></span></div>');
                                }
                            }                   
                        });
                    }           
                });
            });     
        };

        //self excute if in case have attrValRes 
        if(!angular.isArray(rvCtrl.attrValRes)){
            getTooltipContent();
        }     
       
        
        /**This function used for convert file into base64
        * @fileObj : (object)
        **/
        function getBase64Url(fileObj) {
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
        *Listen after click on addtocart or buy now for enable/disable loader/button
        *@param : string (enable/diable)
        *@param : Boolean 
        */
        function _enbdsbLodBtn(strflag,btnFlag){
            if(!_.isUndefined(strflag) && strflag!==''){
                rvCtrl.loading['addTocart_and_bynow'] = (strflag==='enable')? true : false;             
            }
            rvCtrl.loading['disableBtn'] = btnFlag;
        };
    };

    //start from filter handler;
    function startFromHandler(){
    	return function(input, start) {
	        start = +start; //parse to int
	        return input.slice(start);
	    };
    };

    //init module & controller
    angular.module('promotionGiftApp', ['jsonseivice'], function($interpolateProvider){
	    $interpolateProvider.startSymbol('<%');
	    $interpolateProvider.endSymbol('%>');
	});
    //inject dependency
    promotionGiftHandler.$inject = ['$scope','salesfactoryData','$filter','$window','$timeout','$sce', 'dataManipulation'];
    angular.module('promotionGiftApp').controller('promotionGiftCtrl', promotionGiftHandler);

    angular.module('promotionGiftApp').filter("startFrom", startFromHandler);

   
})(angular, undefined);


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
};