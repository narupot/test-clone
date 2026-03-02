var csrftoken = window.Laravel.csrfToken;
app.value('froalaConfig', {
        toolbarInline: false,
        enter: $.FroalaEditor.ENTER_BR,

        //Folder Path
        userFolderDefaultPath: window.userFolderDefaultPath,
        placeholderText: 'Edit Your Content Here!',

        // Set the image Load URL.
        imageManagerLoadURL: '/en/seller/froalaloadimages?folder='+window.userFolderDefaultPath,

        // Set the Default Path
        imageManagerDefaultURL: '/en/seller/froalaloadimages?folder='+window.userFolderDefaultPath,
        
        // // Set the image delete URL.
        // imageManagerDeleteURL: './delete_image.php?folder='+window.userFolderDefaultPath,

        // // Set the Default image delete URL.
        imageManagerDefaultDeleteURL: '/en/seller/froaladeletefolder?_token='+csrftoken+'&folder='+window.userFolderDefaultPath,
        imageUploadParam: 'image',

        imageUploadMethod: 'post',
        // Set the image upload URL.
        imageUploadURL: '/en/seller/froalaupload?folder='+window.userFolderDefaultPath, 
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
        imageManagerDeleteURL: '/en/seller/froaladeletefolder?_token='+csrftoken+'&folder='+window.userFolderDefaultPath,

        // // Set the Default Upload Path
        imageManagerDefaultUploadURL: '/en/seller/froalaupload?_token='+csrftoken+'&folder='+window.userFolderDefaultPath,

        // Set the new folder URL.
        imageManagerNewFolderURL: '/en/seller/froalaNewFolder?_token='+csrftoken+'&path='+window.userFolderDefaultPath,

        // imageManagerNewFolderParams:{
        //  _token:  csrftoken
        // },

        // Set the default new folder urlURL.
        imageManagerNewFolderDefaultURL: '/en/seller/froalaNewFolder?_token='+csrftoken+'&path='+window.userFolderDefaultPath,
        
    });


 (function(d, s, id){
 var js, fjs = d.getElementsByTagName(s)[0];
 if (d.getElementById(id)) {return;}
 js = d.createElement(s); js.id = id;
 js.src = "//connect.facebook.net/en_US/sdk.js";
 fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));