"use strict";
(function(){
	angular.module('jsonseivice',[])
		.factory("salesfactoryData", ['$q', '$http', '$window','$rootScope', AjaxServiceHandler])
		.factory("httpLoaderInterceptor", ['$rootScope', httpLoaderHandler])
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
            		console.log(queuedFiles.customData.percent) 
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