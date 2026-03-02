/*
*This controller used to add and edit user blog
*Author : Smoothgraph connect pvt. ltd.
*Date :  09/02/2018
*/
(function(){
	var thumbFile;

	function blogCtrlHandler($scope, $timeout, $http, salesfactoryData, $q, $window){
		var self = $scope;
		var edit_page = false;
		//for model
		self.blog ={
			file_interface : {},
			defaultImage : "0",
			prevImage : [],	
			//for cate model
			category :[],
			prevDefaultImage : "",
			//for tags
			tags : [],
		};
		//for data mainuplution
		self.blogData ={
			blogCate :[],
			//for loader
			loading:{
		    	"save_and_continue" : false,
		    	"disableBtn" : false,
		    	"btnloaderpath" :tableLoaderImgUrl,
		    },
		    save_btn_action : '',
		    up_cl_all : false,
		};

		//FOR uploaded file id array 
        var _uploadedFileIdArray =[];

		//get blog category form server and set in cate tree directive
		function getBlogCate(obj){
			//if()
			salesfactoryData.getData(categorylisturl,"GET",obj)
			.then(function(response){
				if(response.data!==undefined && angular.isArray(response.data) && response.data.length>0){
					self.blogData.blogCate = response.data;
				}else{
					self.blogData.blogCate =[];
				}
			},function(error){
			   _error();
			});			
		};		

		//for Edit page previous created data 
		// prd_defalut_img for check product default image in case of edit
		var prd_defalut_img={"prevImg" : "", "dropImg" : ""};
		var blog_id ='';
		
		function setPreviousData(){
			try{
				if(typeof previousData!=="undefined" && previousData){
					blog_id = previousData.id;
					self.blog.image_base_url = image_base_url;
					self.blog.cms_url = (previousData.url!==undefined && previousData.url)? previousData.url : "";	
					self.blog.status  = (previousData.status!==undefined && !isNaN(previousData.status))? previousData.status.toString() : "";
					self.blog.features = (previousData.features!==undefined && !isNaN(previousData.features))? previousData.features.toString() : "";
					self.blog.comment = (previousData.comment!==undefined && !isNaN(previousData.comment))? previousData.comment.toString() : "";
					self.blog.publish = (previousData.publish!==undefined && !isNaN(previousData.publish))? previousData.publish.toString() : "";
					self.blog.tags = previousData.tags;
					//filter all cate id
					if(previousData.blog_cat!==undefined && previousData.blog_cat.length>0){
						self.blog.category = previousData.blog_cat.map(function(item){
							return(item.cat_id!== undefined && item.cat_id).toString();
						});
					}
					//for thunb image 
					self.thumb_image = previousData.image;
					//for file 
					if(previousData.blog_images!==undefined && previousData.blog_images.length>0){
						self.blog.prevImage = previousData.blog_images;
						//check previous default image
						var indf = _getIndex(previousData.blog_images, "1", "is_default");
						if(indf!== -1){
							self.blog.prevDefaultImage = indf.toString();
							self.blog.defaultImage = "";
							prd_defalut_img.prevImg = indf;							
						}else {
							self.blog.prevDefaultImage = "";
							self.blog.defaultImage = "0";
						}
						//console.log(self.prev_ind);
						file_upload_setting.max_number_file = (file_upload_setting.max_number_file - previousData.blog_images.length);
					}
					//for blog mutilange section model
					if(previousData.blog_decses!==undefined && previousData.blog_decses.length>0){
						self.blog_title={};
						self.blog_desc ={};
                        self.short_description={};
						self.blog_heading ={};
						self.meta_desc={};
						self.meta_keyword={};
						self.meta_title={};
						
						angular.forEach(previousData.blog_decses, function(item,index){
							if(item.blog_title !== undefined && item.blog_title)
								self.blog_title[item.lang_id] = item.blog_title;
							if(item.blog_desc!==undefined && item.blog_desc)
								self.blog_desc[item.lang_id] = item.blog_desc;
                            if(item.short_description!==undefined && item.short_description)
                                self.short_description[item.lang_id] = item.short_description;
                            
							if(item.blog_heading!==undefined && item.blog_heading)
								self.blog_heading[item.lang_id] = item.blog_heading;							
							if(item.meta_desc!==undefined && item.meta_desc)
								self.meta_desc[item.lang_id] = item.meta_desc;
							if(item.meta_keyword!==undefined && item.meta_keyword)
								self.meta_keyword[item.lang_id] = item.meta_keyword;							
							if(item.meta_title!==undefined && item.meta_title)
								self.meta_title[item.lang_id] = item.meta_title;
						});					
					}										
				}				
			}catch(e){
				console.log;
			}
		}
		//call
		if(typeof previousData!=="undefined" && previousData){
			edit_page = true;
			setPreviousData();
			getBlogCate({action : "edit"});
		}else{
			getBlogCate({action : "add"});
		}
		


		// Listen for when the interface has been configured.
        self.$on('$dropletReady', function whenDropletReady() {
			if(self.blog.file_interface.allowedExtensions){
				self.blog.file_interface.allowedExtensions(file_upload_setting.allowed_extension);
				self.blog.file_interface.useArray(true);
				//number of file can upload onces				
				self.blog.file_interface.setMaximumValidFiles(file_upload_setting.max_number_file);
				// total number of file can upload
				self.blog.file_interface.setMaxNumberFiles(file_upload_setting.max_number_file);
			}			
	    });
        // Listen for when the files have been successfully uploaded.
        self.$on('$dropletSuccess', function onDropletSuccess(event, response, files) {
            self.uploadCount = files.length;
            self.success     = true;
            $timeout(function timeout() {
                self.success = false;
            }, 5000);
        });

        self.$on('$dropletFileAdded', function onDropletSuccess(event,file) {
        	//for file size check (file size in Bytes)
    		var FSize  = file.file.size;
    		FSize =(FSize/1024/1024).toFixed(2);
    		var validDropletFiles = self.blog.file_interface.getFiles().filter(function(file) {
				return (file.type !== 4);
			});    		

        	if(file.type & self.blog.file_interface.FILE_TYPES.INVALID){
        		if(file.extension !== undefined && _getIndex(file_upload_setting.allowed_extension, file.extension) == -1){
        			//console.log("in extension check")
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

         	self.blogData.up_cl_all = true;
        });

        /**
         *file upload new handler xmlHttprequest
         * @method uploadFiles
         * @return {$q.promise}
         */
        self.uploadFiles = function uploadFiles(_url, method_type, imgObj) {           
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
                        self.$evalAsync(function(){
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
                self.$evalAsync(function(){
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
                self.$evalAsync(function(){
                    queuedFiles.customData.inprogress = true;

                    if (event.lengthComputable){ 
                        queuedFiles.customData.percent = Math.floor((event.loaded / event.total) * 100) +"%";
                        // console.log(queuedFiles.customData.percent) 
                    }
                });             
            }.bind(this); 

            // Iterate all of the valid files to append them to the previously created
            // `formData` object.
            formData.append("blog_image", queuedFiles.file, $window.encodeURIComponent(queuedFiles.file.name));
            formData.append("request_from", "blog");         
            httpRequest.send(formData);

            return deferred.promise;

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
                imgObj.customData.error = false;
                _error();
            });
        };

        //Listen on Image upload
        self.onImageUpload = function(imageItem, strFlag){
            // console.log(imageItem);
            var _sp = self.blog.file_interface;
            
            //for single image upload 
            if(strFlag!== undefined && strFlag === "single_upload"){                
                upload(imageItem);
            }else if(strFlag!== undefined && strFlag === "all_upload"){
                //for multi image  upload
                _.map(_sp.getFiles(), function(file){
                    if (file.type !== 4 ){
                        if(file.customData!== undefined && file.customData.completed === true){
                            //In case file is already uploaded on server
                        }else{
                            upload(file);
                        }
                    }                  
                });             
            }

            //upload to server one by one
            function upload(imgObj){
                self.uploadFiles(upload_image_url, "POST", imgObj)
                .then(function(data){
                    var response = JSON.parse(data);
                    if(response.status!== undefined && response.status === "success"){                      
                        imgObj.customData.completed = true; 
                        imgObj.customData.img_id = response.img_id;
                        imgObj.customData.inprogress = false;
                        // imgObj.customData.percent = "0%";
                        imgObj.customData.error = false;                                        
                        _uploadedFileIdArray.push({"img_id" : response.img_id, "img_name" : response.img_name});
                    }else{
                        //not valid response
                        imgObj.customData.completed = false; 
                        imgObj.customData.inprogress = false;
                        imgObj.customData.percent = "0%";
                        imgObj.customData.error = true;
                    }
                },function(error){
                    imgObj.customData.completed = false;
                    imgObj.customData.img_id = "";
                    imgObj.customData.inprogress = false;
                    imgObj.customData.percent = "0%";
                    imgObj.customData.error = true;
                    _error();                   
                }).finally(function(){
                    console.log;
                })
            };          
        };
 /*****end file upload***/

        //Listen on clear all file
		self._clearallfiles = function(){
			var temp_file = self.blog.file_interface.getFiles(self.blog.file_interface.FILE_TYPES.VALID);
			angular.forEach(temp_file, function(item){
				//In case file is already uploaded on server then remove from temp table
				if(item.customData!== undefined && (item.customData.completed!== undefined && item.customData.completed === true)){
				   _removeImage(item);
				}else{
				   item.deleteFile();
				}
			});
			//In case of edit page then set previous image default
			if(edit_page === true){
				getSetPrevImgDefault("set","clearall");
			}			
		};

		//Listen on cancel file
		self.onDeleteImageFromImageUploadList = function(fileitem,fileIndex){
			//remove already uploaded on server
            if(fileitem.customData!== undefined && (fileitem.customData.completed!== undefined && fileitem.customData.completed === true)){
                _removeImage(fileitem);             
            }else{
                fileitem.deleteFile();
            } 

			// Filter out images that are not "ngDroplet - type:4 - Deleted".
			var validDropletFiles = self.blog.file_interface.getFiles().filter(function(file) {
				return (file.type !== 4);
			});		
			//console.log(validDropletFiles)
			defaultImgIndx = parseInt(self.blog.defaultImage);
						
			if(!isNaN(defaultImgIndx) &&  (edit_page === true && defaultImgIndx == fileIndex)){
				getSetPrevImgDefault("set", "cancel");
			}else if(!isNaN(defaultImgIndx) &&  (validDropletFiles.length > 0)){
				//console.log("her???" + defaultImgIndx);
				// In case the number of images are less than the last activated "set as display image" index
				if(defaultImgIndx >= validDropletFiles.length){
					self.blog.defaultImage = "" + (validDropletFiles.length - 1);
				}
			}
		};

		/*********This function used for delete previous uploaded image on product add page**********/
	    self.deletePrevUploadImg =($event,fileId, index)=>{
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
		    }).then((isConfirm)=>{
		        if (isConfirm) {
		        	salesfactoryData.getData(deleteBlogImage, "POST", {"image_id" : fileId})		        			
		            .then(function(response){
		                if(response.data.status=='success') {
		                   _messageHandler(response.data.status,response.data.message,"Cool");
						   var indx = _getIndex(self.blog.prevImage, fileId , "id"); 
						   if(indx!=-1){
							 self.blog.prevImage.splice(index,1); 
							 if(prd_defalut_img.prevImg!=="" && prd_defalut_img.prevImg == index){
							 	prd_defalut_img.prevImg ="";							 	
								getSetPrevImgDefault('set', "delete");
							 }
						   }						   
		                   //calculate previous upload file and set file upload restiction
						   file_upload_setting.max_number_file = parseInt(file_upload_setting.max_number_file) + 1 ;
						   self.blog.file_interface.setMaximumValidFiles(file_upload_setting.max_number_file);
						   self.blog.file_interface.setMaxNumberFiles(file_upload_setting.max_number_file);
		                }
		                if(response.data.status=='error'){	
		                	_messageHandler(response.data.status,response.data.message,"Error"); 
		                }
		            },function(error){
		            	_error();
		            });            
		        }
		    });
		};

		/*
		*Listen on file upload (set default chenge).
		* @param item 
		* @param flag (dropImg/prevImg)
		* @param index
		*/		
		self.addCheckAttrInItem=(item,flag,index)=>{
			if(flag==="prevImg"){
				prd_defalut_img[flag] = index;
				prd_defalut_img["dropImg"] = "";
				self.blog.prevDefaultImage = index.toString();
				self.blog.defaultImage = "";
			}else if(flag==="dropImg"){
				prd_defalut_img[flag] = index;
				prd_defalut_img["prevImg"] = "";
				self.blog.prevDefaultImage = "";
				self.blog.defaultImage = index.toString();
			}	
			console.log(prd_defalut_img);	
		};

		/*Listen on get and set prev image default 
		*@param  :(set/get)
		*@param  : action->string (like clearall/cancel)
		*/
		function getSetPrevImgDefault(flag,action){
			//if action clear all 
			if(action!==undefined && (action=== "clearall" || action=== "cancel" ||  action=== "delete")){
				if(prd_defalut_img.prevImg!== undefined && prd_defalut_img.prevImg!==""){
					self.blog.prevDefaultImage = prd_defalut_img.prevImg.toString();
					self.blog.defaultImage = "";
					prd_defalut_img.dropImg = "";
				}else{					
					var indf = _getIndex(previousData.blog_images, "1", "is_default");
					
					if(indf!== -1){
						self.blog.prevDefaultImage = indf.toString();
						self.blog.defaultImage = "";
						prd_defalut_img.prevImg = indf;	
						prd_defalut_img.dropImg = "";
					}
				}
			}			
		};

		//This function used for load product tags on user input
		self._loadProdTags = function(query){
			if(query=='') return;
			var o={'query' : query};
			return salesfactoryData.getData(prdtagurl,"GET", o)
			.then(function(r){
				return r.data;
			},function(error){
				_error();
			});
		};

		//Listen on save or save continue blog
		self.saveBlog= function($event, blogForm, btn_action){
			self.save_btn_action = btn_action;	
			//get all Blog Image valid file 
			var validDropletFiles = self.blog.file_interface.getFiles().filter(function(file){
				return (file.type !== 4)
			});
			
            //check thumb image 
            var thum_upload_status = $("#thumb_image").attr("data-image_upload_status");
            if(thum_upload_status!== undefined && thum_upload_status!="false"
                && isDataURL($("#thumb_image").attr("src"))){
               thumbFile = $("#thumb_image").attr("src");
            }

			//check edit page 
			var fileCheck = false;
			if(edit_page === true){
				if(self.blog.prevImage.length === 0 && validDropletFiles.length === 0){
					fileCheck = true;
				}else if(validDropletFiles.length){
                    if(_uploadedFileIdArray.length == 0 || _uploadedFileIdArray.length!= validDropletFiles.length){
                       fileCheck = true;
                    }
                }else{ 
					fileCheck = false;
				}
			}else{
				if(validDropletFiles.length === 0 || thumbFile === undefined || _uploadedFileIdArray.length === 0 || (_uploadedFileIdArray.length!== validDropletFiles.length))
					fileCheck = true;
				else 
					fileCheck = false;
			}
			
			//check form is valid or not(error message section)
			if(self.cmsForm.$invalid === true || fileCheck === true){
				var errorHtml = '';			
				for(var i in formFieldName){
					//console.log(self.cmsForm[formFieldName[i]]);
					//in case input box in different language
					if(formFieldName[i] === "blog_title" || formFieldName[i] === "blog_heading" || formFieldName[i] === "blog_desc" ||  formFieldName[i] === "meta_title" || formFieldName[i] === "meta_keyword" || formFieldName[i] === "meta_desc"){
						var lang1 = formFieldName[i]+"[1]",
							lang2 = formFieldName[i]+"[2]",
							lang3 = formFieldName[i]+"[3]",
							lang4 = formFieldName[i]+"[4]";
						if((self.cmsForm[lang1]!== undefined && self.cmsForm[lang1].$error!== undefined && self.cmsForm[lang1].$error.required === true) ||
							(self.cmsForm[lang2]!== undefined && self.cmsForm[lang2].$error!== undefined && self.cmsForm[lang2].$error.required === true) || 
							(self.cmsForm[lang3]!==undefined && self.cmsForm[lang3].$error!== undefined && self.cmsForm[lang3].$error.required === true) ||
							(self.cmsForm[lang4]!== undefined && self.cmsForm[lang4].$error!== undefined && self.cmsForm[lang4].$error.required === true)){
								errorHtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>'+errorMsg[i]+'</div>';
						}						
					}else if((self.cmsForm[formFieldName[i]]!==undefined && self.cmsForm[formFieldName[i]].$error!== undefined &&
						 self.cmsForm[formFieldName[i]].$error.required === true) && 
						 self.cmsForm[formFieldName[i]].$invalid === true){
						 	errorHtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>'+errorMsg[i]+'</div>';
					}
				}				
				//In case Blog Image is not upload
				if(fileCheck === true){
					errorHtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please upload file</div>';
				}
				if(thumbFile === undefined && !edit_page){
					errorHtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>Please upload thumb image</div>';
				}
				
				_messageHandler("error", errorHtml, "error");
				return;
			}
			
			//enable page loader and disable save,save_continue button
			self.blogData.loading.save_and_continue = true;
			self.blogData.loading.disableBtn = true;
			
			//send to server
			$http({
				method : "POST",
				url : form_action_url,
				headers: { 'Content-Type': undefined },
				transformRequest: function (data){
				    var formData = new FormData();
				    //append file
				    //for edit page get default file 
				    var defImg={};
				    var def='';
				    if(edit_page === true){
				    	if(prd_defalut_img.prevImg!==undefined && prd_defalut_img.prevImg!==""){
				    		var ind = parseInt(self.blog.prevDefaultImage);
				    		defImg["img_id"] = self.blog.prevImage[ind].id;
				    		defImg["is_default"] = true;
				    		defImg["img_name"]= self.blog.prevImage[ind].image;
				    		defImg["index"] = prd_defalut_img.prevImg;
                            defImg["blog_id"] = blog_id;		
				    	}else{
				    		def = parseInt(self.blog.defaultImage);
                            defImg["img_id"] = _uploadedFileIdArray[def].img_id;
                            defImg["is_default"] = true;
                            defImg["img_name"]= _uploadedFileIdArray[def].img_name;
                            defImg["index"] = def;
                            defImg["blog_id"] = null;
				    	}
				    }else{
				    	def = parseInt(self.blog.defaultImage);
                        defImg["img_id"] = _uploadedFileIdArray[def].img_id;
                        defImg["is_default"] = true;
                        defImg["index"] = def;
                        defImg["img_name"] = _uploadedFileIdArray[def].img_name;
				    }

				   
					formData.append("blog_images", angular.toJson(_uploadedFileIdArray));
					formData.append("defaultImage", angular.toJson(defImg));
					
					for(var i=1;i<=4; i++){
						if(self.blog_desc[i]!== undefined && self.blog_desc[i]){ 
							formData.append("blog_desc["+i+"]", self.blog_desc[i]);
						}else{
							formData.append("blog_desc["+i+"]", "");
						}

						if(self.blog_title[i]!==undefined && self.blog_title[i]){
							formData.append("blog_title["+i+"]", self.blog_title[i]);
						}else{
							formData.append("blog_title["+i+"]", "");
						}

                        if(self.short_description[i]!==undefined && self.short_description[i]){
                            formData.append("short_description["+i+"]", self.short_description[i]);
                        }else{
                            formData.append("short_description["+i+"]", "");
                        }

						if(self.meta_desc[i]!==undefined && self.meta_desc[i]){
							formData.append("meta_desc["+i+"]", self.meta_desc[i]);
						}else{
							formData.append("meta_desc["+i+"]", "");
						}

						if(self.meta_keyword[i]!==undefined && self.meta_keyword[i]){
							formData.append("meta_keyword["+i+"]", self.meta_keyword[i]);
						}else{
							formData.append("meta_keyword["+i+"]", "");
						}

						if(self.meta_title[i]!== undefined && self.meta_title[i]){
							formData.append("meta_title["+i+"]", self.meta_title[i]);
						}else{
							formData.append("meta_title["+i+"]", "");
						}				
					}
					//console.log("u are ");
					formData.append("action", self.save_btn_action);
					formData.append("cms_url", self.blog.cms_url);
					formData.append("status", self.blog.status);
					formData.append("features", self.blog.features);
					formData.append("comment", self.blog.comment);
					formData.append("publish", self.blog.publish);
					formData.append("category", self.blog.category);
					formData.append("tags", angular.toJson(self.blog.tags));
						
					//console.log("heru after data")
					if(blog_id!==undefined && blog_id)
						formData.append("blog_id" ,blog_id);
					if(thumbFile!==undefined && thumbFile)
						formData.append('thumb_image', thumbFile);
					//console.log("before data send");
					
					return formData; 

				},
				data : {files: validDropletFiles},
			}).then(function(resp){
				var result = resp.data;
				if((result.status!== undefined && result.action_url!==undefined) && (result.status === "success" && result.action_url)){
					_messageHandler(result.status, result.message, "Good");
					window.location.href = result.action_url;
				}else if(result.status!== undefined && result.status === "unsuccess"){
					_messageHandler("error", result.message, result.status);
				}
			},function(error){
				_error();
			}).finally(function(){
				self.blogData.loading.save_and_continue = false;
				self.blogData.loading.disableBtn = false;
			});
			//console.log(blogForm.$invalid);

		}

		//Listen on validation error in form
		self.formValidation = function(inputName){
			var form = self.cmsForm; 
			console.log("here")
          	return (form.$submitted || form[inputName].$touched) && !form[inputName].$valid;
		};
	}//end controller
	angular.module("blogApp").controller("blogAddEditCtrl",blogCtrlHandler);

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
    	var index;
    	index = destObj.findIndex(function(item){
    		if(matchType!== undefined && matchType){
    			return (item[matchType] == matchEle);
    		}else{
    			return (item == matchEle);
    		}
    	});
    	return index;
    }
  	//single file upload (thumb image)
  	/*document.getElementById("thumb_image").addEventListener("change",function(){
  		var self = this;
  		var file = self.files[0];
  		var type = file.type.split('/').pop().toLowerCase();
  		//check valid extension
  		if (_getIndex(file_upload_setting.allowed_extension, type) == -1) {
	        _messageHandler('warning','file extension not valid \n Please upload : ' +file_upload_setting.allowed_extension,'Opps..');
	        self.value = '';
		    return false;
	    }
	    //check valid size (convert in MB)
	    var fs = (file.size/1024/1024).toFixed(2);
	    if (fs > file_upload_setting.max_file_size) {
	        _messageHandler('warning','File is too Large than max allowed  : \t'+file_upload_setting.max_file_size +'MB','Opps..');
	        self.value = '';
	        return false;
	    }
  		//file is valid then store
  		thumbFile = file;
  		//console.log(thumbFile);
  		return true;
  	});*/ 

    function isDataURL(s) {
        return !!s.match(isDataURL.regex);
    };

    isDataURL.regex = /^\s*data:([a-z]+\/[a-z]+(;[a-z\-]+\=[a-z\-]+)?)?(;base64)?,[a-z0-9\!\$\&\'\,\(\)\*\+\,\;\=\-\.\_\~\:\@\/\?\%\s]*\s*$/i;

})(window.angular);