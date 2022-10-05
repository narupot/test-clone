if($("#custom_time_from").length && $("#custom_time_to").length){
    flatpickr("#custom_time_from, #custom_time_to", {
        enableTime: true,
        noCalendar: true,
        time_24hr:true
        //dateFormat: "h:i K",
        //dateFormat: "H:i",
    });
}

/*****seller shop info *********/
/*****check store name*****/
jQuery('input[name="store_names"]').change(function(e){

    var _this = jQuery(this);
    var store_name = $.trim(_this.val());
    if(store_name == '' || store_name == undefined){
        _this.focus();
        return false;
    }
    var data = {store_name:store_name};
    callAjaxRequest(url_checkStoreName,'post',data,function(result){
        if(result.status=='success'){
            jQuery('#e_store_name').html('');
        }else{
            swal({
                    title: lang_oops, 
                    type: "warning", 
                    html : '<div class="alert alert-danger">'+result.msg+'</div>',
                    confirmButtonText : text_ok_btn
                });
            jQuery('#e_store_name').html(result.msg);
        }
    });
});

/*****check store url*****/
jQuery('input[name="store_urls"]').change(function(e){

    var _this = jQuery(this);
    var store_url = $.trim(_this.val());
    if(store_url == '' || store_url == undefined){
        _this.focus();
        return false;
    }
    var data = {store_url:store_url,user_id:seller_user_id};
    callAjaxRequest(url_checkStoreUrl,'post',data,function(result){
        if(result.status=='success'){
            jQuery('#e_store_url').html('');
        }else{
            swal({
                    title: lang_oops, 
                    type: "warning", 
                    html : '<div class="alert alert-danger">'+result.msg+'</div>',
                    confirmButtonText : text_ok_btn
                });
            jQuery('#e_store_url').html(result.msg);
        }
    });
});

/***submit shop info data******/
jQuery('#btn_shop_info').click(function(evt){
    evt.preventDefault();
    var formAction = $(this).closest('form').attr('action');
    var formId = $(this).closest('form').attr('id');
    var formMethod = $(this).closest('form').attr('method');
    var _this = $(formId);

    var form_data = new FormData($("#"+formId)[0]);

    $('.error').text('');
    _this.prop('disabled',false);
    callAjaxFormRequest(formAction,formMethod,form_data,function(result){

            if(result.status=='fail'){
                if(result.error == 'validation'){
                    $.each(result.msg, function(key,val){

                        $('#'+formId+' p[id=e_'+key+']').text(val);
                      
                    });
                }else{
                    showSweetAlertError(result.msg);
                }   
                _this.prop('disabled',false);
                return false;

            }else if(result.status=='success'){
                swal(lang_success, result.msg, "success").then(function(){
                        location.reload();
                });
            }
    });
});


if (window.File && window.FileList && window.FileReader) {

    $(document).on("change",'.location_image', function(e) {
        showPreviewImg(e,$(this),'map');
    });

    $(document).on("change",'.shop_image', function(e) {
        showPreviewImg(e,$(this),'shop');
    });
}

function showPreviewImg(e,_this,type){
    var f_key = "file_"+Math.random().toString(36).substr(2, 9);
    
    var files = e.target.files,
    filesLength = files.length;
    _this.attr('id',f_key)
    _this.hide();
    for (var i = 0; i < filesLength; i++) {
        var f = files[i]
        var fileReader = new FileReader();
        fileReader.onload = (function(e) {
        var file = e.target;
        if(type == 'shop'){
            $("#shop-upload-img").append('<li><img src="'+e.target.result+'" height="50" width="50"><br><span class="removeShopImg" data-fileid="'+f_key+'"><i class="fas fa-times"></i></span></li>');

            $('#shop_img_span').append('<input type="file" class="shop_image" name="shop_image[]"  accept="image/*" />');
        }else{
            $("#map-upload-img").append('<li><img src="'+e.target.result+'" height="50" width="50"><br><span class="removeMap" data-fileid="'+f_key+'"><i class="fas fa-times"></i></span></li>');

            $('#map_img').append('<input type="file" class="location_image" name="location_image[]"  accept="image/*" />');  
        }
        
          
        });
        fileReader.readAsDataURL(f);
    }
}

$(document).on('click',".removeMap, .removeShopImg",function(){
    var id = $(this).data('fileid');
    $('#'+id).remove();
    $(this).parent("li").remove();
});

/**shop open and bargain change******/
$('input[name="shop_status"]').click(function(e){
    e.stopImmediatePropagation();
    manaShopHandler($(this), 'shop_status');
    return false;
});

$('input[name="bargaining"]').click(function(e){
    e.stopImmediatePropagation();
    manaShopHandler($(this), 'bargaining');
    return false;
});

function manaShopHandler($that, type){
    // sweetAlert['confirmButtonText'] = 'lang_yes';
    swal({
        title: are_you_sure,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: lang_yes,
        cancelButtonText: txt_no,
        closeOnConfirm: true,
        closeOnCancel: true,
    }).then(function(isConfirm){
        if (isConfirm) {
            var shop_status = ($that.is(':checked')) ? 1 : 0;
            callAjaxRequest(updateStatusUrl, 'POST',{'type': type,'shop_status' : shop_status}, function(res){
                if(res.status && res.status === 'success'){
                    var st = (parseInt(res.value)) ? true :false;
                    $that.prop('checked', st);
                    swal(lang_success, res.msg, 'success');
                    return;
                }
                swal('Opps..!', res.msg, 'error');
            });
        }
    }, function(err){
       console.log;
    });
};

/***delete shop and map image from database**/

$('.deleteShopImg').click(function(e){
    var _this = $(this);
    var type = _this.data('type');
    var val  = _this.data('val');
    swal({
        title: txt_delete_confirm,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: yes_delete_it,
        cancelButtonText: txt_no,
        closeOnConfirm: true,
        closeOnCancel: true,
    }).then(function(isConfirm){
        if (isConfirm) {

            var data = {type:type,val:val};
            callAjaxRequest(url_deleteshopimage,'post',data,function(result){
                if(result.status=='success'){
                    _this.parent("li").remove();
                }else{
                   showSweetAlertError(result.msg);
                }
            });
        }
    },function(){
        return false;
    });
});

$(document).on('click',".shop-wish",function(){
    var shop_url = $(this).attr('id');
    var data = {shop_url:shop_url};
    callAjaxRequest(url_manageFavoriteShop,'post',data,function(result){
        if(result.status=='success'){
            swal({
                type: result.status, 
                text: result.msg, 
                confirmButtonText : text_ok_btn,
            }).then(function(){
                location.reload();
            });
        }else{
            swal({
                type: result.status, 
                text: result.msg, 
                confirmButtonText : text_ok_btn
            }).then(function(){
                if(result.redirect_url)
                    window.location.href = result.redirect_url;
            });
        }
    });
});

$(document).on('click',".credit_request",function(){
    var shop_id = $(this).attr('id');
    var data = {'shop_id':shop_id};
    callAjaxRequest(checkLogin_url,'post',data,function(result){
                if(result.status=='loggedin'){
                    swal({
                        title: result.title,
                        text: result.message,
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: yes_send_it,
                        cancelButtonText: txt_no,
                    }).then((response) => {
                        if (response) {
                            var data = {'shop_id':shop_id};
                            callAjaxRequest(credit_request_url,'post',data,function(resp){
                                if(resp.status=='success'){
                                    swal({
                                        type: resp.status, 
                                        text: resp.message, 
                                        confirmButtonText : text_ok_btn
                                    }).then(function(){
                                        location.reload();
                                    });
                                }else{
                                    swal({
                                        type: resp.status, 
                                        text: resp.message, 
                                        confirmButtonText : text_ok_btn
                                    }).then(function(){
                                        window.location.href = resp.redirect_url;
                                    });
                                }
                            }); 
                        }
                    })
                }else if(result.status=='not-loggedin'){
                    $('#loginModal').modal('show');
                    window['login_status'] = 'no';
                    $(document).on("hide.bs.modal",'#loginModal', function(e){
                        if(typeof login_status!="undefined" && login_status == 'yes'){
                            swal({
                                title: result.title,
                                text: not_login_msg,
                                type: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: yes_send_it
                            }).then((response) => {
                                if (response) {
                                    var data = {'shop_id':shop_id};
                                    callAjaxRequest(credit_request_url,'post',data,function(resp){
                                        if(resp.status=='success'){
                                            swal({
                                                type: resp.status, 
                                                text: resp.message, 
                                                confirmButtonText : text_ok_btn
                                            }).then(function(){
                                                window.location.reload(true);
                                            });
                                        }else{
                                            swal({
                                                type: resp.status, 
                                                text: resp.message, 
                                                confirmButtonText : text_ok_btn
                                            }).then(function(){
                                                window.location.href = resp.redirect_url;
                                            });
                                        }
                                    }); 
                                }
                            })
                        }
                    });
                }
            });
});

