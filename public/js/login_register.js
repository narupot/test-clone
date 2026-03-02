
$('.alphabetsOnly').change(function (e) {
    var val = $(this).val();
    var newval = val.replace(/\d+/g, '');
    $(this).val(newval);
});


jQuery('input[name="loginuse"]').click(function(e){
    var loginval = jQuery(this).val();
    showloginuse(loginval);
});

if ($('input[name="loginuse"]').length > 0){
    var loginuseval = jQuery('input[name="loginuse"]:checked').val();
    showloginuse(loginuseval);
}

function showloginuse(loginval){
    if(loginval=='email'){
        jQuery('#emaildiv').show();
        jQuery('#ph_numberdiv').hide();
    }else{
        jQuery('#ph_numberdiv').show();
        jQuery('#emaildiv').hide();
    }
}

jQuery('body').on('click','input[name="find_by_use"]',function(e){
    
    var val = jQuery(this).val();
    if(val == 'email'){
        jQuery('#find_by_ph_no').hide();
        jQuery('#find_by_email').show();
    }else{
        jQuery('#find_by_ph_no').show();
        jQuery('#find_by_email').hide();
    }
});

var register_user_by = '';
var register_user_by_val = '';
var register_user_id = (typeof register_user_id!="undefined")? register_user_id : 0;
var register_by = (typeof register_by!="undefined")? register_by : '';
// historyManagement('set', 'http://192.168.1.250:8014/en/register');
/***user submit buyer register form***/
$('body').on('click',"#register",function(evt){ 

    evt.preventDefault();
    var formAction = $(this).closest('form').attr('action');
    var formId = $(this).closest('form').attr('id');
    var formMethod = $(this).closest('form').attr('method');
    var _this = $(this);
    _this.prop('disabled',true);
    var form_data = new FormData($("#"+formId)[0]);
    form_data.append('register_by',register_by);
    $('.error').text('');

    var loginuse = jQuery('#'+formId+' input[name="loginuse"]:checked').val();

    callAjaxFormRequest(formAction,formMethod,form_data,function(result){
        var response = jQuery.parseJSON(result); 

            if(response.success==false){
                if(response.type=='validation'){
                    $.each(response.message, function(key,val){

                        $('#'+formId+' p[id='+key+']').text(val);
                      
                    })
                }else{
                    console.log(response);
                    console.log(response.message);
                    showSweetAlertError(response.message);
                }
                
                _this.prop('disabled',false);
                return false;

            }else{
                if(response.success==true){
                    register_user_id = response.user_id;
                    $('.content-wrap').html(response.blade);
                    
                    historyManagement('set', response.url);
                    // _this.prop('disabled',false);
                    // window.location.href=response.url;
                    return false;
                }        
            }
    });

});

/****user submit new otp request*****/
jQuery('body').on('click','#btn_otp_request',function(e){
    var data = {id:register_user_id,use_by:register_user_by};

    callAjaxRequest(request_otp_url,'post',data,function(result){
        if(result.status=='success'){
            swal(lang_success);
        }else{
            swal({
                    title: lang_oops, 
                    type: "warning", 
                    html : '<div class="alert alert-danger">'+result.msg+'</div>',
                });
        }
    });
});

/***user submit otp******/
jQuery('body').on('click','#btn_otp_confirm',function(e){

    var otp = $.trim(jQuery('#confirm_otp').val());
    var type = jQuery('#request_from').val();
    if(otp == '' || otp==undefined){
        jQuery('#confirm_otp').focus();
        return false;
    }
    var data = {id:register_user_id,otp:otp,use_by:register_user_by};
    //console.log(type);
    callAjaxRequest(confirm_otp_url,'post',data,function(result){
        if(result.status=='success'){
            
            if(type == 'popup'){
                swal(lang_success, '', "success");
                $('#otpModal').modal('hide');
                $('#resetPwdModal').modal('show');
            }else{
                if(register_by == 'seller'){
                    window.location.href=result.url;
                }else{
                    swal(lang_success, txt_verify_success, "success");
                    jQuery('#otp_form_div').hide();
                    jQuery('#thanks_form_div').show();/***if register then show thanks page***/
                    jQuery('#pwd_form_div').show();/**if forget password***/
                }
                
            }
            
        }else{
            swal({
                    title: lang_oops, 
                    type: "warning", 
                    html : '<div class="alert alert-danger">'+result.msg+'</div>',
                });
        }
    });
});

/***user click for resend email verification link*****/
jQuery('body').on('click','#btn_resend',function(e){
    
    jQuery('#register_form_div').hide();
    jQuery('#thanks_form_div').hide();
    jQuery('#email_form_div').show();
});


$('#confirmation_mail').click(function(e){ 

  e.stopImmediatePropagation();
  resendConfirmMail($(this));
});

/***user submit resend email verification link*****/
$('body').on('click',"#btn_resend_mail",function(e){ 

  e.stopImmediatePropagation();
  resendConfirmMail($(this));
});

/****when user press enter button for login*****/
$(document).on('keypress', '#pageloginForm, #popuploginForm', function(evt){
  var code = (evt.keyCode ? evt.keyCode : evt.which);
  if(code == 13) $('.btn_login').trigger('click');
});

/***user submit login form******/
$('body').on('click',".btn_login",function(){
    var formId = $(this).closest('form').attr('id');
    var login_url = $('#'+formId).attr('action');
    if($('#'+formId+' #login_email_phone').val() == ''){
        $('#'+formId+' #login_email_phone').css('border-color', 'red');
        $('#'+formId+' #login_email_phone').focus();
        return false;
    }else{
        $('#'+formId+' #login_email_phone').css('border-color', '');
    }
    if($('#'+formId+' #login_password').val() == ''){
        $('#'+formId+' #login_password').css('border-color', 'red');
        $('#'+formId+' #login_password').focus();
        return false;
    }else{
        $('#'+formId+' #login_password').css('border-color', '');
    }
    var form_data = $("#"+formId).serialize();

    $.ajax({
        url: login_url,
        method: 'post',
        data: form_data,
        success: function(result){

            if(result.status == 'fail'){
                $('#'+formId+' #login-error').html('<span class="error">'+result.mesg+'</span>');
            }

            if(result.status=='success'){                 
                //in case login mode from shop credit pay 
                if(typeof login_status!="undefined" && login_status == 'no'){
                    login_status = 'yes';
                    $('#loginModal').modal('hide'); 
                    return;
                }
                $('#loginModal').modal('hide'); 
                var source = $('#btn-type').val();
                if(source=='addwishlist'){
                    //$sc = angular.element(document.getElementById('loginProduct')).scope().$parent.rvCtrl;
                    //$sc.addIntoWishlist(e, prd_id, index);
                }
                else if(result.url) {
                    window.location.href=result.url;
                }else{
                    location.reload(true);
                }
            }
            if(result.status=='resend_verify_email'){
                $('#login-error').html('<span class="error">'+result.mesg+'</span>');
                $('#verify_div').html('<label class="align-top"><a href="'+result.url+'" class="btn btn-sm px-2">'+txt_verify+'</a></label>');

            }  

        },
        error: function (error) {
            $('#login-error').html(error.responseJSON.errors.email[0]);
            var timeinsec = $('span#seconds_decrease').html(); 
            setInterval(function(){ 
                timeinsec = Number(timeinsec)-1; 
                if(timeinsec > 0){ 
                    $('span#seconds_decrease').html(timeinsec); 
                }else{ 
                    $('#login-error').hide(); 
                } 
            }, 1000);
        }
    });  
});

/***user click forget button then forget form show******/
$('body').on('click',"#link_forget_password",function(){ 

    $('#login_form_div').hide();
    $('#forget_form_div').show();
});


/***submit forget form ********/
$('body').on('click',"#btn_forget",function(e){ 
    e.stopImmediatePropagation();

    var formAction = $(this).closest('form').attr('action');
    var formId = $(this).closest('form').attr('id');
    var formMethod = $(this).closest('form').attr('method');
    var form_data = $("#"+formId).serialize();

    var loginuseval = jQuery('input[name="find_by_use"]:checked').val();
    var type = jQuery('#'+formId+' input[name="post_from"]').val();

    if(loginuseval == '' || loginuseval == undefined){
        alert('error');
    }else if(loginuseval == 'email'){
        if($('#'+formId+' #emailForget').val() == ''){
            $('#'+formId+' #emailForget').css('border-color', 'red');
            $('#'+formId+' #emailForget').focus();
            return false;
        }else{
            $('#'+formId+' #emailForget').css('border-color', '');
        }
    }else{
        if($('#'+formId+' #phoneForget').val() == ''){
            $('#'+formId+' #phoneForget').css('border-color', 'red');
            $('#'+formId+' #phoneForget').focus();
            return false;
        }else{
            $('#'+formId+' #phoneForget').css('border-color', '');
        }
    }
    
    $.ajax({
        url: formAction,
        type:formMethod,
        data:form_data,
        beforeSend: function(){
            showHideLoader('showLoader');
        },
        success:function(result){  
            if(result.status=='success'){
                register_user_by = loginuseval;
                register_user_by_val = $('#'+formId+' #phoneForget').val();
                register_user_id = result.user_id;
                //console.log(register_user_id);
                if(type=='page'){
                    jQuery('#forget_form_div').hide();
                    jQuery('#otp_form_div').show();
                }else{
                    jQuery('#forgotModal').modal('hide');
                    jQuery('#otpModal').modal('show');
                }
                $('#otp_msg').text(result.msg);
            }else{
                swal({
                    title: lang_oops, 
                    type: "warning", 
                    html : '<div class="alert alert-danger">'+result.msg+'</div>',
                });
            }
            /*if(loginuseval == 'email'){
                if (result == '1') {
                    jQuery('#'+formId+' #btn_forget').prop("disabled", true);
                    swal(lang_success, 'A password link has been sent to your email.', "success").then(function(){
                        location.reload();
                    });
                } else {
                    swal({
                        title: lang_oops, 
                        type: "warning", 
                        html : '<div class="alert alert-danger">'+result.msg+'</div>',
                    });
                }
            }else{
                if(result.status=='success'){
                    register_user_by = loginuseval;
                    register_user_by_val = $('#'+formId+' #phoneForget').val();
                    register_user_id = result.user_id;
                    console.log(register_user_id);
                    if(type=='page'){
                        jQuery('#forget_form_div').hide();
                        jQuery('#otp_form_div').show();
                    }else{
                        jQuery('#forgotModal').modal('hide');
                        jQuery('#otpModal').modal('show');
                    }
                    
                }else{
                    swal({
                        title: lang_oops, 
                        type: "warning", 
                        html : '<div class="alert alert-danger">'+result.msg+'</div>',
                    });
                }
                
            }*/
            
        }
    })
      .always(function(){
        showHideLoader('hideLoader');
    });
});

/****user submit reset password form******/
$('body').on('click','#btn_smt_pws',function(e){
    e.stopImmediatePropagation();

    var formAction = $(this).closest('form').attr('action');
    var formId = $(this).closest('form').attr('id');
    var formMethod = $(this).closest('form').attr('method');
    var form_data = $("#"+formId).serialize() + "&user_id=" + register_user_id;

    callAjaxRequest(formAction,formMethod,form_data,function(result){
        
        if(result.status=='success'){
            swal(lang_success, result.msg, "success").then(function(){
                location.reload();
            });
            
        }else{
            swal({
                    title: lang_oops, 
                    type: "warning", 
                    html : '<div class="alert alert-danger">'+result.msg+'</div>',
                });
        }
    });

});

function resendConfirmMail(_this){
    
    var txt_resend_email = $.trim(jQuery('#txt_resend_email').val());

    if(txt_resend_email == '' || txt_resend_email==undefined){
        $('#txt_resend_email').focus();
        return false;
    }

    var data = {'email':txt_resend_email};

    callAjaxRequest(resend_email_url,'post',data,function(result){
        if(result.status=='success'){
            swal(lang_success);
        }else{
            swal({
                    title: lang_oops, 
                    type: "warning", 
                    html : '<div class="alert alert-danger">'+result.msg+'</div>',
                });
        }
    });
}


/*
*@desc : browser history management
*@param : flag {string} -> set | delete
*@param : stateUrl {url}
*/

function historyManagement(flag, stateUrl){
    
    if(flag === 'set'){
        window.history.pushState({
            'page_name' : 'registration_confirm',
            'page_id' : 'sg_otp_validate',
        }, '', stateUrl);
    }
};

window.addEventListener('popstate', evt=>{
    console.log(evt.state);
}, false);