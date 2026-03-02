function validateForm(formId,rules,messages){
    var formAction = $('#'+formId).attr('action');
    $("#"+formId).validate({
        rules: rules,
        messages: messages,
        submitHandler : function(from, evt){               
            var formData = new FormData($('#'+formId)[0]);
            $('button[type="submit"]').prop('disabled',true);
            callAjaxFormRequest(formAction,'post',formData,function(response){
                $('p[class="error"]').html('');
                if(response.status=='fail'){
                    if(response.validation){
                        $.each(response.message, function(key,val){
                            $('p[id='+key+']').text(val);
                        });
                    }else{
                        alert(response.message);
                    }
                    
                    return false;
                }
                if(response.status=='update'){
                    toastr.options.positionClass = 'toast-top-right';
                    _toastrMessage('success', records_updated_successfully);
                }
                if(response && response.status === 'success')
                {
                    toastr.options.positionClass = 'toast-top-right';
                    _toastrMessage('success', records_updated_successfully);
                    setTimeout(function() {
                        window.location.href = response.url;
                    }, 1000);
                }
                if(response.status=='error'){
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: response.message,
                        confirmButtonColor: '#d33',
                    });
                    $('button[type="submit"]').prop('disabled',false);
                }
            });
            
        },
    });
}

function callAjaxFormRequest(url,type,data,callback){
    $.ajax({
        url: url,
        type:type,
        data:data,
        headers : {
            '_token' : window.Laravel.csrfToken,
            'X-CSRF-TOKEN' : window.Laravel.csrfToken,
        },
        processData: false, //prevent jQuery from converting your FormData into a string //required incase of file upload
        contentType: false, //jQuery does not add a Content-Type header for you //required incase of file upload    
        beforeSend: function(){
            showHideLoader('showLoader');
        },
        success:function(result){  
            callback(result);
        },
        complete: function () {
            showHideLoader('hideLoader');
        }
    });
}

function callAjaxRequest(url,type,data,callback){
  data['_token']=window.Laravel.csrfToken;
    $.ajax({
        url: url,
        type:type,
        data:data,
        beforeSend: function(){
            showHideLoader('showLoader');
        },
        success:function(result){  
            showHideLoader('hideLoader');
            callback(result);
        }
    }).always(function(){
        showHideLoader('hideLoader');
    });
}


function callForAjax(ajax_url, resp_id) {
    //alert(ajax_url+'==='+resp_id);return false;

    $('#'+resp_id).text('');
    
    $.ajax({

        url:ajax_url,
        method:'GET',
        data:{call_type:'ajax_data'},
        beforeSend: function(){
            showHideLoader('showLoader');
        },        
        success:function(responce)
        {
            showHideLoader('hideLoader');
            if(responce){    
                $('#'+resp_id).html(responce);
            } 
        }
    });    
}

function callAjax(url, type, data, callback){
  data['_token']= window.Laravel.csrfToken;
  $.ajax({
    url: url,
    type:type,
    headers : {'X-CSRF-TOKEN' : window.Laravel.csrfToken, '_token' : window.Laravel.csrfToken},
    data: data,
    success:function(result){  
         callback(result);
    }
  });
}


function callAjaxUpload(url,type,data,callback){
  data['_token']=window.Laravel.csrfToken;
  $.ajax({
    url: url,
    type:type,
    data:data,
    processData: false, //prevent jQuery from converting your FormData into a string //required incase of file upload
    contentType: false, //jQuery does not add a Content-Type header for you //required incase of file upload    
    success:function(result){  
         callback(result);
    }
  });
}
function createSlug(str) {
    var url_str = str.toLowerCase().trim();
    url_str = url_str.replace(/[^a-z0-9\s-]/g, ' ');   // replace invalid chars with spaces
    url_str = url_str.replace(/[\s-]+/g, '-');  // replace multiple spaces or hyphens with a single hyphen
    return url_str;
}

function updateIsdCode() {
    var country = $('#country');
    var country_id = country.val();
    if(country_id > 0) {
        var isd_code = country.find('option:selected').attr('isd_code');
        $('.isd_code').val('+'+isd_code);
    }
}
function showSweetAlertError(msg){
    swal({
        type: 'error', 
        text: msg,
        confirmButtonColor: '#d33',
        confirmButtonText: lang_ok
    });
}

// Below function is used to allow user to enter numeric value only
function isNumericKey(evt) {

    var charCode = (evt.which) ? evt.which : evt.keyCode;
    
    if (charCode!=46) {
        
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        else {
            return true;
        }
    }
}

// Below function is used to allow user to enter number only
function isNumberKey(evt) {

    var charCode = (evt.which) ? evt.which : evt.keyCode;

    if (charCode != 8 && charCode != 0 && (charCode < 48 || charCode > 57)) {

        return false;
    }
    else {
        return true;
    }    
}

//show hide loader front
function showHideLoader(strFlag) {
    
    if(strFlag === "showLoader") {
        jQuery("#showHideLoader").removeClass('d-none').fadeIn();
    } else if(strFlag === "hideLoader") {
        jQuery("#showHideLoader").addClass('d-none').fadeOut();
    }
};

//show hide loader admin
function showHideLoaderAdmin(strFlag) {
    
    if(strFlag === "showLoader") {
        $("#showHideLoader").removeClass('d-none').fadeIn();;
    } else if(strFlag === "hideLoader") {
        $("#showHideLoader").addClass('d-none').fadeOut();
    }
};
//View uploead image method Start
//Read Url
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            jQuery(input).siblings('.upload-img').show();
            jQuery(input).siblings('.upload-img').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
};

   

(function($){

    // for single upload
    jQuery(document).on('change','.file-upload',function(){

        var file = this.files[0];
        var ext = file.name.split('.').pop().toLowerCase();
        if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
            jQuery(this).val('');
            jQuery(this).siblings('.upload-img').attr('src', '');
            alert('invalid extension!');
            return false;
        }
        
        readURL(this);
    });  

    // for multiple file upload    
    jQuery(document).on('change','#upload_multiple_image',function(e){
    
        var files = e.target.files,
            filesLength = files.length;
        for (var i = 0; i < filesLength; i++) {
            var f = files[i]
            var fileReader = new FileReader();
            fileReader.onload = (function(e) {
                var file = e.target;
                $("#image_preview").append('<img class="profile-pic" src="'+e.target.result+'" title="'+file.name+'" width="200"/>');
            });
            fileReader.readAsDataURL(f);
        }
    });
})(jQuery);

try{
    /* set default value of sweetalert*/
    sweetAlert.setDefaults({
        confirmButtonText : lang_ok,
    });   
}catch(er){
    console.log;
}

jQuery(document).on('click','.btn-buyer-chat',function(){
    var data = {};
    var pid_val = $(this).data('val');
    
    callAjaxRequest(chat_token_url, 'post', data, result=>{
        
        if(result.status=='success'){
            if(pid_val){
                var redirect_url =result.url+'&product='+pid_val;
            }else{
                var redirect_url =result.url;
            }
            window.open(redirect_url, "_blank");
        }
    });
});