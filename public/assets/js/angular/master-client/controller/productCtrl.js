/*
*@Name : productCtrl.js
*@description :  This controllter used to handel all product related functionality
*@Careated :  18 sep 2017
*@Author :  Smoothgraph Connect Pvt. Ltd.
*/

(function(){
	"used strict";

	function productCtrl($scope,salesfactoryData,$http,$sce,$timeout,$filter, treeNodeService,$rootScope, $q, $window){
		$scope.product={
			name :[],
			prdtags :[],
			stbprice :false,
			processing_time:"",
			initial_price:"",
			special_price:"",
			// sp_fromdate:,
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
			createupdatetype:'',
			defaultwhouse:defaultwarehouse, 
			seo:{meta_title:[],meta_keyword:[],meta_description:[]},
			name :{},
			productDesc :{},
		};
		/******This variable define fro data section *****/
		$scope.prdData={
			currency_detail:currency_detail,
			//variant section
			variants:variantdata,
			selectTags:[{value:""}],
			//categeroy section
			cateTree:[{"id":13,"parent_id":0,"total_products":85,"categorydesc":{"id":53,"category_name":"Mobile 2","cat_id":13},"category":[],"checked":false,"name":"Mobile 2","children":[]},{"id":14,"parent_id":0,"total_products":32,"categorydesc":{"id":57,"category_name":"samsung","cat_id":14},"category":[{"id":3,"url":"sellercat","parent_id":14,"total_products":312},{"id":30,"url":"belt","parent_id":14,"total_products":11}],"checked":false,"name":"samsung","children":[{"id":3,"parent_id":14,"total_products":312,"categorydesc":{"id":13,"category_name":"sellercat","cat_id":3},"category":[{"id":2,"url":"lorem-ipsum-12","parent_id":3,"total_products":7},{"id":132,"url":"amit456","parent_id":3,"total_products":7}],"checked":false,"name":"sellercat","children":[{"id":2,"parent_id":3,"total_products":7,"categorydesc":{"id":214,"category_name":"Lorem Ipsum 12","cat_id":2},"category":[],"checked":false,"name":"Lorem Ipsum 12","children":[]},{"id":132,"parent_id":3,"total_products":7,"categorydesc":{"id":864,"category_name":"amit456","cat_id":132},"category":[],"checked":false,"name":"amit456","children":[]}]},{"id":30,"parent_id":14,"total_products":11,"categorydesc":{"id":121,"category_name":"belt","cat_id":30},"category":[],"checked":false,"name":"belt","children":[]}]},{"id":22,"parent_id":0,"total_products":68,"categorydesc":{"id":89,"category_name":"watch","cat_id":22},"category":[],"checked":false,"name":"watch","children":[]},{"id":29,"parent_id":0,"total_products":13,"categorydesc":{"id":117,"category_name":"action123","cat_id":29},"category":[],"checked":false,"name":"action123","children":[]},{"id":32,"parent_id":0,"total_products":64,"categorydesc":{"id":129,"category_name":"sdfsdafdf","cat_id":32},"category":[{"id":33,"url":"shirt","parent_id":32,"total_products":402}],"checked":false,"name":"sdfsdafdf","children":[{"id":33,"parent_id":32,"total_products":402,"categorydesc":{"id":137,"category_name":"shirt","cat_id":33},"category":[],"checked":false,"name":"shirt","children":[]}]},{"id":34,"parent_id":0,"total_products":10,"categorydesc":{"id":141,"category_name":"Black T - Shirt","cat_id":34},"category":[],"checked":false,"name":"Black T - Shirt","children":[]},{"id":35,"parent_id":0,"total_products":4,"categorydesc":{"id":145,"category_name":"Shirt","cat_id":35},"category":[{"id":36,"url":"black-shirt","parent_id":35,"total_products":4}],"checked":false,"name":"Shirt","children":[{"id":36,"parent_id":35,"total_products":4,"categorydesc":{"id":149,"category_name":"Black shirt","cat_id":36},"category":[],"checked":false,"name":"Black shirt","children":[]}]},{"id":37,"parent_id":0,"total_products":9,"categorydesc":{"id":153,"category_name":"White shirt","cat_id":37},"category":[],"checked":false,"name":"White shirt","children":[]},{"id":38,"parent_id":0,"total_products":5,"categorydesc":{"id":157,"category_name":"SEAFOOD","cat_id":38},"category":[],"checked":false,"name":"SEAFOOD","children":[]},{"id":41,"parent_id":0,"total_products":4,"categorydesc":{"id":236,"category_name":"clothing 45","cat_id":41},"category":[{"id":67,"url":"rwerwer","parent_id":41,"total_products":2}],"checked":false,"name":"clothing 45","children":[{"id":67,"parent_id":41,"total_products":2,"categorydesc":{"id":665,"category_name":"rwerwer","cat_id":67},"category":[{"id":40,"url":"seafood26","parent_id":67,"total_products":3}],"checked":false,"name":"rwerwer","children":[{"id":40,"parent_id":67,"total_products":3,"categorydesc":{"id":228,"category_name":"SEAFOOD26","cat_id":40},"category":[],"checked":false,"name":"SEAFOOD26","children":[]}]}]},{"id":59,"parent_id":0,"total_products":0,"categorydesc":{"id":471,"category_name":"Pqr","cat_id":59},"category":[],"checked":false,"name":"Pqr","children":[]},{"id":62,"parent_id":0,"total_products":1,"categorydesc":{"id":645,"category_name":"lenovo","cat_id":62},"category":[{"id":63,"url":"lenovo2","parent_id":62,"total_products":1},{"id":64,"url":"lenovo1","parent_id":62,"total_products":1},{"id":65,"url":"lenovo232","parent_id":62,"total_products":1},{"id":66,"url":"lenovo-k5","parent_id":62,"total_products":1}],"checked":false,"name":"lenovo","children":[{"id":63,"parent_id":62,"total_products":1,"categorydesc":{"id":649,"category_name":"lenovo2","cat_id":63},"category":[],"checked":false,"name":"lenovo2","children":[]},{"id":64,"parent_id":62,"total_products":1,"categorydesc":{"id":653,"category_name":"Lenovo1","cat_id":64},"category":[],"checked":false,"name":"Lenovo1","children":[]},{"id":65,"parent_id":62,"total_products":1,"categorydesc":{"id":657,"category_name":"Lenovo232","cat_id":65},"category":[],"checked":false,"name":"Lenovo232","children":[]},{"id":66,"parent_id":62,"total_products":1,"categorydesc":{"id":661,"category_name":"Lenovo K5","cat_id":66},"category":[],"checked":false,"name":"Lenovo K5","children":[]}]},{"id":68,"parent_id":0,"total_products":2,"categorydesc":{"id":669,"category_name":"motog 566","cat_id":68},"category":[{"id":86,"url":"moto566.06","parent_id":68,"total_products":2}],"checked":false,"name":"motog 566","children":[{"id":86,"parent_id":68,"total_products":2,"categorydesc":{"id":751,"category_name":"moto566.06","cat_id":86},"category":[],"checked":false,"name":"moto566.06","children":[]}]},{"id":69,"parent_id":0,"total_products":15,"categorydesc":{"id":673,"category_name":"yollo mobile","cat_id":69},"category":[],"checked":false,"name":"yollo mobile","children":[]},{"id":70,"parent_id":0,"total_products":0,"categorydesc":{"id":677,"category_name":"motooo","cat_id":70},"category":[{"id":74,"url":"mobile4851","parent_id":70,"total_products":0}],"checked":false,"name":"motooo","children":[{"id":74,"parent_id":70,"total_products":0,"categorydesc":{"id":693,"category_name":"mobile4851","cat_id":74},"category":[],"checked":false,"name":"mobile4851","children":[]}]},{"id":72,"parent_id":0,"total_products":0,"categorydesc":{"id":685,"category_name":"cat667","cat_id":72},"category":[],"checked":false,"name":"cat667","children":[]},{"id":73,"parent_id":0,"total_products":0,"categorydesc":{"id":689,"category_name":"Lenovo1232","cat_id":73},"category":[{"id":79,"url":"moto-g555","parent_id":73,"total_products":0},{"id":80,"url":"moto-f5","parent_id":73,"total_products":0},{"id":85,"url":"mithilesh0555","parent_id":73,"total_products":0}],"checked":false,"name":"Lenovo1232","children":[{"id":79,"parent_id":73,"total_products":0,"categorydesc":{"id":713,"category_name":"moto g555","cat_id":79},"category":[],"checked":false,"name":"moto g555","children":[]},{"id":80,"parent_id":73,"total_products":0,"categorydesc":{"id":717,"category_name":"Moto F5","cat_id":80},"category":[],"checked":false,"name":"Moto F5","children":[]}]},{"id":77,"parent_id":0,"total_products":0,"categorydesc":{"id":705,"category_name":"motokg","cat_id":77},"category":[{"id":76,"url":"motok","parent_id":77,"total_products":0}],"checked":false,"name":"motokg","children":[{"id":76,"parent_id":77,"total_products":0,"categorydesc":{"id":701,"category_name":"motok","cat_id":76},"category":[{"id":78,"url":"moto-k5","parent_id":76,"total_products":0}],"checked":false,"name":"motok","children":[{"id":78,"parent_id":76,"total_products":0,"categorydesc":{"id":709,"category_name":"moto k5","cat_id":78},"category":[{"id":75,"url":"moto7954","parent_id":78,"total_products":0}],"checked":false,"name":"moto k5","children":[{"id":75,"parent_id":78,"total_products":0,"categorydesc":{"id":697,"category_name":"moto7954","cat_id":75},"category":[],"checked":false,"name":"moto7954","children":[]}]}]}]},{"id":87,"parent_id":0,"total_products":0,"categorydesc":{"id":755,"category_name":"fruit","cat_id":87},"category":[],"checked":false,"name":"fruit","children":[]},{"id":89,"parent_id":0,"total_products":0,"categorydesc":{"id":759,"category_name":"mobile8754888","cat_id":89},"category":[],"checked":false,"name":"mobile8754888","children":[]},{"id":90,"parent_id":0,"total_products":0,"categorydesc":{"id":763,"category_name":"cate23222232","cat_id":90},"category":[],"checked":false,"name":"cate23222232","children":[]},{"id":130,"parent_id":0,"total_products":0,"categorydesc":{"id":856,"category_name":"C category","cat_id":130},"category":[],"checked":false,"name":"C category","children":[]},{"id":131,"parent_id":0,"total_products":0,"categorydesc":{"id":860,"category_name":"amar category","cat_id":131},"category":[],"checked":false,"name":"amar category","children":[]},{"id":136,"parent_id":0,"total_products":400,"categorydesc":{"id":876,"category_name":"cloth","cat_id":136},"category":[],"checked":false,"name":"cloth","children":[]},{"id":137,"parent_id":0,"total_products":400,"categorydesc":{"id":880,"category_name":"jacket","cat_id":137},"category":[],"checked":false,"name":"jacket","children":[]},{"id":138,"parent_id":0,"total_products":399,"categorydesc":{"id":884,"category_name":"mens","cat_id":138},"category":[{"id":151,"url":"testingcloth1","parent_id":138,"total_products":0},{"id":152,"url":"testingcloth2","parent_id":138,"total_products":0},{"id":153,"url":"testingcloth3","parent_id":138,"total_products":0},{"id":154,"url":"testingcloth5","parent_id":138,"total_products":0}],"checked":false,"name":"mens","children":[{"id":151,"parent_id":138,"total_products":0,"categorydesc":{"id":932,"category_name":"testingcloth1","cat_id":151},"category":[],"checked":false,"name":"testingcloth1","children":[]},{"id":152,"parent_id":138,"total_products":0,"categorydesc":{"id":936,"category_name":"testingcloth2","cat_id":152},"category":[],"checked":false,"name":"testingcloth2","children":[]},{"id":153,"parent_id":138,"total_products":0,"categorydesc":{"id":940,"category_name":"testingcloth3","cat_id":153},"category":[],"checked":false,"name":"testingcloth3","children":[]},{"id":154,"parent_id":138,"total_products":0,"categorydesc":{"id":944,"category_name":"testingcloth5","cat_id":154},"category":[],"checked":false,"name":"testingcloth5","children":[]}]},{"id":141,"parent_id":0,"total_products":3,"categorydesc":{"id":896,"category_name":" cloth","cat_id":141},"category":[],"checked":false,"name":" cloth","children":[]},{"id":142,"parent_id":0,"total_products":0,"categorydesc":{"id":900,"category_name":"testings","cat_id":142},"category":[],"checked":false,"name":"testings","children":[]},{"id":149,"parent_id":0,"total_products":1,"categorydesc":{"id":928,"category_name":"testingcloth","cat_id":149},"category":[],"checked":false,"name":"testingcloth","children":[]},{"id":155,"parent_id":0,"total_products":1,"categorydesc":{"id":948,"category_name":"Blog Category-1","cat_id":155},"category":[],"checked":false,"name":"Blog Category-1","children":[]},{"id":156,"parent_id":0,"total_products":1,"categorydesc":{"id":952,"category_name":"uuu","cat_id":156},"category":[],"checked":false,"name":"uuu","children":[]},{"id":158,"parent_id":0,"total_products":10,"categorydesc":{"id":960,"category_name":"bra","cat_id":158},"category":[{"id":159,"url":"subbar1","parent_id":158,"total_products":10}],"checked":false,"name":"bra","children":[{"id":159,"parent_id":158,"total_products":10,"categorydesc":{"id":964,"category_name":"subbar1","cat_id":159},"category":[],"checked":false,"name":"subbar1","children":[]}]},{"id":160,"parent_id":0,"total_products":10,"categorydesc":{"id":968,"category_name":"pant","cat_id":160},"category":[{"id":161,"url":"subpant1","parent_id":160,"total_products":10},{"id":162,"url":"subpant2","parent_id":160,"total_products":4},{"id":163,"url":"subpant3","parent_id":160,"total_products":4},{"id":164,"url":"subpant4","parent_id":160,"total_products":4},{"id":165,"url":"subpant5","parent_id":160,"total_products":4}],"checked":false,"name":"pant","children":[{"id":161,"parent_id":160,"total_products":10,"categorydesc":{"id":972,"category_name":"subpant1","cat_id":161},"category":[],"checked":false,"name":"subpant1","children":[]},{"id":162,"parent_id":160,"total_products":4,"categorydesc":{"id":976,"category_name":"subpant2","cat_id":162},"category":[],"checked":false,"name":"subpant2","children":[]},{"id":163,"parent_id":160,"total_products":4,"categorydesc":{"id":980,"category_name":"subpant3","cat_id":163},"category":[],"checked":false,"name":"subpant3","children":[]},{"id":164,"parent_id":160,"total_products":4,"categorydesc":{"id":984,"category_name":"subpant4","cat_id":164},"category":[],"checked":false,"name":"subpant4","children":[]},{"id":165,"parent_id":160,"total_products":4,"categorydesc":{"id":988,"category_name":"subpant5","cat_id":165},"category":[],"checked":false,"name":"subpant5","children":[]}]}],
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
		    //save and save_and_continue action flag
			save_btn_action : "",	

			//for enable/disable uploadall/ clear all button
			up_cl_all : false,	
			//for image order 
			image_order : [],
		};
		$scope.product.defaultwhouse.name=true;
		$scope.product.defaultwhouse.stock=true;
		$scope.product.nexpiry=true;
		$scope.product.ready_ship= true;
		$scope.product.autosku= true;
		$scope.product.visibility= '3';

		$scope.product.product_unit = '1';
		$scope.product.package_unit = '1';
		$scope.product.weight_unit = '1';
		$scope.product.total_weight_unit = '1';



		$scope.activalangs = activelangs;
		$scope._attrSetChange = function(){};
		var selectable = true; 
		//FOR uploaded file id array 
		var _uploadedFileIdArray =[];

		//self excute function to gat all product requirement and specification data;
 		function _getReqmtSpeData(){
 			var urlarr =[attribute_requirment_url,warehoseurl,categorylisturl];
 			salesfactoryData.getAllData(urlarr,'GET',{'action' : 'add'}).then(function(rs){
 				//attrbute section
 				var attrData = rs[0].data;
 				$scope.prdData.specslist=attrData.attributes;
				$scope.prdData.spesattrset=attrData.attributesets;
				$scope.prdData.reqlist=attrData.requirements;
				$scope.product.speschoseattrset = attrData.attributesets[0];
				$scope.prdData.reqhas=(attrData.requirements.length>0)?true:false;
				$scope.prdData.description = attrData.description;
				//warehouse section
				var wrData = rs[1].data;
				$scope.prdData.warehouse=wrData;
				$scope.prdData.cateTree=rs[2].data;			

				angular.forEach(wrData, function(unit_warehouse){					
					$scope.product.warehouse_model.push({name:false,stock:false,stock_value:"",lowstock_value:"",safetystock_value:"",warehouse_id:unit_warehouse.warehouse_id});   
				});
				$scope._attrSetChange();
			},function(error){
 				_error();
 			});
 		};
 		_getReqmtSpeData();
		/******This function used for load product tags on user input****/
		$scope._loadProdTags = function(query){
			if(query=='') return;
			var o={'query' : query};
			return salesfactoryData.getData(prdtagurl,'GET',o).then(function(r){
				return r.data;
			},function(error){
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
		$scope.addTireprice = function($event,type){ 
			$event.stopPropagation();
			switch(type){
				case 'tirerpice':
					$scope.product.tprices.push({tireQnt:"",tireQntPrice:""});
					break;
				case 'bundelprice':
					$scope.product.bundelprice.push({bundelqunt:"",bundelprice:""});
					break;
				default:
				 break;
			};
		};
		$scope.removeTireprice = function(index,type){
			switch(type){
				case 'tirerpice':
					if($scope.product.tprices.length>1){
						 $scope.product.tprices.splice(index, 1);
					}else{
						_messageHandler('warning',"You can't remove tier price because \n You have checked tierprice","Opps..");
					}
					break;
				case 'bundelprice':
					if($scope.product.bundelprice.length>1){
						 $scope.product.bundelprice.splice(index, 1);
					}else{
						_messageHandler('warning',"You can't remove bundel price because \n You have checked bundelprice","Opps..");
					}
					break;
				default:
					break;
			};
		};
		$scope.switchPecialPrice = function($event,spType){
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
		$scope.sp_check_fun = function(){
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
			tierPrice : function(item, cpind){
				let self = this;
				$scope.$evalAsync(()=>{					
					if(item.tireQntPrice && !$scope.product.initial_price){
						_messageHandler("error", '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please enter base price first</div>',"Error");
						item.tireQntPrice = "";
					}else if($scope.product.initial_price && parseInt($scope.product.initial_price)<parseInt(item.tireQntPrice)){
						_messageHandler("error", '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Special price must be less than price</div>',"Error");
						item.tireQntPrice = "";
					}
					//check price limit
					if(item.tireQntPrice && self.checkLimit(item.tireQntPrice)){
						_messageHandler("error", '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Price must be lower than '+(price_input_limit+1)+' digits </div>',"Error");
						item.tireQntPrice = "";
					}
					// self.tierValidation(item, cpind);
				});
			},
			checkLimit : function(price){				
				let p = price.split('.');
				return(p[0].length>price_input_limit);
			},
			/*//working
			tierValidation : function(item, cpind){
				if($scope.product.tprices.length<=1) return;

				let qt = parseInt(item.tireQnt),
					pr = parseInt(item.tireQntPrice),
					pqt = parseInt($scope.product.tprices[cpind-1].tireQnt),
					prv = parseInt($scope.product.tprices[cpind-1].tireQntPrice);
				//in case quantity is smaller & price is greater
				if(qt<=pqt && pr>=prv)
					_messageHandler("error", '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Price must be lower than previous price \n Quantity must be greater than previous quantity </div>',"Error");
					console.log('quantity & price is wrong');

				// console.log($scope.product.tprices[]);
			},*/
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
		$scope.removeVariants = function($event,index,tagIndex){
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
			return varients_all_data[index];
			//for only matching element comment above line.
			//return _.filter(varients_all_data[index],function(o){ return o.values.toLowerCase()==(query.toLowerCase())});
			// return .filter(function(option) {
			// 	return option.values.toLowerCase().indexOf(query.toLowerCase()) != -1;
			// });        
		};
		$scope.getAttributValuesAndAttribute = function(index,modelIndex,optIndex){
			if(optIndex<0) return;
			var data = {"vid":$scope.product.varientmodel[index].attr_id};
			variantComb[index] = optIndex;
			salesfactoryData.getData(variantUrl,'GET',data).then(function(r){
				if(_.isArray(r.data) && r.data.length>0) varients_all_data[optIndex] = r.data;
			},function(error){
				_error();
			});
		};
		/****This section used for video**/
		var vidfile ='',user_uploadImg='',imgInd=0;
		$scope.getVideoDetail= function(){
			var youtube_reg = new RegExp(/^(http|https\:\/\/)?(www\.youtube|youtu\.be)\/.+$/);
       		var url_regexp =  /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
       		//check condtion for url 
       		if($scope.product.videodobj.videourl==undefined || $scope.product.videodobj.videourl=='' || !url_regexp.test($scope.product.videodobj.videourl)) return;
			//check youtube share url
			if($scope.product.videodobj.videourl.match(youtube_reg)){
	            var youtube_id=$scope.product.videodobj.videourl.split(/v\/|v=|youtu\.be\//)[1].split(/[?&]/)[0];
	            $scope.product.videodobj.videourl = "http://www.youtube.com/watch?v=" + youtube_id;  
	        } 	        
		    $scope.vloader = true;
		    var obj = {'v_url':$scope.product.videodobj.videourl}; 
		    salesfactoryData.getData(_getvideourl,'POST',obj).then(function(r){
		    	var d = r.data;
		    	if(d.status=='success' && d){ 
		    		$scope.videotemp = d;
		    		if(!_.isUndefined(d.id)){
		    			$scope.product.videodobj.video_id = d.id;
			           	$scope.product.videodobj.video_url = $sce.trustAsResourceUrl(d.video_url);
			            $scope.product.videodobj.video_prev_img = d.thumbnail_small;
			            $scope.product.videodobj.video_title = d.title;
			            $scope.product.videodobj.description = d.description; 
		            }
		    	}else{
		    		_error();
		    	}
		    },function(error){		        
		        _error();
		    }).finally(function(){
		    	$scope.vloader =false;
		    });
		};
		$scope.refreshVideoForm = function(){
			for(var p in $scope.product.videodobj)
			   if($scope.product.videodobj.hasOwnProperty(p))
						$scope.product.videodobj[p] = '';
		};
		$scope.addVideo = function(){
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
		$scope.removeVideo = function(vindex,usrfileIndex){
			$scope.prdData.videolist.splice(vindex, 1);
		    $scope.product.videoFile.splice(vindex, 1);
		    //case if user can change preview image
		   /* if(usrfileIndex!==0)
		        $scope.product.videoFile.splice(usrfileIndex-1,1);*/
		};
		/******* video section ends here *****/
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
        //Listen on file add in droplet
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

        //listen on clear all file from list
		$scope._clearallfiles = function(){
			var temp_file = $scope.product.file_interface.getFiles($scope.product.file_interface.FILE_TYPES.VALID);
			_.map(temp_file, function(item){
				//In case file is already uploaded on server then remove from temp table
				if(item.customData!== undefined && (item.customData.completed!== undefined && item.customData.completed === true)){
					_removeImage(item);					
				}else{
					item.deleteFile();					
				}

				$scope.product.file_interface.addRemoveCustomData("remove_file", {}, item.file);				
			});
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
 		/*****end file upload***/
 		
 		/*****this section used for warehouse****/
 		$scope._wareCheckHendler= function(status,index,actType){
 			switch(actType){
 				case "managewarehouse":
 					if(!status){
 						$scope.product.warehouse_model[index].stock=false;
 						$scope.product.warehouse_model[index].stock_value="";
 						$scope.product.warehouse_model[index].lowstock_value="";
 						$scope.product.warehouse_model[index].safetystock_value="";
 					}
 					break;
 				case "managestock":
					$scope.product.warehouse_model[index].stock_value="";
					$scope.product.warehouse_model[index].lowstock_value="";
					$scope.product.warehouse_model[index].safetystock_value="";
 					break;
 				default:
 				break;
 			};
 		};
 		/********Product variants and specification,requirement section*********/
 		$scope._removeSep= function($event,index,strFlag,item){
			//this function used to remove object from  specs_create_list
			$event.preventDefault(); 
			if(strFlag === 'specification'){
				var temp = angular.copy($scope.prdData.specslist);
				temp = temp.concat(item);
				$scope.prdData.specslist = temp;
				var ind = $scope.prdData.specs_create_list.findIndex(function(x){
					return(x.id!== undefined && x.id ===item.id);
				});
				var ind1 = $scope.product.specmodel.findIndex(function(x){
					return(x.id!== undefined && x.id===item.id);
				});
				$scope.prdData.specs_create_list.splice(ind,1);
				$scope.product.specmodel.splice(ind1,1);
			}else if(strFlag === 'requirement'){  
				var temp = angular.copy($scope.prdData.reqlist);
				temp = temp.concat(item);
				$scope.prdData.reqlist = temp;
				var ind = $scope.prdData.req_create_list.findIndex(function(x){
					return(x.id!== undefined && x.id===item.id);					
				});
				var ind1 = $scope.product.reqmodel.findIndex(function(x){
					return(x.d!== undefined && x.id===item.id);
				});
				$scope.prdData.req_create_list.splice(ind,1);
				$scope.product.reqmodel.splice(ind1,1);
			}
		};

		$scope._attrSetChange = function(){
			var o={};

			if($scope.product.speschoseattrset!==null || $scope.product.speschoseattrset!==""){
			 	o["attr_set_id"]=$scope.product.speschoseattrset.id;
			}else{
				o["attr_set_id"] ='';
				$scope.prdData.spescrtupdate="createattrset";
			}

			salesfactoryData.getData(attributeurl,'GET',o).then(function(r){
				if(r.data){
					$scope.prdData.specs_create_list=[];
	   			    $scope.copyAttributeSetDataToSpecCreateList(r.data.set_attributes);
                    $scope.copyAttributeSetDataToSpecModel(r.data.set_attributes);
					$scope.prdData.specslist = [];
					$scope.prdData.specslist = r.data.except_attributes;
					$scope.prdData.description = r.data.description;

					//$scope.product.specmodel = [];
					//$scope.product.specmodel.push({textVal:null,selectVal:null,textareaVal:null,usevaiant:false,attrselect:null,mulattrselect:null,id:null});
				}
			},function(error){
				_error();
			});
		};
        $scope.copyAttributeSetDataToSpecCreateList = function(attrSetData){
           if(attrSetData !== undefined){
              $scope.prdData.specs_create_list = attrSetData;                      
           }
       	};
        $scope.copyAttributeSetDataToSpecModel = function(attrSetData){
            if(attrSetData !== undefined){
               	$scope.product.specmodel = _.map(attrSetData, function(attrData, indx){
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
               })
            }
       	};
       	$scope.unsetDefaultWarehouse= function(){
       		if($scope.product.defaultwhouse.stock==false){
       			$scope.product.defaultwhouse.stock_value = "";
       			$scope.product.defaultwhouse.lowstock_value = "";
       			$scope.product.defaultwhouse.safetystock_value = "";
       		}
       	};
		$scope._wareCheckHendlerDefault= function(){
			if($scope.product.defaultwhouse.name==false){
				$scope.product.defaultwhouse.stock=false;
			}
		};

		$scope.attrRadiochange= function(flag){
			if(flag ==='updateattrset'){
				$scope.product.sepscreateset={attrname:"",attrdesp:"",labelflag:"",fontcolor:"",flagbgcolor:""};
			}
		};

		$scope._loadSpecMultiselectAttrs = function(attrs){
			return _.map(attrs, attr => attr.values);
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
		
		$scope.specListQueryChanged = function(filteredSpecsList){
			// If the current page number exceeds the maximum page number, after filtering on a farther page.
			// Eg: If currentpage is 10, but after filtering there is only 1 page. This resets back to page 1
			if( $scope.prdData.currentPage > $scope.getSpecTotalPageCount(filteredSpecsList.length)-1 ){
				$scope.prdData.currentPage = Math.max($scope.getSpecTotalPageCount(filteredSpecsList.length)-1, 0);
			}
		}

		$scope.onDeleteImageFromImageUploadList = function(fileitem, ordInd){
			//remove already uploaded on server
			if(fileitem.customData!== undefined && (fileitem.customData.completed!== undefined && fileitem.customData.completed === true)){
				_removeImage(fileitem);
			}else{
				fileitem.deleteFile();
			}
			//remove image order
			$scope.prdData.image_order.splice(ordInd, 1);
			//remove unique file upload
			$scope.product.file_interface.addRemoveCustomData("remove_file", {}, fileitem.file);			
			
			// Filter out images that are not "ngDroplet - type:4 - Deleted".
			var validDropletFiles = _.filter($scope.product.file_interface.getFiles(), file => (file.type !== 4));
			defaultImgIndx = parseInt($scope.product.defaultImage)
			if(!isNaN(defaultImgIndx) && (validDropletFiles.length > 0)){
				// In case the number of images are less than the last activated "set as display image" index
				if(defaultImgIndx >= validDropletFiles.length){
					$scope.product.defaultImage = "" + (validDropletFiles.length - 1);
				}
			}
		};

		$scope.dropCallback = function(index, item, external, type, flag) {
			if(flag==='requirement'){
				var temp =[];
				if(item.get_all_attribute_value_detail.length>0){
					(item.get_all_attribute_value_detail).map(function(it){
						temp.push({check:false,priceval:"",pricetype:"",orderval:"",attr_val_id:it.attr_val_id});
					});
				}
				$scope.$evalAsync(function(){
					$scope.product.reqmodel.push({priceval:"",pricetype:'Fixed',oderval:"",id:item.id,multiopt:temp, id:item.id, front_input: item.front_input, required: item.required});
					var indx = _.findIndex($scope.prdData.reqlist, (x) => x.id === item.id );
					if(indx !== -1){
						$scope.prdData.reqlist.splice(indx, 1);
					}
					$scope.prdData.req_create_list.push(item);
				})
			}
			else if(flag==='specification'){
				$scope.$evalAsync(function(){
					$scope.product.specmodel.push({textVal:"",selectVal:"",textareaVal:"",usevaiant:false,attrselect:"",mulattrselect:"",id:item.id, front_input: item.front_input, required: item.required});
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

		$scope.processFilePickerFiles = function(productObj){
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
			.then(() => productObj)
			.catch((err) => {
				console.log(err);
				return Promise.reject(err);
			});
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
		
		$scope.processRequirements = function(productObj){
			return new Promise(function(res, rej){
				try{
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
					res(productObj);
				}
				catch(err){
					rej(err);
				}
			});
		}

		$scope.processDates = function(productObj){
			return new Promise(function(res, rej){
				try{
					// Convert expiry date to json string
					if(productObj.expiry_date !== undefined && productObj.expiry_date){
						productObj.expiry_date = productObj.expiry_date.replace(/\//g,'-');
					}
					if(productObj.sp_fromdate !== undefined && productObj.sp_fromdate){
						productObj.sp_fromdate = productObj.sp_fromdate.replace(/\//g,'-');
					}
					if(productObj.sp_todate !== undefined && productObj.sp_todate){
						productObj.sp_todate = productObj.sp_todate.replace(/\//g,'-');
					}
					// Convert any dates in spec to json string
					for(var i=0; i<productObj.specmodel.length; i++){
						if(productObj.specmodel[i] !== undefined  && productObj.specmodel[i].deldate !== undefined && productObj.specmodel[i].deldate){
							//productObj.specmodel[i].deldate = productObj.specmodel[i].deldate.toJSON();
							productObj.specmodel[i].deldate = productObj.specmodel[i].deldate.replace(/\//g,'-');
						}
					}
					res(productObj);
				}
				catch(err){
					rej(err);
				}
			});
		}

		function getBase64Url(fileObj) {
			 return new Promise(function(res, rej){
				 try{
					reader = new FileReader();
					reader.readAsDataURL(fileObj);
					reader.onloadend = function(){
						res({
							filename: fileObj.name,
							filecontent: reader.result
						});
					};
				 }
				 catch(err){
					 rej(err);
				 }
			 });
		};

		$scope.convertToRequiredSaveFormat = function(productObj){
			return new Promise(function(res, rej){
				try{
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
								options = [];
								if(_.isArray(productObj.specmodel[i].mulattrselect)){
									options = productObj.specmodel[i].mulattrselect
								}
								else if(_.isObject(productObj.specmodel[i].mulattrselect)){
									options = [productObj.specmodel[i].mulattrselect]
								}
								spec.attribute_value_ids = _.map(options || [], (v) => v.attr_val_id);
							}
						}
						else if(productObj.specmodel[i].front_input === 'browse_file'){
							spec.files = productObj.specmodel[i].filepickerVal;
						}
						specs.push(spec);
					}
					productObj.specmodel = specs;
					res(productObj);
				}
				catch(err){
					rej(err);
				}
			})
		};	

		//Listen on  save or save and continue product section
		$scope.saveProduct= function($event, productForm, btn_action){
			$event.stopPropagation();	
			// console.log(JSON.stringify($scope.product.specmodel));
			//set button action
			$scope.prdData.save_btn_action = btn_action; 

			//grt file to check
			var validDropletFiles = _.filter($scope.product.file_interface.getFiles(), file => (file.type !== 4));
			var errorHtml = '';
			//check file is uploded on server or not
			if(validDropletFiles.length && (_uploadedFileIdArray.length!= validDropletFiles.length)){
				errorHtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please upload all product image</div>';
				// swal("Opps..", "Please upload all image first", "warning");
				// return false;
			}
			
			//check form is valid or not and show error message $scope.productform.$invalid === true || validDropletFiles.length === 0
			if($scope.productform.$invalid === true){
				// var errorHtml = '';	
				for(var i in formFieldName){
					if(($scope.productform[formFieldName[i]]!==undefined &&
						$scope.productform[formFieldName[i]].$error!== undefined && 
						$scope.productform[formFieldName[i]].$error.required === true) &&
						$scope.productform[formFieldName[i]].$invalid === true){
							errorHtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>'+errorMsg[i]+'</div>';
					}
				}

				/*if(validDropletFiles.length === 0){
					errorHtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span>File is required</div>'
				}*/

				_messageHandler("error", errorHtml,"Error");
				return;
			}

			//in case of config product if user selected specification and set used as variant then caheck variant have or not
			if($scope.product.specmodel.length){
				// console.log($scope.produt.specmodel);
				var er = '';
				_.map($scope.product.specmodel, function(item){
					if(item.usevaiant === true){
						if(item.front_input=='select' && (item.attrselect == "" || item.attrselect.length == 0))
							er+='<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please select product variants</div>';
						else if(item.front_input=='multiselect' && (item.mulattrselect == "" || item.mulattrselect.length == 0))
							er+='<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please select product variants</div>';
						else if(item.front_input=='text' && item.textVal == "")
							er+='<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please enter product variants</div>';
						else if(item.front_input=='textarea' && item.textareaVal == "")
							er+='<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please enter product variants</div>';
						/*if(item.attrselect=="" && item.mulattrselect==""){
							er+='<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please select product variants & spec </div>';
						}else if(typeof item.attrselect == "object" && item.attrselect.length === 0){
							er+='<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please select product variants & spec </div>';	
						}else if(typeof item.mulattrselect == "object" && item.mulattrselect.length === 0){
							er+='<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please select product variants & spec </div>';
						}*/						
						//if(item.attrselect.length === 0 ) er+='<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please select product variants & spec </div>';
					}
				});

				if(er!=''){
					_messageHandler("error", er,"Error");
					return;
				}
			}
			

			//enable page loader and disable save,save_continue button
			$scope.prdData.loading.save_and_continue = true;
			$scope.prdData.loading.disableBtn = true;
		
			$scope.product.createupdatetype = $scope.prdData.spescrtupdate;
			var productObj = angular.copy($scope.product);
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
				
				if(typeof product_type !=="undefined" && product_type!==undefined){
					productObj["product_type"] = product_type;	
				}
				
				//for change name and desc to json string
				productObj.name = angular.toJson(productObj.name);
				productObj.productDesc = angular.toJson(productObj.productDesc);				
				
				/*if product have image then  //for default image   //for product image (append id & image order)*/
				var def = '',
					objdef = {};

				if(_uploadedFileIdArray.length){					
					productImageOrder($scope.prdData.image_order, _uploadedFileIdArray);					
					def = parseInt($scope.product.defaultImage);
					objdef["img_id"] = _uploadedFileIdArray[def].img_id;
					objdef["is_default"] = true;
					objdef["index"] = def;
					objdef["img_name"] = _uploadedFileIdArray[def].img_name;
				}
				productObj.product_images = angular.toJson(_uploadedFileIdArray);
				/********end image **************/				
				//final data to send server
				var finalData = {
					product : angular.toJson(productObj),
					defaultImage : angular.toJson(objdef),
				};		
				
				//send request to server to create product
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

		            		//console.log(response.data.error);

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
		//Listen on save all change varient
		$scope.saveVariants = function(){
	        salesfactoryData.getData(variantsaveurl,'POST',$scope.variantsCombinationData)
	        .then(function(response){
	        	if(response.data.status!==undefined && (response.data.status === true || response.data.status == true)){ 
	                angular.element('#variantmodal').modal('hide'); 
	                swal({
	                     title: "Good",
	                     text: "Product successfully created.",
	                     type: "success",
	                     timer: 3000
                     }).then(
                      function(){},
                      function(dismiss){
                      		window.location.href = productvarianturl;
                       // window.location.href = productvarianturl+response.data.parent_id;
	                });                
	            }	               
	        },function(error){
	        	_error();
	        });
	    };
 		/*******Related product section (Table section config)*****/
 		$scope.checksku= function(sku){
        	var data = {'sku':sku };
        	salesfactoryData.getData(skucheckurl,'GET',data).then(function(response){
	    		if(response.data.status){
	                $scope.skuexists = true;
	            }
	            else{
	                $scope.skuexists = false;
	            }
           
        	});
	    };
	    //save new category section savenewcaturl
	   	$scope.saveNewCat= function(){
			if($scope.catname!='' || $scope.catname!=null){
				salesfactoryData.getData(savenewcaturl,'POST',{catname:$scope.catname}).then(function(resp){
					if(resp.data.status==="success"){
						$scope.showcatform = false;
						$scope.hidecreatebutton = true;
						$scope.prdData.cateTree = JSON.parse(resp.data.catobj);
					}
				},function(error){
					//body
				});
			}
		};
		//Bundel product section
		//Listen on product tab (li) active
		$scope.liActiveHandler = function($event,strflag){
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
			// if($event=='') return;
			// $event.preventDefault();
		};
		/**
        *Listen on change date (from to ) and  update date picker
		*@params dateFrom and dateTo
		*@param Datepicker option name (dateFromOption and dateToOption)
		**/
        $scope._updateDatePicker = function(dateFrom, dateTo, dateFromOption, dateToOption){
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
    	}).then(function(isConfirm){
    		 // handle confirm, result is needed for modals with input    		 
       	},function(dismiss){
    		// dismiss can be "cancel" | "close" | "outside"    		
    	});    	
    };

    /*Listen for get index 
    *@param : destObj (oject/array)
    *@param : matchEle (string)
    *@param : matchType (string -optional)
    */   
    function _getIndex(destObj, matchEle, matchType){
        var index = -1;
        // index = destObj.findIndex(function(item){
        //     if(matchType!== undefined && matchType){
        //         return (item[matchType] == matchEle);
        //     }else{
        //         return (item == matchEle);
        //     }
        // });
        // return index;

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

    //Listen to product image order manage
    function productImageOrder(orderModel, uploadedImgArr){  
    	for(var i in uploadedImgArr){
    		uploadedImgArr[i]['img_order'] = (typeof orderModel[i]!="undefined" && (orderModel[i]!== '' || orderModel[i]!= null)) ? orderModel[i] : "";     		
    	};
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
