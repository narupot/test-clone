/*
*@desc : This controller used to handle theme customization (EKONG)
*@author : SMOOTHGRAPH CONNECT PVT LTD
*@created at : 18/09/2018
*@version : sg.0.1
*/

"use strict";
	
(function(angular){	

	/********
	*@desc : theme customization serives
	*********/
	var SEC_SETTING_DATA = {};
	var localServiceHadler = function localServiceHadler(){
		let rowName ="";

  		var loopUtilLeafNode = function(node, rowData, colData) {
           if (!node) return;
           //console.log(node)
           if (node.elements) {
           		_.forEach(node.elements, function(elem){
           			colData['elements'].push({
           				name : (elem.name) ? elem.name : null,
	              	  	setting : (elem.setting) ? elem.setting : null,
						spacing : (elem.spacing) ? elem.spacing : null,
						design : (elem.design) ? elem.design : null,
						visibility : (elem.visibility) ? elem.visibility : null,
						element_type : (elem.element_type) ? elem.element_type : null,
           			});

           			//SECTION SETTING DATA FOR USED IN FRONT FRO VISIBILITY OR ICON SETTING
					if(elem.name && elem.visibility){
						if(rowName){
							SEC_SETTING_DATA[rowName][elem.name] ={'visibility' : elem.visibility};              	  
							angular.extend(SEC_SETTING_DATA[rowName][elem.name], elem.setting);
						}else{
							SEC_SETTING_DATA[elem.name] ={'visibility' : elem.visibility};              	  
							angular.extend(SEC_SETTING_DATA[elem.name], elem.setting);
						}						
					}
           		});
           }
           //In case node have children
           if (node.columns) {
              _.forEach(node.columns, function(childNode) {
              	  var col = {
              	  	name : (childNode.name) ? childNode.name : null,
              	  	setting : (childNode.setting) ? childNode.setting : null,
					spacing : (childNode.spacing) ? childNode.spacing : null,
					design : (childNode.design) ? childNode.design : null,
					visibility : (childNode.visibility) ? childNode.visibility : null,
					elements : [],
              	  };            	 
              	  //SECTION SETTING DATA FOR USED IN FRONT FRO VISIBILITY OR ICON SETTING
              	  if(childNode.name && col.visibility){
              	  	if(rowName){
              	  		SEC_SETTING_DATA[rowName][childNode.name] ={'visibility' : col.visibility};              	  
              	  		angular.extend(SEC_SETTING_DATA[rowName][childNode.name], col.setting);
              	  	}else{
              	  		SEC_SETTING_DATA[childNode.name] ={'visibility' : col.visibility};              	  
              	  		angular.extend(SEC_SETTING_DATA[childNode.name], col.setting);
              	  	}
              	  }

              	  loopUtilLeafNode(childNode, rowData, col);
              	  rowData['columns'].push(col);
              });
           }
        };


		/*
		*@param : allJson
		*/
		this.getSectionData = function(crtjson, allJson){
			rowName = "";
			if(allJson.name!=='blog' && allJson.rows){
				_.forEach(allJson.rows, function(row, rk){
					var rowData = {
						name :  (row.name) ? row.name : null,
						setting : (row.setting) ? row.setting : null,
						spacing : (row.spacing) ? row.spacing : null,
						design :  (row.design) ? row.design : null,
						visibility : (row.visibility) ? row.visibility : null,
						columns : [],
					};
					//SECTION SETTING DATA FOR USED IN FRONT FRO VISIBILITY OR ICON SETTING					
					if((allJson.name === 'product_detail_config' || allJson.name ==='product_detail_bundle') && (row.name == 'list_view' || row.name == 'matrix_view')){
						SEC_SETTING_DATA[row.name] = {};
						rowName = row.name;
					}
					
					if(allJson.name === 'product_thumb'){
						SEC_SETTING_DATA[row.name] = {};
						rowName = row.name;
					}

					if(row.name && row.visibility){
						SEC_SETTING_DATA[row.name] ={'visibility' : row.visibility};              	  
						angular.extend(SEC_SETTING_DATA[row.name], row.setting);
					}
					loopUtilLeafNode(row, rowData);
					crtjson.rows.push(rowData);
				});	
			}
			return JSON.stringify(crtjson);
		};
	};

	/*
	*@desc : Theme customizations controller 
	*/
	var mainControllerHandler = function mainControllerHandler($scope, $timeout, $rootScope, ajaxRequest, localService){		
		//scope variable section		
  		$scope.navigationJson =[];
  		$scope.sec_json ={};
  		$scope.themeJson ={};
		$scope.themeData ={
			spacing_current_json : {},
			text_design_current_json : {},
			setting_design_current_json : {},
			spacing_enable : !1,
			text_design_enable : !1,	
			setting_design_enable : !1,
			// header_positions : [],
			listing_positions : [],
			//setting, design 	
			loading: {
                "save_and_continue": !1,
                "disableBtn": !1,
                "btnloaderpath": LOADER_IMAGE_URL,
                "text" : LANG_MESG.global,
            },
            show_edit_time : null,            
		};
		$rootScope.themeurl = THEME_JSON_URL;

		//clone reference of object
		var $thmd = $scope.themeData;
		var $thmJson = $scope.themeJson;
		var ALL_SECTION_JSON = "";
		//Listen to http request start
        $scope.$on("httpLoaderStart", function(evt){
        	$thmd.loading.save_and_continue = $thmd.loading.disableBtn = !0;
        });

        $scope.$on("httpLoaderEnd", function(){
            $thmd.loading.save_and_continue = $thmd.loading.disableBtn = !1;
        });

        /*
        *@private method section
        */
		var loopUtilLeafNode = function(node, section) {
           if (!node) return;

           if (node.elements) {
           	  /*if(section === "header"){
				$thmd.header_positions = $thmd.header_positions.concat(node.elements);	
           	  }*/
           	  if(section === "product_listing"){
           	  	$thmd.listing_positions = $thmd.listing_positions.concat(node.elements);	
           	  }           	  
           }

           //In case node have children
           if (node.columns) {
              _.forEach(node.columns, function(childNode) {
                 loopUtilLeafNode(childNode, section);
              });
           }
        };

        var enableDisableSection = function enableDisableSection(crtSec, act){        	
        	_.forEach(this , function(item, key){
        		if(crtSec === key){
        			item.spacing_enable = item.text_design_enable = item.setting_design_enable = !1;
        			item[act] = !0;
        		}else{
        			item.spacing_enable = item.text_design_enable = item.setting_design_enable = !1;
        		}
        	});
        };

        /*
        *@get theme customization json section wise
        	action : - get json type wise form local folder(menas current type folder)
        *@param : url {string}
        *@param : methodType {GET | POST -> string}
		*@param : obj {Query - object}
		*@param : section {string}
        */
        var getSectionJson = function (url, methodType, obj, section){
        	ajaxRequest.getData(url, methodType, obj)
			.then(function(response){
				$scope.themeJson[section] = response.data[section];
				//if(section === "header" || section === "product_listing")
				if(section === "product_listing") {
					_.map($scope.themeJson[section].rows, function(item){
						loopUtilLeafNode(item, section);
					});
				}
				//add setting props in case of header
				if(section === "header"){
					let index = _.findIndex($scope.themeJson[section].rows, {"name":"user_menu"});
					if(index>=0){
						let menu = $scope.themeJson[section].rows[index].setting_prop[0].prop_value;
						let oldNewDiff = _.difference($scope.themeJson[section].rows[index].setting_prop[0].prop_value, ACCOUNT_MENU_ARRAY);
						
						if(!menu.length){
							$scope.themeJson[section].rows[index].setting_prop[0].prop_value = ACCOUNT_MENU_ARRAY;
						}
						else if(menu.length && oldNewDiff.length){
							$scope.themeJson[section].rows[index].setting_prop[0].prop_value.concat(oldNewDiff);
						}
					}
				}				
			}, function(error){
				//
			});
        };

        /***
		*@desc : This function used to add and remove popup-open calss from main-tab-section container
		*@param : flag {string add/remove}
		***/
		function togglePopupClass(flag){
			switch(flag){
				case 'add' :
					$('.main-tab-section').addClass('popup-open');
					break;
				case 'remove' : 
					$('.main-tab-section').removeClass('popup-open');
					break;
			};
		};

        /*
        *@init method (self executive function)
        */		
		var init = function init(){	
			//create page navigation page
			let i =0;
			for(i of THEME_PAGES){
				$scope.sec_json[i.section] = {
					"spacing_enable" : !1,
					"text_design_enable" : !1,	
					"setting_design_enable" : !1,
				};
				$scope.themeJson[i.section] = [];
				i.status = (i.status === "0" || i.status == 0) ? "inactive" : "active" ;
			};
			$scope.navigationJson = THEME_PAGES;			
			//get json section wise
			_.forEach($scope.navigationJson, function(item){
				//THEME_JSON_URL  TEMPLATE_URL
				var url = THEME_JSON_URL+'/json/'+item.section+'.json';
				getSectionJson.apply(this, [url, 'GET', {}, item.section]);
			});

			//get all sections json
			ajaxRequest.getData(THEME_JSON_URL+'/json/sections.json', 'GET')
			.then((res)=> {	
				ALL_SECTION_JSON = res.data;
				$thmd.show_edit_time = ALL_SECTION_JSON[0].last_updated;		
			});			
		};
		init();
  		/********************* end ************************/

  		/********************
  		*@scope method section
  		*********************/
		/*
		*@desc : Listen to mouse hover 
		*@param : $evt {$event}
		*@param : action {sting ->in/out}
		*@param : crtObj {object ->current object}
		*@param : crtSection {string - global | header | footer} ->optional
		*/
		$scope.hoverHandler = function($evt, action, crtObj, crtSection){
			$evt.preventDefault();
			$evt.stopPropagation();
			// console.log("hover in out", crtObj);
			try{
				if(action === 'in'){
					crtObj['mouse_in_out'] = !0;
					$thmd.spacing_enable =!1;
				}
				else if(action === 'out')
					crtObj['mouse_in_out'] = !1;
			}catch(err){
				console.log;
			};						
		};

		/* 
		*@desc : Listen to popup action 
		*/
		$scope.popupAction ={
			spacing : function($event, data, crt_section){
				$event.preventDefault();
				enableDisableSection.apply($scope.sec_json, [crt_section, 'spacing_enable']);
				$thmd.spacing_current_json = {};
				$thmd.spacing_current_json = data;
			},
			design : function($event, data, crt_section){
				$event.preventDefault();
				enableDisableSection.apply($scope.sec_json, [crt_section, 'text_design_enable']);
				$thmd.text_design_current_json = {};
				$thmd.text_design_current_json = data;				
			},
			setting : function($event, data, crt_section){
				$event.preventDefault();
				enableDisableSection.apply($scope.sec_json, [crt_section, 'setting_design_enable']);
				$thmd.setting_design_current_json = {};
				$thmd.setting_design_current_json = data;
			},
		};

		/*
		*@desc : left menu action 
		**/		
		$scope.leftMenu = {
			visibilityAction : function(evt, item){
				evt.preventDefault();
				evt.stopImmediatePropagation();

				if(item.visibility === 'lock') return;

				item.visibility = (item.visibility === 'visible') ?  'invisible' : 'visible';
				var ps={'visibility': item.visibility, 'element_type' : item.element_type};				
				//set prop in setting
				_.extend(item.setting, ps);
			},			
			enablePositionActtion : function(evt, item, elemts){
				evt.preventDefault();
				evt.stopImmediatePropagation();
				_.forEach(elemts, function(el){
					if(el.name === item.name){
						if(el.is_active) el.is_active!=el.is_active;
						else el['is_active'] = !0;
					}else el['is_active']= !1;
				});
			},			
		};

		$scope.enableTab = function($evt, item){
			_.forEach($scope.navigationJson, function(sec, key){				
				if(sec.name === item.name){
					sec.status = "active";
					$thmd.loading.text = LANG_MESG[sec.section];
					$thmd.show_edit_time = ALL_SECTION_JSON[key].last_updated;
				}else{
					sec.status = "inactive";
				}
			});

			//closed current open popup && set false all flag for popup(design, setting and space)
			togglePopupClass('remove');
			enableDisableSection.apply($scope.sec_json, ['close_popup', 'close_popup']);
		};

		/*
		*@desc : This function used to handel change from type in global from setting
		*@param : item {object} 
		*@param : itemValue {string}
		*themeJson.global.rows[3].columns[0].setting.from_field_class
		*/
		$scope.changeFromType = function(item, itemValue){
			item.selected_from = itemValue;
			if(itemValue === "small"){
				//console.log(item.css_model);
				angular.extend(item.css_model, item.setting.form_small_class);
				angular.extend(item.spacing, item.setting.form_small_class);				
			}else if(itemValue === "medium"){
				//console.log(item.css_model);
				angular.extend(item.css_model, item.setting.form_medium_class);
				angular.extend(item.spacing, item.setting.form_medium_class);
			}else if(itemValue === "large"){
				//console.log(item.css_model);
				angular.extend(item.css_model, item.setting.form_large_class);
				angular.extend(item.spacing, item.setting.form_large_class);
			}
		};

		/*
		*@desc : Listen to handel product view change (on product listing page)
		*@param : columnSetting
		*@param : viewType
		*/
		$scope.changeProductListingView = function(columnSetting, viewType){
			columnSetting['listing_view_type'] = viewType;
		};

		/***
		*@desc : This function used to handle pagination in global section
		*@param : pItem {object}
		*@param : item {object}
		***/
		$scope.paginationChangeFun = function(pItem, item){
			if(pItem.type_model && pItem.type_model!="none"){
				angular.extend(pItem.setting[item.setting_name], item);				
			}
		};

		/*
		*@desc This function get text lang wise
		*@param : item {object}
		*@param : CURR_LANG_DATA {all key & value}
		*/
		$scope.getLangText = function (item){
			return (item.lang_name && CURR_LANG_DATA[item.lang_name]) ? CURR_LANG_DATA[item.lang_name] : item.show_name;			
		};

		/***
		*@desc : save (Theme customization data)
		*@param : $evt {event}
		*@param : action {string -> save | update}
		***/

		$scope.save = function($evt, action){
			$evt.preventDefault();
			//before save data 
			SEC_SETTING_DATA = {};
			var crts = _.find($scope.navigationJson, {'status' : 'active'}),
				secjson =  angular.copy($scope.themeJson[crts.section]);
			var clone_data = angular.copy($scope.themeJson[crts.section]);
			var result = {
				css_data : localService.getSectionData({"name" : crts.section, "rows" : []}, secjson),
				json_data : angular.toJson(clone_data),
				updated_date : new Date(),
				theme_id : theme_id || "",
				slug : slug || "",
				setting_json_data : angular.toJson(SEC_SETTING_DATA),
			};
			//update sections after updated
			let sind = _.findIndex($scope.navigationJson, {'status' : 'active'});
			ALL_SECTION_JSON[sind].last_updated = new Date();			
			result['sections'] = angular.toJson(ALL_SECTION_JSON);
			//send to save
            ajaxRequest.getData(SAVE_URL, 'POST', result)
            .then(function(response){
            	if(response.data!=null && response.data.status && response.data.status == "success"){
            		swal('Done', response.data.message, response.data.status);
            		$thmd.show_edit_time = ALL_SECTION_JSON[sind].last_updated; 
            	}else
            		swal('Opps..', response.data.message, response.data.status);
            }, function(error){
            	swal('Opps..!', 'Something went wrong', 'error');
            });           
		};
	};

	/*@define module */
	angular.module("sabinaAdminApp")
	.controller("themeCustomizationCtrl", ['$scope', '$timeout', '$rootScope', 'ajaxRequest', 'localService', mainControllerHandler])
	.service('localService', [localServiceHadler]);

})(window.angular);
