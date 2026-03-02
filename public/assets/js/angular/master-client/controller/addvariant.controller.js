/***********
*@desc : This module used to handle add new variant in product
*@created : 05-Apr-2019
*@Author : SMOOTHGRAPH CONNECT Pvt. Ltd
*/

(function(angular){

	"use strict";

	function addVariantController($scope, salesfactoryData, $q, $window){
		$scope.variant = {
			auto_sku : false,
			exist_sku : false,
			sku : '',
			tags : [],
			initial_price : '',
			special_price  : '',
			site_visibility : '',
			variant_option : [],
			unlimited_stock : false,
			stock : '',
		};

		$scope.variantData = {
			file_interface : {},
			up_cl_all : false,
			variant_error : false,
		};

		var _that = $scope;

		/*****This section used for file upload***/
		// Listen for when the interface has been configured.
        $scope.$on('$dropletReady', function whenDropletReady() {
			if(_that.variantData.file_interface.allowedExtensions){
				_that.variantData.file_interface.allowedExtensions(file_upload_setting.allowed_extension);
				_that.variantData.file_interface.useArray(true);						
				//number of file can upload onces
				_that.variantData.file_interface.setMaximumValidFiles(file_upload_setting.max_number_file);
				// total number of file can upload
				_that.variantData.file_interface.setMaxNumberFiles(file_upload_setting.max_number_file);
			}			
	    });

        //Listen on file add in droplet
        $scope.$on('$dropletFileAdded', function onDropletSuccess(event,file) {		
        	//for file size check (file size in Bytes)
    		var FSize  = file.file.size;
    		FSize =(FSize/1024/1024).toFixed(2);
    		var validDropletFiles = _that.variantData.file_interface.getFiles().filter(function(file) {
				return (file.type !== 4);
			});  

         	if(file.type & _that.variantData.file_interface.FILE_TYPES.INVALID){
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

         	_that.variantData.up_cl_all = true;
        });

        /**
         *file upload new handler xmlHttprequest
         * @method uploadFiles
         * @return {$q.promise}
         */
       	_that.uploadFiles = function uploadFiles(_url, method_type, imgObj) {       	
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
		_that.clearAllFiles = function(){
			var temp_file = _that.variantData.file_interface.getFiles(_that.variantData.file_interface.FILE_TYPES.VALID);
			temp_file.map((item)=>{
				//In case file is already uploaded on server then remove from temp table
				if(item.customData!== undefined && (item.customData.completed!== undefined && item.customData.completed === true))
					_removeImage(item);					
				else item.deleteFile();	
				_that.variantData.file_interface.addRemoveCustomData("remove_file", {}, item.file);				
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
		_that.onImageUpload = function(imageItem, strFlag){
			var _sp = _that.variantData.file_interface;			
			//for single image upload 
			if(strFlag!== undefined && strFlag === "single_upload"){				
				upload(imageItem);
			}else if(strFlag!== undefined && strFlag === "all_upload"){
				//for multi image  upload
				var fls = _sp.getFiles().filter((fs)=>{
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
				_that.uploadFiles(upload_image_url, "POST", imgObj)
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


		//Listen to check sku already exist or not
		_that.checksku= function(sku){
			if(!sku) return;

        	salesfactoryData.getData(sku_check_url,'GET', {'sku':sku})
        	.then((response)=>{
        		_that.variant.exist_sku = (response.data.status)? true : false;           
        	});
	    };

	   	//Listen to load tags from db 
	    _that.loadTags = function(query){
			if(!query) return;

			return salesfactoryData.getData(product_tag_url,'GET', {'query' : query})
			.then(function(r){
				return r.data || [];
			},function(error){
				console.log;
			});
		};

		function convertSelectedVariant(data){
			let result = [];
			for(let v in data){
				if(v!=0){
					result.push({
						'attr_id' : Object.keys(data[v])[0],
						'values' : Object.values(data[v])[0],
					});
				}				
			};
			return result;
		};

		_that.saveVariant = function(evt){
			evt.preventDefault();
			let variantObj = angular.copy(_that.variant); 
				variantObj.variant_option = convertSelectedVariant(variantObj.variant_option);			
			salesfactoryData.getData(save_variant_url, 'POST', angular.toJson(variantObj))
			.then((response)=>{
				if(typeof response=="undefined" || response.data== undefined || response.data ==null) return;
				if(response.data.status && response.data.status=='success') location.reload(true);
			}, (err)=>{
				//
			}).fanilly(()=>{
				//
			});
		};



		angular.element(document.getElementById('add_variant')).on('hidden.bs.modal', function(){
			console.log('wooooooo');
		});
	};
	
	angular.module('sabinaAdminApp').controller('addvariantctrl', ['$scope','salesfactoryData', '$q', '$window', addVariantController]);
})(window.angular);