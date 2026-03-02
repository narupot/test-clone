
$(document).on("change",'.product_image', function(e) {
    
    var f_key = "file_"+Math.random().toString(36).substr(2, 9);
    var _this = $(this);
    var files = e.target.files,
    files_length = files.length;
    _this.attr('id',f_key)
    _this.hide();

    for (var i = 0; i < files_length; i++) {

        
        var fileReader = new FileReader();

        fileReader.onload = (function(e) {

            var file = e.target.result;

            $("#product_img_div").append(
                '<li>'+
                    '<div class="img-block"><img src="'+file+'" width="119" height="119"></div>'+
                    '<div class="action-block">'+
                        '<a href="javascript:void(0);" data-fileid="'+f_key+'" class="delete_image">Delete <i class="fas fa-times"></i></a>'+
                    '</div>'+
                '</li>');
                $('#product_img_span').append('<input type="file" class="product_image" name="product_image[]" accept="image/*" multiple="multiple">');
        });

        var f = files[i]

        fileReader.readAsDataURL(f);
    }        
});

$(document).on('click',".delete_image",function() {
    var id = $(this).data('fileid');
    $('#'+id).remove();
    $(this).parent("div").parent("li").remove();
});

// $('body').on('click','.delete_product_image',function() {

//     var _this = $(this);
//     var type = _this.data('type');
//     var val  = _this.data('val');
//     swal({
//         title: txt_delete_confirm,
//         type: "warning",
//         showCancelButton: true,
//         confirmButtonText: yes_delete_it,
//         cancelButtonText: txt_no,
//         closeOnConfirm: true,
//         closeOnCancel: true,
//     }).then(function(isConfirm){
//         if (isConfirm) {

//             var data = {type:type,val:val};
//             callAjaxRequest(url_deleteshopimage,'post',data,function(result){
//                 if(result.status=='success'){
//                     _this.parent("li").remove();
//                 }else{
//                    showSweetAlertError(result.msg);
//                 }
//             });
//         }
//     },function(){
//         return false;
//     });
// });

$('body').on('click','.show_price',function() {
    var show_price = $(this).val();
    if(show_price == 1) {
        $('#price_div').slideDown();
    }
    else {
        $('#price_div').slideUp();
    }
});

$('body').on('change','#stock',function(){
    var isChecked = $(this).is(':checked');
    if(isChecked === true) {
        $('#quantuty_div').slideUp();
    }
    else {
        $('#quantuty_div').slideDown();
    }
});

$('body').on('change','#order_qty_limit',function(){
    var isChecked = $(this).is(':checked');
    if(isChecked === true) {
        $('#min_order_qty_div').slideUp();
    }
    else {
        $('#min_order_qty_div').slideDown();
    }
});

$('body').on('change','#is_tier_price',function(){
    var isChecked = $(this).is(':checked');
    if(isChecked === true) {
        $('#tier_price_div').slideDown();
    }
    else {
        $('#tier_price_div').slideUp();
    }
});

$('body').on('click','.remove_more_btn',function(){

    var property_div = $(this).parents('div .tier-input-group');
    var property_id = property_div.attr('data-attr');
    if(property_id == 'new') {
        property_div.remove();
    }
    else {
        // swal({
        //     //title: 'Are you sure?',
        //     text: "Are you sure to delete this record?",
        //     type: 'warning',
        //     showCancelButton: true,
        //     confirmButtonColor: '#3085d6',
        //     cancelButtonColor: '#d33',
        //     confirmButtonText: 'Yes, delete it!'
        // }).then(function () {

        //     var data = {action:'delete', property_id:property_id};   
        //     callAjax(delete_ajax_url, 'post', data, function(response){
        //         if(response == 'success'){
        //             property_div.remove();
        //             swal(
        //                 'Deleted!',
        //                 'Record has been deleted successfully.',
        //                 'success'
        //             ).then(function () {
        //                 //location.reload();
        //             });                     
        //         } 
        //     });
        // });
    }
});

$('body').on('click','#create_prod_btn, #update_prod_btn,  #copy_prod_btn',function(){

    var ajax_url = $("#product_frm").attr('action');
    var data = new FormData($("#product_frm")[0]);
    callAjaxFormRequest(ajax_url, 'post', data, function(response){
        response = JSON.parse(response);
        if(response.status == 'success'){
        swal({
                type: 'success',
                text: response.message,
                confirmButtonColor: '#d33',
                confirmButtonText: text_ok_btn
            })
           .then(function(){ window.location.href = response.url}); 
        }
        else if(response.status == 'validate_error'){
            $('.error').text('');
            var error_str = '';
            $.each(response.message, function(key,val){
                $('#error_'+key).text(val);
                error_str += '<p class="error">'+val+'</p>';
            });
            swal({
                type: 'error',
                text: error_str,
                confirmButtonColor: '#d33',
                confirmButtonText: text_ok_btn
            }); 
        } 
    });
});


// $('body').on('click','a.action-del',function(e){
//     consoles.log('Delete button clicked');
//     e.preventDefault();
//     var ajax_url = $(this).attr('rel');
   
//     swal({
//         title: error_msg.txt_delete_confirm,
//         type: "warning",
//         showCancelButton: true,
//         confirmButtonText: error_msg.yes_delete_it,
//         cancelButtonText: error_msg.txt_no,
//         closeOnConfirm: true,
//         closeOnCancel: true,
//     }).then(rep =>{
//         callAjaxFormRequest(ajax_url, 'get', '', function(response){
//             response = JSON.parse(response);
//             if(response.status == 'success'){

//                  window.location = response.url;
//                 /*swal({
//                     type: 'success',
//                     text: response.message,
//                     confirmButtonColor: '#d33',
//                     confirmButtonText: text_ok_btn
//                 })
//                .then(function(){ window.location = response.url}); */
//             }
//             else if(response.status == 'validate_error'){
//                 swal({
//                     type: 'error',
//                     text: response.message,
//                     confirmButtonColor: '#d33',
//                     confirmButtonText: text_ok_btn
//                 }); 
//             } 
//         });
//     }, er=>{
//         //error code 
//     });
    
// });

// ในไฟล์ js/seller/product.js หรือใน <script>
// $('body').on('click', '.action-del', function(e) {
//     e.preventDefault();

//     // อ่าน URL จาก data-url หรือ rel
//     var deleteUrl = $(this).data('url') || $(this).attr('rel');
//     console.log("deleteUrl =", deleteUrl);

//     if (!deleteUrl) {
//         console.error('Delete URL not found or invalid.');
//         return;
//     }

//     // SweetAlert (เวอร์ชัน 1.x)
//     swal({
//         title: error_msg.txt_delete_confirm,
//         text: "คุณจะไม่สามารถกู้คืนข้อมูลนี้ได้",
//         type: "warning",
//         showCancelButton: true,
//         confirmButtonColor: "#3085d6",
//         cancelButtonColor: "#d33",
//         confirmButtonText: error_msg.yes_delete_it,
//         cancelButtonText: error_msg.txt_no,
//         closeOnConfirm: false,
//         showLoaderOnConfirm: true
//     }, function(isConfirm) {
//         if (isConfirm) {
//             console.log("User confirmed delete");

//             // AJAX GET เพื่อเรียกลบสินค้า
//             $.get(deleteUrl, function(response) {
//                 console.log("Server response:", response);

//                 if (response.status === 'success') {
//                     swal({
//                         title: "สำเร็จ!",
//                         text: response.message || "ลบข้อมูลสำเร็จ",
//                         type: "success"
//                     }, function() {
//                         if (response.url) window.location = response.url;
//                         else location.reload();
//                     });
//                 } else {
//                     swal("เกิดข้อผิดพลาด", response.message || "ไม่สามารถลบข้อมูลได้", "error");
//                 }
//             }).fail(function() {
//                 swal("ข้อผิดพลาด", "@lang('common.api_error')", "error");
//             });
//         }
//     });
// });




$('body').on('click', '.action-del', function(e) {
    e.preventDefault();

    var deleteUrl = $(this).data('url') || $(this).attr('rel');
    if (!deleteUrl) {
        console.error('Delete URL not found or invalid.');
        return;
    }

    swal({
        title: error_msg.txt_delete_confirm,
        text: "คุณจะไม่สามารถกู้คืนข้อมูลนี้ได้",
        type: 'warning',
        showConfirmButton: true,
        confirmButtonText: error_msg.yes_delete_it,
        cancelButtonText: error_msg.txt_no,
        showCancelButton: true,
    }).then(function() {

        // ใช้ Ajax แบบเดียวกับ act-reject
        callAjaxFormRequest(deleteUrl, 'get', '', function(response) {
    if (response.status == 'success') {
        swal({
            type: 'success',
            text: response.message || "ลบข้อมูลสำเร็จ",
            confirmButtonColor: '#3085d6',
            confirmButtonText: text_ok_btn
        }).then(function() {
            if (response.url) {
                window.location = response.url;
            } else {
                location.reload();
            }
        });
    } else if (response.status == 'validate_error') {
        swal({
            type: 'error',
            text: response.message || "ข้อมูลไม่ถูกต้อง",
            confirmButtonColor: '#d33',
            confirmButtonText: text_ok_btn
        });
    } else {
        swal({
            type: 'error',
            text: response.message || "เกิดข้อผิดพลาดในการลบข้อมูล",
            confirmButtonColor: '#d33',
            confirmButtonText: text_ok_btn
        });
    }
});
    });
});



$('body').on('click','.act-accept',function(e){
    e.preventDefault();
    var ajax_url = $(this).attr('rel');
    swal({text:acceptMessage,
          type:'warning',
          showConfirmButton:true,
          confirmButtonText: text_ok_btn,
          cancelButtonText: txt_no,
          showCancelButton:true,
    }).then(function(){
        callAjaxFormRequest(ajax_url, 'get', '', function(response){
            response = JSON.parse(response);
            if(response.status == 'success'){
                //db.collection("chats").doc(response.docName).collection('messages').add(response.chat_data);
                db.collection("chats").doc(response.docName).collection("messages").add(response.chat_data).then(function(docRef) {
                    db.collection('chats').doc(response.docName).collection("messages").doc(docRef.id).update({createdAt: firebase.firestore.FieldValue.serverTimestamp()});
                });    
                swal({
                    type: 'success',
                    text: response.message,
                    confirmButtonColor: '#d33',
                    confirmButtonText: text_ok_btn
                })
               .then(function(){ window.location = response.url}); 
            }
            else if(response.status == 'validate_error'){
                swal({
                    type: 'error',
                    text: response.message,
                    confirmButtonColor: '#d33',
                    confirmButtonText: text_ok_btn
                }); 
            } 
        });
    });
});


$('body').on('click', 'a.submitAdjustprice',function(e){
    e.preventDefault();
    var id = $(this).attr('rel');
    /*$tr = $(this).parents('tr').prev('tr').find('.base_unit_price').val();
    if(!$tr) {
        swal()
        return;
    };*/
    var ajax_url = $('#submitAdjustprice_'+id).attr('action');
    var data = new FormData($("#submitAdjustprice_"+id)[0]);
    
    var base_unit_price = data.get('base_unit_price');
    if(!base_unit_price) {
        swal({
            type: 'error',
            text: requiredBasePrice,
            confirmButtonColor: '#d33',
            confirmButtonText: text_ok_btn
        });
        return;
    };

    var unit_price = data.get('unit_price');
    if(!unit_price) {
        swal({
            type: 'error',
            text: requiredUnitPrice,
            confirmButtonColor: '#d33',
            confirmButtonText: text_ok_btn
        });
        return;
    };
  
    swal({text:acceptMessage,
          type:'warning',
          showConfirmButton:true,
          confirmButtonText: text_ok_btn,
          cancelButtonText: txt_no,
          showCancelButton:true,
    }).then(function(){
        callAjaxFormRequest(ajax_url, 'post', data, function(response){
            response = JSON.parse(response);
            if(response.status == 'success'){
                db.collection("chats").doc(response.docName).collection("messages").add(response.chat_data).then(function(docRef) {
                        db.collection('chats').doc(response.docName).collection("messages").doc(docRef.id).update({createdAt: firebase.firestore.FieldValue.serverTimestamp()});
                });
                //db.collection("chats").doc(response.docName).collection('messages').add(response.chat_data);
                    
                //db.collection("messages").add(response.chat_data);
                swal({
                    type: 'success',
                    text: response.message,
                    confirmButtonColor: '#d33',
                    confirmButtonText: text_ok_btn
                })
               .then(function(){ window.location = response.url}); 
            }
            else if(response.status == 'validate_error'){
                swal({
                    type: 'error',
                    text: response.message,
                    confirmButtonColor: '#d33',
                    confirmButtonText: text_ok_btn
                }); 
            } 
        });
    });
});



$('body').on('click','.adjustPriceshow a',function(e){
    e.preventDefault();
    var id = $(this).attr('rel');
    $('.adjustPricefrom_'+id).show();
    $('#adjustPriceshow_'+id).hide();
});


$('body').on('click','a.adjustPricehide',function(e){
    e.preventDefault();
    var id = $(this).attr('rel');
    $('.adjustPricefrom_'+id).hide();
    $('#adjustPriceshow_'+id).show();
});

$(document).ready(function() {
    $('input[name="unit_price"]').keyup(function(){  
        var cur = $(this).val().replace(/,/g,'');
        var qty =  $(this).parent().siblings('input[name="qty"]').val();
        var weight_per_unit =  $(this).parent().siblings('input[name="weight_per_unit"]').val();
        var base_unit_price = cur/weight_per_unit;
        $(this).parent().siblings().children('input[name="base_unit_price"]').val(formatNumberforView(base_unit_price.toString()));
        var totalbaramount = cur*qty;
        totalbaramount = totalbaramount.toString();
        $(this).parent().siblings('.bargainTotal').text(formatNumberforView(totalbaramount) + ' ' + currency);

    });
    
    $('input[name="base_unit_price"]').keyup(function(){  
       var cur = $(this).val().replace(/,/g,'');
       var qty =  $(this).parent().siblings('input[name="qty"]').val();
       var weight_per_unit =  $(this).parent().siblings('input[name="weight_per_unit"]').val();
       var unit_price = cur*weight_per_unit;
       $(this).parent().siblings().children('input[name="unit_price"]').val(formatNumberforView(unit_price.toString()));
       var totalbaramount = unit_price*qty;
       $(this).parent().siblings('.bargainTotal').text(formatNumberforView(totalbaramount.toString()) + ' ' +currency);
    });

    /*$('input[name="total_price"]').keyup(function(){  
       var cur = $(this).val();
       var qty =  $(this).parent().siblings('input[name="qty"]').val();
       $(this).parent().siblings().children('input[name="unit_price"]').val(cur/qty);
    });*/


    // $('body').on('click', 'input:radio[name="product_cat"]', function(e){
    //     if ($(this).is(':checked')) {
    //         var cat_id = $(this).val();
    //         var data = new Array;
    //         var ajax_url = base_unit_url+'/'+cat_id;
    //         callAjaxFormRequest(ajax_url, 'get', data, function(response){
    //             //response = JSON.parse(response);
                
    //             var html = '<option value="">---'+lang_json.select+'---</option>';
    //             var selected = '';
    //             //var html = '';
    //             $.each(response, function( index, value ) {
    //                 selected = '';
    //                 if(base_unit_id == value.id){
    //                     selected = 'selected="selected"';
    //                 }
    //                 html += '<option value="'+value.id+'" '+selected+' >'+value.unit_name+'</option>';
    //             });

    //             $('#baseunit').html(html);
              
    //         });
    //     }
       
    // });

    $('body').on('click', 'input:radio[name="product_cat"]', function(e) {
        if ($(this).is(':checked')) {
            var cat_id = $(this).val();
            var ajax_url = parent_cat_data_url + '/' + cat_id;
            
            callAjaxFormRequest(ajax_url, 'get', {}, function(response) {
       
                //Base Unit
                var baseUnitHtml = '<option value="">---'+lang_json.select+'---</option>';
                $.each(response.base_units, function(index, value) {
                    var selected = (base_unit_id == value.id) ? 'selected="selected"' : '';
                    baseUnitHtml += '<option value="'+value.id+'" '+selected+'>'+value.unit_name+'</option>';
                });
                $('#baseunit').html(baseUnitHtml);

                //Package
                var packageHtml = '<option value="">---'+lang_json.select+'---</option>';
                $.each(response.packages, function(index, value) {
                    var selected = (package_id == value.package_id) ? 'selected="selected"' : '';
                    packageHtml += '<option value="'+value.package_id+'" '+selected+'>'+value.package_name+'</option>';
                });
               
                $('#weightperunit').html(packageHtml);
            });
        }
    });


    /*$('input:radio[name="product_cat"]').change(function(){
        
    });*/

   
    /*$('body').on('click', '.select-product-img .radio-wrap input[name="product_cat"]',function(){
        var cat_id = $(this).val();
        var ajax_url = base_unit_url+'/'+cat_id;
        alert(ajax_url);
        callAjaxFormRequest(ajax_url, 'get', null, function(response){
            response = JSON.parse(response); 
        });
        
    });*/


});


$('body').on('change', '.checkedAll input[type="checkbox"]', function(){          
    $('input:checkbox').not(this).prop('checked', this.checked);
});


$(document).on('click', 'button.refuse_all_bargaining', function(evt){
   
    let $checkedPrdList = $('.table-content ul input[type="checkbox"]:checked');
    if($checkedPrdList.length ===0){
        showSweetAlertError(error_msg.now_ckeck);
        return;
    }

    let data = [];
    $.each($checkedPrdList, function(){
        data.push({
            'reject_id' : $(this).val(),
        });
    });
    swal({
        title: rejectMessage,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: yes_delete_it,
        cancelButtonText: txt_no,
        closeOnConfirm: true,
        closeOnCancel: true,
    }).then(rep =>{
        callAjaxRequest(rejectAllBargain, 'post', {'data':JSON.stringify(data)}, result=>{
            if(result.status == 'success'){
               $.each(result.data_charts, function( index, response ) {

                    db.collection("chats").doc(response.docName).collection("messages").add(response.chat_data).then(function(docRef) {
                        db.collection('chats').doc(response.docName).collection("messages").doc(docRef.id).update({createdAt: firebase.firestore.FieldValue.serverTimestamp()});
                    });
  
               });
                

                swal({
                    type: 'success',
                    text: result.msg,
                    confirmButtonColor: '#d33',
                    confirmButtonText: text_ok_btn
                })
               .then(function(){ window.location = window.location.href});
            }else{
               showSweetAlertError(result.msg);
            }
        });
    }, er=>{
        //error code 
    });
});



//Shopby customer hide/show link
$(document).ready(function(){   
    $('.bargain-order-table .customer-head .del-action a').on('click',function(){ 
        //alert(1);          
        $(this).find('i').toggleClass('fa-chevron-up');
        $(this).parents('.customer-head').toggleClass('active');
        $(this).parents('.bargain-order-table').find('.table').toggle();
    });
});


$('body').on('click','.saveProductPrice',function(e){
    e.preventDefault();
    var ajax_url = $("#FormPrice").attr('action');
    var data = new FormData($("#FormPrice")[0]);
    callAjaxFormRequest(ajax_url, 'post', data, function(response){
        response = JSON.parse(response);
        if(response.status == 'success'){
            
            if(response.url == '#'){
                window.location.href = window.location.href;
            }else{
                window.location.href = response.url;
            }


            /*swal({
                    type: 'success',
                    text: response.message,
                    confirmButtonColor: '#d33',
                    confirmButtonText: lang_ok
                }).then(function(){
                if(response.url == '#'){
                    window.location.href = window.location.href;
                }else{
                    window.location.href = response.url;
                }
            });*/ 
        }else if(response.status == 'validate_error'){
            $('.error').text('');
            var error_str = '';
            $.each(response.message, function(key,val){
                $('#error_'+key).text(val);
                error_str += '<p class="error">'+val+'</p>';
            });
            /*swal({
                type: 'error',
                text: error_str,
                confirmButtonColor: '#d33',
                confirmButtonText: text_ok_btn
            }); */
        }else if(response.status == 'fail'){
            swal({
                    type: 'error',
                    text: response.msg,
                    confirmButtonColor: '#d33',
                    confirmButtonText: text_ok_btn
                });

        }


    });     

});


$('body').on('click','.switch-orange',function(e){
    var checkText = $(this);
    var status = '0';
    if(checkText.is(':checked')){
        status = '1';
    }
    
    var product_id = checkText.val();
    var ajax_url = updateStatus+'/'+product_id+'/'+status;
    //alert(product_id);
    var data = {
        
    };
    callAjaxFormRequest(ajax_url, 'GET', data, function(response){
        response = JSON.parse(response);
    });
});

