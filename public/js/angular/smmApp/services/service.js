"use strict";
(function(){
	angular.module('jsonseivice',[])
		.factory("salesfactoryData", ['$q', '$http', '$window','$rootScope', AjaxServiceHandler])
		.factory("httpLoaderInterceptor", ['$rootScope', httpLoaderHandler])
		.service('dataManipulation', DataManipulationHandler)
		.service('commanFun', CommanServiceHandler);

	/*
	*@description :
	**/
	function AjaxServiceHandler($q, $http, $window, $rootScope){
		var JsonData = {};
		//Listen on getDate for one request
		JsonData.getData = function(url,methodType,obj) {
			var methodType = methodType || 'POST';
			var deferred = $q.defer();
			if(methodType ==='POST'){
				$http({
				  method : methodType,
				  url : url,
				  data : obj
				}).then(function(data) {
				  deferred.resolve(data);
				},function(data, status, headers, config) {
				  deferred.reject(status);
				});
				return deferred.promise;
			}else if(methodType ==='GET'){
			    $http({
				  method : methodType,
				  url : url,
				  params : obj
				}).then(function(data) {
				  deferred.resolve(data);
				},function(data, status, headers, config) {
				  deferred.reject(status);
				});
				return deferred.promise;
			}else if(methodType ==='DELETE'){
			    $http({
				  method : methodType,
				  url : url,
				  params : obj
				}).then(function(data) {
				  deferred.resolve(data);
				},function(data, status, headers, config) {
				  deferred.reject(status);
				});
				return deferred.promise;
			}else if (methodType === 'PUT') {
                $http({
                    method: methodType,
                    url: url,
                    params: obj,
                }).then(function(data) {
                    deferred.resolve(data);
                }, function(data, status, headers, config) {
                    deferred.reject(status);
                });
                return deferred.promise;
            }
	  	};

	  	//Listen on send all request 
	  	JsonData.getAllData=function(urlarr,methodType,obj){
	  		var promises = urlarr.map(function(item){
	  			var deferred = $q.defer();
	  			$http({
				  method : methodType,
				  url : item,
				  params : obj
				}).then(function(data) {
				  deferred.resolve(data);
				},function(data, status, headers, config) {
				  deferred.reject(status);
				});
				return deferred.promise;
	  		});
	  		return $q.all(promises);
	  	};

	  	//Listen on send file to server
	  	JsonData.uploadFiles = function(_url, method_type, imgObj){
	  		var deferred = $q.defer(),
	  		    formData = new $window.FormData(),
	  		    httpRequest   = new $window.XMLHttpRequest(),
	  		    queuedFiles = imgObj;

	  		 //add custom property
  			queuedFiles.customData = {
  				completed : false, 
  				error : false,
  				inprogress : false, 
  				percent : 0,	  				
  			};

	  		/**
             * @method attachEventListeners
             * @return {void}
             */
            (function attachEventListeners() {
                // Configure the event listeners for the impending request.
                //httpRequest.addEventListener("progress", onProgress);
				httpRequest.addEventListener("load", onComplete);
				httpRequest.addEventListener("error", onFailed);
				httpRequest.addEventListener("abort", onCanceled);

            })();

	  		// Initiate the HTTP request.
            httpRequest.open(method_type, _url, true); 

            //Invoke progress on transfers from the server to the client (downloads)
	  		httpRequest.upload.onprogress = function onProgress(event){
	  			var requestLength = queuedFiles.file.size;
	  			queuedFiles.customData.inprogress = true;

            	if (event.lengthComputable){ 
            		queuedFiles.customData.percent = Math.round((event.loaded / requestLength) * 100) +"%";
            	}
            }.bind(this);
                       

            /**
	        * @method appendCustomData
	        * @return {void}
	        */
            (function appendCustomData() {
            	// Setup the file size of the request.
                httpRequest.setRequestHeader("X-CSRF-TOKEN", window.Laravel.csrfToken);
            })();

            // Iterate all of the valid files to append them to the previously created
            // `formData` object.
           	formData.append("product_image", queuedFiles.file, $window.encodeURIComponent(queuedFiles.file.name));
           
            
          	httpRequest.send(formData);

	  		//Invoked once everything has been uploaded.
	  		function onComplete(evt){	  			
	  			deferred.resolve(httpRequest.responseText);	
	  			imgObj.customData.inprogress = false;  			
	  		}

	  		//In case of error
	  		function onFailed(evt){
	  			deferred.reject(httpRequest.responseText);
	  		}
	  		//In case of abort by user
	  		function onCanceled(evt){
	  			queuedFiles.customData.completed = false;
	  			console.log("The transfer has been canceled by the user.");
	  		}

            return deferred.promise;
	  	};

		return JsonData;
	};

	/*
	*@description : DataManipulationHandler
	**/
	function DataManipulationHandler(){
		var self = this;
		var resultJson = {
			tabularData  : [],
			total_qty : [],
			total_soldOut : false,
			product_info : [],
			attr_json : [],
			products : [],
		};

		/*
		*check its number or not
		*/
		var isNumber = function(str){			
	        var pattern = /^\d+$/;
	        return pattern.test(str);  // returns a boolean	    
		};

		/*
		*@desc : set properties
		    props : 
		    	1. quantity
		    	2. stock
		    	3. soldout	
		@type {normal || bundel || config}	    	
		*/
		var setProps = function(obj, type, value){
			if(typeof obj == "undefined" || typeof type == "undefined") return;
			//There are tow case for quantity (limited || unlimited)
			if(obj.total_quantity!= undefined){
				//unlimited case
				if(!isNumber(obj.total_quantity) && obj.total_quantity.toLowerCase() === "unlimited"){
					obj.sold_out = false;
				}else{
					obj.sold_out = (parseInt(obj.total_quantity) >= 1) ? false : true;
				}

				obj.quantity = value;
				resultJson.total_qty.push(value);
			}			
		};

		var pushData = function(destArray, props1, props2, props3, props4){
			if(typeof destArray == "object"){
				var p = {
				 	"mainprdid" : props1,
				 	"type" : props2,
				 	"product_attribute_count" : props3 || 0,
				 	"product_type" : props4,
				};
				destArray.push(p);				
			}
		};

		/*
        *@desc : private method (used for set custom props in attribute data)
        *@partam : cmb_arr {array} -> array of combination set
        *@param  : curent_elem {object} -> current attr node 
        */  
        var setAttrProps  = function(cmb_arr, curent_elem){
            var val_id = parseInt(curent_elem.valId);
            var index = _getIndex(cmb_arr, val_id);

            if(index>=0){
                curent_elem.disable_attr = false;
            }else {
                curent_elem.disable_attr = true;                
            }
        };

        		
		/**** public function section ***/

		//Listen to check product sold out
		this.soldout = function(prdData){
			// var result = _.filter(prdData, function(p){
			// 	return (p.sold_out!== undefined && p.sold_out == true)
			// });
			// return (result.length);
			return ((prdData.sold_out)? true : false);
		};

		//Listen to construct list data 
		this.constructDataList = function(prdData, type, attrs){
			if(typeof prdData == "undefined" && typeof type == "undefined") return;

			// angular.forEach(prdData, function(item){								
			// 	//set props 
			// 	switch(type){
			// 		case "bundle" : 
			// 			setProps(item, 'bundle', 0);
			// 			break;
			// 		case "configrable" :
			// 			setProps(item, 'configrable', 1);
			// 			break;
			// 		case "normal" :
						setProps(prdData, 'normal', 1);
			// 			break;
			// 	};

			// 	//attr_count this variable use to store count of attribute of every product 
                var attr_count = 0;
   //              if(attrs && attrs[item.mainProductId]){
   //              	attr_count = attrs[item.mainProductId].length;
   //                  var at = {"main_prd_id" : item.mainProductId, "attr_id" : []};
   //                  resultJson.attr_json.push(at);
   //              }
 
				pushData(resultJson.product_info, prdData.mainProductId, 'normal', attr_count, 'normal');
			// 	//for total price implemented
				resultJson.products.push({'product_id' : prdData.id, 'quantity' : prdData.quantity, 'sold_out' : prdData.sold_out});
			// });

			//check total sold out 
			//resultJson.total_soldOut = (self.soldout(prdData) == prdData.length) ? true : false;
			resultJson.total_soldOut = (self.soldout(prdData)) ? true : false;
			return resultJson;
		};

		//this one used for single product props getter and setter
		this.onePrdGetterSetter = function(o, t, v){
			setProps(o, t, v);
			return o;
		};

		/*=============================
		*@quantity increase/decrease 
		*@param : max_val {int/string}-> if string means unlimited else limited
		*@param : str (string) -> flag for action (+-)
		*@param : prdDetail {object} -> current product
		*@param : prd_type {string} -> current product type
		*=============================*/
		this.quantityHandler = function(max_val, str, prdDetail, prd_type){
			var result = {'status' : 'fail'};
			//check prdDetail is object or not
			if(typeof prdDetail == "undefined" || typeof prdDetail != "object") return result;

			var tq = parseInt(prdDetail.quantity);
				max_val = (!isNaN(max_val) && isNumber(max_val)) ? parseInt(max_val) : max_val.toLowerCase();
			
			switch(str){
				case "up":
					if((isNumber(max_val) && tq < max_val) || (!isNumber(max_val) && max_val === "unlimited")){
						//quantity will increase in both case if is limited then up to max_val else  infinity
						prdDetail.quantity = tq + 1;
						result['status'] = 'success';
					}
					break;
				case "down":
					//check product is normal
					if(prd_type && prd_type == "normal" && tq >1 && parseInt(prdDetail.min_order_qty) == 0){
						prdDetail.quantity = tq - 1;
						result['status'] = 'success';
					}
					//in case of min_order_qty 
					else if(prd_type && prd_type == "normal" && tq >1 && parseInt(prdDetail.min_order_qty)>0){
						prdDetail.quantity = (parseInt(prdDetail.min_order_qty) == tq) ? tq : tq - 1;
						result['status'] = 'success';
					}
					//check product is config or bundel
					else if(prd_type && prd_type!="normal" && tq >=1){
						prdDetail.quantity = tq - 1;
						result['status'] = 'success';
					}
					break;
				case "tqchange":
					//limited case 
					if(isNumber(max_val) && tq > max_val)	{
						if(prd_type && prd_type=="normal" && parseInt(prdDetail.min_order_qty) == 0){
							prdDetail.quantity = 1;
							result['status'] = 'success';
						}else if(prd_type && prd_type=="normal" && parseInt(prdDetail.min_order_qty)>0){
							prdDetail.quantity = prdDetail.min_order_qty;
							result['status'] = 'success';
						}else{
							prdDetail.quantity = 0;
							result['status'] = 'success';
						}
					}else if((isNumber(max_val) && tq <= max_val) || (!isNumber(max_val) && max_val === "unlimited")){
						if(prd_type && prd_type=="normal" && tq == 0 && parseInt(prdDetail.min_order_qty) == 0){
							prdDetail.quantity =1;
							result['status'] = 'success';
						}else if(prd_type && prd_type=="normal" && tq == 0 && parseInt(prdDetail.min_order_qty)>0){
							prdDetail.quantity = prdDetail.min_order_qty;
							result['status'] = 'success';
						}else{
							prdDetail.quantity = tq;
							result['status'] = 'success';
						}
					}
					break;
				default :
					break;
			};
			return result;
		};

		/**
		*@Description :  calculate total price of product as per as quantity (only available product)
		*@param : obj {array} ->array of product with id and price 
		* under development
		**/	
		this.calculateTotalPrice = function(){		
			/*let total_price = 0;	
			angular.forEach(price_calculation.products, (obj)=>{
				let qty = (isNumber(obj.quantity)) ? parseInt(obj.quantity) : 0;				
				//in case product have quantity & not sold out
				if(qty!= 0 && !obj.sold_out){					
					let x = (obj.product_price.replace(/,/g, '')),
						r = parseFloat(x).toFixed(2);
					total_price = total_price + Number(r);					
				}
			});

			return total_price.formatMoney(2, '', ',', '.');*/
		};


		/*
		*@desc : before cart check 
		*@action {addtocart || buynow || addtocartall, buynowall}
			1. addtocart means add single product vs buynow
			2. addtocatall means add all product vs buynowall
		*@viewType : (1-list , 2 -grid|| matrix || tabular)
		@return (product - object for add to cart)
		//code is under development
		**/

		(function productCartBlueprint(){
			self.CartModel = function CartModel(){};

			self.CartModel.prototype = {
				checkQuantity : function checkQuantity(prd){
					// return _.filter(prd, (o)=>{
					// 	return (o.quantity && !o.sold_out);
					// });
					return (prd.quantity && !prd.sold_out) ? prd : null;
				},
				checkVariant : function checkVariant(prd, prdInfo, selectedAttrs){
					return _.filter(prd, (o)=>{
						var p = _.find(prdInfo, {"mainprdid" : o.mainProductId}),
							// l = _.flatMap(selectedAttrs[p.mainprdid]).length;
							l = _.filter(selectedAttrs[p.mainprdid], (el)=> el).length;							
						return (!o.sold_out && o.quantity && selectedAttrs[p.mainprdid] && (l === p.product_attribute_count));
					});
				},
				checkSelection : function checkSelection (prd){
					return _.filter(prd, (o)=>{
						return (o.is_selected && !o.sold_out);
					});
				},
				getMatrixConfig : function(mp, matrixPrd){
					var result =[];
					_.map(mp, function(item){
						result = _.concat(result, matrixPrd[item.mainProductId].data);
					});

					return result;
				},
			};
		})();

		/*
		*@viewType {0-list, 1-- grid (matrix)}
		*@action 
		*@prdInfo
		*@prdData 
		*@prdSelectedAttrs
		*@matrixData
		*/

		this.beforeCart = function beforeCart(viewType, action, prdInfo, prdData, prdSelectedAttrs, matrixData){
			var result = {
					"gotocart" : "yes",
					"qtcheck" : !1,
					"vtcheck" : !1,
					"query" : []
				},
				ptype = prdInfo[0]['product_type'];
			var pqt;
			var pvt;
			var model =  new self.CartModel();

			//in case product is simple (both case same)
			if(ptype === "normal"){
				pqt =  model.checkQuantity(prdData);
				if(pqt) result.query = pqt;
				else result.qtcheck = !0;
			}
			//in case product type is config 
			else if(ptype === "configrable"){
				if(viewType === "list"){
					pqt =  model.checkQuantity(prdData);
					pvt = model.checkVariant(prdData, prdInfo, prdSelectedAttrs);
				}else if(viewType === "grid"){	
					pqt = model.checkQuantity(matrixData[prdInfo[0].mainprdid].data);
					pvt = model.checkSelection(matrixData[prdInfo[0].mainprdid].data);
					if(pqt.length) pvt = model.checkSelection(matrixData[prdInfo[0].mainprdid].data);						
					if(pvt.length){
						var tp = model.checkQuantity(pvt);
						pqt = (tp.length == pvt.length) ? tp : [];
					}
				}				
				result.query = (pqt.length && pvt.length) ? pvt : [];
				result.vtcheck = pqt.length && !pvt.length;			
				result.qtcheck = !pqt.length && pvt.length || !pqt.length && !pvt.length;
			}			
			//in case of list view & product type is bundel
			else if(ptype === "bundle"){				
				let np = _.filter(prdData, {"prdType" : "normal"});
				let cp = _.filter(prdData, {"prdType" : "configrable"});
				
				if(viewType === "grid"){
					let npQty = model.checkQuantity(np);
					let mxp = model.getMatrixConfig(cp, matrixData);
					let mxpqt = model.checkQuantity(mxp);
					let mxps = model.checkSelection(mxp);
					//in case call product have quantity zero 
					result.qtcheck = !npQty.length && !mxpqt.length;
					//in case user set quantity in normal product
					if(npQty.length){							
						result.qtcheck = !1;
						result.query = _.concat(result.query, npQty);
					}
					//in case of config product
					if(mxp.length){
						if(mxps.length>mxpqt.length){
							result.qtcheck = !0;
						}else if(mxps.length<mxpqt.length){
							result.vtcheck = !0;
						}

						let rs = (mxps.length && mxpqt.length && !result.vtcheck && !result.qtcheck) ? mxps : [];
						result.query = _.concat(result.query, rs);
					}
				}else if(viewType === "list"){
					let npQty = model.checkQuantity(np);
					let cnfpqt = model.checkQuantity(cp);
					let cnfvt = model.checkVariant(prdData, prdInfo, prdSelectedAttrs);
					//in case call product have quantity zero 
    				result.qtcheck = !npQty.length && !cnfpqt.length;
    				//in case user set quantity in normal product
				    if(npQty.length){             
				      result.qtcheck = !1;
				      result.query = _.concat(result.query, npQty);
				    }

				    //in case of config product
				    if(cp.length){
						if(cnfvt.length>cnfpqt.length){
							result.qtcheck = !0;
						}else if(cnfvt.length<cnfpqt.length){
							result.vtcheck = !0;
						}

						let rs = (cnfvt.length && cnfpqt.length && !result.vtcheck && !result.qtcheck) ? cnfvt : [];
      						result.query = _.concat(result.query, rs);				    	
				    }

				}
			}

			return result;
		};

		//change id to product id for add to cart
		this.changeTabularCartData = function(data){
			var result=angular.copy(data);
			angular.forEach(result, function(elem){
				elem.productId = elem.id;
				elem.quantity = elem.quantity;
				delete elem.id;
				delete elem.quantity;
			});

			return result;
		};

		/*
        *@desc : This function used to set attribute id according to main product id(store all selected attribute id).
        *@param : attrArray {array} -> type json
        *@param : mpid {int} -> main product id
        *@param : atr_id {int} -> current attribute id to set in attribute json array
        *@return : type {int} -> index {0-....}
        */
        this.setCurrentAttrbute = function(attrArray, mpid, atr_id){
            var ind = _getIndex(attrArray, mpid, "main_prd_id");
            if(ind!=-1){
                var _i = _getIndex(attrArray[ind].attr_id, atr_id);
                if(_i==-1) attrArray[ind].attr_id.push(atr_id);
            }
            return ind;
        };

        /*
        *@desc : map all combination using main product id from attrValRes 
         and check all deleted variant and set disable for selection
        *@param : attrValRes {array} -> object of array 
        *@param : atrkeyArr {array} -> all attribute keys[]
        *@param : attrValArr {array} -> all attribute value id      
        */
        this.combinationMap = function(attrValRes, atrkeyArr, attrValArr){          
            var c_key;
            attrValArr = attrValArr.map(Number);
            atrkeyArr = atrkeyArr.map(Number);
            
            angular.forEach(attrValRes, function(elem, key){
                //check attribute is not exist then perform action(means skip current attribute set)
                c_key = parseInt(key);
                if(atrkeyArr.indexOf(c_key)>=0){
                    angular.forEach(elem, function(c_elem){
                        setAttrProps(attrValArr, c_elem)
                    });                 
                }
            });
        };
	};

	/*
    *@Description : commanFun used for declare all comman function
    */
    function CommanServiceHandler() {
        /*
         *@desc - Listen on error
         *@param : errorType ->string->{error | success | warning}
         *@param : errorMsg ->string{errorMsg}
         *@param : erorrHtml ->string {optional}
         */
        this.errorHandler = function(errorType, errorMsg, errorHtml, timer, btnConfig) {
            return new Promise(function(resolve, reject) {
                var SWALOBJ = {
                    showCloseButton: true,
                };

                //error type config
                if (typeof errorType != "undefined") {
                    switch (errorType) {
                        case "error":
                            SWALOBJ["title"] = "Opps..!";
                            SWALOBJ["type"] = "error";
                            break;
                        case "success":
                            SWALOBJ["title"] = "Done..";
                            SWALOBJ["type"] = "success";
                            break;
                        case "warning":
                            SWALOBJ["title"] = "Opps..!";
                            SWALOBJ["type"] = "warning";
                            break;
                        default:
                            break;
                    }
                }

                //error message Config
                if (typeof errorHtml != "undefined" && errorHtml) {
                    var error_html = '';                    
                    angular.forEach(errorHtml, function(error) {
                        error_html += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span>' + error + '</div>';
                    });
                    SWALOBJ["html"] = error_html;
                } else if (typeof errorMsg != "undefined" && errorMsg) {
                    SWALOBJ["text"] = errorMsg;
                }

                //config timer
                if(typeof timer!= "undefined" && timer){
                    SWALOBJ["timer"] = parseInt(timer);
                }

                //button config setup
                if(typeof btnConfig!== "undefined" && angular.isObject(btnConfig)){
                    angular.forEach(btnConfig, function(item){
                        SWALOBJ[item.keys] = item.value;
                    });
                };

                // swal setup 
                swal(SWALOBJ).then(function(done) {
                    resolve(done);
                }, function(isDismiss) {
                    reject(isDismiss)
                });
            }).catch(function(err) {
                return Promise.reject(err);
            });
        };

        /*Listen for get index 
         *@param : destObj (oject/array)
         *@param : matchEle (string)
         *@param : matchType (string -optional)
         */
        this._getIndex = function(destObj, matchEle, matchType) {
            var index = -1;

            destObj.forEach(function(item, indx) {
                if (matchType !== undefined && matchType) {
                    if (item[matchType] == matchEle)
                        index = indx;
                } else {
                    if (item == matchEle)
                        index = indx;
                }
            });
            return index;
        };

        /**
        *Listen to conver array to object
        *@param : arrayData {array} 
        **/

        this.arrayToObject = function(arrayData){
            if(typeof arrayData === "undefined" || !angular.isArray(arrayData)) return;

            var result = arrayData.reduce(function(acc, cur, i) {
                 if(cur!== undefined){
                    acc[i] = cur;
                 }                    
                 return acc;
            }, {});

            return result;
        }; 

        /*
        *Listen to chunk array as per as size
        *@param : array
        *@param : size
        *@param : return (array)
        */
        this.chunkArray = function(arr, size){
            var newArr = [];

            if(!angular.isArray(arr)) return newArr;

            size = parseInt(size);

            for (var i=0; i<arr.length; i+=size) {
                newArr.push(arr.slice(i, i+size));
            }
            return newArr;
        };

        /***
        *Private method
        ***/
        var getError = function(keys, arr){
           return $.map(arr, function(item){
                if(item[keys]!== undefined && item[keys]){
                  return (item[keys][0].en);  
                }               
            })                          
        };

        /*
        *error message handler
        *return string
        **/
        this.errorMessageHandler = function($form, formFieldName, errorMesgArr){
            var errorHtml='';
            for (var i in formFieldName){
                if(($form[formFieldName[i]]!== undefined && $form[formFieldName[i]].$error !== undefined &&
                    $form[formFieldName[i]].$error.required === true) && $form[formFieldName[i]].$invalid === true){
                    errorHtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>' + errorMesgArr[i] + '</div>';
                }                
            }

            return errorHtml; 
        };        
    };

     /*
    *@description : thhp loader inspector (init loader)
    *@return : event (broadcast $on start and end request);
    *@$broadcast('httpLoaderStart')
    *@$broadcast('httpLoaderEnd');
    */
    function httpLoaderHandler($rootScope){
       // Active request count
       var requestCount = 0; 

       function startRequest(config) {
        // If no request ongoing, then broadcast start event
        if( !requestCount ) {
          $rootScope.$broadcast('httpLoaderStart');
        }

        requestCount++;
        return config;
      };

      function endRequest(arg) {
        // No request ongoing, so make sure we don’t go to negative count
        if( !requestCount )
          return;
        
        requestCount--;
        // If it was last ongoing request, broadcast event
        if( !requestCount ) {
          $rootScope.$broadcast('httpLoaderEnd');
        }

        return arg;
      };

      // Return interceptor configuration object
      return {
        'request': startRequest,
        'requestError': endRequest,
        'response': endRequest,
        'responseError': endRequest
      };
    };

    function _getIndex(destObj, matchEle, matchType){
        var index;
        index = destObj.findIndex(function(item){
            if(matchType!== undefined && matchType){
                return (item[matchType] == matchEle);
            }else{
                return (item == matchEle);
            }
        });
        return index;
	};

})();