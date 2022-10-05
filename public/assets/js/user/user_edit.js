
if($(".date-select").length){
    $(".date-select").flatpickr();
}

if($("#custom_time_from").length && $("#custom_time_to").length){
    flatpickr("#custom_time_from, #custom_time_to", {
        enableTime: true,
        noCalendar: true,
        time_24hr:true
        //dateFormat: "h:i K",
        //dateFormat: "H:i",
    });
}

$().fancybox({
    selector : '.mapfancy'
}); 
$().fancybox({
    selector : '.shopfancy'
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
            $("#shop-upload-img").append('<li><img src="'+e.target.result+'" height="50" width="50"><br><span class="removeShopImg"><i class="fas fa-times"></i></span></li>');

            $('#shop_img_span').append('<input type="file" class="shop_image" name="shop_image[]"  accept="image/*" />');
        }else{
            $("#map-upload-img").append('<li><img src="'+e.target.result+'" height="50" width="50"><br><span class="removeMap"><i class="fas fa-times"></i></span></li>');

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

/***submit shop info data******/
jQuery('#btn_shop_info').click(function(evt){
    evt.preventDefault();
    var formAction = $(this).closest('form').attr('action');
    var formId = $(this).closest('form').attr('id');
    var _this = $(formId);
    var form_data = new FormData($("#"+formId)[0]);

    $('.error').text('');
    _this.prop('disabled',false);
    callAjaxFormRequest(formAction,'post',form_data,function(result){

            if(result.status=='fail'){
                if(result.error == 'validation'){
                    var str_e = '';
                    $.each(result.msg, function(key,val){

                        $('#'+formId+' p[id=e_'+key+']').text(val);
                        str_e += '<p class="error">'+val+'</p>';
                    });
                    showSweetAlertError(str_e);
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

/*  assign category to seller */
$(document).on('click',"#assign_cat_seller",function(evt){ 
	evt.preventDefault();
	var formAction = $(this).closest('form').attr('action');
	var formId = $(this).closest('form').attr('id');
	var _this = $(this);
	var form_data = new FormData($("#"+formId)[0]);
	_this.prop('disabled',false);
	$('.error').text('');
	callAjaxFormRequest(formAction,'post',form_data,function(result){

        if(result.status=='fail'){
            if(result.error == 'validation'){
                var str_e = '';
                $.each(result.msg, function(key,val){

                    $('#'+formId+' p[id=e_'+key+']').text(val);
                    str_e += '<p class="error">'+val+'</p>';
                });
                showSweetAlertError(str_e);
            }else{
                showSweetAlertError(result.msg);
            }   
            _this.prop('disabled',false);
            return false;

        }else{
        	swal(lang_success,records_updated_successfully, "success");         
        }
    });
});


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

            var data = {type:type,val:val,shop_id:shop_id};
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

/***buyer***/