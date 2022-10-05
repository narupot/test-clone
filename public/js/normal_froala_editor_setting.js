var editor;

function froalaEditorApplyModel() {
    editor = new FroalaEditor(".froala-editor-apply", {

        key: 'bMA6aA6A4D3F3D2D1xROKLJKYHROLDXDRH1e1YYGRe1Bg1G3I3A2C6D6A3C3E4B2E2==',

        enter: FroalaEditor.ENTER_BR,
        toolbarSticky: true,

        tabSpaces: 4,
        //zIndex: 701,

        htmlAllowedEmptyTags: ['i', 'span'],
        htmlUntouched: true,

        // Require Code mirror JS
        codeMirrorOptions: {
            indentWithTabs: true,
            lineNumbers: true,
            tabSize: 4,
            lineWrapping: true,
            mode: 'text/html',
            tabMode: 'indent'
        },

        imageStyles: {
            'fr-rounded': 'Rounded',
            'img-fluid': 'Responsive'
        },

        lineHeights: {
            Default: '',
            Single: '1',
            '1.15': '1.15',
            '1.5': '1.5',
            Double: '2'
        },

        //enter: FroalaEditor.ENTER_BR,
        //enter: $.FroalaEditor.ENTER_BR,
        //iframe: true,
        tabSpaces: 4,
        htmlAllowedEmptyTags: ['li', 'ul', 'i', 'span', 'textarea','iframe'],
        htmlUntouched: true,
        refreshAfterCallback: true,

        /*toolbarButtons: {
          'moreText': {
            'buttons': ['bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', 'fontFamily', 'fontSize', 'textColor', 'backgroundColor', 'inlineClass', 'inlineStyle', 'clearFormatting'],
            'buttonsVisible': 0,
          },
          'moreParagraph': {
            'buttons': ['alignLeft', 'alignCenter', 'formatOLSimple', 'alignRight', 'alignJustify', 'formatOL', 'formatUL', 'paragraphFormat', 'paragraphStyle', 'lineHeight', 'outdent', 'indent', 'quote'],
            'buttonsVisible': 0,
          },
          'moreRich': {
            'buttons': ['insertLink', 'insertImage', 'insertVideo', 'insertTable', 'emoticons', 'fontAwesome', 'specialCharacters', 'embedly', 'insertHR'],
            'buttonsVisible': 0,
          },
          'moreMisc': {
            'buttons': ['undo', 'redo', 'fullscreen', 'print', 'getPDF', 'spellChecker', 'selectAll', 'html', 'help'],
            'align': 'right',
            'buttonsVisible': 2
          }
        },*/
        //toolbarButtons: ['fullscreen', 'bold', 'italic', 'underline', 'strikeThrough', 'subscript', 'superscript', '|', 'fontFamily', 'fontSize', 'lineHeight', 'color', 'inlineClass', 'inlineStyle', 'paragraphStyle', '|', 'paragraphFormat', 'align', 'formatOL', 'formatUL', 'outdent', 'indent', 'quote', '-', 'insertLink', 'insertImage', 'insertVideo', 'embedly', 'insertTable', '|', 'emoticons', 'fontAwesome', 'specialCharacters', 'insertHR', 'selectAll', 'clearFormatting', '|', 'print', 'getPDF', 'spellChecker', 'help', 'html', '|', 'undo', 'redo'],
        videoInsertButtons: ['videoBack', '|', 'videoByURL', 'videoEmbed', 'videoUpload'],
        //Folder Path
        userFolderDefaultPath: window.userFolderDefaultPath,

        // Set the image Load URL.
        imageManagerLoadURL: froalaloadimages_url + '?_token=' + csrftoken + '&folder=' + window.userFolderDefaultPath,

        // Set the Default Path
        imageManagerDefaultURL: froalaloadimages_url + '?_token=' + csrftoken + '&folder=' + window.userFolderDefaultPath,

        // // Set the image delete URL.
        // imageManagerDeleteURL: './delete_image.php?folder='+window.userFolderDefaultPath,

        // // Set the Default image delete URL.
        imageManagerDefaultDeleteURL: froaladeletefolder_url + '?_token=' + csrftoken + '&folder=' + window.userFolderDefaultPath,
        imageUploadParam: 'image',
        imageAllowedTypes: ['jpeg', 'jpg', 'png'],
        imageUploadMethod: 'post',
        // Set the image upload URL.
        imageUploadURL: froalaupload_url + '?folder=' + window.userFolderDefaultPath,
        imageUploadParams: {
            froala: 'true',
            location: 'images',
            // This allows us to distinguish between Froala or a regular file upload.
            _token: csrftoken
            // This passes the laravel token with the ajax request.
        },
        imageManagerDeleteParams: {
            _token: csrftoken

        },

        // Set the image delete URL.
        imageManagerDeleteURL: froaladeletefolder_url + '?_token=' + csrftoken + '&folder=' + window.userFolderDefaultPath,

        // // Set the Default Upload Path
        imageManagerDefaultUploadURL: froalaupload_url + '?_token=' + csrftoken + '&folder=' + window.userFolderDefaultPath,

        // Set the new folder URL.
        imageManagerNewFolderURL: froalaNewFolder_url + '?_token=' + csrftoken + '&path=' + window.userFolderDefaultPath,

        // imageManagerNewFolderParams:{
        //  _token:  csrftoken
        // },

        imageDefaultWidth: 0,


        // Set the default new folder urlURL.
        imageManagerNewFolderDefaultURL: froalaNewFolder_url + '?_token=' + csrftoken + '&path=' + window.userFolderDefaultPath,

        // file upload  ////////////////
        // Set the file upload parameter.
        fileUploadParam: 'file',

        // Set the file upload URL.
        fileUploadURL: froalaupload_url + '?folder=' + window.userFolderDefaultPath,

        // Additional upload params.
        fileUploadParams: {
            location: 'files',
            // This allows us to distinguish between Froala or a regular file upload.
            _token: csrftoken
            // This passes the laravel token with the ajax request.
        },

        // Set request type.
        fileUploadMethod: 'POST',

        // Set max file size to 20MB.
        //fileMaxSize: 20 * 1024 * 1024,

        // Allow to upload any file.
        // fileAllowedTypes: ['*'],
        //The list of allowed attributes to be used for tags.
        htmlAllowedAttrs: ['accept', 'accept-charset', 'accesskey', 'action', 'align', 'allowfullscreen', 'allowtransparency', 'alt', 'async', 'autocomplete', 'autofocus', 'autoplay', 'autosave', 'background', 'bgcolor', 'border', 'charset', 'cellpadding', 'cellspacing', 'checked', 'cite', 'class', 'color', 'cols', 'colspan', 'content', 'contenteditable', 'contextmenu', 'controls', 'coords', 'data', 'data-.*', 'datetime', 'default', 'defer', 'dir', 'dirname', 'disabled', 'download', 'draggable', 'dropzone', 'enctype', 'for', 'form', 'formaction', 'frameborder', 'headers', 'height', 'hidden', 'high', 'href', 'hreflang', 'http-equiv', 'icon', 'id', 'ismap', 'itemprop', 'keytype', 'kind', 'label', 'lang', 'language', 'list', 'loop', 'low', 'max', 'maxlength', 'media', 'method', 'min', 'mozallowfullscreen', 'multiple', 'muted', 'name', 'novalidate', 'open', 'optimum', 'pattern', 'ping', 'placeholder', 'playsinline', 'poster', 'preload', 'pubdate', 'radiogroup', 'readonly', 'rel', 'required', 'reversed', 'rows', 'rowspan', 'sandbox', 'scope', 'scoped', 'scrolling', 'seamless', 'selected', 'shape', 'size', 'sizes', 'span', 'src', 'srcdoc', 'srclang', 'srcset', 'start', 'step', 'summary', 'spellcheck', 'style', 'tabindex', 'target', 'title', 'type', 'translate', 'usemap', 'value', 'valign', 'webkitallowfullscreen', 'width', 'wrap', 'sample', 'lineHeights'],

    });
};
froalaEditorApplyModel();

$(document).ready(function(e) {

    FroalaEditor.DefineIcon('alert', {
        NAME: 'info',
        SVG_KEY: 'help'
    });
    FroalaEditor.RegisterCommand('alert', {
        title: 'Hello',
        focus: false,
        undo: false,
        refreshAfterCallback: false,
        callback: function() {
            alert('Hello!');
        }
    });

    FroalaEditor.DefineIcon('email_variable', {
        NAME: 'cog',
        SVG_KEY: 'cogs'
    });
    FroalaEditor.RegisterCommand('email_variable', {
        title: 'Advanced options',
        type: 'dropdown',
        focus: false,
        undo: false,
        refreshAfterCallback: true,
        options: {
            '[v1]': 'Option 1',
            '[v2]': 'Option 2'
        },
        callback: function(cmd, val) {
            console.log(val);
            this.html.insert(val);
        },
        // Callback on refresh.
        refresh: function($btn) {
            console.log('do refresh');
        },
        // Callback on dropdown show.
        refreshOnShow: function($btn, $dropdown) {
            console.log('do refresh when show');
        }
    });

});