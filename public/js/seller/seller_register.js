
/*****seller shop info *********/
/*****check panel no**
jQuery('input[name="panel_no"]').change(function(e){

    var _this = jQuery(this);
    var panel_no = $.trim(_this.val());
    if(panel_no == '' || panel_no == undefined){
        _this.focus();
        return false;
    }
    var data = {panel_no:panel_no,user_id:seller_user_id};
    callAjaxRequest(url_checkPanel,'post',data,function(result){
        if(result.status=='success'){
            jQuery('#e_panel_no').html('');
        }else{
            swal({
                    title: lang_oops, 
                    type: "warning", 
                    html : '<div class="alert alert-danger">'+result.msg+'</div>',
                });
            jQuery('#e_panel_no').html(result.msg);
        }
    });
});***/

/*****check citizen id***
jQuery('input[name="citizen_id"]').change(function(e){

    var _this = jQuery(this);
    var citizen_id = $.trim(_this.val());
    if(citizen_id == '' || citizen_id == undefined){
        _this.focus();
        return false;
    }
    var data = {citizen_id:citizen_id,user_id:seller_user_id};
    callAjaxRequest(url_checkCitizen,'post',data,function(result){
        if(result.status=='success'){
            jQuery('#e_citizen_id').html('');
        }else{
            swal({
                    title: lang_oops, 
                    type: "warning", 
                    html : '<div class="alert alert-danger">'+result.msg+'</div>',
                });
            jQuery('#e_citizen_id').html(result.msg);
        }
    });
});**/

/*****check store name*****/
jQuery('input[name="store_name"]').change(function(e){

    var _this = jQuery(this);
    var store_name = $.trim(_this.val());
    if(store_name == '' || store_name == undefined){
        _this.focus();
        return false;
    }
    var data = {store_name:store_name,user_id:seller_user_id};
    callAjaxRequest(url_checkStoreName,'post',data,function(result){
        if(result.status=='success'){
            jQuery('input[name="store_url"]').val(result.store_url);
            jQuery('#e_store_name').html('');
        }else{
            swal({
                    title: lang_oops, 
                    type: "warning", 
                    html : '<div class="alert alert-danger">'+result.msg+'</div>',
                });
            jQuery('#e_store_name').html(result.msg);
        }
    });
});

/*****check store url*****/
jQuery('input[name="store_url"]').change(function(e){

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
    var _this = $(this);

    var form_data = new FormData($("#"+formId)[0]);
    form_data.append('user_id',seller_user_id);
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
                window.location.href=result.url;        
            }
    });
});

jQuery('select[name="bank_id"]').change(function(e){

    var _this = jQuery(this);
    var bank_id = $.trim(_this.val());
    if(bank_id == '' || bank_id == undefined){
        _this.focus();
        return false;
    }
    var data = {bank_id:bank_id};

    callAjaxRequest(branch_list_url,'post',data,function(result){
        if(result.status=='success'){
            var opt_html='';
            $.each(result.data, function(key,val){

                opt_html +='<option value="'+val.id+'">'+val.branch_name.branch_name+'</option>';
              
            });
            $('#branch_select').html(opt_html);
        }else{
            swal({
                    title: lang_oops, 
                    type: "warning", 
                    html : '<div class="alert alert-danger">'+result.msg+'</div>',
                });
            jQuery('#e_store_name').html(result.msg);
        }
    });
});

/***submit account info data******/
jQuery('#btn_account_info').click(function(evt){
    evt.preventDefault();
    var formAction = $(this).closest('form').attr('action');
    var formId = $(this).closest('form').attr('id');
    var formMethod = $(this).closest('form').attr('method');
    var _this = $(this);

    var form_data = new FormData($("#"+formId)[0]);
    form_data.append('user_id',seller_user_id);
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
                window.location.href=result.url;        
            }
    });
});
