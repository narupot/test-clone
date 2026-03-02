var cuurOption =[];
try{listingPrd;stateUrl;curCode;}catch(e){
	if(e instanceof ReferenceError){
		listingPrd= listingPrd || '';
		showProductPopup = showProductPopup || '';
		stateUrl = stateUrl || '';
		curCode = curCode || '';
		shopFlag = shopFlag || '';
	}
}
app.config(function($stateProvider,$urlRouterProvider,$locationProvider,$stickyStateProvider,$provide, $compileProvider) {
	// cfpLoadingBarProvider.includeSpinner = true;
 // 	cfpLoadingBarProvider.parentSelector  ='body';,'LCS.loadingBar' ,cfpLoadingBarProvider
  	$stickyStateProvider.enableDebug(false);
  	$compileProvider.aHrefSanitizationWhitelist(/^\s*(whatsapp|fb-messenger|line|https?|ftp|mailto|http):/);
	$locationProvider.html5Mode({
	    enabled: true,
	    requireBase: true,
	    rewriteLinks: false
	})
	$stateProvider.state('listingPrd', {
		url: stateUrl+'?{p_type:(?:/[^/]+)?} & {min_price:(?:/[^/]+)?} & {max_price:(?:/[^/]+)?} & {s_status:(?:/[^/]+)?}& {p_stype:(?:/[^/]+)?} & {sort:(?:/[^/]+)?} & {min_review:(?:/[^/]+)?} & {max_review:(?:/[^/]+)?} & {search_type:(?:/[^/]+)?} & {stext:(?:/[^/]+)?} & {cat_id:(?:/[^/]+)?}',
		sticky: true,
		params: {
			min_price: {value: null,squash: true,dynamic: true},
			max_price: {value: null,squash: true,dynamic: true},
			p_type: {array: true,value: [],squash: true,dynamic: true},
			p_stype : {array: true,value: [],squash: true,dynamic: true}, 
		    s_status: {array: true,value: [],squash: true,dynamic: true},
		    sort : {array: true,value: [],squash: true,dynamic: true},
		    min_review: {value: null,squash: true,dynamic: true},
			min_review: {value: null,squash: true,dynamic: true},
			search_type: {value: null,squash: true,dynamic: true},
			stext: {value: null,squash: true,dynamic: true},
			cat_id: {value: null,squash: true,dynamic: true},
		},
		views: { main: {templateUrl: listingPrd }}
	});
	$stateProvider.state('item', {
		url: '^/en/:type/:id',
		//url: '^/en/product/:id',
		sticky: true,
		resolve: {
			resolvedData: function($stateParams){
				return $stateParams.id
			},
			jsonData : function($stateParams,getJsonData){
				var objId = (stateUrl.indexOf('/shop/')!== -1)? {'id' : $stateParams.id,'shop': 'shop'}:{'id' : $stateParams.id};
				return getJsonData.getDataFromServer(productPopupUrl,'GET',objId).then(function(response){
						return response;
				},function(error,config,headers){
					handleError(error);
					return false;
				});
			}
		},
		proxy: {  // Custom config processed in $stateChangeStart
			//external: 'item.full',
			internal: 'item.uibModal'
		}

	});
	$stateProvider.state('item.uibModal', {
		views: {
		  "modal@": {
		    templateUrl: showProductPopup,//'view/modalPopUp.html',
		    controller: 'ModalresolveCtrl as rvCtrl',
		  }
		},
		isModal: true // Custom config processed in $stateChangeStart
	});
    //$urlRouterProvider.otherwise('/');
	$urlRouterProvider.rule(function ($injector, $location) {
       //what this function returns will be set as the $location.url
        var path = $location.path(), normalized = path.toLowerCase();
        if (path != normalized) {
            //instead of returning a new url string, I'll just change the $location.path directly so I don't have to worry about constructing a new url string and so a new state change is not triggered
            $location.replace().path(normalized);
        }
        // because we've returned nothing, no state change occurs
    });
 // $urlRouterProvider.otherwise('/en/user/home');
 	$stateProvider.state('query_state', { 
		sticky: true,
		//url: '/en/product/filter?{/:query}'
		//params: ['price'],
		//url : '/en/product/filter?qr',
        url: '^/en/product/filter?{p_type:(?:/[^/]+)?} & {min_price:(?:/[^/]+)?} & {max_price:(?:/[^/]+)?} & {s_status:(?:/[^/]+)?}& {p_stype:(?:/[^/]+)?} & {sort:(?:/[^/]+)?}', 
		params: {
			min_price: {value: null,squash: true,dynamic: true},
			max_price: {value: null,squash: true,dynamic: true},
			p_type: {array: true,value: [],squash: true,dynamic: true},
		    s_status: {array: true,value: [],squash: true,dynamic: true},
		    sort : {array: true,value: [],squash: true,dynamic: true},
		    min_review: {value: null,squash: true,dynamic: true},
			min_review: {value: null,squash: true,dynamic: true},
		},
		// views: {'': { 
		//   templateUrl: listingPrd 
		// } }
		// ,
		// // params: {
		// // obj: null
		// // },
		// views : {
		// 	main :{
		// 		templateUrl: listingPrd,
		// 		controller: 'shopeCtrl as ftCtrl',
		// 	}
		// },
		// title: 'Life compass Shopping',
		resolve: { function(){
			//console.log('i am resolve in ');
		} },
	})
}).run(['$rootScope', '$state', '$uibModal','$location',function($rootScope, $state, $uibModal,$location){
	var stateBehindModal = {},
      modalInstance = null;
     $rootScope.qrystr ='';
 	 $rootScope.$on('$stateChangeStart', function(evt, toState, toStateParams, fromState, fromStateParams){
	    showHideLoader('show','overlayLoader');
	 	// Implementing "proxy": redirect to the state according to where it's from.
	 	if(toState.proxy){
		  evt.preventDefault();
		  if(fromState.name === '' || fromState.name === toState.proxy.external){
		    // Visiting directly via URL or from the external state,
		    // redirect to external (full) state.
		    $state.go(toState.proxy.external, toStateParams);
		    
		  }else{
		    // Visiting from another state, redirect to internal (modal) state
		    $state.go(toState.proxy.internal, toStateParams);
		  }
		  return;
		}
		// Implementing "isModal":
		// perform the required action to transitions between "modal states" and "non-modal states".
		//
		if(!fromState.isModal && toState.isModal){
		  stateBehindModal = {
		    state: fromState,
		    params: fromStateParams
		  };
		  modalInstance = $uibModal.open({
		  	template : '<div ui-view="modal">',
			controller:'ModalInstanceCtrl',
			//backdrop: false,
			windowClass :'product-modal',
			//controller : 'buyerHomePageController',
			controllerAs: 'pMCtrl',
			backdrop  : 'static',
  			keyboard  : false
		});
	  	  modalInstance.result.finally(function() {
		    // Reset instance to mark that the modal has been dismissed.
		    modalInstance = null
		    // Go to previous state
		    $state.go(stateBehindModal.state, stateBehindModal.params);
		  });
		}else if(fromState.isModal && !toState.isModal){
		  // Modal state ---> non-modal state
		  // Directly return if the instance is already dismissed.
		  if (!modalInstance) {
		    return;
		  }
		  // Dismiss the modal, triggering the reset of modalInstance
		  modalInstance.dismiss();
		}
	 })
	 $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams){ 
	 	  showHideLoader('hide','overlayLoader');
	 })
	$rootScope.$on('$locationChangeSuccess', function($stateParams) {
		//searchDirectory(); 
		//console.log($location);
	});
}]).controller('ModalresolveCtrl',['$scope','resolvedData','jsonData','getJsonData','$location','$anchorScroll','$rootScope','socialAction','Upload','$state','$timeout','$sce',function($scope,resolvedData,jsonData,getJsonData,$location,$anchorScroll,$rootScope,socialAction,Upload,$state,$timeout,$sce){
	var rvCtrl = this;
	 /*****This object used for share board information****/
    var shareObjInfo = {}; 
    var file_name ='';
	rvCtrl.productImg = [], rvCtrl.productInfo = [], rvCtrl.attrRes = [], rvCtrl.attrValRes = [], rvCtrl.selAttrVal = [], rvCtrl.productReview=[],rvCtrl.orderInfo=[], rvCtrl.optionInfo=[],rvCtrl.optnameArr=[];
	rvCtrl.totalQtyVal = 1;
	rvCtrl.friends = rvCtrl.complainType = [];
	
   	rvCtrl.messageShow = false;
	$scope.message = '';
	rvCtrl.senddropdown = true;
	rvCtrl.sendshare = false;
    rvCtrl.id = +resolvedData;
    rvCtrl.curCode = curCode;
    rvCtrl.colorPath = colorPath;
	rvCtrl.productImg = jsonData.productImage;
	rvCtrl.productInfo = jsonData.productInfo;
	rvCtrl.complainType = jsonData.complainType;
	rvCtrl.attrRes = jsonData.attrres;
	rvCtrl.attrValRes = jsonData.attrValRes;
	rvCtrl.selAttrVal = jsonData.selectedAttrVal;
	rvCtrl.optionInfo = jsonData.optionInfo;
	rvCtrl.optnameArr = jsonData.optnameArr;
	rvCtrl.productReview = (jsonData.userProductReview!=undefined &&  (jsonData.userProductReview.length)>=1)?jsonData.userProductReview :[];
	rvCtrl.orderInfo = (Object.keys(jsonData.orderInfo).length !== 0)?jsonData.orderInfo : '';
	rvCtrl.starModal= rvCtrl.productInfo.userProductRating;
	rvCtrl.prevId = jsonData.ids.prevId;
	rvCtrl.nextId = jsonData.ids.nextId;
	rvCtrl.nextPrdType=jsonData.ids.nextType;
	rvCtrl.prevPrdType=jsonData.ids.prevType;    
	rvCtrl.prdBlogUrl = (rvCtrl.productInfo.prdType=='product') ? prdUrl : blogUrl;
	rvCtrl.avgRating = rvCtrl.productInfo.avaragerating;
	varientInfo = jsonData.varientInfo;
    rvCtrl.feedbackForm = (rvCtrl.starModal)?false:true;
    rvCtrl.loaderImgSrc='';
    rvCtrl.cmobsArr = jsonData.attributeArr;
	$timeout(function(){ 
		if(angular.isUndefined(rvCtrl.attrRes) || !rvCtrl.attrRes.lenth || angular.isUndefined(rvCtrl.attrRes[0].attribute_id)) return;
		var a = rvCtrl.getCombArr(rvCtrl.cmobsArr),b = rvCtrl.getCombArr(rvCtrl.selAttrVal);
		a= a.concat(b);
		var atid = rvCtrl.attrRes[0].attribute_id;
		cuurOption = rvCtrl.getCombArr(rvCtrl.attrValRes[atid]);
		if(a.length>0) manageAttribute(a,b);
	});
	if(rvCtrl.optionInfo.length>=1){
		rvCtrl.optionInfo.map(function(item,keys){
		var len = (item.option_attr_value_detail).length;
		if(len>=1){
			item.option_attr_value_detail.map(function(itm,key){
				var index = rvCtrl.optnameArr.findIndex(function(x){
					 return(x.id!== undefined && x.id == itm.attribute_value_id);
				});
				rvCtrl.optionInfo[keys].option_attr_value_detail[key].attribute_value =rvCtrl.optnameArr[index].value;
			});
		  };
		});
	}
	//$rootScope.spinner.active = false;
	$scope.scrollToReview = function(event){
		// set the location.hash to the id of
      	// the element you wish to scroll to.
      	$location.hash('reviewBox');

      	// call $anchorScroll()
      	$anchorScroll();
	}
   /****related product*****/
    rvCtrl.page = 0;
	rvCtrl.paging =0;
	rvCtrl.related_Items = [];
	rvCtrl.loadingMore = false;
	rvCtrl.showMore = false;
	rvCtrl.loadingUser = false;
	rvCtrl.lcsGridOption ={
		gridWidth : 235,
		gutterSize : 15,
		performantScroll :false,
		gridNo : 'auto',
	};
	rvCtrl.linkorstate= true;
	rvCtrl.errorInfoLog ='<div class="no-info-blank"><h3><i class="icon-doc"></i> You have no information </h3></div>';
    rvCtrl.loadMoreData = function(){
		if (rvCtrl.showMore)return;
		//cfpLoadingBar.start(); 
		rvCtrl.page++;
		rvCtrl.loadingMore = true;
		var objPage={page: rvCtrl.page,'shopid':4,'category':''};	
		var urlstring = jsonData.ids.relatedUrl;
		//shopproductlisturl;
		getJsonData.getDataFromServer(urlstring, 'GET', objPage).then(function(res) {
			if(res.status=='unsuccess'){
				rvCtrl.no_result_found =(rvCtrl.related_Items.length>=1)? false : true;
				rvCtrl.showMore = true,rvCtrl.loadingMore = false;
				//cfpLoadingBar.complete();
		        return;
			}
			rvCtrl.no_result_found = false;
			var tempRes = angular.copy(rvCtrl.related_Items);
			tempRes = tempRes.concat(res.data);
			rvCtrl.related_Items = tempRes;
			rvCtrl.loadingMore = false;
		},function(error){
			try{throw new Error("Something went badly wrong!");}
    	    catch(e){console.log("error: " + e); rvCtrl.loadingMore = false,rvCtrl.showMore=false;}
		});
		//cfpLoadingBar.complete();
	};
	rvCtrl.loadMoreData();
	//window.onscroll = angular.bind(this, function(){
	window.addEventListener('scroll', function(e) {
		//this.scrollPos = document.body.scrollTop || document.documentElement.scrollTop || 0;
		//$scope.$digest();
	});
	/******end related prouctd*****/
	rvCtrl.updateProductLike = function(type,event){
		var obj = {"product_id" : rvCtrl.productInfo.id}
		var url = (type == 'remove') ? '/user/removefavourite' : '/user/productfavourite';
		socialAction.updateService(obj,url).then(function(response){
			if(type=='remove'){
				rvCtrl.productInfo.userLike ='';
				rvCtrl.productInfo.total_likes--;
			}else{
				rvCtrl.productInfo.userLike = 1;
				rvCtrl.productInfo.total_likes++;
			}
		});
	};
	rvCtrl.openSpinDataModal = function(prodtImg,prodtName,prodtId){
		openSpinData(prodtImg,prodtName,prodtId);
		zoomSlideDisable();
		socialAction.updateService('','/social/spinitpopupboardslist').then(function(response){
            angular.element('.board-container ul.board-name').html(response.html);
          
	    });
	};
	rvCtrl.friendsData = function() {
		rvCtrl.messageShow = false;
		var objPage={page:1};
		zoomSlideDisable();
		socialAction.updateService(objPage,'/social/friendslisting').then(function(dataRes){
			var tempRes = angular.copy(rvCtrl.friends);
			tempRes = tempRes.concat(dataRes.data);
			rvCtrl.friends = tempRes;
		});
    };
	rvCtrl.shareBoardpop = function($event, index){
        $event.stopImmediatePropagation();
        rvCtrl.messageShow = false,rvCtrl.sendshare = true;
        /*this is for user id where posts and boards will share*/
        shareObjInfo=socialAction.shareBoardpopService(rvCtrl.productInfo,rvCtrl.friends,index,'type');
        rvCtrl.senddropdown = true;
    };  
    rvCtrl.shareBoards = function($event) {
    	$event.stopImmediatePropagation();
	    shareObjInfo['message'] = rvCtrl.formData.message;
	    shareObjInfo['sharetype'] = 'product';
	    socialAction.updateService(shareObjInfo,'/social/shareprofile').then(function(resp){
	       if(resp.status == 'success'){
                 rvCtrl.messageShow = true,rvCtrl.message = resp.message;
                 rvCtrl.sendshare = false,rvCtrl.senddropdown = true;
            }
		})
	};
    rvCtrl.shareBoardpopClose = function(){
	    rvCtrl.messageShow = false,rvCtrl.sendshare = false,rvCtrl.senddropdown = true;
    } 
	rvCtrl.removeReview = function(rid,index){
		swal({title: "Are you sure want to delate review ?",type: "warning",showCancelButton: true }).then(function(result) {
		      var obj = {'reviewId' : rid};
				getJsonData.getDataFromServer(deleteProductReview, 'POST', obj).then(function(resp){
					if(resp){
						rvCtrl.productInfo.totProductReview = rvCtrl.productInfo.totProductReview -1;
						rvCtrl.productReview.splice(index,1);
					}
					//$scope.ratingSuccess = 'Rating updated successfully';
				})
			}, function(dismiss) {
            	return false;
         });
	};

	//$scope.starModal =0;
	rvCtrl.writeReview = function(){
		if(rvCtrl.starModal ==''){
			swal("", "Please select rating", "error");
			return false;
		}
		//loaderImage('show');
		var obj = {
			'reviewValue' : rvCtrl.reviewValue,'rating' : rvCtrl.starModal,
			'productId' : rvCtrl.productInfo.id,
		}
		getJsonData.getDataFromServer(insertProductReview, 'POST', obj).then(function(resp){
			if(resp){
				rvCtrl.productReview.splice(0, 0, resp);
				rvCtrl.feedbackForm = false;
			}
			//loaderImage('hide');
			//$scope.ratingSuccess = resp;
		})
		
	}
	rvCtrl.changeContent = function(index,attrId){
		rvCtrl.mainProductId = rvCtrl.productInfo.mainProductId;
		var obj = {'mainProductId' : rvCtrl.productInfo.mainProductId,'attrVal' : rvCtrl.selAttrVal,
			'currentAttrId' : attrId,'currentAttrValId' : rvCtrl.selAttrVal[index].valId,};
		getJsonData.getDataFromServer(getVarientProduct, 'POST', obj).then(function(resp) {
		 var tmp_path =resp.productUrl.split('product/')[1];
			$state.go('item', {id:tmp_path}, {reload:false});
			rvCtrl.productImg = resp.productImage;
			rvCtrl.productInfo = resp.productInfo;
			rvCtrl.selAttrVal = resp.selectedAttrVal;
			rvCtrl.cmobsArr = resp.attributeArr;
			rvCtrl.totalQtyVal = 1;
			rvCtrl.productReview = resp.userProductReview;
			rvCtrl.orderInfo = (Object.keys(resp.orderInfo).length !== 0)?resp.orderInfo : '';
			rvCtrl.starModal= resp.productInfo.userProductRating;
			rvCtrl.feedbackForm = (rvCtrl.starModal)?false:true;
			rvCtrl.avgRating = rvCtrl.productInfo.avaragerating;
			var a = rvCtrl.getCombArr(rvCtrl.cmobsArr),b = rvCtrl.getCombArr(rvCtrl.selAttrVal);
			a= a.concat(b);
			manageAttribute(a,b);
		})
	}
	/*****
	* function for when user contact to non salable product
	* ****/
	rvCtrl.contactNonSalableProduct = function() {
	    if(rvCtrl.contactMessage =='' || rvCtrl.contactMessage == undefined)
        	 rvCtrl.errMsg =true;
    	var prodData = {"productId": rvCtrl.productInfo.id,"message": rvCtrl.contactMessage};
  		  $http({  
            method: 'POST',  
            url: nonSalableProductUrl,  
            headers: { 'Content-Type': undefined },  
            transformRequest: function (data) {  
            	var formData = new FormData();  
                formData.append("formDatas", angular.toJson(data.formDatas));
                formData.append("productId", rvCtrl.productInfo.id); 
                formData.append("shopId", rvCtrl.productInfo.shop_id); 
                formData.append("message", rvCtrl.contactMessage); 
                formData.append("file", data.files);  
                return formData; 
            },  
            data: {formDatas: prodData,files: rvCtrl.contactFiles}  
        }).success(function (response) { 
			if(response.success == true)
				swal(response.message);
			else
				swal("", response, "error");
		}).error(function (response) { 
             return false
        });
	};
	rvCtrl.getPriceByOption = function($event) {
		if($event == '')
			return;
		//loaderImage('show');
		var valId = (rvCtrl.optionFieldId!= undefined) ? getFieldId(rvCtrl.optionFieldId) : [];
		var chkVal = (rvCtrl.optionValueCheck!= undefined) ? getFieldId(rvCtrl.optionValueCheck) : [];
		if (rvCtrl.myFiles != null) {
			var optId = angular.element("#fileUpdBtn").attr('optionId');
			valId.push(parseInt(optId));
		}
		var obj = {
			'productId' : rvCtrl.productInfo.id,
			"quantity" : rvCtrl.totalQtyVal,
			"optionValueId" : chkVal,
			"optionId" : valId
		};
		getJsonData.getDataFromServer(productPriceByOption, 'POST', obj).then(function(resp) {
			rvCtrl.productInfo.productPrice = resp;
			//loaderImage('hide');
		});
	}
	rvCtrl.incDcrQuntity = function(valMax, str) {
		if (str == "up" && rvCtrl.totalQtyVal < valMax) {
			rvCtrl.totalQtyVal++;
			rvCtrl.getPriceByOption();
		} else if (str == "down" && rvCtrl.totalQtyVal > 1) {
			rvCtrl.totalQtyVal--;
			rvCtrl.getPriceByOption();
		}
	}
	/*****
	 * This function call on click on add to cart button and check cases if valid the call addtocart function
	 * ******/
	rvCtrl.addToCartHandler = function() {
		var data = {"productId" : rvCtrl.productInfo.id,"quantity" : rvCtrl.totalQtyVal};
		getJsonData.getDataFromServer(checkProductBeforeCart, "POST", data).then(function(response) {
			if(response == '100'){
				addToCart();
			}else{
				swal("", response, "error");
				return false;
			}
		});
	}
	$scope.$watch('rvCtrl.myFiles', function() {
		var allowed = ["jpeg", "png", "gif", "jpg"];
		var found = false;
		if (rvCtrl.myFiles != null) {
			angular.forEach(allowed, function(extension) {
				if (rvCtrl.myFiles.type.match('image/' + extension)) {
					found = true;
				}
			});
			if (!found) {
				alert('file type should be .jpeg, .png, .jpg, .gif');
				return;
			} else {
				var reader = new FileReader();
				reader.onload = function(loadEvent) {
					$scope.$apply(function() {
						$scope.filepreview = loadEvent.target.result;
					});
				}
				reader.readAsDataURL(rvCtrl.myFiles);
				rvCtrl.myFiles.upload = Upload.upload({
					dataType : 'json',
					url : uploadAction,
					data : {
						"upload_path" : upload_path,
						"uploadfile" : rvCtrl.myFiles
					}
				}).then(function(res) {
					if (res.status == 200) {
						file_name = res.data.file_name;
						rvCtrl.getPriceByOption();
					}
				});
			}
		}
	});
    $scope.setReview = function(event, data) {
		 rvCtrl.starModal = data.rating;
	};
	rvCtrl.getCombArr = function(cmbarr){
		var cmbArr =[];
		angular.forEach(cmbarr, function(item){
			if(item.length!==undefined && item.length>1){
				for(var i=0;i<item.length;i++){ var v = parseInt(item[i]); if(cmbArr.indexOf(v)<0) cmbArr.push(v);}
			}else if(item.length!==undefined && item.length===1){ var v = parseInt(item); if(cmbArr.indexOf(v)<0)cmbArr.push(v);}
			if(item.valId!==undefined){ var v = parseInt(item.valId); if(cmbArr.indexOf(v)<0)cmbArr.push(v);}
		});
		return cmbArr;
	};
	
	rvCtrl.getTooltipContent = function(contentType,contentObj){
		var tpHtml ='';
		if(contentType!==undefined && contentType!=='')
			tpHtml='<div class="varient-tooltip"><image src="'+rvCtrl.colorPath+contentType+'" alt="'+contentType+'" width="50" height="50"></div>';
		else if(contentType!==undefined && contentType == '')
			tpHtml = '<div class="varient-tooltip"><span style="background :'+contentObj.color_code+'; width: 50px; height: 50px; display:inline-block"></span></div>';
		rvCtrl.prdTooltip =$sce.trustAsHtml(tpHtml);
		return rvCtrl.prdTooltip;
	};
	/*******Custom function section******/
	function addToCart() {
		//jQuery('#addToCartdiv').modal('show');
		    //  return false;
		//loaderImage('show');
		var valId = (rvCtrl.optionFieldId != undefined) ? getFieldId(rvCtrl.optionFieldId) : [];
		var chkVal = (rvCtrl.optionValueCheck != undefined) ? getFieldId(rvCtrl.optionValueCheck) : [];
		var optionDataArr = {};
		if (rvCtrl.myFiles != null) {
			var optId = angular.element("#fileUpdBtn").attr('optionId');
			valId.push(parseInt(optId));
			var tempVal = angular.copy(rvCtrl.optionFieldId);
			optionDataArr[optId] = file_name;//[$scope.myFiles][0].name;
			angular.merge(optionDataArr, tempVal);
		}else {
		  optionDataArr = angular.copy(rvCtrl.optionFieldId);
		}
		var tmpSelAtt = angular.copy(rvCtrl.selAttrVal);
		var attrValIdObj =[];
		var atrLen = rvCtrl.attrRes.length,seltdAtrLen = rvCtrl.selAttrVal.length;
		if(atrLen>=1 && seltdAtrLen===0){
			zoomSlideDisable();
			swal("Oops...", "Please choose before add to shopping bag!", "error");
			zoomSlideEnable();
			return false;
		}else if(rvCtrl.attrRes.length>=1 && rvCtrl.selAttrVal.length>=1){
			attrValIdObj = rvCtrl.attrRes.map(function(currentValue, currentIndex) {
				var temp = tmpSelAtt[currentIndex];
				temp["attribute_id"] = currentValue.attribute_id;
				return temp;
			});
		}
		
		var data = {"productId" : rvCtrl.productInfo.id,
			"quantity" : rvCtrl.totalQtyVal,"attrDetail" : attrValIdObj,
			"optionId" : valId,"optionIdDetail" : optionDataArr,"optionValueId" : chkVal,
		};
		//return;
		getJsonData.getDataFromServer(addProductToCart, 'POST', data).then(function(response) {
			if (response == 1) {
		      var totalCartProduct = $('#totalCartProduct').html();       
		      var totalNew = Number(totalCartProduct)+1;     
		      jQuery('#totalCartProduct').show();    
		      jQuery('#totalCartProduct').html(totalNew);
		    }
		    loaderImage('hide');
		    jQuery('#addToCartdiv').modal('show');
		      return false;
		});

	};
	function getFieldId(arr) {
		var tempArr = [];
		angular.forEach(arr, function(value, key) {
			if (value) {
				tempArr.push(parseInt(key));
			}
		})
		return tempArr;
	}
}]).controller('ModalInstanceCtrl',['$uibModalInstance','$stateParams',function($uibModalInstance,$stateParams){
	var pMCtrl = this;
	pMCtrl.closeModal = function(){
		$uibModalInstance.close();
		jQuery.removeData(jQuery('#img_01'), 'elevateZoom');
      	jQuery('.zoomContainer').remove();
	}
	$uibModalInstance.opened = function($state){
		//console.log($uibModalInstance);
	}
	
}]);
(function($){
	jQuery(document).on('change','.pdetail-row .attrVal',function(){
        cuurOption =[];
        jQuery(this).find('option').each(function(){
        	cuurOption.push(parseInt(jQuery(this).val()));
        });
    });
	jQuery(document).on('click','.custom-select .pdetail-row ul li input[type=radio]',function(){
		cuurOption =[];
		jQuery(this).closest('ul').find('li input[type=radio]').each(function(){
			cuurOption.push(parseInt(jQuery(this).val()));
		});
	});
}(jQuery));
function manageAttribute(result,selectedVal){ 
 if(jQuery(document).find('.custom-select .pdetail-row ul li input[type=radio]').length>0){
	  $('.custom-select .pdetail-row ul li').find('input[type=radio]').each(function() {
			var v = parseInt($(this).val());
			if (result.indexOf(v)<0) {
			  $(this).prop('disabled', true);
			  if($(this).next().find('img').length>0)$(this).next().find('img').css('opacity',.5);
			  if($(this).next().find('div').length>0)$(this).next().find('div').css('opacity',.5);
			}else if (result.indexOf(v)>=0) {
			  $(this).prop('disabled', false);
			  if($(this).next().find('img').length>0)$(this).next().find('img').css('opacity',1);
			  if($(this).next().find('div').length>0)$(this).next().find('div').css('opacity',1);
			}
			if(cuurOption.indexOf(v)>=0){
				$(this).prop('disabled', false);
				if($(this).next().find('img').length>0)$(this).next().find('img').css('opacity',1);
				if($(this).next().find('div').length>0)$(this).next().find('div').css('opacity',1);
			}
	});
  }
  if(jQuery(document).find('.pdetail-row select.attrVal').length>0){
	$('.pdetail-row .attrVal').find('option').each(function() {
		var v = parseInt($(this).val());
		if (result.indexOf(v)<0) {
		  // $(this).prop('disabled', true);
		  $(this).addClass('dropdownUnavailable');
		} else if (result.indexOf(v)>=0) {
		 // $(this).prop('disabled', false);
		  $(this).removeClass('dropdownUnavailable');
		}
		if(cuurOption.indexOf(v)>=0) $(this).removeClass('dropdownUnavailable');
	});
  }
};
// (function(Modal) {
//    Modal.prototype.hideModal = function() {
//     var that = this;
//     this.$element.hide();
//     this.backdrop(function() {
// 		if (that.modalOpen) {
// 		that.$body.removeClass('modal-open');
// 		}
// 		that.resetScrollbar();
// 		that.$element.trigger('hidden.bs.modal');
// 		zoomSlideEnable();
//     });
//   };
// })($.fn.modal.Constructor);
/*****Error handling function****/
function handleError(error) {
	swal({type: 'error',text: 'Opps..'});
	showHideLoader('hide','overlayLoader');
	try{throw new Error("Something went badly wrong!");}
    catch(e){console.log("error: " + e);}
};
// alert(01211);

// $('.product-modal').on('shown.bs.modal', function () {
//  	// $('#myInput').focus()
//  	alert(220);
// })
// $(document).on("shown", ".product-modal", function() {
//     $(".product-modal").unbind("scroll");
//     $(".product-modal").scroll(function(){
//        alert(01211);
//     });
// }); 

// $('body').scroll(function(){
// 	alert('dfsdfdsfsd f');
// });           