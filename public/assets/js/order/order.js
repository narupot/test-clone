/****change order status********/
$('.ord_status_change').click(function(e){
    var _this = $(this);

    var val  = _this.data('val');
    var type = _this.data('type');
    if(type == 'cancel'){
        var lang_msg = lang_ord_cancel;
    }else{
        var lang_msg = lang_ord_complete;
    }

    swal({
        title: lang_msg,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: lang_yes,
        cancelButtonText: lang_no,
        closeOnConfirm: true,
        closeOnCancel: true,
    }).then(function(isConfirm){
        if (isConfirm) {

            var data = {type:type,order_shop_id:order_shop_id};
            callAjaxRequest(ord_status_url,'post',data,function(result){
                if(result.status=='success'){
                    swal(lang_success, result.msg, "success").then(function(){
                        location.reload();
                    }); 
                }else{
                   showSweetAlertError(result.msg);
                }
            });
        }
    },function(){
        return false;
    });
})

/***change order item status**/
$('.ord_item_change').click(function(e){
    var _this = $(this);

    var val  = _this.data('val');
    var type = _this.data('type');
    if(type == 'cancel'){
        var lang_msg = lang_cancel;
    }else{
        var lang_msg = lang_receive;
    }
    swal({
        title: lang_msg,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: lang_yes,
        cancelButtonText: lang_no,
        closeOnConfirm: true,
        closeOnCancel: true,
    }).then(function(isConfirm){
        if (isConfirm) {

            var data = {type:type,order_detail_id:val};
            callAjaxRequest(change_url,'post',data,function(result){
                if(result.status=='success'){
                    $('#item_status_'+val).text(result.item_status);
                    $('#shop_status').text(result.shop_status);
                    swal(lang_success,result.msg, "success"); 
                    _this.hide();
                }else{
                   showSweetAlertError(result.msg);
                }
            });
        }
    },function(){
        return false;
    });
});

/***buyer***/