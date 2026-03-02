$('body').on('click','a.action-del',function(e){
    e.preventDefault();
    var ajax_url = $(this).attr('rel');
    var confirms = confirm(confirmMessage);
     
    if(confirms){
      callAjaxFormRequest(ajax_url, 'get', '', function(response){
            response = JSON.parse(response);
            if(response.status == 'success'){
                swal({
                    type: response.status,
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
    }
});



$('body').on('click','.act-reject',function(e){
    e.preventDefault();
    var ajax_url = $(this).attr('rel');
    var confirms = confirm(rejectMessage);
    if(confirms){
        callAjaxFormRequest(ajax_url, 'get', '', function(response){
            response = JSON.parse(response);
            if(response.status == 'success'){
                swal({
                    type: response.status,
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
    }
});


$('body').on('click','.act-accept',function(e){
    e.preventDefault();
    var ajax_url = $(this).attr('rel');
    //var confirms = confirm(acceptMessage);
    swal({text:acceptMessage,
          type:'warning',
          showConfirmButton:true,
          confirmButtonText : text_ok_btn,
          cancelButtonText: txt_no,
          showCancelButton:true,
    }).then(function(){
      callAjaxFormRequest(ajax_url, 'get', '', function(response){
            response = JSON.parse(response);
            if(response.status == 'success'){
                swal({
                    type: response.status,
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
    var ajax_url = $('#submitAdjustprice_'+id).attr('action');
    var data = new FormData($("#submitAdjustprice_"+id)[0]);
    
    var base_unit_price = data.get('base_unit_price');
    //alert(base_unit_price);
    if(!base_unit_price) {
        swal({
            type: 'error',
            text: requiredBasePrice,
            confirmButtonColor: '#d33',
            confirmButtonText: text_ok_btn,
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
    
    swal({text:addbargainMessage,
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

                          
              
                    //db.collection("chats").doc(response.docName).set({createdAt: serverTimestamp()});
                    //db.collection("messages").add(response.chat_data);
                    swal({
                        type: 'success',
                        text: response.message,
                        confirmButtonColor: '#d33',
                        confirmButtonText: text_ok_btn
                    }).then(function(){ 
                       //alert(response.url);
                       window.location.href = response.url;
                    }); 
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


/*$('unit_price').on('click','a.adjustPricehide',function(e){
    e.preventDefault();
    var id = $(this).attr('rel');
    $('.adjustPricefrom_'+id).hide();
    $('#adjustPriceshow_'+id).show();
});*/


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

});

$('body').on('click', 'a.addtoCartorBuyNow',function(e){
    e.preventDefault();

    var bar_id = $(this).attr('rel');
    var cart_action = $(this).attr('rev');
    $("#submitaddtoCartorBuyNow_"+bar_id +' input[name="action"]').val(cart_action);
    var ajax_url = addProductToCart;
    var data = new FormData($("#submitaddtoCartorBuyNow_"+bar_id)[0]);
    var message = addtocartMessage;

    if(cart_action == 'buynowfrombargin'){
       message = buyMessage;
    }
    //var confirms = confirm(message);

    swal({text:message,
          type:'warning',
          showConfirmButton:true,
          confirmButtonText: text_ok_btn,
          cancelButtonText: txt_no,
          showCancelButton:true,
    }).then(function(){
        callAjaxFormRequest(ajax_url, 'post', data, function(response){
            if(response.status == 'success'){
                if(cart_action == "buynowfrombargin" || cart_action == 'addtocartfrombargin'){
                    db.collection('chats').doc(response.docName).collection("messages").where('bargainId', '==', response.chat_data.bargainId)
                    .orderBy("createdAt", "desc")
                    .get().then(function(querySnapshot){                        
                        querySnapshot.forEach(function(doc){
                            if (doc.data().bargainDetailId != response.chat_data.bargainDetailId) {
                                db.collection('chats').doc(response.docName).collection("messages").doc(doc.id).update({ disabled: true });    
                            }                       
                        })
                    }).catch((error) => {
                        console.log("Error getting documents: ", error);
                    });

                    db.collection("chats").doc(response.docName).collection("messages").add(response.chat_data).then(function(docRef) {
                        db.collection('chats').doc(response.docName).collection("messages").doc(docRef.id).update({createdAt: firebase.firestore.FieldValue.serverTimestamp()});
                    });

                    

                    swal({
                        type: 'success',
                        text: 'success',
                        confirmButtonColor: '#d33',
                        confirmButtonText: text_ok_btn
                    }).then(function(){
                       window.location.href = cartUrl;

                    });
                    return;

                }else{
                   window.location.href = window.location; 
                   return;
                }
            }
            else if(response.status == 'fail'){
                swal({
                    type: 'error',
                    text: response.msg,
                    confirmButtonColor: '#d33',
                    confirmButtonText: text_ok_btn
                });
            }

        });
    });
});

$(document).on('click', 'button.add_to_cart_all_bargain', function(evt){
   
    let $checkedPrdList = $('.table-content ul input[type="checkbox"]:checked, .bargin-tbl-row input[type="checkbox"]:checked');
    if($checkedPrdList.length ===0){ 
        showSweetAlertError(now_ckeck);
        return;
    }

    let data = [];
    $.each($checkedPrdList, function(){
        data.push({
            'barg_id' : $(this).val(),
        });
    });
    swal({
        title: addtocartAllMessage,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: yes_add_to_cart_it,
        cancelButtonText: txt_no,
        closeOnConfirm: true,
        closeOnCancel: true,
    }).then(rep =>{
        callAjaxRequest(selectedAddtoCart, 'post', {'data':JSON.stringify(data)}, result=>{
            if(result.status=='success'){
                /*$.each($checkedPrdList, function(){ 
                  $('#listbar_'+$(this).val()).remove();  
                });*/  
                var msgcon = '';
                $.each(result.msg,function(index, value){
                    if(value.status == 'success'){
                       $('#listbar_'+index).remove();
                    }
                    /*if(!msgcon){
                       msgcon = value.sku+' - '+value.msg;
                    }else{
                       msgcon += '<br>'+value.sku+' - '+ value.msg; 
                    }*/
                });
                
                $.each(result.data_chart,function(index, response){
                    db.collection('chats').doc(response.docName).collection("messages").where('bargainId', '==', response.chat_data.bargainId)
                    .orderBy("createdAt", "desc")
                    .get().then(function(querySnapshot){                        
                        querySnapshot.forEach(function(doc){
                            if (doc.data().bargainDetailId != response.chat_data.bargainDetailId) {
                                db.collection('chats').doc(response.docName).collection("messages").doc(doc.id).update({ disabled: true });    
                            }                       
                        })
                    }).catch((error) => {
                        console.log("Error getting documents: ", error);
                    });

                    db.collection("chats").doc(response.docName).collection("messages").add(response.chat_data).then(function(docRef) {
                        db.collection('chats').doc(response.docName).collection("messages").doc(docRef.id).update({createdAt: firebase.firestore.FieldValue.serverTimestamp()});
                    });

                });

                swal({
                    type: 'success',
                    text: 'success',
                    confirmButtonColor: '#d33',
                    confirmButtonText: text_ok_btn
                }).then(function(){
                    window.location.href = cartUrl;

                });
                
            }else{
               showSweetAlertError(result.msg);
            }
        });
    }, er=>{
        //error code 
    });
});


$(document).on('click', 'a.action-delBarg', function(evt){
    evt.preventDefault();
    var bid = $(this).attr('rel');
    var data = {
        b_id: bid
    };
    swal({
        text: txt_delete_confirm,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: yes_delete_it,
        cancelButtonText: txt_no,
        closeOnConfirm: true,
        closeOnCancel: true,
    }).then(rep =>{
        callAjaxRequest(removeBargain, 'post', data, result=>{
            if(result.status=='success'){
                $('#listbar_'+bid).remove(); 
                db.collection('chats').doc(result.docName).collection("messages").where('bargainId', '==', result.chat_data.bargainId)
                    .orderBy("createdAt", "desc")
                    .get().then(function(querySnapshot){                        
                        querySnapshot.forEach(function(doc){
                            db.collection('chats').doc(result.docName).collection("messages").doc(doc.id).update({ disabled: true });  
                                                   
                        })
                }).catch((error) => {
                        console.log("Error getting documents: ", error);
                }); 
                swal({
                    type: 'success',
                    text: result.msg,
                    confirmButtonColor: '#d33',
                    confirmButtonText: text_ok_btn
                })   

                //'chat_data'=>$data_chart, 'docName'=>$docName
                
            }else{
               showSweetAlertError(result.msg);
            }
        });
    }, er=>{
        //error code 
    });
});

$('body').on('change', '.checkedAll input[type="checkbox"]', function(){          
    $('input:checkbox').not(this).prop('checked', this.checked);
});

$(document).on('click', 'button.delete_all_bargain', function(evt){
   
    let $checkedPrdList = $('.table-content ul input[type="checkbox"]:checked, .bargin-tbl-row input[type="checkbox"]:checked');
    if($checkedPrdList.length ===0){ 
        showSweetAlertError(now_ckeck);
        return;
    }

    let data = [];
    $.each($checkedPrdList, function(){
        data.push({
            'barg_id' : $(this).val(),
        });
    });
    swal({
        title: txt_delete_confirm,
        type: "warning",
        showCancelButton: true,
        confirmButtonText: yes_delete_it,
        cancelButtonText: txt_no,
        closeOnConfirm: true,
        closeOnCancel: true,
    }).then(rep =>{
        callAjaxRequest(removeAllBargain, 'post', {'data':JSON.stringify(data)}, result=>{
            if(result.status=='success'){
                $.each($checkedPrdList, function(){ 
                  $('#listbar_'+$(this).val()).remove();  
                }); 
                $.each(result.chat_data, function( index, response ) {
                        db.collection('chats').doc(response.docName).collection("messages").where('bargainId', '==', response.bargainId)
                        .orderBy("createdAt", "desc")
                        .get().then(function(querySnapshot){                        
                            querySnapshot.forEach(function(doc){
                                db.collection('chats').doc(response.docName).collection("messages").doc(doc.id).update({ disabled: true });  
                                                       
                            })
                        }).catch((error) => {
                            console.log("Error getting documents: ", error);
                        });
                     
                })  
                swal({
                    type: 'success',
                    text: result.msg,
                    confirmButtonColor: '#d33',
                    confirmButtonText: text_ok_btn
                }).then(function(){
                   window.location.href = window.location;

                })
                
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
    // $('.bargain-order-table .customer-head .del-action a').on('click',function(){           
    //     $(this).parents('.bargain-order-table').toggleClass('active');
    //     if($('.bargain-order-table').hasClass('active')){                   
    //         $(this).html('Show <i class="fas fa-chevron-up"></i>');
    //         $(this).parents('.customer-head').next('.table').hide();
    //     }
    //     else {
    //         $(this).parents('.customer-head').next('.table').show();
    //         $(this).html('Hide <i class="fas fa-chevron-down"></i>');
    //     }
    // });

    $('.bargain-order-table .customer-head .del-action a').on('click',function(){ 
        //alert(1);          
        $(this).find('i').toggleClass('fa-chevron-up');
        $(this).parents('.customer-head').toggleClass('active');
        $(this).parents('.bargain-order-table').find('.table').toggle();
    });


});















