	var csrftoken = angular.element('#csrftoken')[0].content;
	
	// add the module with global defaults for froala
	var myApp = angular.module('myApp', ['froala']).
	value('froalaConfig', {
		toolbarInline: false,
		enter: $.FroalaEditor.ENTER_BR,

		//Folder Path
		userFolderDefaultPath: window.userFolderDefaultPath,

		placeholderText: 'Edit Your Content Here!',

		// Set the image Load URL.
		imageManagerLoadURL: froalaloadimages_url+'?folder='+window.userFolderDefaultPath,

		// Set the Default Path
		imageManagerDefaultURL: froalaloadimages_url+'?folder='+window.userFolderDefaultPath,
		
		// // Set the image delete URL.
		// imageManagerDeleteURL: './delete_image.php?folder='+window.userFolderDefaultPath,

		// // Set the Default image delete URL.
		imageManagerDefaultDeleteURL: froaladeletefolder_url+'?_token='+csrftoken+'&folder='+window.userFolderDefaultPath,
		imageUploadParam: 'image',

		imageUploadMethod: 'post',
		// Set the image upload URL.
	    imageUploadURL: froalaupload_url+'?folder='+window.userFolderDefaultPath, 
	    imageUploadParams: {
	    	location: 'images', 
		    // This allows us to distinguish between Froala or a regular file upload.
		    _token:  csrftoken
		    // This passes the laravel token with the ajax request.
		},
		imageManagerDeleteParams :{
			_token:  csrftoken

		},
		
		// Set the image delete URL.
		imageManagerDeleteURL: froaladeletefolder_url+'?_token='+csrftoken+'&folder='+window.userFolderDefaultPath,

		// // Set the Default Upload Path
		imageManagerDefaultUploadURL: froalaupload_url+'?folder='+window.userFolderDefaultPath,

		// Set the new folder URL.
	 	imageManagerNewFolderURL: froalaNewFolder_url+'?_token='+csrftoken+'&path='+window.userFolderDefaultPath,

	 	// imageManagerNewFolderParams:{
	 	// 	_token:  csrftoken
	 	// },

		// Set the default new folder urlURL.
	 	imageManagerNewFolderDefaultURL: froalaNewFolder_url+'?_token='+csrftoken+'&path='+window.userFolderDefaultPath,
	 	
	})
	// create the controller and inject Angular's $scope
	myApp.controller('mainController', function($scope) {

		$scope.titleOptions = {
			placeholderText: 'Add a Title',
			charCounterCount: false,
			toolbarInline: true,
			events: {
				'froalaEditor.initialized': function() {
					console.log('initialized');
				}
			}
		};

		$scope.initialize = function(initControls) {
			$scope.initControls = initControls;
			$scope.deleteAll = function() {
				initControls.getEditor()('html.set', '');
			};
		};

		$scope.myTitle = '<span style="font-family: Verdana,Geneva,sans-serif; font-size: 30px;">My Document\'s Title</span><span style="font-size: 18px;"></span></span>';
		$scope.sample2Text = '';
		$scope.sample3Text = '';

		$scope.imgModel = {src: 'image.jpg'};

		$scope.buttonModel = {innerHTML: 'Click Me'};

		$scope.inputModel = {placeholder: 'I am an input!'};
		$scope.inputOptions = {
			angularIgnoreAttrs: ['class', 'ng-model', 'id', 'froala']
		}

		$scope.initializeLink = function(linkInitControls) {
			$scope.linkInitControls = linkInitControls;
		};
		$scope.linkModel = {href: 'https://www.froala.com/wysiwyg-editor'}

	});
	myApp.factory("FooService", function($http, CSRF_TOKEN) {
	    console.log(CSRF_TOKEN);
	});