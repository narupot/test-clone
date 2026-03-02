/*
*@Description : This controller used to handel all product detail function
*@Author :  Smoothgraph Connect PVT LTD.
*@Created At : 06/011/2018
*/

(function(){
	
	function productDetailHandler($scope,salesfactoryData,$filter,$window,$timeout,$http,$location,$anchorScroll,$rootScope,$sce){
		//variable define section
		var rvCtrl = this;
		rvCtrl.productImg = [];
		rvCtrl.productInfo = []; 
		rvCtrl.attrRes = [];
		rvCtrl.orderInfo=[];
		rvCtrl.attrValRes = []; 
		rvCtrl.selAttrVal = [];
		//rvCtrl.selAttrVal = {};

		rvCtrl.productReview=[];
		rvCtrl.optionInfo=[];
		rvCtrl.optnameArr=[];
		rvCtrl.ptag=[];
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
		rvCtrl.loading = {
			"disableBtn" : false,
			"btnloaderpath" : btnloaderpath,
			"addTocart_and_bynow" : false,
		};

		//rvCtrl.optionFieldId=[];
		//used for store all information of product
		var prd_info =[];
		//In case if all product have quantity zero(0)
		rvCtrl.soldoutFlag = false;
				
		console.log(_.filter(productData.productInfo,function(o){
			return o.totQty>=0
		}));

		/*
		*Self execute loop for set total quantity model, set product info (with main product id and type).
		*Check all product quantity for enable sold out button
		*/
		
		_.forEach(productData.productInfo,function(item,index){
			
			if(item.prdType!== undefined && item.prdType === "normal"){
				var t = {};
				//t[i] = 1;
				if(item.totQty!==undefined && (item.totQty == 0 || item.totQty == "")){
					rvCtrl.totalQtyVal.push(0);
				}else{
					rvCtrl.totalQtyVal.push(1);
				}								
			}else if(item.prdType!== undefined && (item.prdType == "bundle"  || item.prdType == "configrable")){
				//
				rvCtrl.totalQtyVal.push(0);
			}

			prd_info.push(
				{"mainprdid" : item.mainProductId,
				 "type" : item.prdType, 
				}
			);

			//check all product quantity have zero
			var tq = parseInt(item.totQty);
			if(item.totQty!==undefined &&  tq>=1){
				rvCtrl.soldoutFlag = false;
			}else{
				rvCtrl.soldoutFlag = true;
			}
		});

		rvCtrl.prdBlogUrl = prdBlogUrl;
		rvCtrl.productImg = productData.productImage;
		rvCtrl.curCode = curCode;
		rvCtrl.colorPath = colorPath;
		rvCtrl.productImg = productData.productImage;
		rvCtrl.productVideo = productData.productVideo;
		rvCtrl.productInfo = productData.productInfo;
		rvCtrl.oneProductInfo = productData.oneProductInfo;
		rvCtrl.attrRes = productData.attrres;
		rvCtrl.attrValRes = productData.attrValRes;
		rvCtrl.selAttrVal = productData.selectedAttrVal;
		rvCtrl.selAttrValDetails = productData.selectedAttrValDetails;
		rvCtrl.varientType = productData.varientType;
		rvCtrl.optionInfo = productData.optionInfo;
		rvCtrl.optnameArr = productData.optnameArr;
		rvCtrl.ptag 	= productData.productTags;
		rvCtrl.spcfAttr = productData.sattrRes;
		rvCtrl.spcfAttrVal = productData.sattrValRes;
		rvCtrl.productReview = productData.userProductReview;
		rvCtrl.orderInfo = (Object.keys(productData.orderInfo).length !== 0)?productData.orderInfo : '';
		rvCtrl.contactProduct = productData.contactProduct;
		rvCtrl.starModal= rvCtrl.productInfo.userProductRating;
		rvCtrl.feedbackForm = (rvCtrl.starModal)?false:true;

		_createThumbVideoHtml(rvCtrl.productImg,rvCtrl.productVideo);

		//Function section
		$scope.scrollToReview = (event)=>{
			// set the location.hash to the id of
			// the element you wish to scroll to.
			$location.hash('reviewBox');
			// call $anchorScroll()
			$anchorScroll();
		};
		rvCtrl.openSpinDataModal = (prodtImg,prodtName,prodtId)=>{
			openSpinData(prodtImg,prodtName,prodtId);
			//zoomSlideDisable();
		};
		//this function used for change attribute
		//$index,atr.attributedesc.attr_id,prdDetails.mainProductId
		rvCtrl.changeContent = (index,attrId,mainPrdId,prdId,pIndex)=> { 
			rvCtrl.totalQtyVal[pIndex]=1;
			$scope.styleopacity =true;
			let prd_type = (!_.isUndefined(productData.oneProductInfo.prdType))? productData.oneProductInfo.prdType : ' ';
			//$scope.mainProductId = rvCtrl.productInfo.mainProductId;
			var dataObj ={
				'mainProductId' : mainPrdId,
				'attrVal' : rvCtrl.selAttrVal[mainPrdId],
				'currentAttrId' : attrId,
				'currentAttrValId' : rvCtrl.selAttrVal[mainPrdId][index].valId,
				'product_type' : prd_type,
			};
			MagicZoom.stop();
			MagicScroll.stop('MagicScrollDiv');	

			salesfactoryData.getData(getVarientProduct,'POST',dataObj)
			.then((r)=>{
				if(r.status===200 && Object.keys(r.data).length>0){
					var data = r.data;
					//var tmp_path =data.productUrl.split('product/')[1];
					if(prd_type==='bundle'){
						rvCtrl.productInfo[pIndex] = data.productInfo
						rvCtrl.selAttrVal[mainPrdId] = data.selectedAttrVal;
						rvCtrl.totalQtyVal[pIndex] = 0;
					}else if(prd_type === "configrable"){
						rvCtrl.productImg = data.productImage;
						rvCtrl.productInfo[pIndex] = data.productInfo;
						rvCtrl.selAttrVal[mainPrdId] = data.selectedAttrVal;
						//rvCtrl.cmobsArr = data.attributeArr;
						//rvCtrl.totalQtyVal = 1;
						//rvCtrl.productReview = data.userProductReview;
						//rvCtrl.orderInfo = (Object.keys(data.orderInfo).length !== 0)?data.orderInfo : '';
						//rvCtrl.starModal= data.productInfo.userProductRating;
						//rvCtrl.feedbackForm = (rvCtrl.starModal)?false:true;
						_createThumbVideoHtml(rvCtrl.productImg);
					}


					//let a = rvCtrl.getCombArr(rvCtrl.cmobsArr),b = rvCtrl.getCombArr(rvCtrl.selAttrVal);
					//a= a.concat(b);
					//manageAttribute(a,b);				
				}
				$timeout(()=>{
					$scope.styleopacity =false;
					//cfpLoadingBar.complete();
				},2000);
			},(error)=>{
				try{throw new Error("Something went badly wrong!");}catch(e){console.log('error'+e);$scope.styleopacity =false;}
			}).finally(()=>{
				$scope.styleopacity =false;
				MagicScroll.start('MagicScrollDiv');
				MagicZoom.start();;
			});
		};
		//this function used for get array combination
		rvCtrl.getCombArr =(cmbarr)=>{
			let cmbArr =[];
			_.forEach(cmbarr, (item)=>{
				if(item.length!==undefined && item.length>1){
					for(let i=0;i<item.length;i++){ 
						let v = parseInt(item[i]); 
						if(cmbArr.indexOf(v)<0) 
							cmbArr.push(v);
					}
				}else if(item.length!==undefined && item.length===1){
				 let v = parseInt(item);
				 if(cmbArr.indexOf(v)<0)
				 	cmbArr.push(v);
				}
				if(item.valId!==undefined){
				 let v = parseInt(item.valId);
				 if(cmbArr.indexOf(v)<0)
				 	cmbArr.push(v);
				}
			});
			return cmbArr;
		};
		//this function used for get price by option
		rvCtrl.getPriceByOption = ($event,str,prdId,index)=> { 
			// $scope.$evalAsync(function(){
			// })
			
			if(str==="tqchange" && (_.isUndefined(rvCtrl.totalQtyVal[index]) || rvCtrl.totalQtyVal[index]==0) ) return;
			//loaderImage('show');
			let mainPrdId = _.filter(rvCtrl.productInfo,(o) => {return o.id == prdId})[0].mainProductId; 
			//get all option and option value id 
			var valId = (rvCtrl.optionFieldId!= undefined) ? getFieldId(rvCtrl.optionFieldId,mainPrdId,'optchange') : [];
			var chkVal = (rvCtrl.optionValueCheck!= undefined) ? getFieldId(rvCtrl.optionValueCheck,mainPrdId,'valchange') : [];		
			var dataObj = {
				'productId' : prdId,
				"quantity" : rvCtrl.totalQtyVal[index],
				"optionValueId" : chkVal,
				"optionId" : valId
			};
			salesfactoryData.getData(productPriceByOption, 'POST', dataObj)
			.then((resp) =>{ 
				rvCtrl.productInfo[index].productPrice = resp.data;
				loaderImage('hide');
			},(error)=> console.log);
		};
		//this function used for handel quantiy increase decrease
		rvCtrl.incDcrQuntity = ($event,valMax,str,prdId,index)=> {
			$event.stopPropagation();
			if (str == "up" && rvCtrl.totalQtyVal[index] < valMax) {
				rvCtrl.totalQtyVal[index]++;
				rvCtrl.getPriceByOption($event,str,prdId,index);
			} else if (str == "down" && rvCtrl.totalQtyVal[index] > 1) {
				rvCtrl.totalQtyVal[index]--;
				//rvCtrl.getPriceByOption($event,str,prdId,index);			
			}
		};
		/****
		* This function call on click on add to cart button and check cases if valid the call addtocart function
		* all main product id and check before add to cart all product have selected attribue
		* @event : (event)
		* @strflag :(string)
		* ******/
		rvCtrl.addToCartHandler = function($event,strflag) {
			$event.stopPropagation();
			
			/**closure function to check data condition is attribute or quatity selected or not
			* @product  : (normal, configrable, bundle) 
			**/
			function _checkAttrSelected(){
				var t =[]
				_.forEach(prd_info,(item,index)=>{
					if(item.type==="normal"){
						t.push(true)
					}else if(item.type==="configrable" || item.type==="bundle"){
						if(!_.isUndefined(rvCtrl.selAttrVal[item.mainprdid])){
							t.push(true);
						}
					}
				});
				return t;
			};
			let chk = _checkAttrSelected();
			if(chk.indexOf(true)==-1){
				swal("Opps..",'Please Select Attribute in All Product','warning');
				return;
			}
			_enbdsbLodBtn('enable',true);
			//get all product id 
			let pid = _.map(rvCtrl.productInfo, 'id');
			let dataObj=[];
			for(d in pid){
				dataObj.push({
				  productId :  pid[d],
				  quantity : (_.isUndefined(rvCtrl.totalQtyVal[d])) ? 0 :  rvCtrl.totalQtyVal[d],
				});
			}
			salesfactoryData.getData(checkProductBeforeCart, "POST", dataObj)
			.then((r)=> {
				if(r.data==='100'){
					addToCart(strflag);
				}else{
					_enbdsbLodBtn('disabled',false);
					swal("", r.msg, "error");
					return false;
				}
			},(error)=>console.log)
			.finally(function(){
				_enbdsbLodBtn('disabled',false);
			});
		};
		//this function used to set rating of product
		$scope.setReview = (event, data)=> {
		 rvCtrl.starModal = data.rating;
		};
		//This function used to handel product thum image and video section
		$scope.thumbimagevedio=($event,type,site,videoid)=>{
			if(type==='video'){
				rvCtrl.activeimgprevtab =false;
				let tempurl = (site=='youtube')?'https://www.youtube.com/embed/':'https://player.vimeo.com/video/';
				rvCtrl.prdvideourl = tempurl + videoid;
			}else if(type==='thumbimage'){
				rvCtrl.activeimgprevtab =true;
			}
	        $event.preventDefault();
		};
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
        $scope.$on('$dropletFileAdded', function onDropletSuccess(event,fileObje) {
         	if(fileObje.type & rvCtrl.file_interface.FILE_TYPES.INVALID){
         		swal('file extension not valid')
         	}else if(rvCtrl.file_interface.FILE_TYPES.VALID){
         		//Listen when file is valid then upload to server
         		getBase64Url(fileObje.file)
         		.then((obj)=>{
         			salesfactoryData.getData(uploadAction,'POST',{'upload_path':upload_path,'uploadfile': obj})
         			.then((resp)=>{
         				if(resp.data.status==="success")
         					rvCtrl.getPriceByOption(event,'tqc',parseInt(fileObje.productId),parseInt(fileObje.productIndex));
         			},(error)=>console.log);
         		}).catch(()=> console.log)
         	}
        });
		//Listen after check quantity availabel in store
		function addToCart(strflag) {
			var optionDataArr = {};
			var tmpSelAtt = angular.copy(rvCtrl.selAttrVal);
			var attrValIdObj =[];
			var atrLen = Object.keys(rvCtrl.attrRes).length,
				seltdAtrLen = Object.keys(rvCtrl.selAttrVal).length;
			if(atrLen>=1&& seltdAtrLen===0){
				swal("Oops...", "Please choose before add to shopping bag!", "error");
				return false;
			}
			//closure function to get all selected attribute as per as main product id
			function _getSelectedAttr(mprd){
				var tempRes  =[];
			    _.forEach(rvCtrl.attrRes,function(currentValue, currentIndex) {
				   var temp = tmpSelAtt[mprd];
					if(!_.isUndefined(temp) && temp.length>0 && currentIndex==mprd){
						_.forEach(currentValue,(cVal,cInd)=>{
							let t = temp[cInd];
							t["attribute_id"] = cVal.attribute_id;
						});
					  tempRes = tempRes.concat(temp);
					}
				});
				return tempRes;
			};
			//loaderImage('show');
			//create data for add to cart
			// @datap :[]
			var dataObj =[];
			var qtycheck = 0;
			_.map(rvCtrl.productInfo,(item,ind)=>{
				if(rvCtrl.totalQtyVal[ind] > 0){
					qtycheck++;
					dataObj.push({
					  productId	: item.id,
					  mainproductid :  item.mainProductId,
					  quantity : rvCtrl.totalQtyVal[ind],
					  optionId : (rvCtrl.optionFieldId != undefined) ? getFieldId(rvCtrl.optionFieldId,item.mainProductId,'optchange') : [],
					  optionValueId : (rvCtrl.optionValueCheck != undefined) ? getFieldId(rvCtrl.optionValueCheck,item.mainProductId,'valchange') : [],
					  optionIdDetail : (rvCtrl.optionFieldId != undefined) ? getFieldId(rvCtrl.optionFieldId,item.mainProductId,'optDetailchange') : [],
					  attrDetail : (atrLen>=1&& seltdAtrLen>=1)? _getSelectedAttr(item.mainProductId) : [], 
					});
				}
				
			});
			if(qtycheck == 0){
				swal("Oops...", "Please add quantity!", "error");
				loaderImage('hide');
				return false;
			}
			
			salesfactoryData.getData(addProductToCart, 'POST', dataObj)
			.then((response)=> {
				_enbdsbLodBtn('disabled',false);
				if (response.data.success === "success" && (_.isUndefined(strflag) ||  strflag=='')) {
			      var totalCartProduct = $('#totalCartProduct').html();       
			      var totalNew = Number(totalCartProduct)+1;       
			      jQuery('#totalCartProduct').html(totalNew);
			      jQuery('#addToCartdiv').modal('show');
			    }else if(response.data.success === "success" && !_.isUndefined(strflag) && strflag==="buynow"){
			    	window.location.href=cartUrl;
			    }
			    if(!_.isUndefined(response.data.success) && response.data.success !== "success" )
			    	swal('Opps','Please check your network connection','error');
			    loaderImage('hide');
			    return false;
			},(error)=>{
				swal('Opps','Something wrong','error');
			}).finally(()=>_enbdsbLodBtn('disabled',false));
		};

		//Listen on mouseover to get tooltip html()
		rvCtrl.getTooltipContent = function(contentType,contentObj){
			let tpHtml ='';
			if(contentType!==undefined && contentType!==''){
				tpHtml='<div class="varient-tooltip"><image src="'+rvCtrl.colorPath+contentType+'" alt="'+contentType+'" width="50" height="50"></div>';
			}
			else if(contentType!==undefined && contentType == ''){
				tpHtml = '<div class="varient-tooltip"><span style="background :'+contentObj.color_code+'; width: 50px; height: 50px; display:inline-block"></span></div>';
			}
			rvCtrl.prdTooltip =$sce.trustAsHtml(tpHtml);
			return rvCtrl.prdTooltip;
		};

		//Listen on add wishlist
		rvCtrl.addIntoWishlist = function($event, product_id, index) {
		  $event.stopImmediatePropagation();
		  salesfactoryData.getData(addIntoWishlist,'GET', {"product_id" : product_id})
		  .then((response)=>{
		     if(response.data.status!== undefined && response.data.status == "success"){
		     	rvCtrl.productInfo[index].wishlist = true;	
		     	 _toastrMessage(response.data.status, response.data.message);		     	
		     }                       
		  },(error)=>{
		    //_error();
		  });
		};
		//Listen on remove wishlist
		rvCtrl.removeFromWishlist = function($event, product_id, index) {
		$event.stopImmediatePropagation();
		  salesfactoryData.getData(removeFromWishlist, 'GET', {"product_id" : product_id})
		  .then((response)=>{
		      if(response.data.status!== undefined && response.data.status == "success"){
		      	rvCtrl.productInfo[index].wishlist = false;
		      	_toastrMessage(response.data.status, response.data.message);		      
		      } 
		  },(error)=>{
		    //_error();
		  });
		};

		/**This function used for convert file into base64
		* @fileObj : (object)
		**/
	    function getBase64Url(fileObj) {
			 return new Promise((res, rej) => {
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
		/*This function used for get all attribute filed id
		* @arra :(type [])
		* @prdid : (type int)
		***/
		function getFieldId(arr,mainPrdId,flag) {
			var tempArr = [],
				tempObj ={};
			_.forEach(arr,(value,key)=>{
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
				let uploadimgId = [];
				let uplodimgprdid =0;
				_.map(rvCtrl.file_interface.getFiles(),(fileObj)=>{
					let optid = parseInt(fileObj.optionId),
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

	angular.module('productDetailApp').controller('productDetailCtrl',productDetailHandler);


	function QuantityHandler(){
		return{
			restrict : "E",
			scope : {
				field  : '=field',
				totalQuantity : "=?",
				prdDetail : "=?",
				prdIndex : "=?",
				getPriceByOption : "&priceChange",	
			},
			template:'<span class="spiner"  ng-class="{\'disable-button\' : enable_sold_out}">'+
        			 '<span class="decrease icon-minus" ng-click="quntCounter($event,totalQuantity,\'down\', prdDetail, prdIndex)"></span>'+
       			 	 '<input type="text" ng-model="field"  class="spinNum input-text qty" ng-blur="inputChange($event,totalQuantity, \'tqchange\', prdDetail, prdIndex)" />'+
       			 	 '<span class="increase icon-plus" ng-click="quntCounter($event,totalQuantity,\'up\',prdDetail, prdIndex)"></span>'+
      				 '</span>',
      		link : function(scope, element, attrs){
      			scope.enable_sold_out= (scope.totalQuantity!==undefined && parseInt(scope.totalQuantity) === 0) ? true : false;  
      			//scope.field = scope.field
      			//Listen on key up event
      			$(element).bind("keyup",function(e){
      				//console.log(e);
      				var input = element.find('input');
	                var inputVal = input.val();

	                //clearing left side zeros
	                if(scope.field!==undefined && scope.field.length>1){
	                	while (scope.field.charAt(0) == '0') {
	                   		 scope.field = scope.field.substr(1);
	                	}
	                }               
	                //check any string in model(a-z)
	                scope.$evalAsync(function(){
	                	scope.field = scope.field.replace(/[^\d.\',']/g, '');	
	                })	               
      			});
      		},
      		controller : function($scope){
      			//Listen on increase/decrease quantity
      			$scope.quntCounter = function($event, totalQnt, dirFlag, pdeatil,index){
      				$event.preventDefault();
      				//$scope.$evalAsync(function(){
	      				//in case of direction up->increase
	      				if((dirFlag!==undefined && dirFlag ==="up") && ($scope.field!==undefined && $scope.field < totalQnt)){
	      					$scope.field = 1 +parseInt($scope.field);
	      					$scope.getPriceByOption({$event : $event, str : dirFlag, prd_id :pdeatil.id, prd_ind : index});      					
	      				}else if((dirFlag!==undefined && dirFlag ==="down") && ($scope.field!==undefined && $scope.field >=1)){
	      					$scope.field = parseInt($scope.field) - 1;
	      					$scope.getPriceByOption({$event : $event, str : dirFlag, prd_id :pdeatil.id, prd_ind : index});
	      				}  
      				//})   
      				//$scope.$apply(); 				
      			};
      			//Listen on input change by user
      			$scope.inputChange = function($event, totlQnt, flag, pdeatil,index){
      				$scope.field = parseInt($scope.field);
      				//In case model vlaue grater than total quantity then reset
      				if(!isNaN($scope.field) && $scope.field > totlQnt){
      					$scope.field = 0;
      				}else if(!isNaN($scope.field) && $scope.field <= totlQnt && $scope.field>0){
      					$scope.getPriceByOption({$event : $event, str : flag, prd_id :pdeatil.id, prd_ind : index});      				  					
      				}
      			}
      		},
		};
	};

	angular.module("productDetailApp").directive('quantityCounter',QuantityHandler);
}).call(this);
//Listen after thumb or video have data then create html 
//@MagicScrollDiv to add html
function _createThumbVideoHtml(dataImg,dataVid,strflag){
		var thumbHtml ='',
			zoomHtml = '';
		if(!angular.isUndefined(dataImg) && dataImg.length>0){
			angular.forEach(dataImg,(item,index)=>{
				if(item.isDefault!==undefined && (item.isDefault=="1" || item.isDefault==true)){
					zoomHtml+='<a id="Zoom-1" class="MagicZoom" title="Zoom Image." href="'+item.original+'"><img src="'+item.large+'" alt=""/></a>';
				}else if(item.isDefault==undefined &&  index===0){
					zoomHtml+='<a id="Zoom-1" class="MagicZoom" title="Zoom Image." href="'+item.original+'"><img src="'+item.large+'" alt=""/></a>';
				}
				thumbHtml+= "<a data-slide-id='zoom' data-zoom-id='Zoom-1' href='"+item.original+"'  data-image='"+item.large+"' onclick='thumbimagevedio(event,\"thumbimage\")'> <img src='"+item.thumb+"' alt='"+item.image+"' /></a>";
		   });
		}
		if(!angular.isUndefined(dataVid) && dataVid.length>0){
			angular.forEach(dataVid,(item,index)=>{
				thumbHtml+="<a data-slide-id='video-1' href='javascript:void(0)' onclick='thumbimagevedio(event,\"video\",\""+item.site+"\",\""+item.vid+"\")'> <span class='glyphicon glyphicon-play'></span> <img src='"+item.thumb_small+"' alt='"+item.title+"' /></a>";
			});
		}
		jQuery('#imgZoomPreview').html(zoomHtml);
	    jQuery('#MagicScrollDiv').html(thumbHtml);
	    jQuery('#vidZoomPreview').hide();
		jQuery('#imgZoomPreview').show();
	    MagicScroll.start('MagicScrollDiv');
	    MagicZoom.start();
	};
//Thumb nail tab event handler
//@event,type,site,videoid
function thumbimagevedio(event,type,site,videoid){
	if(type==='video'){
		let tempurl = (site=='youtube')?'https://www.youtube.com/embed/':'https://player.vimeo.com/video/';
		 	tempurl+=videoid;
		 jQuery('#vidIframe').attr('src',tempurl);
		 jQuery('#imgZoomPreview').hide();	
		 jQuery('#vidZoomPreview').show();
	}else if(type==='thumbimage'){
		 jQuery('#vidZoomPreview').hide();
		 jQuery('#imgZoomPreview').show();	
	}
	event.stopPropagation();
    event.preventDefault();
};