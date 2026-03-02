/****
*This controllter used to handel all product related functionality
*Careated 18 sep 2017
*Author Smoothgraph Connect Pvt Ltd
*****/

(function(){
	"used strict";
	
	function productCtrl($scope,salesfactoryData,$http,$sce,$timeout,$filter, treeNodeService,$rootScope, $q, $window){
		$scope.product={
			name :{},
			productDesc:{},
			prdtags :[],
			stbprice :false,
			processing_time:"",
			initial_price:"",
			special_price:"",
			//sp_fromdate:new Date(),
			//sp_todate :new Date(),
			// expiry_date:,
			//price section
			stbprice:'',
			tprices:[{tireQnt:"",tireQntPrice:""}],
			bundelprice:[{bundelqunt:"",bundelprice:""}],
			// tire_quantity:[{val:null}],
			// tire_quantity_price:[{val:null}],
			//variant section
			hasvariants:false,
			varientmodel:[],
			variantTagsData:[],
			//vedio section
			videodobj:{videourl:"",video_prev_img:"",video_id:"",video_title:"",description:"",video_url:""},
			videoFile:[],
			//file upload section
			file_interface:{},
			remind_icon_file_interface:{},
			setdefaultimg :true,
			//warehouse sectio
			warehouse_model:[],
			/****This section used for specification model****/
			specmodel:[],
			speschoseattrset:"",
			sepscreateset:{attrname:"",attrdesp:"",labelflag:"",fontcolor:"",flagbgcolor:""},
			//this section used for requirement model
			reqmodel:[],
			// Selected Category Tree Items
			selectedCateTree: [],
			productImages: [],
			defaultImage: "0",
			prevDefaultImage : "",
			seo:{meta_title:[],meta_keyword:[],meta_description:[]},
			//defaultwhouse
			defaultwhouse:defaultwarehouse, 
		};
		/******This variable define fro data section *****/
		$scope.prdData={			
			currency_detail:currency_detail,   
			//variant section
			variants:variantdata,
			selectTags:[{value:""}],
			//categeroy section
			cateTree:[],
			//warehouse sectio
			warehouse: [],
			//video section
			videolist:[],
			//specification section 
			specslist :[],
			currentPage :0,pageSize:10,query:'',
			specs_create_list:[],
			spesattrset :[],
			spescrtupdate:"createattrset",
			spesdateoption:{
				format:'dd-MMMM-yyyy',
				opened: true,
			},
			//product requirement section
		    reqlist:[],reqCurPage:0,reqPageSize:10,reqQuery:'',
		    pagearr:[10,20,30,40,50],
		    req_create_list:[],
		    reqhas:false,
		    //date picker option for date and time
		    dateFromOption :{ 
		    	wrap: true,
		    	dateFormat : 'd/m/Y',		    	
		    },
		    dateToOption :{
		    	wrap: true,
		    	dateFormat : 'd/m/Y',
		    },
		    dateOption :{
		    	wrap: true,
		    	dateFormat : 'd/m/Y',		     	
		    },
		    loading:{
		    	"save_and_continue" : false,
		    	"disableBtn" : false,
		    	"btnloaderpath" :tableLoaderImgUrl,
		    },

		    parent_id : '',
		    //save and save_and_continue action flag
			save_btn_action : "",

			//for enable/disable uploadall/ clear all button
			up_cl_all : false,
			
			//for image order 
			image_order : [],
			prev_image_order : _.pluck(product_detail.has_product_image, "sequence").map(Number),			
		};
		

		//FOR uploaded file id array 
		var _uploadedFileIdArray =[];

		
		//prev uploaded image section
		// prd_defalut_img for check product default image in case of edit
		var prd_defalut_img={"prevImg" : "", "dropImg" : ""};
		$scope.prevImage = product_detail.has_product_image;
		$scope.image_url= product_detail.image_url;
		$scope.prdData.product_type= product_detail.product_type;

		if(product_detail.has_cod == 1){	
			$scope.product.cod = true;
			$scope.product.codrange = product_detail.cod_range;
		}
		else{
			$scope.product.cod = false;
			$scope.product.codrange = '';
		}

		$scope.product.productImages = angular.copy($scope.prevImage);
		//calculate previous upload file and set file upload restiction
		file_upload_setting.max_number_file = parseInt(file_upload_setting.max_number_file) - $scope.prevImage.length;
		getSetPrevImgDefault("set", "cancel");

		//get sepecifiaction and requirement data from server
		function _getSpecification(){
			salesfactoryData.getData(attribute_requirment_url,'GET','')
			.then((r)=>{
				var attrData = r.data; 
				if(attrData){
					// _.forEach(attrData.attributes,(item,key)=>{}); 					
					$scope.prdData.specslist=attrData.attributes;
					// console.log(JSON.stringify($scope.prdData.specslist));
					$scope.prdData.spesattrset=attrData.attributesets;
					if(typeof current_blade_type !== "undefined" &&  (current_blade_type === "copy_product" || current_blade_type === "copy" )){
						$scope.prdData.hide = false;
					}else{
						$scope.prdData.hide = true;
					}
 					
					$scope.prdData.description = product_detail.has_attributeset.description;					
					//requirement
					$scope.prdData.reqlist=attrData.requirements;
					if($scope.prdData.reqlist){
						_setPrevSelectedReq();
					}
					$scope.prdData.reqhas=(attrData.requirements.length>0)?true:false;
					
					if(!angular.isUndefined(product_detail.attribute_set_id) && product_detail.attribute_set_id!=''){
						var o = _.filter($scope.prdData.spesattrset,(o)=> {return o.id==product_detail.attribute_set_id;});
						
						$scope.product.speschoseattrset=o[0];
						$scope._attrSetChange('prevAttrSet');
					}	
				}
			},(err)=>console.log);
		};
		//get sepecifiaction from server
		function _getCatogary(){
			salesfactoryData.getData(categorylisturl,'GET',{action : "productCatEdit"})
			.then((r)=>{
				if(r.data)
				  $scope.prdData.cateTree=r.data;
			},(err)=>_error());
		};
		//get warehouse data from server
		function _getWarehouse(){
			salesfactoryData.getData(warehoseurl,'GET','')
			.then((r)=>{
				var wrData = r.data;
				if(wrData){
					$scope.prdData.warehouse=wrData;
					for(var p in wrData){						
					   $scope.product.warehouse_model.push({
						   	name:false,
						   	stock:false,
						   	stock_value:"",
						   	lowstock_value:"",
						   	safetystock_value:"",
						   	warehouse_id:wrData[p].warehouse_id,
					   });  
					};
					_setPrevSelectedWarehouse();
				}
			},(error)=>_error());
		};

		_getCatogary();
		_getWarehouse();
		//if product type is bundel then not specification and requirement 
		if(product_type!==undefined && product_type!="bundle"){
			_getSpecification();
		}
		//Listen on set tire and bundel price
		$scope.setTireBundlePrice = (type)=>{
			if(type =='tire'){
				var tprices = [];
				angular.forEach(product_detail.has_tire_bundle_price, function(value, key) {
				    this.push({tireQnt:value.quantity,tireQntPrice:value.price});
				}, tprices);
				if(tprices.length>=1){
					$scope.product.tprices = tprices;	
				}
			} 
			if(type=='bundle'){
				var bprices = [];
				angular.forEach(product_detail.has_tire_bundle_price, function(value, key) {
				    this.push({bundelqunt:value.quantity,bundelprice:value.price});
				}, bprices);
				if(bprices.length>=1){
					$scope.product.bundelprice = bprices;	
				}
			}
		};
		//self executing function in case of product edit case for set all data
		function _editProductDataSet(){
			//set parent id for visibility
			$scope.prdData.parent_id = product_detail.parent_id;
			//This section used for default value in case of edit
			if(!angular.isUndefined(product_detail) && angular.isObject(product_detail)){
				if(!angular.isUndefined(product_detail.has_description) && angular.isArray(product_detail.has_description) && product_detail.has_description.length>0){
					_.map(product_detail.has_description,(item)=>{
						$scope.product.name[item.lang_id]=item.name;
						$scope.product.productDesc[item.lang_id]=item.description;
						$scope.product.seo.meta_title[item.lang_id]=item.meta_title;
						$scope.product.seo.meta_keyword[item.lang_id]=item.meta_keyword;
						$scope.product.seo.meta_description[item.lang_id]=item.meta_description;

					});
				}
				if(!angular.isUndefined(product_detail.has_tags) && angular.isArray(product_detail.has_tags) && product_detail.has_tags.length>0){
					$scope.product.prdtags= _.pluck(product_detail.has_tags,'get_tags');
				}

				$scope.product.autosku = (product_detail.auto_sku == 1 ) ? true: false;
				$scope.product.sku = product_detail.sku;
				$scope.product.initial_price = product_detail.initial_price;
				$scope.product.sp_tp_bp = (product_detail.has_sp_tp_bp=='true') ? true: false;
				$scope.product.selectedCateTree=_.pluck(product_detail.has_categories,'cat_id');
				//$scope.product.visibility= (product_detail.site_visibility!==undefined && product_detail.site_visibility==1)? true:false;
				$scope.product.visibility= product_detail.site_visibility.toString();
				

				//Special Price / Tire Price / Bundle Price enable/disable section
				if(product_detail.sp_tp_bp_type == 1){
					$scope.product.stbprice="specialprice";	
					$scope.product.special_price = product_detail.special_price;						
					 
					if(product_detail.from_date !== "0000-00-00"){
						$scope.product.sp_fromdate = product_detail.from_date;
					}			
					if(product_detail.to_date !== "0000-00-00"){
						$scope.product.sp_todate = product_detail.to_date;						
					}
					//set max and min date
					$scope.prdData["dateFromOption"].maxDate = $scope.product.sp_todate;
            		$scope.prdData["dateToOption"].minDate = $scope.product.sp_fromdate;							
				}else if (product_detail.sp_tp_bp_type == 2){
					$scope.product.stbprice="tireprice";
					$scope.setTireBundlePrice('tire');

				}else if(product_detail.sp_tp_bp_type == 3){
					$scope.product.stbprice="bundleprice";
					$scope.setTireBundlePrice('bundle');			
				}
		
				//processing time set
				if(product_detail.rship==1){
					$scope.product.ready_ship = true;
				}else{
					$scope.product.ready_ship = false;
					//set processing day and type
					$scope.product.processing_time = product_detail.delivertime;
					$scope.product.processing_type = parseInt(product_detail.processing_type);
				}
				
				//check expiry date and set 
				if(product_detail.nexpiry == 1){
					$scope.product.nexpiry = true;
				}else{
					$scope.product.nexpiry = false;
					if(product_detail.expiry_datetime!== "0"){
						$scope.product.expiry_date = product_detail.expiry_datetime;
					}
				}

				//product requirement set 
				if(getattributestructrue!==undefined){
					$scope.prdData.req_create_list = angular.copy(getattributestructrue);					
				}
				//product video 
				if(product_detail.has_product_video!==undefined && product_detail.has_product_video.length>0){
					$scope.prdData.videolist.length=0;
					// $scope.product.videoFile.length =0;
					_.forEach(product_detail.has_product_video,(item)=>{
						if(item.site =="youtube"){
							item.video_url = 'https://www.youtube.com/embed/'+item.vid;
						}else{
							item.video_url = 'https://player.vimeo.com/video/'+item.vid; 
						}						
					});
					$scope.prdData.videolist = angular.copy(product_detail.has_product_video);
					// $scope.product.videoFile = angular.copy(product_detail.has_product_video);
				}
				//unit nad dimension setting
				if(product_detail.product_width!==undefined && product_detail.product_width){
					$scope.product.product_width = product_detail.product_width;
				}
				if(product_detail.product_length!==undefined && product_detail.product_length){
					$scope.product.product_length = product_detail.product_length;
				}
				if(product_detail.product_height!==undefined && product_detail.product_height){
					$scope.product.product_height = product_detail.product_height;
				}
				if(product_detail.product_unit!==undefined && product_detail.product_unit){
					$scope.product.product_unit = product_detail.product_unit.toString();
				}
				if(product_detail.package_width!==undefined && product_detail.package_width){
					$scope.product.package_width = product_detail.package_width;
				}
				if(product_detail.package_length!==undefined && product_detail.package_length){
					$scope.product.package_length = product_detail.package_length;
				}
				if(product_detail.package_height!==undefined && product_detail.package_height){
					$scope.product.package_height = product_detail.package_height;
				}
				if(product_detail.package_unit!==undefined && product_detail.package_unit){
					$scope.product.package_unit = product_detail.package_unit.toString();
				}
				if(product_detail.product_weight!==undefined && product_detail.product_weight){
					$scope.product.product_weight = product_detail.product_weight;
				}
				if(product_detail.product_weight_unit!==undefined && product_detail.product_weight_unit){
					$scope.product.weight_unit = product_detail.product_weight_unit.toString();
				}
				if(product_detail.total_weight!==undefined && product_detail.total_weight){
					$scope.product.total_weight = product_detail.total_weight;
				}
				if(product_detail.total_weight_unit!==undefined && product_detail.total_weight_unit){
					$scope.product.total_weight_unit = product_detail.total_weight_unit.toString();
				}
								
			}
		};
		_editProductDataSet();

		$scope.activalangs = activelangs;
		var selectable = true; 
		/******This function used for load product tags on user input****/
		$scope._loadProdTags =(query)=>{
			if(query=='') return;
			var o={'query' : query};
			return salesfactoryData.getData(prdtagurl,'GET',o).then((r)=>{
				return r.data;
			},(error)=>{
				_error();
			});			
		};
		
		//Listen to load specification tag 
		$scope.loadSpecificationTag = function($qr, itemCollection){
			var deferred = $q.defer();
			deferred.resolve(itemCollection);			
			return deferred.promise.then(function(){
				return itemCollection.filter(function(spec) {
		        	return spec.values.toLowerCase().indexOf($qr.toLowerCase()) != -1;
		    	});
			});
		};
		/******This section used for price section like tire bundel sepcial
		*where mType = tprices,qnty = tire_quantity,qtPrice = tire_quantity_price,
		***/
		$scope.addTireprice =($event,type)=>{ 
			$event.stopPropagation();
			switch(type){
				case 'tirerpice':
					$scope.product.tprices.push({tireQnt:0,tireQntPrice:0});
					break;
				case 'bundelprice':
					$scope.product.bundelprice.push({bundelqunt:0,bundelprice:0});
					break;
				default:
				 break;
			};
		};
		$scope.removeTireprice =(index,type)=>{
			switch(type){
				case 'tirerpice':
					if($scope.product.tprices.length>1){
						 $scope.product.tprices.splice(index, 1);
					}else{
						_messageHandler('warning',"You can't remove tire price because \n You have checked tireprice",'Opps..');
					}
					break;
				case 'bundelprice':
					if($scope.product.bundelprice.length>1){
						 $scope.product.bundelprice.splice(index, 1);
					}else{
						_messageHandler('warning',"You can't remove bundel price because \n You have checked bundelprice",'Opps..');
					}
					break;
				default:
					break;
			};
		};
		$scope.switchPecialPrice =($event,spType)=>{
			$event.stopPropagation();
			console.log(spType);
			if(spType==='tireprice'){
				//reset bundel
				$scope.product.bundelprice=[];
				$scope.product.bundelprice.push({bundelqunt:"",bundelprice:""});
				//reset special price
				$scope.product.sp_fromdate = "";
				$scope.product.sp_todate = "";
				$scope.product.special_price = "";
				$scope.prdData["dateToOption"].minDate = "";
				$scope.prdData["dateFromOption"].maxDate = "";
			}else if(spType==='bundleprice'){
				//reset tire 
				$scope.product.tprices=[];
				$scope.product.tprices.push({tireQnt:"",tireQntPrice:""});
				//reset special 
				$scope.product.sp_fromdate = "";
				$scope.product.sp_todate = "";
				$scope.product.special_price = "";
				$scope.prdData["dateToOption"].minDate = "";
				$scope.prdData["dateFromOption"].maxDate = "";
			}else if(spType==='specialprice'){
				$scope.product.tprices=[];
				$scope.product.tprices.push({tireQnt:"",tireQntPrice:""});
				$scope.product.bundelprice=[];
				$scope.product.bundelprice.push({bundelqunt:"",bundelprice:""});
			}
		};
		$scope.sp_check_fun =()=>{
			if($scope.product.sp_tp_bp)
				$scope.product.stbprice ="specialprice";
			else{
				$scope.product.stbprice ="";
				$scope.product.tprices=[];
				$scope.product.tprices.push({tireQnt:"",tireQntPrice:""});
				$scope.product.bundelprice=[];
				$scope.product.bundelprice.push({bundelqunt:"",bundelprice:""});
				$scope.product.sp_fromdate = "";
				$scope.product.sp_todate = "";
				$scope.product.special_price = "";
				$scope.prdData["dateToOption"].minDate = "";
				$scope.prdData["dateFromOption"].maxDate = "";
			};
			// console.log($scope.product.sp_tp_bp);
		};

		/**validation initial price to special price**/
		let price_input_limit = (typeof priceInputLimit!="undefined" && priceInputLimit) ? parseInt(priceInputLimit) : 5;
		$scope.priceValidation = {
			basePrice : function(){
				let self = this;
				$scope.$evalAsync(function(){
					if($scope.product.initial_price && $scope.product.sp_tp_bp && $scope.product.stbprice=='specialprice' && $scope.product.special_price && 
						parseInt($scope.product.special_price)>parseInt($scope.product.initial_price)){
						_messageHandler("error", '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Base price must be greater than Special price </div>',"Error");
						$scope.product.initial_price = "";
					}
					//check price limit
					if($scope.product.initial_price && self.checkLimit($scope.product.initial_price)){						
						_messageHandler("error", '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Price must be lower than '+(price_input_limit+1)+' digits </div>',"Error");
						$scope.product.initial_price = "";
					}
				});
			},
			specialPrice : function(){
				let self = this;
				$scope.$evalAsync(function(){
					if($scope.product.special_price && !$scope.product.initial_price){
						_messageHandler("error", '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please enter base price first</div>',"Error");
						$scope.product.special_price = "";
					}else if($scope.product.initial_price && parseInt($scope.product.initial_price)<parseInt($scope.product.special_price)){
						_messageHandler("error", '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Special price must be less than price</div>',"Error");
						$scope.product.special_price = "";
					}

					//check price limit
					if($scope.product.special_price && self.checkLimit($scope.product.special_price)){						
						_messageHandler("error", '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Price must be lower than '+(price_input_limit+1)+' digits </div>',"Error");
						$scope.product.special_price = "";
					}
				});
			},
			tierPrice : function(item){
				let self = this;
				$scope.$evalAsync(()=>{
					if(item.tireQntPrice && self.checkLimit(item.tireQntPrice)){
						_messageHandler("error", '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Price must be lower than '+(price_input_limit+1)+' digits </div>',"Error");
						item.tireQntPrice = "";
					}
				});
			},
			checkLimit : function(price){				
				let p = price.split('.');
				return(p[0].length>price_input_limit);
			},	
		};
		
		/***** This section used for all varient function******/
		var clickCount =1,
			variantComb =[],
			varients_all_data=[];
		$scope.addVariants = function(){
		    clickCount++;
		    $scope.prdData.selectTags.push({});
		    if((($scope.prdData.variants.length)-1)<clickCount)
		        $scope.disAddAnotherBtn= true;
		}
		$scope.removeVariants =($event,index,tagIndex)=>{
		  $event.stopPropagation();
		  if($scope.prdData.selectTags.length>1){
		        $scope.disAddAnotherBtn= false;
		        $scope.prdData.selectTags.splice(index,1);
		        clickCount--;
		        $scope.product.varientmodel.splice(index,1);
		        $scope.product.variantTagsData[tagIndex]=[];
		        variantComb.splice(index,1);
		   }else{
		   	 _messageHandler('warning',"You can't remove this variants because \n You have checked multiple variants",'Opps..');
		   }
		};
		$scope._varientCheckAction = function(vstatus){
		    if(vstatus==true && $scope.prdData.selectTags.length<1){
		        $scope.prdData.selectTags=[{value:""}];
		    }
		    /****This case used to remove vaitant if unchecked****/
		    if(!vstatus){
		        $scope.product.varientmodel =[];
		        $scope.product.variantTagsData =[];
		    }
		};
		$scope.loadvariantoptions = function(query,index) {
			console.log('here?>>>');
			return varients_all_data[index];
			//for only matching element comment above line.
			//return _.filter(varients_all_data[index],(o)=>{ return o.values.toLowerCase()==(query.toLowerCase())});
			// return .filter(function(option) {
			// 	return option.values.toLowerCase().indexOf(query.toLowerCase()) != -1;
			// });        
		};
		$scope.getAttributValuesAndAttribute = function(index,modelIndex,optIndex){
			if(optIndex<0) return;
			var data = {"vid":$scope.product.varientmodel[index].attr_id};
			variantComb[index] = optIndex;
			salesfactoryData.getData(variantUrl,'GET',data).then((r)=>{
				if(_.isArray(r.data) && r.data.length>0) varients_all_data[optIndex] = r.data;
			},(error)=>{
				_error();
			});
		};
		/****This section used for video**/
		var vidfile ='',user_uploadImg='',imgInd=0;
		$scope.getVideoDetail=()=>{
			var youtube_reg = new RegExp(/^(http|https\:\/\/)?(www\.youtube|youtu\.be)\/.+$/);
       		var url_regexp =  /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
       		//check condtion for url 
       		if($scope.product.videodobj.videourl==undefined || $scope.product.videodobj.videourl=='' || !url_regexp.test($scope.product.videodobj.videourl)) return;
			//check youtube share usl
			if($scope.product.videodobj.videourl.match(youtube_reg)){
	            var youtube_id=$scope.product.videodobj.videourl.split(/v\/|v=|youtu\.be\//)[1].split(/[?&]/)[0];
	            $scope.product.videodobj.videourl = "http://www.youtube.com/watch?v=" + youtube_id;  
	        }
			
		    $scope.vloader = true;
		    var obj = {'v_url':$scope.product.videodobj.videourl}; 
		    salesfactoryData.getData(_getvideourl,'POST',obj).then((r)=>{
		    	var d = r.data;
		    	if(d.status=='success' && d){ 
		    		$scope.videotemp = d;
		    		if(!_.isUndefined(d.id)){
		    			$scope.product.videodobj.video_id = d.id;
			           	$scope.product.videodobj.video_url = $sce.trustAsResourceUrl(d.video_url);
			            $scope.product.videodobj.video_prev_img = d.thumbnail_small;
			            $scope.product.videodobj.video_title = d.title;
			            $scope.product.videodobj.description = d.description;  
			            $scope.vloader =false;  
		           }
		    	}else{
		    		$scope.vloader =false;
		        	_error();
		    	}
		    },(error)=>{
		        $scope.vloader =false;
		        _error();
		    });
		};
		$scope.refreshVideoForm =()=>{
			for(var p in $scope.product.videodobj)
			   if($scope.product.videodobj.hasOwnProperty(p))
						$scope.product.videodobj[p] = '';
		};
		$scope.addVideo = ()=>{
		  //case if user can change preview image
		  /*  if(typeof vidfile !='undefined' && vidfile!=''){
		        $scope.product.videoFile.push(vidfile);
		        $scope.videotemp.usrfileIndex = ++imgInd;
		        vidfile ='';
		    }else {$scope.videotemp.usrfileIndex = 0};*/
		    $scope.videotemp.vid = $scope.product.videodobj.video_id;
		    $scope.videotemp.videourl = $scope.product.videodobj.videourl;
		    $scope.videotemp.video_title = $scope.product.videodobj.video_title;
		    $scope.videotemp.description = $scope.product.videodobj.description;
		    $scope.videotemp.user_uploadImg = user_uploadImg;
		    //set video in liast nad file
		    var video_temp = angular.copy($scope.videotemp);
		    $scope.prdData.videolist.push(video_temp);
		    $scope.product.videoFile.push(video_temp);
		    //reset videodobj field
		    user_uploadImg ='',$scope.product.videodobj.videourl="",$scope.product.videodobj.video_id="",$scope.product.videodobj.video_title="",$scope.product.videodobj.description="";
		};
		$scope.removeVideo = (vindex,usrfileIndex)=>{
			//check pre uploaded vidoe 
			if(product_detail.has_product_video.length>0){
				var prev_vidoe = _.find(product_detail.has_product_video,{'id' : usrfileIndex.id});
				if(prev_vidoe!= undefined){
					$scope.prdData.loading.save_and_continue = true;
					$scope.prdData.loading.disableBtn = true;
					salesfactoryData.getData(prdvideo_delete_url,'POST',{"vid" : prev_vidoe.id,"product_id": prev_vidoe.product_id })
					.then((resp)=>{
						$scope.prdData.loading.save_and_continue = false;
						$scope.prdData.loading.disableBtn = false;
						if((resp.data.status).toString()=="success"){
							 _messageHandler(resp.data.status,resp.data.mesg,resp.data.mesg_title);	
							 var vind = _.findIndex(product_detail.has_product_video,(o)=>{return o.id ==  prev_vidoe.id});
							 $scope.prdData.videolist.splice(vindex, 1);
							 product_detail.has_product_video.splice(vind, 1);
						}else{
							_error();
						}
					},(error)=>{
						$scope.prdData.loading.save_and_continue = false;
						$scope.prdData.loading.disableBtn = false;
						_error();
					});
				}
			}else{
				$scope.prdData.videolist.splice(vindex, 1);
		   		$scope.product.videoFile.splice(vindex, 1);
			}
		    //case if user can change preview image
		   /* if(usrfileIndex!==0)
		        $scope.product.videoFile.splice(usrfileIndex-1,1);*/
		};
		/*****This section used for file upload***/		
	    // Listen for when the interface has been configured.
        $scope.$on('$dropletReady', function whenDropletReady() {
        	if($scope.product.file_interface.allowedExtensions){
				$scope.product.file_interface.allowedExtensions(file_upload_setting.allowed_extension);
				$scope.product.file_interface.useArray(true);
				//number of file can upload onces
				$scope.product.file_interface.setMaximumValidFiles(file_upload_setting.max_number_file);
				// total number of file can upload
				$scope.product.file_interface.setMaxNumberFiles(file_upload_setting.max_number_file);
			}
			if($scope.product.remind_icon_file_interface.allowedExtensions){
				$scope.product.remind_icon_file_interface.allowedExtensions(file_upload_setting.allowed_extension);
				$scope.product.remind_icon_file_interface.useArray(true);
			}				
        });
        // Listen for when the files have been successfully uploaded.
        $scope.$on('$dropletSuccess', function onDropletSuccess(event, response, files) {
      	    $scope.uploadCount = files.length;
            $scope.success     = true;
            $timeout(function timeout() {
                $scope.success = false;
            }, 5000);
        });

        $scope.$on('$dropletFileAdded', function onDropletSuccess(event,file) {
        	//for file size check (file size in Bytes)
    		var FSize  = file.file.size;
    		FSize =(FSize/1024/1024).toFixed(2);
    		var validDropletFiles = $scope.product.file_interface.getFiles().filter(function(file) {
				return (file.type !== 4);
			});

         	if(file.type & $scope.product.file_interface.FILE_TYPES.INVALID){
         		if(file.extension !== undefined && _getIndex(file_upload_setting.allowed_extension, file.extension) == -1){
        			_messageHandler('warning','file extension not valid \n Please upload : ' +file_upload_setting.allowed_extension,'Opps..');
         		}else if(file.maximum!== undefined && validDropletFiles.length > file.maximum){
         			file.deleteFile();
         			_messageHandler('warning','You are exceeds max allowed \t'+file_upload_setting.max_number_file,'Opps..');
         		}else if(file.type && FSize>file_upload_setting.max_file_size){
         			file.deleteFile();         	 
         	   		_messageHandler('warning','File is too Large than max allowed  : \t'+file_upload_setting.max_file_size +'MB','Opps..');
         		}
         	}else if(file.type && FSize>file_upload_setting.max_file_size){
         	   // In case file size larger than alowed file size
         	   file.deleteFile();         	 
         	   _messageHandler('warning','File is too Large than max allowed  : \t'+file_upload_setting.max_file_size +'MB','Opps..');
         	}else if(file.maximum!== undefined && validDropletFiles.length > file.maximum){
         		file.deleteFile();
         		_messageHandler('warning','You are exceeds max allowed \t'+file_upload_setting.max_number_file,'Opps..');
         	}

         	$scope.prdData.up_cl_all = true;
        });

        /**
         *file upload new handler xmlHttprequest
         * @method uploadFiles
         * @return {$q.promise}
         */
       $scope.uploadFiles = function uploadFiles(_url, method_type, imgObj) {       	
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

			// Initiate the HTTP request.
            httpRequest.open(method_type, _url, true);

            /**
	        * @method appendCustomData
	        * @return {void}
	        */
            (function appendCustomData() {
            	// Setup the file size of the request.
            	httpRequest.setRequestHeader('X-File-Size', queuedFiles.file.size);
                httpRequest.setRequestHeader("X-CSRF-TOKEN", window.Laravel.csrfToken);
            })();

            /**
			 * @method isValid
			 * @param value {String|Number}
			 * @param values {Array}
			 * @return {Boolean}
			 * @private
			 */
			var isValid = function isValid(value, values) {
			    var conditionallyLowercase = function conditionallyLowercase(value) {
			        if (typeof value === 'string') {
			            return value.toLowerCase();
			        }
			        return value;
			    };

			    return values.some(function some(currentValue) {
			        var isRegExp = (currentValue instanceof $window.RegExp);

			        if (isRegExp) {
			            // Evaluate the status code as a regular expression.
			            return currentValue.test(conditionallyLowercase(value));
			        }
			        return conditionallyLowercase(currentValue) === conditionallyLowercase(value);
			    });
			};

            /**
	         * Invoked once everything has been uploaded.
	         *
	         * @method success
	         * @return {void}
	         */
            httpRequest.onreadystatechange = function onReadyStateChange() {
                if (httpRequest.readyState === 4) {
                	if(isValid(httpRequest.status, [/2.{2}/])){
						$scope.$evalAsync(function(){
							deferred.resolve(httpRequest.responseText);	
						});
                		return;
                	}
                	// Error was thrown instead.
                	httpRequest.upload.onerror();
                }
            }.bind(this);

             /**
             * Invoked when an error is thrown when uploading the files.
             *
             * @method error
             * @return {void}
             */
            httpRequest.upload.onerror = function onError() {
            	$scope.$evalAsync(function(){
            		deferred.reject(httpRequest.responseText);
            	});                
            }.bind(this);

            /**
             * Invoked each time there's a progress update on the files being uploaded.
             *
             * @method progress
             * @return {void}
             */
	  		httpRequest.upload.onprogress = function onProgress(event){
	  			var requestLength = queuedFiles.file.size;
	  			$scope.$evalAsync(function(){
	  				queuedFiles.customData.inprogress = true;

	            	if (event.lengthComputable){ 
	            		queuedFiles.customData.percent = Math.floor((event.loaded / event.total) * 100) +"%";
	            	}
	  			});	  			
            }.bind(this); 

            // Iterate all of the valid files to append them to the previously created
            // `formData` object.
           	formData.append("product_image", queuedFiles.file, $window.encodeURIComponent(queuedFiles.file.name));
                      
          	httpRequest.send(formData);

          	return deferred.promise;
       };

        //Listen on all clear file
		$scope._clearallfiles = ()=>{
			var temp_file = $scope.product.file_interface.getFiles($scope.product.file_interface.FILE_TYPES.VALID);
			_.map(temp_file, function(item){
				//In case file is already uploaded on server then remove from temp table
				if(item.customData!== undefined && (item.customData.uploaded!== undefined && item.customData.uploaded === true)){
					_removeImage(item);					
				}else{
					item.deleteFile();					
				}

				$scope.product.file_interface.addRemoveCustomData("remove_file", {}, item.file);				
			});
			getSetPrevImgDefault("set","clearall");
		};

		//Invoke on remove image from temp table 
		function _removeImage(imgObj){
			imgObj.customData.inprogress = true;
			var _t = {img_id : imgObj.customData.img_id};

			salesfactoryData.getData(img_temp_delete_url, "POST", _t)
			.then(function(response){
				if(response.data.status!== undefined &&  response.data.status === "success"){
					var index =  _getIndex(_uploadedFileIdArray, _t.img_id, "img_id");
					if(index!= -1){
						_uploadedFileIdArray.splice(index, 1);
					}
					imgObj.deleteFile();
					imgObj.customData.inprogress = false;
				}else{
					imgObj.customData.inprogress = false;
				}
			},function(error){
				imgObj.customData.inprogress = false;
				imgObj.customData.error	= false;
				_error();
			});
		}

		//Listen on Image upload
		$scope.onImageUpload = function(imageItem, strFlag){
			var _sp = $scope.product.file_interface;			
			//for single image upload 
			if(strFlag!== undefined && strFlag === "single_upload"){				
				upload(imageItem);
			}else if(strFlag!== undefined && strFlag === "all_upload"){
				//for multi image  upload
				var fls = _.filter(_sp.getFiles(), function(fs){
					return (fs.type!==4 && !fs.customData);
				});	
				var fsc = fls[0];
					fls.splice(0, 1);
				upload(fsc, fls);			
			}
			
			/*
			*desc : upload to server one by one
			*@imgObj {file}
			*@fileLists {array of file}
			*/
			function upload(imgObj, fileLists){				
				$scope.uploadFiles(upload_image_url, "POST", imgObj)
				.then(function(data){
					var response = JSON.parse(data);
					if(response.status!== undefined && response.status === "success"){						
						imgObj.customData.completed = true; 
						imgObj.customData.img_id = response.img_id;
						imgObj.customData.inprogress = false;
						// imgObj.customData.percent = "0%";
						imgObj.customData.error	= false;										
						_uploadedFileIdArray.push({"img_id" : response.img_id, "img_name" : response.img_name});
					}else{
						//not valid response
						imgObj.customData.completed = false; 
						imgObj.customData.inprogress = false;
						imgObj.customData.percent = "0%";
						imgObj.customData.error	= true;
					}
				},function(error){
					imgObj.customData.completed = false;
					imgObj.customData.img_id = "";
					imgObj.customData.inprogress = false;
					imgObj.customData.percent = "0%";
					imgObj.customData.error	= true;
					_error();					
				}).finally(function(){				
					if(typeof fileLists!="undefined" && fileLists.length) {
						var f = fileLists[0];
							fileLists.splice(0,1);
						upload(f, fileLists);
						
					}
				});
			};		
		};

		/*********This function used for delete previous uploaded image on product add page**********/
	    $scope.deletePrevUploadImg = function($event,fileId, index){
	    	$event.stopPropagation();
	        swal({
		        title: "Are you sure?",
		        text: "You will not be able to recover this imaginary file!",
		        type: "warning",
		        showCancelButton: true,
		        confirmButtonColor: '#DD6B55',
		        confirmButtonText: 'Yes, I am sure!',
		        cancelButtonText: "No, cancel it!",
		        closeOnConfirm: false,
		        closeOnCancel: false
		    }).then(function(isConfirm){
		        if (isConfirm) {
		        	//In case of copy product or bundel copy don't go to remove image from server
		        	if(typeof current_blade_type !== "undefined" &&  (current_blade_type === "copy_product" || current_blade_type === "copy" )){
		        		removePrevImg(index, fileId);
		        		_messageHandler("success", "Image deleted successfully", "Cool");
		        		//$scope.prdData.hide=true;
		        		return false;
		        	}

		        	//action to server 
		        	salesfactoryData.getData(prdimage_delete_url,'POST',{"imageId" : fileId})
		            .then(function(response){
		                if(response.data.status=='success') {
		                   _messageHandler(response.data.status,response.data.mesg,response.data.mesg_title);
						   removePrevImg(index, fileId);

		                }
		                if(response.data.status=='error'){		                	
		                    swal({
		                      title: response.data.mesg_title,
		                      text: response.data.mesg,
		                      type: "error",
		                      confirmButtonText: response.data.c_btn_text
		                    });
		                }
		            },function(erro){
		            	_error();
		            });            
		        }
		    });

		    //Listen on remove previous product image 
		    function removePrevImg(index, fileId){		    	
		    	$scope.$evalAsync(function(){
					var indx = _.findIndex($scope.product.productImages,function(o){
						return o.id!==undefined && o.id==fileId;
					});

					if(indx!=-1){
						 $scope.prevImage.splice(index,1); 
						 $scope.product.productImages.splice(indx,1);
						 $scope.prdData.prev_image_order.splice(index, 1);
						 
						 if(prd_defalut_img.prevImg!==undefined && prd_defalut_img.prevImg==index){							 	
							prd_defalut_img.prevImg ="";							 	
							getSetPrevImgDefault('set', "delete");							
						 }else if(prd_defalut_img.prevImg!==undefined && prd_defalut_img.prevImg){
						 	var _p = _getIndex($scope.prevImage, "1", "isDefault");
						 	prd_defalut_img.prevImg = _p;
						 	getSetPrevImgDefault('set', "delete");						 
						 }
					}

					//calculate previous upload file and set file upload restiction
					file_upload_setting.max_number_file = parseInt(file_upload_setting.max_number_file) + 1 ;
					$scope.product.file_interface.setMaximumValidFiles(file_upload_setting.max_number_file);
					$scope.product.file_interface.setMaxNumberFiles(file_upload_setting.max_number_file);
				});
		    }
		};
 		/*****end file upload***/
 		/*****this section used for warehouse****/
 		$scope._wareCheckHendler=(status,index,actType)=>{
 			switch(actType){
 				case "managewarehouse":
 					if(!status){
 						$scope.product.warehouse_model[index].stock=false;
 						$scope.product.warehouse_model[index].stock_value="";
 						$scope.product.warehouse_model[index].lowstock_value="";
 						$scope.product.warehouse_model[index].safetystock_value="";
 						//$scope.product.warehouse_model[index].warehouse_id=null;
 					}
 					break;
 				case "managestock":
					$scope.product.warehouse_model[index].stock_value="";
					$scope.product.warehouse_model[index].lowstock_value="";
					$scope.product.warehouse_model[index].safetystock_value="";
					//$scope.product.warehouse_model[index].warehouse_id=null;
 					break;
 				default:
 				break;
 			};
 		};
 		/********Product variants and specification,requirement section*********/
 		//edit section
 		// var t= _.filter($scope.prdData.spesattrset,product_detail.attribute_set_id);
 		// // var t = _.filter($scope.prdData.spesattrset,function(o) {
 		// // 	return o.id==product_detail.attribute_set_id;
 		// // });
 		$scope.dropCallback = function(index, item, external, type, flag) {
			if(flag==='requirement'){
				var temp =[];
				if(item.get_all_attribute_value_detail.length>0){
					(item.get_all_attribute_value_detail).map((it)=>{
						temp.push({check:false,priceval:"",pricetype:"",orderval:"",attr_val_id:it.attr_val_id});
					});
				}
				$scope.$evalAsync(() => {
					$scope.product.reqmodel.push({priceval:"",pricetype:'Percent',oderval:"",id:item.id,multiopt:temp, id:item.id, front_input: item.front_input, required: item.required});
					var indx = _.findIndex($scope.prdData.reqlist, (x) => x.id === item.id );
					if(indx !== -1){
						$scope.prdData.reqlist.splice(indx, 1);
					}
					$scope.prdData.req_create_list.push(item);
				})
			}
			else if(flag==='specification'){
				$scope.$evalAsync(() => {
					$scope.product.specmodel.push({textVal:"",textareaVal:"",usevaiant:false,attrselect:"",mulattrselect:"",id:item.id, front_input: item.front_input, required: item.required});
					var indx = _.findIndex($scope.prdData.specslist, (x) => x.id === item.id );
					if(indx !== -1){
						$scope.prdData.specslist.splice(indx, 1);
					}
					$scope.prdData.specs_create_list.push(item);
				})
			}
			// Return false here to cancel drop. Return true if you insert the item yourself.
			return true;
		};
 		$scope._removeSep= ($event,index,strFlag,item)=>{
			//this function used to remove object from  specs_create_list
			$event.preventDefault(); 
			if(strFlag === 'specification'){
				var temp = angular.copy($scope.prdData.specslist);
				temp = temp.concat(item);
				$scope.prdData.specslist = temp;
				var ind = $scope.prdData.specs_create_list.findIndex(x=>x.id===item.id);
				var ind1 = $scope.product.specmodel.findIndex(x=>x.id===item.id);
				$scope.prdData.specs_create_list.splice(ind,1);
				$scope.product.specmodel.splice(ind1,1);
			}else if(strFlag === 'requirement'){  
				var temp = angular.copy($scope.prdData.reqlist);
				temp = temp.concat(item);
				$scope.prdData.reqlist = temp;
				var ind = $scope.prdData.req_create_list.findIndex(x=>x.id===item.id);
				var ind1 = $scope.product.reqmodel.findIndex(x=>x.id===item.id);
				$scope.prdData.req_create_list.splice(ind,1);
				$scope.product.reqmodel.splice(ind1,1);
			}
		};		
		$scope.moveHandler=(item,flag)=>{
			if(flag==='requirement'){
				var temp =[];
				if(item.get_all_attribute_value_detail.length>0){
					(item.get_all_attribute_value_detail).map((it)=>{
						temp.push({check:false,priceval:"",pricetype:"",orderval:"",attr_val_id:it.attr_val_id});
					});
				}
				$scope.product.reqmodel.push({priceval:"",pricetype:'Percent',oderval:"",id:item.id,multiopt:temp});
			}
			else if(flag==='specification'){
				$scope.product.specmodel.push({textVal:"",textareaVal:"",usevaiant:false,attrselect:"",mulattrselect:"",id:item.id, front_input: item.front_input, required: item.required});
				$scope.$evalAsync(() => {
					$scope.prdData.specslist = _.without($scope.prdData.specslist, item);
				})
			}
			//{textVal:null,textareaVal:null,usevaiant:false,attrselect:null,mulattrselect:null,id:null,deldate:null}
		};

		$scope._attrSetChange =(strflag)=>{
			var o={"product_id" : product_detail.id};
			if($scope.product.speschoseattrset!==null || $scope.product.speschoseattrset!=="") o["attr_set_id"]=$scope.product.speschoseattrset.id;
			else{
				o["attr_set_id"] ='';
				$scope.prdData.spescrtupdate="createattrset";
			}
			salesfactoryData.getData(attributeurl,'GET',o).then((r)=>{
				if(r.data){
					//empty all array 
					$scope.prdData.specs_create_list.length=0;
					$scope.prdData.specslist.length = 0;
					$scope.product.specmodel.length =0;
					//set value
					$scope.copyAttributeSetDataToSpecCreateList(r.data.set_attributes);					
					$scope.prdData.specslist = r.data.except_attributes;					
					$scope.copyAttributeSetDataToSpecModel(r.data.set_attributes);
					if(strflag!==undefined && strflag==="prevAttrSet"){
						_setPrevSelectedSpec();
					}					
				}
			},(error)=>{
				_error();
			});
		};
		//Listen on copy attr set
		$scope.copyAttributeSetDataToSpecCreateList = (attrSetData) => {
           if(attrSetData !== undefined){
              $scope.prdData.specs_create_list = attrSetData;                      
           }
       	};
        $scope.copyAttributeSetDataToSpecModel = (attrSetData) => {
            if(attrSetData !== undefined){
               $scope.product.specmodel = _.map(attrSetData, (attrData, indx) => {
                   var specData = { 
                           textVal:"",
                           selectVal:"",
                           textareaVal:"",
                           usevaiant: (attrData['is_varient'] === "1")? true : false, 
                           attrselect:"",
                           mulattrselect:"",
                           id: attrData['id'],
                           front_input: attrData['front_input'],
                           required: attrData['required']
                   }
                   return specData;
               });
            }
       	};

		$scope.attrRadiochange=(flag)=>{
			if(flag ==='updateattrset'){
				$scope.product.sepscreateset={attrname:"",attrdesp:"",labelflag:"",fontcolor:"",flagbgcolor:""};
			}
		};
		//lISTEN ON SET PREVIOUS SPECIFICATION SELECTED VALUE
		function _setPrevSelectedSpec(){		
			angular.forEach($scope.prdData.specs_create_list, (item,index)=>{
				if(item.front_input==="multiselect"){
					//var _filerObj = _.find(spec_selected_value,{"attribute_id" : item.id});
					var _filerObj = _.find(spec_selected_value, function(mpsitem){
						return (mpsitem.attribute_id == item.id);						
					});
					var v =[];					
					for(var i in _filerObj.attribute_values){
						var r = _.filter(item.get_all_attribute_value_detail,(o)=>{return o.attr_val_id == _filerObj.attribute_values[i]});
						v= v.concat(r);					
					}
					$scope.product.specmodel[index]={
						 textVal:"",
						 textareaVal:"",
						 usevaiant:false,
						 attrselect:"",
						 mulattrselect:(v.length>0)?v: "",
						 id:item.id, 
						 front_input: item.front_input,
						 required: item.required,
					};					
				}else if(item.front_input==="select"){
					//thre are tow condition in this section is_varient or not!
					//var _filerObj = _.find(spec_selected_value,{"attribute_id" : item.id});
					var _filerObj = _.find(spec_selected_value, function(spitem){
						return (spitem.attribute_id == item.id);						
					});
					var ind = _.findIndex(item.get_all_attribute_value_detail,(o)=>{
						return (o.attr_val_id == _filerObj.attribute_values);
					});
					$scope.product.specmodel[index]={
						 textVal:"",
						 textareaVal:"",
						 usevaiant:(parseInt(item.is_varient)===1) ? true : false,
						 attrselect: (ind!=-1)? item.get_all_attribute_value_detail[ind] : "",
						 mulattrselect:"",
						 id:item.id, 
						 front_input: item.front_input,
						 required: item.required,
					};					
				}else if(item.front_input === "text"  || item.front_input === "textarea"){
					//var _filerObj = _.find(spec_selected_value,{"attribute_id" : item.id});
					var _filerObj = _.find(spec_selected_value, function(txtItem){
						return(txtItem.attribute_id == item.id);
					});
					$scope.product.specmodel[index]={
						 textVal:(_filerObj.attribute_values && item.front_input === "text")? _filerObj.attribute_values : "",
						 textareaVal:(_filerObj.attribute_values && item.front_input === "textarea")? _filerObj.attribute_values : "",
						 usevaiant:(parseInt(item.is_varient)===1) ? true : false,
						 attrselect: "",
						 mulattrselect:"",
						 id:item.id, 
						 front_input: item.front_input,
						 required: item.required,
					};					
				}else if(item.front_input=== 'date_picker'){
					//var _filerObj = _.find(spec_selected_value,{"attribute_id" : item.id});
					var _filerObj = _.find(spec_selected_value, function(dpItem){
						return(dpItem.attribute_id == item.id);
					});
					$scope.product.specmodel[index]={
						 textVal: "",
						 textareaVal:"",
						 usevaiant:false,
						 attrselect: "",
						 mulattrselect:"",
						 deldate : _filerObj.attribute_values.replace(/\-/g,'/'),
						 // new Date(_filerObj.attribute_values),
						 id:item.id, 
						 front_input: item.front_input,
						 required: item.required,
					};
					//$scope.product.specmodel[index] = {"deldate" : _filerObj.attribute_values};		
					
				}else if(item.front_input==="browse_file"){
					var _filerObj = _.find(spec_selected_value,{"attribute_id" : item.id});
					$scope.product.specmodel[index]={
						 textVal: null,
						 textareaVal:null,
						 usevaiant:(parseInt(item.is_varient)===1) ? true : false,
						 attrselect: null,
						 mulattrselect:null,						
						 id:item.id, 
						 front_input: item.front_input,
						 required: item.required,
						 attribute_values : (_filerObj.attribute_values)? _filerObj.attribute_values : "",
					};
				}
			});			
		};
		//LISTEN ON SET PREVIOUS REQUIREMENT SELECTED VALUE
		function _setPrevSelectedReq(){
			var check_selected_req =false;
			//check selected requirement 
			if(selected_requirment!==undefined && angular.isArray(selected_requirment) && selected_requirment.length>0){
				check_selected_req =true;
			}else {
				check_selected_req =false;
			}
			//remove prev selected requirement list
			angular.forEach($scope.prdData.req_create_list, (item,index)=>{
				var ind = _.findIndex($scope.prdData.reqlist,(o)=>{ return o.id == item.id;});
				if(check_selected_req===true){
					//if item.front_input==="multiselect" || item.front_input==="select"
					//else if "item.front_input=='text' || item.front_input=='textarea' || item.front_input=='browse_file' || item.front_input=='date_picker'"
					if(item.front_input==="multiselect" || item.front_input==="select"){
						var _filerObj = _.find(selected_requirment,{"attribute_id" : item.id});
						var v =[];
						if(_filerObj.option_attr_value_detail.length>0)	{
							for(var i in _filerObj.option_attr_value_detail){
								var r = _.filter(item.get_all_attribute_value_detail,(o)=>{return o.attr_val_id == _filerObj.option_attr_value_detail[i].attribute_value_id});
								if(r.length>0){
									v[i] = {
										"check" : true,
										"priceval" : _filerObj.option_attr_value_detail[i].price,
										"pricetype" : (_filerObj.option_attr_value_detail[i].price_type == 1)? "Fixed" : "Percent" ,
										"orderval" : _filerObj.option_attr_value_detail[i].visibility_order,
										"attr_val_id" : _filerObj.option_attr_value_detail[i].attribute_value_id,
									};
								}			
							}
						}
						//create model and set value 
						$scope.product.reqmodel[index]={
							priceval:"",pricetype:'Percent',
							oderval:_filerObj.visibility_order,
							id:item.id,
							multiopt:(v.length>0) ? v : [], 
							id:item.id,
						    front_input: item.front_input, 
						    required: item.required,
						};	
					}else if(item.front_input === "text"  || item.front_input === "textarea" || item.front_input==="browse_file" || item.front_input==="date_picker"){
						var _filerObj = _.find(selected_requirment,{"attribute_id" : item.id});
						if(_filerObj){
							$scope.product.reqmodel[index] = {
								"priceval" : _filerObj.price,
								"pricetype" : (_filerObj.price_type == 1)? "Fixed" : "Percent",
								"oderval"  : _filerObj.visibility_order,
								"id" : item.id,
								"multiopt" :[],
								"front_input": item.front_input,
								"required": item.required,
							};
						}
					}
				}
				if(ind!=-1){
					$scope.prdData.reqlist.splice(ind,1);
				}
			});
		};
		//LISTEN ON SET PREVIOUS WAREHOUSE SELECTED VALUE
		function _setPrevSelectedWarehouse(){
			if(product_detail.has_warehouse.length>0){
				var index= 0;
				_.forEach(product_detail.has_warehouse,(item,key)=>{
					if(parseInt(item.is_default) === 1){
						if(parseInt(item.has_unlimited)===1){
							$scope.product.defaultwhouse.isunlimited = true;
							$scope.product.defaultwhouse.id= item.warehouse_id;
						}else if(parseInt(item.has_unlimited)!==1){
							$scope.product.defaultwhouse.isunlimited = false;
							$scope.product.defaultwhouse.stock_value = (item.quantity) ? item.quantity : 0;
							$scope.product.defaultwhouse.lowstock_value = (item.low_stock) ? item.low_stock : 0;
							$scope.product.defaultwhouse.safetystock_value =(item.safty_stock) ? item.safty_stock : 0;
							$scope.product.defaultwhouse.id= item.warehouse_id;
						}
					}else if(parseInt(item.is_default) === 2){
						$scope.product.warehouse_model[index]={
							name:(item.has_unlimited == 1 || item.has_unlimited == 2)? true : false,
							stock:(item.has_unlimited == 2 )? true : false,
							stock_value:item.quantity,
							lowstock_value:item.low_stock,
							safetystock_value:item.safty_stock,
							warehouse_id:item.warehouse_id,
						}; 
						index++;							
					}					
				});
			}
		};
			
		//Listen on get and set prev image default @param (set/get)
		function getSetPrevImgDefault(flag, action){
			$scope.$evalAsync(function(){				
				//if action clear all 
				if(action!==undefined && (action=== "clearall" || action=== "cancel" ||  action=== "delete")){
					if(prd_defalut_img.prevImg!== undefined && prd_defalut_img.prevImg!==""){
						$scope.product.prevDefaultImage = prd_defalut_img.prevImg.toString();
						$scope.product.defaultImage = "";
						prd_defalut_img.dropImg = "";
					}else{					
						var indf = _getIndex(product_detail.has_product_image, "1", "isDefault");										
						if(indf!== -1){
							$scope.product.prevDefaultImage = indf.toString();
							$scope.product.defaultImage = "";
							prd_defalut_img.prevImg = indf;
							prd_defalut_img.dropImg = "";	
						}
					}
				}
			});			
		};
		/*
		*Listen on file upload (set default chenge).
		* @param item 
		* @param flag (dropImg/prevImg)
		* @param index
		*/		
		$scope.addCheckAttrInItem=(item,flag,index)=>{
			if(flag==="prevImg"){
				prd_defalut_img[flag] = index;
				prd_defalut_img["dropImg"] = "";
				$scope.product.prevDefaultImage = index.toString();
				$scope.product.defaultImage = "";	
			}else if(flag==="dropImg"){
				prd_defalut_img[flag] = index;
				prd_defalut_img["prevImg"] = "";
				$scope.product.prevDefaultImage = "";
				$scope.product.defaultImage = index.toString();
			}	
		};

		//get number of filter data 
        $scope.getFilterData = function(){
          return $filter('filter')($scope.prdData.specslist, $scope.prdData.query);
        };

		//get total number of attribute count
        $scope.getSpecTotalPageCount = (totalFilteredSpecCount) => {
        	if(typeof totalFilteredSpecCount!="undefined"){
        		return Math.ceil(totalFilteredSpecCount/$scope.prdData.pageSize); 
        	}else {
        		return Math.ceil($scope.getFilterData().length/$scope.prdData.pageSize);  
        	}              
        };		
		
		$scope.specListQueryChanged = (filteredSpecsList) => {
			// If the current page number exceeds the maximum page number, after filtering on a farther page.
			// Eg: If currentpage is 10, but after filtering there is only 1 page. This resets back to page 1
			if( $scope.prdData.currentPage > $scope.getSpecTotalPageCount(filteredSpecsList.length)-1 ){
				$scope.prdData.currentPage = Math.max($scope.getSpecTotalPageCount(filteredSpecsList.length)-1, 0);
			}
		}
		//Listen on product image cancel
		$scope.onDeleteImageFromImageUploadList = (fileitem,index) => {
			//remove already uploaded on server
			if(fileitem.customData!== undefined && (fileitem.customData.completed!== undefined && fileitem.customData.completed === true)){
				_removeImage(fileitem);				
			}else{
				fileitem.deleteFile();				
			}
			//remove image order
			$scope.prdData.image_order.splice(index, 1);
			$scope.product.file_interface.addRemoveCustomData("remove_file", {}, fileitem.file);

			// Filter out images that are not "ngDroplet - type:4 - Deleted".
			var validDropletFiles = _.filter($scope.product.file_interface.getFiles(), file => (file.type !== 4));
			defaultImgIndx = parseInt($scope.product.defaultImage)

			if(!isNaN(defaultImgIndx) &&  (defaultImgIndx == index)){
				getSetPrevImgDefault("set", "cancel");
			}else if(!isNaN(defaultImgIndx) &&  (validDropletFiles.length > 0)){
				// In case the number of images are less than the last activated "set as display image" index
				if(defaultImgIndx >= validDropletFiles.length){
					$scope.product.defaultImage = "" + (validDropletFiles.length - 1);
				}
			}						
		};

		$scope.processFilePickerFiles = (productObj) => {
			return _.reduce(productObj.specmodel, (acc, spec, indx) => {
				if(productObj.specmodel[indx].filepickerFile !== undefined){
					// File picker references cant be cloned to productObj (clone of $scope.product)
					// Hence obtain the references from $scope.product
					acc = acc.then(() => getBase64Url($scope.product.specmodel[indx].filepickerFile[0]))
										.then((fileJsonObj) => {
											productObj.specmodel[indx].filepickerVal = fileJsonObj
										})
										.catch((err) => console.log );
				}
				return acc;
			}, Promise.resolve())
			.then(() => productObj);
		}

		$scope.processRemindIcon = (productObj) => {
			return Promise.resolve()
			.then(() => {
				if(_.isEmpty($scope.product.remind_icon_file_interface)) return Promise.resolve();
				// The ngDroplet file interface cannot be cloned, hence we need to look into $scope.product object
				var validDropletFiles = _.filter($scope.product.remind_icon_file_interface.getFiles(), file => (file.type !== 4));
				if(validDropletFiles[0] !== undefined && validDropletFiles[0].file){
					return getBase64Url(validDropletFiles[0].file)
								.then((fileJsonObj) => {
									productObj.sepscreateset.remind_icon = fileJsonObj;
									return productObj;
								})
								.catch((err) => {
									console.log(err);
									return Promise.reject(err);
								});
				}
				return Promise.resolve();
			})
			.then(() => {
				return Promise.resolve(productObj);
			})
			.catch((err) => {
				console.log(err);
				return Promise.reject(err);
			});
		}	
		

		$scope.processRequirements = (productObj) => {

			for(var i=0; i<productObj.reqmodel.length; i++){
				// If the requirement is a multiple option select requirement
				if((productObj.reqmodel[i] !== undefined) && (productObj.reqmodel[i].multiopt !== undefined)){
					for(var j=productObj.reqmodel[i].multiopt.length-1; j >= 0; j--){
						if(productObj.reqmodel[i].multiopt[j].check !== true){
							productObj.reqmodel[i].multiopt.splice(j, 1);
						}
					}
				}
			}
			return Promise.resolve(productObj);
		}

		$scope.processDates = (productObj) => {
			// Convert expiry date to json string
			if(productObj.expiry_date !== undefined && productObj.expiry_date){
				if(typeof productObj.expiry_date == "object"){
					productObj.expiry_date = productObj.expiry_date.toLocaleDateString().replace(/\//g,'-');
				}else if(typeof productObj.expiry_date == "string"){
					productObj.expiry_date = productObj.expiry_date.replace(/\//g,'-');
				}								
			}
			if(productObj.sp_fromdate !== undefined && productObj.sp_fromdate){
				if(typeof productObj.sp_fromdate == "object"){
					productObj.sp_fromdate = productObj.sp_fromdate.toLocaleDateString().replace(/\//g,'-');						
				}else if(typeof productObj.sp_fromdate == "string"){
					productObj.sp_fromdate = productObj.sp_fromdate.replace(/\//g,'-');
				}				
			}
			if(productObj.sp_todate !== undefined && productObj.sp_todate){
				if(typeof productObj.sp_todate == "object"){
					productObj.sp_todate = productObj.sp_todate.toLocaleDateString().replace(/\//g,'-');
				}else if(typeof productObj.sp_todate == "string"){
					productObj.sp_todate = productObj.sp_todate.replace(/\//g,'-');
				}				
			}
			// Convert any dates in spec to json string
			for(var i=0; i<productObj.specmodel.length; i++){
				if(productObj.specmodel[i] !== undefined  && productObj.specmodel[i].deldate !== undefined && productObj.specmodel[i].deldate){
					// productObj.specmodel[i].deldate = productObj.specmodel[i].deldate.toJSON();
					if(typeof productObj.specmodel[i].deldate == "object"){
						productObj.specmodel[i].deldate = productObj.specmodel[i].deldate.toLocaleDateString().replace(/\//g,'-');
					}else if(typeof productObj.specmodel[i].deldate == "string"){
						productObj.specmodel[i].deldate = productObj.specmodel[i].deldate.replace(/\//g,'-');
					}
					
				}
			}
			return Promise.resolve(productObj);
		};

		function getBase64Url(fileObj) {
			 return new Promise((res, rej) => {
				reader = new FileReader();
				reader.readAsDataURL(fileObj);
				reader.onloadend = function(){
					res({
						filename: fileObj.name,
						filecontent: reader.result
					});
				};
			 });
		};

		$scope.convertToRequiredSaveFormat = (productObj) => {			
			var specs = [];
			for(var i=0; i<productObj.specmodel.length; i++){
				var spec = {};
				spec.attribute_id = productObj.specmodel[i].id;
				spec.front_input = productObj.specmodel[i].front_input;
				spec.usevaiant = productObj.specmodel[i].usevaiant;
				spec.required = productObj.specmodel[i].required;

				if(productObj.specmodel[i].front_input === 'text'){
						spec.attribute_values = productObj.specmodel[i].textVal;
				}
				else if(productObj.specmodel[i].front_input === 'textarea'){
						spec.attribute_values = productObj.specmodel[i].textareaVal;
				}
				else if(productObj.specmodel[i].front_input === 'date_picker'){
					spec.attribute_values = productObj.specmodel[i].deldate;
				}
				else if(productObj.specmodel[i].front_input === 'select'){						
					if(productObj.specmodel[i].attrselect !== null || productObj.specmodel[i].attrselect !== ""){
						options = [];
						if(_.isArray(productObj.specmodel[i].attrselect)){
							options = productObj.specmodel[i].attrselect
						}
						else if(_.isObject(productObj.specmodel[i].attrselect)){
							options = [productObj.specmodel[i].attrselect]
						}
						spec.attribute_value_ids = _.map(options || [], (v) => v.attr_val_id);
					}
					// if(productObj.specmodel[i].attrselect !== null){
					// 	spec.attribute_value_ids = productObj.specmodel[i].attrselect.attr_val_id;
					// }
				}
				else if(productObj.specmodel[i].front_input === 'multiselect'){
					if(productObj.specmodel[i].mulattrselect !== null || productObj.specmodel[i].mulattrselect !== ""){
						spec.attribute_value_ids = _.map(productObj.specmodel[i].mulattrselect || [], (v) => v.attr_val_id);
					}
				}
				else if(productObj.specmodel[i].front_input === 'browse_file'){
					spec.files = productObj.specmodel[i].filepickerVal;
				}
				specs.push(spec);
			}
			productObj.specmodel = specs;
			return Promise.resolve(productObj);
		};

		/******* save product section*******/
		//Listen on  save or save and continue product section
		$scope.saveProduct=($event, productForm, btn_action)=>{
			$event.stopPropagation();			
			//set button action
			$scope.prdData.save_btn_action = btn_action; 
			//get file to check current upload or not if not then check pervious file
			var checkFile = false;
			var validDropletFiles = _.filter($scope.product.file_interface.getFiles(), file => (file.type !== 4));
			var errorHtml = '';	
			/*if(validDropletFiles.length === 0 && ($scope.prevImage!==undefined && $scope.prevImage.length === 0)){
				checkFile = true;
			}*/
			if(validDropletFiles.length && (_uploadedFileIdArray.length!= validDropletFiles.length)){
				// errorHtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please upload all product image</div>';
    			// swal("Opps..", "Please upload all image first", "warning");
    			// return false;
    			checkFile=true;
			}else{
				checkFile = false;
			}
			
			//check form is valid or not and show error message
			if($scope.productform.$invalid === true || checkFile === true){
				
				for(var i in formFieldName){
					if(($scope.productform[formFieldName[i]]!==undefined &&
						$scope.productform[formFieldName[i]].$error!== undefined && 
						$scope.productform[formFieldName[i]].$error.required === true) &&
						$scope.productform[formFieldName[i]].$invalid === true){
							errorHtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>'+errorMsg[i]+'</div>';
					}
				}

				if(checkFile === true){
					errorHtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please upload all product image</div>';
				}

				_messageHandler("error", errorHtml,"Error");
				return;
			}
			
			//enable page loader and disable save,save_continue button
			$scope.prdData.loading.save_and_continue = true;
			$scope.prdData.loading.disableBtn = true;
			$scope.product.createupdatetype = $scope.prdData.spescrtupdate;
			var productObj = angular.copy($scope.product)			
			$scope.processFilePickerFiles(productObj)
			.then((productObj) => $scope.processRemindIcon(productObj))
			.then((productObj) => $scope.processRequirements(productObj))
			.then((productObj) => $scope.processDates(productObj))
			.then((productObj) => $scope.convertToRequiredSaveFormat(productObj))
			.then((productObj) => {	
				
				//set product id related,crosssale ,upsale ,simple product and config product			
				productObj["config_prd_id"] = $rootScope.prd_data.config_product_id;
				productObj["simple_prd_id"] = $rootScope.prd_data.simple_product_id;
				productObj["sorting_order"] = $rootScope.prd_data.simple_config_prd_order;

				productObj["related_prd_id"] = $rootScope.prd_data.related_product_id_id;
				productObj["crossale_prd_id"] = $rootScope.prd_data.cross_sale_product_id;
				productObj["upsale_prd_id"] = $rootScope.prd_data.up_sale_product_id;

				productObj['selected_rel_bundle']  = selected_rel_bundle;
				productObj['product_id']  = product_detail.id;

				if(typeof product_type !=="undefined"){
					productObj["product_type"] = product_type;	
				}
				//for change name and desc to json string
				productObj.name = angular.toJson(productObj.name);
				productObj.productDesc = angular.toJson(productObj.productDesc);

				//for product image (uploaded id)
				var cords = [];
				var pords = [];
				if(_uploadedFileIdArray.length){
					//cords = productImageOrder($scope.prdData.image_order, _uploadedFileIdArray);
					_.forEach(_uploadedFileIdArray, function (item , key){
						item['img_order'] = $scope.prdData.image_order[key] || "";						
					});					
					productObj.product_images = angular.toJson(_uploadedFileIdArray);					
				}else{
					productObj.product_images = null;
				}
				//add image order in product json 
				pords = cords.concat(productImageOrder($scope.prdData.prev_image_order, $scope.prevImage));
				productObj.product_image_order = angular.toJson(pords);
				//for default image 
				var defImg={};
				var def='';				
				
				if($scope.prevImage.length && $scope.product.prevDefaultImage!==""){
		    		var ind = parseInt($scope.product.prevDefaultImage);
		    		defImg["img_id"] = $scope.prevImage[ind].id;
		    		defImg["is_default"] = true;
		    		defImg["img_name"]= $scope.prevImage[ind].image;
		    		defImg["index"] = prd_defalut_img.prevImg;
		    		defImg["product_id"] = product_detail.id; 		
		    	}else if(_uploadedFileIdArray.length){
		    		def = parseInt($scope.product.defaultImage);		    		
	    			defImg["img_id"] = _uploadedFileIdArray[def].img_id;
		    		defImg["is_default"] = true;
		    		defImg["img_name"]= _uploadedFileIdArray[def].img_name;
		    		defImg["index"] = def;
		    		defImg["product_id"] = null;
		    	}

		    	//In case of copy product or bundel copy get old product image id
				if(typeof current_blade_type !== "undefined" &&  (current_blade_type === "copy_product" || current_blade_type === "copy" )){
					productObj.product_old_images = _.map($scope.prevImage, "id");							
				}
				
		    	//final data to send server
				var finalData = {
					product : angular.toJson(productObj),
					defaultImage : angular.toJson(defImg),
					actiontype : (typeof current_blade_type !== "undefined" &&  (current_blade_type === "copy_product" || current_blade_type === "copy" ))? "copy" : "",
					product_log_data: product_log_data
				};
				
				//action to server
				salesfactoryData.getData(producturl, "POST", finalData)
				.then(function(response){
					if(typeof response!=="undefined" && (response.data!== undefined && response.data!=null)){
						//validation error in product then enable error tab
						if(!angular.isUndefined(response.data.status) && response.data.status=='error'){
							if(response.data.section=='product'){
		            			$scope.liActiveHandler('','enable_product_tab');
							}else if(response.data.section=='warehouse'){	            		
		            			$scope.liActiveHandler('','enable_warehouse_tab');
		            		}else if(response.data.section == 'bundle'){
		            			$scope.liActiveHandler('','enable_bundel_tab');
		            		}

		            		var errorhtml = '';
		            		angular.forEach(response.data.error, (error)=>{
								errorhtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span>'+error+'</div>'
							});

							swal({title: '', type: 'error', html: errorhtml, showCloseButton: true, showCancelButton: false, focusConfirm: false, showConfirmButton: false});
						}else if(!angular.isUndefined(response.data.status) && response.data.status=='success'){
							swal({
		                        title: "Good",
		                        text: (response.data.mesg)? response.data.mesg : "Product successfully created.",
		                        type: response.data.status,
		                        showConfirmButton:false,
		                        timer: 3000
		                    }).then(function(done){
		                    	//window.location.href = getRedirectUrl(response.data, $scope.prdData.save_btn_action);
		                    }, function(dismiss){
		                    	window.location.href = getRedirectUrl(response.data, $scope.prdData.save_btn_action);
		                    });
						}
					}else{
					  _error();
					}
				},function(error){
					_error();
				}).finally(function(){
					$scope.prdData.loading.save_and_continue = false;
					$scope.prdData.loading.disableBtn = false;
				});
		    })
			.then((res)=>{
				console.log(res);
			})
			.catch((err) => {
				console.log(err)
			});
		 //  this.sendImagesViaAjax();
		};

		//Listen on copy product
		$scope.copyProduct = ($event, productForm, btn_action)=>{
			$event.stopPropagation();
			$scope.saveProduct($event, productForm, btn_action);			
		};
	 	
 		 		
 		/*******Related product section (Table section config)*****/


 		$scope.checksku= function(sku){
	        var data = {
	        		'sku':sku,
	        		"prd_id" :  (product_detail.id!==undefined && product_detail.id)? product_detail.id : '',
	    		};
	        salesfactoryData.getData(skucheckurl,'GET',data).then(function(response){
	            if(response.data.status == true){
	                $scope.skuexists = true;
	            }
	            else{
	                $scope.skuexists = false;
	            }
		    });
	    };

	    $scope.checkcopysku= function(sku){
	        var data = {'sku':sku};
	        salesfactoryData.getData(skucheckurl,'GET',data).then(function(response){
	            if(response.data.status == true){
	                $scope.skuexists = true;
	            }
	            else{
	                $scope.skuexists = false;
	            }
		    });
	    };

	   	//Bundel product section
		//Listen on product tab (li) active
		$scope.liActiveHandler =($event,strflag)=>{
			for (var p in $rootScope.prd_tab) {
			    if ($rootScope.prd_tab.hasOwnProperty(p) && p===strflag) {
			        $rootScope.prd_tab[p] = true;
			        if(strflag==='enable_related_tab'){
			        	$scope.$broadcast('getRelatedPrdData');
			        }
			    }else{
					$rootScope.prd_tab[p] = false;
				}
			}
			if($event=='') return;
			$event.preventDefault();
		};
		//Listen on form validation 
		$scope.showError = function(inputName) {
          var form = $scope.productform; 
          return (form.$submitted || form[inputName].$touched) && !form[inputName].$valid;
        };
        /**
        *Listen on change date (from to ) and  update date picker
		*@dateFrom and dateTo
		*@Datepicker option name (dateFromOption and dateToOption)
		**/
        $scope._updateDatePicker =(dateFrom, dateTo,dateFromOption,dateToOption)=>{
        	// if(typeof dateFrom == "undefined" ||  typeof dateTo == "undefined")
        	// 	return;

        	if(dateFrom!== undefined){
        		var date = dateFrom.split("/");
	        	var _d = parseInt(date[0]),
	        		_m = parseInt(date[1]),
	        		_y = parseInt(date[2]);
	        	var dateObj = new Date(_y, _m, _d);
	        	dateObj.setDate(dateObj.getDate() + 1);
        	}
        	

        	if(!isNaN(dateObj)){
        		dateFrom = dateObj.getDate() +"/" + dateObj.getMonth() + "/" + dateObj.getFullYear();
        	}

        	$scope.prdData[dateFromOption].maxDate = dateTo;
            $scope.prdData[dateToOption].minDate = dateFrom;
        };
      

	};

	var filesInputDirective = function() {
		return {
			require: "ngModel",
			link: function postLink(scope,elem,attrs,ngModel) {
				elem.on("change", function(e) {
					var files = elem[0].files;
					ngModel.$setViewValue(files);
				})
			}
		}
	};

	angular.module("sabinaAdminApp").controller('productCtrl', ['$scope','salesfactoryData','$http','$sce','$timeout','$filter', 'treeNodeService','$rootScope', '$q', '$window',productCtrl]);
	angular.module("sabinaAdminApp").directive("filesInput", filesInputDirective);  

	//Listen on error 
    function _error(){
      try{throw new Error("Something went badly wrong!");}
      catch(e){
      	_messageHandler('error','Something went wrong!','Oops...')
        console.log("Opps "+e);      
      };
    };
    /****
    *This function used to message display using sweert alert.
    *@_type : like error, sucess
    *@_txt : message
    *@_title : message title
    *@_time :timer for auto hide after timeout
	*@_showCancelButton :  true or false
    *****/
    function _messageHandler(_type,_txt,_title,_time,_showCancelButton){
    	swal({
    		position: 'top-right',
    		type: _type,
    		title: _title,
    		text: _txt,
    		//timer : _time || 1500,
    		closeOnConfirm: false,
	        closeOnCancel: false
    	}).then((isConfirm)=>{
    		 // handle confirm, result is needed for modals with input    		 
       	},(dismiss)=>{
    		// dismiss can be "cancel" | "close" | "outside"    		
    	});    	
    };

    /*Listen for get index 
    *@param : destObj (oject/array)
    *@param : matchEle (string)
    *@param : matchType (string -optional)
    */
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

    //Listen to product image order manage
    function productImageOrder(orderModel, uploadedImgArr){
    	var result =[];  
    	for(var i in uploadedImgArr){
    		result.push({
    			"img_id" : uploadedImgArr[i].id || uploadedImgArr[i].img_id,
    			"img_order" : (typeof orderModel[i]!="undefined" && (orderModel[i]!== '' || orderModel[i]!= null)) ? orderModel[i] : "",
    		});
    	};
    	return result;
    };

    //Listen to get re-direct url 
    function getRedirectUrl(obj, actFlag){
    	switch(actFlag){
    		case "save" :
    			return obj.save_url;
    			break;
    		case "save_and_continue":
    			return obj.cont_url;
    			break;
    		case "save_and_preview":
    			return obj.preview_url;
    			break;   		
    	};
    };
    
})(window.angular);
// jQuery section for product section
jQuery(document).ready(function($) {
	setTimeout(function(){
		$('.colorpicker-element').colorpicker();
	}, 200);
	
});