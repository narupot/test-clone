(function (factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as anonymous module.
    define(['jquery'], factory);
  } else if (typeof exports === 'object') {
    // Node / CommonJS
    factory(require('jquery'));
  } else {
    // Browser globals.
    factory(jQuery);
  }
})(function ($) {

  'use strict';

  var console = window.console || { log: function () {} };
  var croppedImage ='';
  var _URL = window.URL || window.webkitURL;

  function checkImgDimensions(file){    
    console.log("dfdf");
    return new Promise(function(resolve, reject){
      var img = new Image();
      img.onload = function(){
        var res = false;
        if(this.width<cropper_setting.width || this.height<cropper_setting.height){
           res = true;
        }else{
          res = false;
        }
        resolve(res);
      };

      img.onerror = function(){
         reject(false);
      }; 
      img.src = _URL.createObjectURL(file);
    });    
  };

  function CropAvatar($element) {    
    this.$container = $element;
    this.$avatarView = this.$container.find('.avatar-view');
    this.$avatar = this.$avatarView.find('img');
    this.$avatarModal = this.$container.find('#avatar-modal');
    this.$loading = this.$container.find('.loading');
    this.$avatarForm = this.$avatarModal.find('.avatar-form');
    this.$avatarUpload = this.$avatarForm.find('.avatar-upload');
    this.$avatarSrc = this.$avatarForm.find('.avatar-src');
    this.$avatarData = this.$avatarForm.find('.avatar-data');
    this.$avatarInput = this.$avatarForm.find('.avatar-input');
    this.$avatarSave = this.$avatarForm.find('.avatar-save');
    this.$avatarBtns = this.$avatarForm.find('.avatar-btns');

    this.$avatarWrapper = this.$avatarModal.find('.avatar-wrapper');
    this.$avatarPreview = this.$avatarModal.find('.avatar-preview');

    this.init();
  }

  CropAvatar.prototype = {
    constructor: CropAvatar,

    support: {
      fileList: !!$('<input type="file">').prop('files'),
      blobURLs: !!window.URL && URL.createObjectURL,
      formData: !!window.FormData
    },

    init: function () {
      this.support.datauri = this.support.fileList && this.support.blobURLs;

      if (!this.support.formData) {
        this.initIframe();
      }

      this.initTooltip();
      this.initModal();
      this.addListener();
    },

    addListener: function () {
      this.$avatarView.on('click', $.proxy(this.click, this));
      this.$avatarInput.on('change', $.proxy(this.change, this));
      this.$avatarForm.on('submit', $.proxy(this.submit, this));
      this.$avatarBtns.on('click', $.proxy(this.rotate, this));
      this.$avatarSave.on("click", $.proxy(this.done, this));
    },

    initTooltip: function () {
      this.$avatarView.tooltip({
        placement: 'bottom'
      });
    },

    initModal: function () {
      this.$avatarModal.modal({
        show: false
      });
    },

    initPreview: function () {     
     var url = this.$avatar.attr('src');
     this.$avatarPreview.html('<img src="' + url + '">');
    },

    initIframe: function () {
      var target = 'upload-iframe-' + (new Date()).getTime();
      var $iframe = $('<iframe>').attr({
            name: target,
            src: ''
          });
      var _this = this;

      // Ready ifrmae
      $iframe.one('load', function () {

        // respond response
        $iframe.on('load', function () {
          var data;

          try {
            data = $(this).contents().find('body').text();
          } catch (e) {
            console.log(e.message);
          }

          if (data) {
            try {
              data = $.parseJSON(data);
            } catch (e) {
              console.log(e.message);
            }

            _this.submitDone(data);
          } else {
            _this.submitFail('Image upload failed!');
          }

          _this.submitEnd();

        });
      });

      this.$iframe = $iframe;
      this.$avatarForm.attr('target', target).after($iframe.hide());
    },

    click: function () {
      this.$avatarModal.modal('show');
      this.initPreview();
     
    },

    change: function () {
      var files;
      var file;
     
      if (this.support.datauri) {
        files = this.$avatarInput.prop('files');

        if (files.length > 0) {
          file = files[0];
 console.log("here???");
          if (this.isImageFile(file)) {
            console.log("here???");
            checkImgDimensions(file)
            .then(function(resp){
              console.log("done", resp);
              //sucess
            }, function(error){
              //error
            });

            if (this.url) {
              URL.revokeObjectURL(this.url); // Revoke the old one
            }

            this.url = URL.createObjectURL(file);
            this.startCropper();
          }
        }
      } else {
        file = this.$avatarInput.val();

        if (this.isImageFile(file)) {
          this.syncUpload();
        }
      }
    },

    submit: function () {
      if (!this.$avatarSrc.val() && !this.$avatarInput.val()) {
        return false;
      }

      if (this.support.formData) {
        this.ajaxUpload();
        return false;
      }
    },

    //Listen on button click on blog page after crope done
    done : function(){       
       if (!this.$avatarSrc.val() && !this.$avatarInput.val()) {
          //in case user have already crop and again open modal
          //then check data-image_upload_status == true
          var upload_status = $('#banner_image').attr("data-image_upload_status");
          if(upload_status!== undefined && upload_status!="false"){
             this.cropDone("not_form");
             this.$avatarInput.val('');
             this.uploaded = true;
             return false;
          }else{
            $('#banner_image').attr("data-image_upload_status", false);
            $("#banner_image_input").val("");
            return false;
          }          
       }

       if (this.support.formData) {       
         $('#banner_image').attr("src", croppedImage);
         $("#banner_image_input").val(croppedImage);
         $('#banner_image').attr("data-image_upload_status", true);
         this.cropDone("not_form");
         this.$avatarInput.val('');
         this.uploaded = true;
         return false;
       }

    },

    rotate: function (e) {
      var data;

      if (this.active) {
        data = $(e.target).data();

        if (data.method) {
          this.$img.cropper(data.method, data.option);
        }
      }
    },

    isImageFile: function (file) {
      if (file.type) {
        return /^image\/\w+$/.test(file.type);
      } else {
        return /\.(jpg|jpeg|png|gif)$/.test(file);
      }
    },

    startCropper: function () {
      var crpWidth, crpHeight;
      //check cropper_setting is defined or not
      if(typeof cropper_setting!=="undefined" && typeof cropper_setting === 'object'){
          crpWidth = cropper_setting.width;
          crpHeight = cropper_setting.height;
      }else{
        crpWidth = 135;
        crpHeight = 135;
      }

      var _this = this;      
      if (this.active) {
        this.$img.cropper('replace', this.url);
      } else {       
        this.$img = $('<img src="' + this.url + '">');
        this.$avatarWrapper.empty().html(this.$img);
        this.$img.cropper({
          aspectRatio: crpWidth/crpHeight,
          minCropBoxWidth: crpWidth,
          minCropBoxHeight: crpHeight,
          preview: this.$avatarModal.find('.avatar-preview'),         
          viewMode: 1,
          dragMode: 'move',             
          //autoCropArea: 0.65,
          restore: false,
          guides: false,
          highlight: false,
          cropBoxMovable: false,
          cropBoxResizable: false,       
          crop: function (e) {
             var json = [
                  '{"x":' + e.x,
                  '"y":' + e.y,
                  '"height":' + e.height,
                  '"width":' + e.width,
                  '"rotate":' + e.rotate + '}'
             ].join();

            _this.$avatarData.val(json);
             var croppedCanvas = $(this).cropper('getCroppedCanvas', {
              x: e.x,
              y: e.y,
              rotate : e.rotate,
              width: crpWidth,
              height: crpHeight,
              fillColor :'#FFF'
            });
            croppedImage = croppedCanvas.toDataURL('image/jpeg');
          }
        });

        this.active = true;
      }

      this.$avatarModal.on('hidden.bs.modal', function () {
        _this.$avatarPreview.empty();
        _this.stopCropper();
      });
    },

    stopCropper: function () {
      if (this.active) {
        this.$img.cropper('destroy');
        this.$img.remove();
        this.active = false;
      }
    },

    ajaxUpload: function () {
      var url = this.$avatarForm.attr('action');
      var data = new FormData(this.$avatarForm[0]);
      var _this = this;
      data.append('imageData',croppedImage);
      $.ajax(url, {
        type: 'post',
        data: data,
        dataType: 'json',
        processData: false,
        contentType: false,

        beforeSend: function () {
          _this.submitStart();
        },

        success: function (data) {
          _this.submitDone(data);
          $('#banner_image').val(data.filename);        
        },

        error: function (XMLHttpRequest, textStatus, errorThrown) {
          _this.submitFail(textStatus || errorThrown);
        },

        complete: function () {
          _this.submitEnd();
        }
      });
    },

    syncUpload: function () {      
      this.$avatarSave.click();
    },

    submitStart: function () {
      this.$loading.fadeIn();
    },

    submitDone: function (data) {     

      if ($.isPlainObject(data) && data.state === 200) {
        if (data.result) {
          this.url = data.result;

          if (this.support.datauri || this.uploaded) {
            this.uploaded = false;
            this.cropDone();
          } else {
            this.uploaded = true;
            this.$avatarSrc.val(this.url);
            this.startCropper();
          }

          this.$avatarInput.val('');
        } else if (data.message) {
          this.alert(data.message);
        }
      } else {
        this.alert('Failed to response');
      }
    },

    submitFail: function (msg) {
      this.alert(msg);
    },

    submitEnd: function () {
      this.$loading.fadeOut();
    },

    cropDone: function (flag) {
      if(flag == undefined && flag!== "not_form"){
        this.$avatarForm.get(0).reset();
        this.$avatar.attr('src', this.url);
      }

      //this.$avatarForm.get(0).reset();  
      //this.$avatar.attr('src', this.url);    
      this.stopCropper();
      this.$avatarModal.modal('hide');
    },

    alert: function (msg) {
      var $alert = [
            '<div class="alert alert-danger avatar-alert alert-dismissable">',
              '<button type="button" class="close" data-dismiss="alert">&times;</button>',
              msg,
            '</div>'
          ].join('');

      this.$avatarUpload.after($alert);
    }
  };

  $(function () {
   return new CropAvatar($('#crop-avatar'));
  });
});