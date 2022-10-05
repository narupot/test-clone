/*
*
*
*/

(function(){
	angular.module('blogApp',["ngDroplet","froala","ui.tree","tree.structure.Dir","ngTagsInput","jsonseivice"],function($interpolateProvider){
		$interpolateProvider.startSymbol('<%');
		$interpolateProvider.endSymbol('%>');
	}).config(function(treeConfig, tagsInputConfigProvider) {
		 treeConfig.defaultCollapsed = true; // collapse nodes by default
	  	 treeConfig.appendChildOnHover = true; // append dragged nodes as children by default
		//tag input config 
	  	tagsInputConfigProvider.setDefaults('tagsInput', {
	       addOnComma : false,	      
	    });
	}).value('froalaConfig', {
		toolbarInline: false,
		//enter: $.FroalaEditor.ENTER_BR,
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

	 	//The list of allowed attributes to be used for tags.
	 	htmlAllowedAttrs : ['accept', 'accept-charset', 'accesskey', 'action', 'align', 'allowfullscreen', 'allowtransparency', 'alt', 'async', 'autocomplete', 'autofocus', 'autoplay', 'autosave', 'background', 'bgcolor', 'border', 'charset', 'cellpadding', 'cellspacing', 'checked', 'cite', 'class', 'color', 'cols', 'colspan', 'content', 'contenteditable', 'contextmenu', 'controls', 'coords', 'data', 'data-.*', 'datetime', 'default', 'defer', 'dir', 'dirname', 'disabled', 'download', 'draggable', 'dropzone', 'enctype', 'for', 'form', 'formaction', 'frameborder', 'headers', 'height', 'hidden', 'high', 'href', 'hreflang', 'http-equiv', 'icon', 'id', 'ismap', 'itemprop', 'keytype', 'kind', 'label', 'lang', 'language', 'list', 'loop', 'low', 'max', 'maxlength', 'media', 'method', 'min', 'mozallowfullscreen', 'multiple', 'muted', 'name', 'novalidate', 'open', 'optimum', 'pattern', 'ping', 'placeholder', 'playsinline', 'poster', 'preload', 'pubdate', 'radiogroup', 'readonly', 'rel', 'required', 'reversed', 'rows', 'rowspan', 'sandbox', 'scope', 'scoped', 'scrolling', 'seamless', 'selected', 'shape', 'size', 'sizes', 'span', 'src', 'srcdoc', 'srclang', 'srcset', 'start', 'step', 'summary', 'spellcheck', 'style', 'tabindex', 'target', 'title', 'type', 'translate', 'usemap', 'value', 'valign', 'webkitallowfullscreen', 'width', 'wrap','sample'],

	});
	
})(window.angular);
