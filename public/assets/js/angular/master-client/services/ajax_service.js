(function() {
    "use strict";

    angular.module('jsonservice', [])
        .factory("ajaxRequest", ['$q', '$http', '$window', '$rootScope', AjaxServiceHandler])
        .factory("httpLoaderInterceptor", ['$rootScope', httpLoaderHandler])
        .service('commanFun', CommanServiceHandler)
        .service('FileUpload', ['$q', '$window', FileUploadHandler]);

    /*
     *@Description : ajaxrequest factory used for action to server
     */
    function AjaxServiceHandler($q, $http, $window, $rootScope) {
        var JsonData = {};
        //Listen on getDate for one request
        JsonData.getData = function(url, methodType, obj) {
            var methodType = methodType || 'POST';
            var deferred = $q.defer();
            if (methodType === 'POST') {
                $http({
                    method: methodType,
                    url: url,
                    data: obj
                }).then(function(data) {
                    deferred.resolve(data);
                }, function(data, status, headers, config) {
                    deferred.reject(status);
                });
                return deferred.promise;
            } else if (methodType === 'GET') {
                $http({
                    method: methodType,
                    url: url,
                    params: obj
                }).then(function(data) {
                    deferred.resolve(data);
                }, function(data, status, headers, config) {
                    deferred.reject(status);
                });
                return deferred.promise;
            } else if (methodType === 'DELETE') {
                $http({
                    method: methodType,
                    url: url,
                    params: obj
                }).then(function(data) {
                    deferred.resolve(data);
                }, function(data, status, headers, config) {
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
        JsonData.getAllData = function(urlarr, methodType, obj) {
            var promises = urlarr.map(function(item) {
                let deferred = $q.defer();
                $http({
                    method: methodType,
                    url: item,
                    params: obj
                }).then(function(data) {
                    deferred.resolve(data);
                }, function(data, status, headers, config) {
                    deferred.reject(status);
                });
                return deferred.promise;
            });
            return $q.all(promises);
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
        *@desc : check valid response 
        *@param : resp 
        *@param : checkDataLength {boolean}
        ***/

        this.checkResponse = function(resp, checkDataLength){
            var result = "no";
            if(typeof resp === "undefined" || resp.data === null || resp.xhrStatus === "error") return result;
            if(resp.data!== undefined && resp.data.status!== undefined && resp.data.status === "success"){
                if(typeof checkDataLength!=="undefined" && checkDataLength === true) {
                    if(resp.data.data!== undefined && resp.data.data.length){
                        result = "yes";
                    }else {
                        result = "no";
                    }                                  
                }else {
                    result = "yes";             
                }
                
                return result;
            }else{
                if(resp.data!== undefined && resp.data.status!== undefined){
                    return resp;
                }
                else{
                    return result; 
                }
            } 
        };

        /*******
        *@desc : check and parse valid json (return objet | [])
        *******/
        this.parseJson = function(jsonString){
            try{
                var o = JSON.parse(jsonString);
                if(o && typeof o === "object") 
                    return o;
            }catch(e){
                return false;
            }            
        };

        /***
        *Private method
        ***/
        var getError = function(keys, arr){
            let er ="";          
            angular.forEach(arr, function(item){                
                if(item[keys]){                   
                  er+= item[keys][0].en;  
                }               
            });

            return er;                         
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
                    errorHtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>' + getError(formFieldName[i], errorMesgArr) + '</div>';
                }                
            }

            return errorHtml; 
        }; 

        /*
        *@desc : This service used to handel check file validation & return error | success
        *@param : FileObj {file object}
        *@param : validFiles {array } collections all valid file
        *@param : pageType  {string -> add | edit}
        *@param : imgRequired {boolean -> true | false}
        */
        this.checkFileValidation = function(FileObj, validFiles, pageType, imgRequired){
            var r = 'success',
                vFLength = validFiles.length,
                uImgLength =  FileObj.uploadedImage.length,
                pUImgLen = FileObj.prev_img_array.length;
            //In case file have drop but not uploaded on server(normal case it will apply in both case img_requied or not)
            if(vFLength && (vFLength!=uImgLength)) r = 'error';
            //In case image is required 
            if(imgRequired){
                //In case add page 
                if((pageType === 'add' || pageType === 'create') && (vFLength === 0 || (vFLength!=uImgLength))) r = 'error';
                //In case of edit page 
                if(pageType === 'edit' && (vFLength === 0 && pUImgLen === 0)) r = 'error';
            }

            return r;
        }; 

        /*
        *@desc : This function used to show simple message in sweertalert 
        *@param : type  {string | required} 
        *@param : text  {string | required}
        *@param : title {string | optional}
        */
        this.showSweetAlert = function(type, text, title){
            try{
                title = (typeof title === "undefined") ? ((type === 'error') ? 'Opss..!' : 'Done') : title;
                swal(title, text, type);
            }catch(e){
                console.log();
            }
        };

        /*
        *@desc : This function used to show form validation error message in sweetaler
        *@param : $form {object} onject of form 
        *@param : reqFieldName {array} - all required field name 
        *@param : reqFieldErrMesg {error} all required field error message (multi language)
        */
        this.requiredError = function($form , reqFieldName, reqFieldErrMesg){
            var erH = "",
                resp = "success";

            for(var f in reqFieldName){
                var n = reqFieldName[f];
                if($form[n] && $form[n].$error && $form[n].$error.required && $form[n].$invalid){
                    errorHtml += '<div class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign text-left" aria-hidden="true"></span><span class="sr-only">Error:</span>' + reqFieldErrMesg[f] + '</div>';
                }
            };

            resp = (erH!="") ? ("error", this.showSweetAlert('error', erH, 'Opps..!')) : resp;
            return resp;
        };
    };

    /*
     *@Description : FileUpload service used for upload file using http request
     */

    function FileUploadHandler($q, $window) {
        /*
         *@Description : Listen to upload image on server
         *@param : url {string}
         *@param : method_type {string ->post | get}
         *@param : imgObj {object}
         *@param : name {string -> keys of image send to server}
         *@param : $sp ($scope of current controller {object})
         */

        this.uploadImage = function(_url, method_type, imgObj, name, $sp) {
            var deferred = $q.defer(),
                formData = new $window.FormData(),
                httpRequest = new $window.XMLHttpRequest(),
                queuedFiles = imgObj;
            //add custom property
            queuedFiles['customData'] = {
                completed: false,
                error: false,
                inprogress: false,
                percent: 0,
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
                    if (isValid(httpRequest.status, [/2.{2}/])) {
                        $sp.$evalAsync(function() {
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
                $sp.$evalAsync(function() {
                    deferred.reject(httpRequest.responseText);
                });
            }.bind(this);

            /**
             * Invoked each time there's a progress update on the files being uploaded.
             *
             * @method progress
             * @return {void}
             */
            httpRequest.upload.onprogress = function onProgress(event) {
                var requestLength = queuedFiles.file.size;
                $sp.$evalAsync(function() {
                    queuedFiles.customData.inprogress = true;

                    if (event.lengthComputable) {
                        queuedFiles.customData.percent = Math.floor((event.loaded / event.total) * 100) + "%";
                        // console.log(event.loaded, event.total);
                    }
                });
            }.bind(this);

            // Iterate all of the valid files to append them to the previously created
            // `formData` object.
            
            formData.append(name, queuedFiles.file, $window.encodeURIComponent(queuedFiles.file.name));
            //append version add if not undefined 
            if(queuedFiles.version_id!== undefined && queuedFiles.version_id)
                formData.append("version_id", $window.encodeURIComponent(queuedFiles.version_id));
            //append version number
            if(queuedFiles.version!== undefined && queuedFiles.version)
                formData.append("version", $window.encodeURIComponent(queuedFiles.version));

            //append slug_name 
            if(queuedFiles.slug_name!== undefined && queuedFiles.slug_name)
                formData.append("slug_name", $window.encodeURIComponent(queuedFiles.slug_name));

            httpRequest.send(formData);
            return deferred.promise;
        };

        /**
        *@Description : Listen to upload image on server in chunk
        **/

        //uploader function
        var uploaders = [],
            UPLOAD_URL;

        var upload = function(blobOrFile, fileType, fileName, fileSize){
            var deffer = $q.defer(),
                formData = new $window.FormData(),
                xhr = new $window.XMLHttpRequest();

            xhr.open('POST', UPLOAD_URL, true);

            /*
            *append token
            */
            (function(){
                // Setup the file size of the request.
                xhr.setRequestHeader('X_FILE_NAME', fileName);
                // xhr.setRequestHeader('Content-Type', fileType)
                xhr.setRequestHeader('X-File-Size', fileSize);
                xhr.setRequestHeader("X-CSRF-TOKEN", window.Laravel.csrfToken);
            })();

            /*
            *
            */
            xhr.upload.onprogress = function (e) {
                // if (e.lengthComputable) {
                //      meter.value = Math.round((e.loaded / e.total) * 100);
                //      meter.textContent = parseFloat(meter.value) + '%';
                //      meter.style.width = meter.textContent;
                // }
                // if (meter.textContent === '100%') progress.classList.add('success');
            }.bind(this);

            xhr.onloadend = function (e) {
                uploaders.pop();
                if (!uploaders.length) {
                 //bars.appendChild(document.createTextNode(' All Done! '));
                }
            };

            uploaders.push(xhr);
            formData.append("plugin_version_file", blobOrFile, $window.encodeURIComponent(fileName));
            xhr.send(formData);

        };

        this.chunkFileUploader = function(file, uploadUrl){ 
            var BYTES_PER_CHUNK = 1000 * 1024,
                SIZE =  file.size,
                NUM_CHUNKS = Math.max(Math.ceil(SIZE / BYTES_PER_CHUNK), 1),
                start = 0,
                end = BYTES_PER_CHUNK;
            var blob = file;
                UPLOAD_URL = uploadUrl;

            //call to uploader
            while (start < SIZE) {
                
                var size_done = start + BYTES_PER_CHUNK;
                var percent_done = Math.floor( ( size_done / file.size ) * 100 );
                console.log("percentage \t" + percent_done);
                 upload(blob.slice(start, end), file.type, file.name, file.size);
                 start = end;
                 end = start + BYTES_PER_CHUNK;
            };
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
})();