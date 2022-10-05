var resent_otp = 0;

$('body').on('change','#change_password',function(){
    var isChecked = $(this).is(':checked');
    if(isChecked === true) {
        $('#password_div').slideDown();
    }
    else {
        $('#password_div').slideUp();
    }
});

$('body').on('click','#update_profile',function(){
	$('.error').text('');
	var profile_frm = $('#update_profile_frm');
	var ajax_url = profile_frm.attr('action');
	var data = profile_frm.serialize();
	//console.log(ajax_url, data);
    callAjaxRequest(ajax_url, 'post', data, function(result) {
    	result = JSON.parse(result);
        if(result.status == 'success') {
            swal({
            	type: 'success',
                text: result.msg,
                confirmButtonText: lang_json.ok,
            }).then(function () {
            	location.reload();
            });
            setTimeout(function(){ location.reload(); }, 2000);
        }
        else if(result.status == 'validation_error') {
        	var error_str = '';
            $.each(result.msg, function(key,val){
                $('#error_'+key).text(val);
                error_str += '<p class="error">'+val+'</p>';
            });
            swal({
            	type: 'error',
                text: error_str,
				confirmButtonColor: '#d33',
				confirmButtonText: lang_json.ok
            });                        
        }
    });
});

$('body').on('click','#confirm_pass_btn',function(){

	$('#confirm_cur_pass').css('border-color', '');

	var ajax_url = confirm_password_url;
	var current_password = $.trim($('#confirm_cur_pass').val());

	if(current_password == '') {
		$('#confirm_cur_pass').css('border-color', 'red');
		$('#confirm_cur_pass').focus();
		return false;		
	}

	var data = {'current_password':current_password};

    callAjaxRequest(ajax_url, 'post', data, function(result) {
    	result = JSON.parse(result);
        if(result.status == 'success') {
        	$('#confirmModal').modal('hide');
        	$('#updateModal').modal('show');
        }
        else if(result.status == 'fail') {
        	$('#confirm_cur_pass').css('border-color', 'red');
        	$('#error_confirm_cur_pass').text(result.msg);
        }
    });	
});

jQuery('body').on('click','input[name="login_type"]',function(){
    
    var val = $(this).val();
    if(val == 'email'){
        $('#phone_div').hide();
        $('#email_div').show();
    }else{
        $('#phone_div').show();
        $('#email_div').hide();
    }
});

$('body').on('click','#update_login',function(){

    var ajax_url = $('#login_info_frm').attr('action');
    var data = $("#login_info_frm").serialize();
    var login_type = $('input[name="login_type"]:checked').val();
    var email = $.trim($('#login_type_email').val());
    var phone = $.trim($('#login_type_phone').val());

    $('#login_type_email').css('border-color', '');
    $('#login_type_phone').css('border-color', '');
    if(login_type == 'email' && email == ''){
        $('#login_type_email').css('border-color', 'red');
        $('#login_type_email').focus();
        return false;
    }else if(login_type == 'phone' && phone == '') {
        $('#login_type_phone').css('border-color', 'red');
        $('#login_type_phone').focus();
        return false;
    }

    callAjaxRequest(ajax_url, 'post', data, function(result) {
    	result = JSON.parse(result);
        if(result.status == 'success') {
        	$('#otp_msg_label').text(result.msg);
        	$('#updateModal').modal('hide');
        	$('#otpModal').modal('show');

        	if(resent_otp == 1) {
        		$('#error_confirm_otp').removeClass('error').addClass('green');
        		$('#error_confirm_otp').text(lang_json.otp_resent_successfully);
        	}        	
        }
        else if(result.status == 'validation_error') {
            $.each(result.msg, function(key,val){
                $('#error_'+key).text(val);
            });
        }
        else {
        	if(resent_otp == 1) {
        		$('#error_confirm_otp').text(result.msg);
        	}
        	else {
        		$('#error_login_type_fail').text(result.msg);
        	}
        }
    });	
});

jQuery('body').on('click','#resend_otp_btn',function(e){
    resent_otp = 1;
    $('#update_login').trigger('click');
});

$('body').on('click','#confirm_otp_btn',function(e){

    var otp = $.trim($('#confirm_otp').val());
    if(otp == ''){
    	$('#confirm_otp').css('border-color', 'red');
        $('#confirm_otp').focus();
        return false;
    }
    var data = {'otp':otp};

    callAjaxRequest(confirm_otp_url,'post',data,function(result){

    	result = JSON.parse(result);
        if(result.status == 'success') {
        	$('#otpModal').modal('hide');
            swal({
            	type: 'success',
                text: result.msg,
                confirmButtonText: lang_json.ok,
            }).then(function () {
            	location.reload();
            });        	        	
        }
        else {
        	$('#error_confirm_otp').text(result.msg);
        }
    });
});


$('.receive_items').click(function(e){

    var _this = $(this);
    var val_id = _this.data('val');

    swal({
        title: lang_receive_item,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: lang_yes,
        cancelButtonText: lang_no,
        closeOnConfirm: true,
        closeOnCancel: true,
    }).then(function(isConfirm){
        if (isConfirm) {

            var data = {ord_shop_id:val_id};
            callAjaxRequest(receive_item_url,'post',data,function(result){
                if(result.status=='success'){
                    swal(lang_success, result.msg, "success").then(function(){
                            location.reload();
                    });
                    /*if(result.main_status){
                        $('#order_status').text(result.main_status);
                        $('.receive_all').off('click');
                    }

                    $('.item_status_'+val_id).text(result.item_status);
                    $('#shop_status_'+val_id).text(result.item_status);
                    swal(lang_success, result.msg, "success");
                    _this.remove();*/
                }else{
                   showSweetAlertError(result.msg);
                }
            });
        }
    },function(){
        return false;
    });
});

$('body').on('click','.receive_all',function(e){

    var _this = $(this);
    var val_id = _this.data('val');
    swal({
        title: lang_receive_item,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: lang_yes,
        cancelButtonText: lang_no,
        closeOnConfirm: true,
        closeOnCancel: true,
    }).then(function(isConfirm){
        if (isConfirm) {

            var data = {formatted_id:val_id};
            callAjaxRequest(receive_ord_url,'post',data,function(result){
                if(result.status=='success'){
                    swal(lang_success, result.msg, "success").then(function(){
                            location.reload();
                    });
                    /*$('#order_status').text(result.main_status);
                    $('.receive_items').remove();
                    $('.shop_status').text(result.main_status);
                    $('.detail_status').text(result.main_status);
                    
                    _this.remove();*/
                }else{
                   showSweetAlertError(result.msg);
                }
            });
        }
    },function(){
        return false;
    });
});

$('body').on('click','.delete-favorite-shop',function(){
    var f_shop_del_url = $(this).attr('data-del_url');
    var data = {};
    swal({
        title:are_you_sure,
        type:'warning',
        text: want_to_delete_shop_from_fav_list_,
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: txt_no,
        confirmButtonText: text_yes_remove_it
        }).then((result) => {
            if(result) {
                callAjaxRequest(f_shop_del_url,'GET',data,function(result){
                    if(result.status=='error'){
                        showSweetAlertError(result.message);

                    }else if(result.status=='success'){
                        swal({
                            type: result.status, 
                            title: text_success, 
                            text: result.message,
                            confirmButtonText : text_ok_btn,
                        }).then(function(){
                            location.reload();
                        });    
                    }
                });
            }
    });
});