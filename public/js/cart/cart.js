/*
*@desc : Cart Modules used to handle cart page action
*@author : Smoothgraph Connect Pvt. Ltd
*@created : 28-june-2019
*/

//global variable section 

(function cartModule($) {
    
    /*********
    *@desc : Quantity model handle qunatity related action 
            1. increase/decrease quantity 
            2. change quantity
            3. remove/delete product from product list
    ***********/
        var ship_method = $('input[name="ship_method"]:checked').val();
        if(ship_method=='3'){
            var shipId = $('#dd_shipping').val();
            if(shipId){
                getDeliveryFee(shipId);
            }else{
                $('#shipping_address').html('');
            } 
        }
        
        

    (function Quantity(){
        //event 
        $(document).on('click', '.increase', function(){
            quantityHandler($(this), 'increase');
        });
        $(document).on('click', '.decrease', function(){
            quantityHandler($(this), 'decrease')
        });
        $(document).on('change', 'input.spinNum', function(){
             quantityHandler($(this), 'change');
        });
        $(document).on('click', '.cart-remove', function(evt){
            quantityHandler($(this), 'removecartproduct');
        });

        //handler 
        function maxQuantity(qty, maxQty, flag) {     
            if(flag === 'increase' || flag === 'change') return (parseInt(qty)<parseInt(maxQty) || false);
            else if(flag === 'decrease') return (parseInt(qty)>1 || false);
        };

        function update(data) {
            return new Promise((resolve, reject) => {
                callAjaxRequest(updateCart,"post",data,result=>{resolve(result)});
            });
        };

        function quantityHandler($that, flag) {  
            let $input = $that.parent('.spiner').find('input.spinNum');
            let $prd_total_price = $that.parents('ul').find('li label.prd-total-price');
            let $prd_unit_price = $that.parents('ul').find('li label.prd-unit-price');          
            let data = {
                cartId: $that.parent('.spiner').data('cartid'),
                quantity: parseInt($input.val()),
            };
            switch (flag) {
                case 'increase':  
                case 'decrease':  
                case 'change' :                     
                    if(flag === 'increase') data.quantity = (parseInt($input.val()) + 1);
                    else if(flag === 'decrease' && $input.val() && parseInt($input.val())>1)  data.quantity = (parseInt($input.val()) - 1);
                    else if(flag === 'change'){
                        //in case user change quantiy max then allowed
                        if(parseInt($input.val())> parseInt($input.attr('max'))){     
                            var newerr = error_msg.max_quantity + '  '+$input.attr('max');                       
                            
                            showSweetAlertError( newerr);  
                            //$input.val(1);                           
                        }/*in case user set quantity zero or null*/
                        else if(!$input.val() || $input.val() == '0'){
                            showSweetAlertError(error_msg.quantity_blank_zero);
                            $input.val(1);
                        } 
                        data.quantity = parseInt($input.val());
                    }             
                                        
                    if(maxQuantity($input.val(), $input.attr('max'), flag)){
                        update(data)
                        .then(resp=>{
                            if(resp && resp.status == "success"){
                                let val = (flag === 'increase') && (parseInt($input.val()) + 1) ||  (flag === 'decrease') && (parseInt($input.val()) - 1) 
                                            || (flag === 'change') && $input.val();
                                $input.val(val);
                                $('#tot_order_amount').text(resp.ordAmount);
                                $('#tot_order_qty').text(resp.totQty);
                                resp.tot_prd_price && $prd_total_price.text(resp.tot_prd_price);
                                resp.product_price && $prd_unit_price.text(resp.product_price);
                            }else{
                               showSweetAlertError(resp.msg);
                            }                                
                        }, err=>{
                           showSweetAlertError(error_msg.server_error);
                        }); 
                    }                                               
                    break;
                case 'removecartproduct' : 
                    let ul_id = $that.closest('ul').attr('id');
                    data.cartId = ul_id.replace('cart_', '');
                    swal({
                        title: error_msg.txt_delete_confirm,
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonText: error_msg.yes_delete_it,
                        cancelButtonText: error_msg.txt_no,
                        closeOnConfirm: true,
                        closeOnCancel: true,
                    }).then(rep =>{
                        callAjaxRequest(removeCart, 'post', data, result=>{
                            if(result.status=='success'){
                                swal(lang_success, result.msg, "success").then(function(){
                                    location.reload();
                                });
                                $('#tot_order_qty').text(result.totQty);
                                $('#tot_order_amount').text(result.ordAmount);
                                $('#tot_cart_items').text(result.cart_item);
                                $('#tot_cart_items_cart').text(result.cart_item);
                                $('#tot_prd_noti').text(result.cart_item);
                                $('#'+ul_id).remove();
                            }else{
                               showSweetAlertError(result.msg);
                            }
                        });
                    }, er=>{
                        //error code 
                    });
                    break;
            }
        };  
    })();  

    /*******
    *@desc : Buy now and selection model handle bunow & selection action
            1. buynow 
            2. select all product 
            3. select/ de-select product 
    *********/
    (function buyNowAndSelection(){
        //event
        $(document).on('click', '.buynow', function(){
            buyNowHandler($(this), 'buynow');
        });
        $(document).on('change', '.checkwrap-sel-all input[type="checkbox"]', function(evt){
            buyNowHandler($(this),'select_all');
        });
        $(document).on('change', '.table-content ul input[type="checkbox"]', function(evt){
            buyNowHandler($(this),'prd_select_change');
        });
        $(document).on('click', '.all_pay_credit', function(){
            buyNowHandler($(this), 'all_pay_credit');
        });

        //function 
        function buyNowHandler($that, flag){
            switch(flag){
                case 'buynow':
                    let $checkedPrdList = $('.table-content ul input[type="checkbox"]:checked');
           
                    if($checkedPrdList.length ===0){
                        showSweetAlertError(error_msg.buynow_ckeck);
                        return;
                    } 

                    let data = [];
                    $.each($checkedPrdList, function(){
                        data.push({
                            'cartId' : $(this).parents('ul').find('li .spiner').data('cartid'),
                            'quantity' : $(this).parents('ul').find('li .spiner input').val(),
                        });
                    });
                    //cart action after buy now
                    try{
                      swal({
                          title : error_msg.buynow_title,
                          showCloseButton: true,
                          showCancelButton : true,
                          showConfirmButton : true,
                          cancelButtonText : error_msg.buynow,
                          confirmButtonText : error_msg.end_shopping,
                          confirmButtonColor : '#004CFF',
                          cancelButtonColor : '#CE232A',
                      }).then(res=> {
                          if(res) {
                                callAjaxRequest(payProduct, 'post', {'data': JSON.stringify(data),'type':'end_shopping'}, function(response){
                                    if(response && response.status === 'success') window.location.href = response.url;
                                    else {
                                        if(response.type=='price'){
                                            $('#cart_'+response.cart_id).css("background-color","yellow");
                                            $('#cart_'+response.cart_id+' li.price_li').append('<br><a href="javascript:;" class="update_cart_price text-primary">'+error_msg.update_price+'</a>')
                                        }else{
                                            showSweetAlertError(response.msg);
                                            $('#cart_'+response.cart_id).append('<p class="error">'+response.msg+'</p>')
                                        }
                                    }
                                    
                                });
                          }
                      }, rej=>{
                          if(rej && rej === 'cancel') {
                                callAjaxRequest(payProduct, 'post', {'data': JSON.stringify(data),'type':'buynow'}, function(response){
                                    if(response && response.status === 'success') window.location.href = response.url;
                                    else showSweetAlertError(response.msg);
                                    $('#cart_'+response.cart_id).append('<p class="error">'+response.msg+'</p>')
                                });
                          }
                      });
                    }catch(er){
                        console.log;
                    };                    
                    break;
                case 'select_all' :                     
                    let $checkedPrdLists = $('.table-content ul input[type="checkbox"]');
                    $.each($checkedPrdLists, function(){
                        if($that.is(':checked')) $(this).prop('checked', true);
                        else $(this).prop('checked', false);
                    });
                    break;
                case 'prd_select_change': 
                    //in case all select is checkd then user uncheck any product then uncheck select all (check box)
                    if(!$that.is(':checked') && $('.checkwrap-sel-all input[type="checkbox"]').is(':checked'))
                        $('.checkwrap-sel-all input[type="checkbox"]').prop('checked', false);
                    //in case product is checked then check rest all is checked if yes then checked all select box 
                    else if($that.is(':checked') && ($('.table-content ul input[type="checkbox"]:checked').length === $('.table-content ul').length))
                        $('.checkwrap-sel-all input[type="checkbox"]').prop('checked', true);
                    break;
                case 'all_pay_credit':
                    let $checkPrdList = $('.table-content ul input[type="checkbox"]:checked');
                    let act = $that.data('action');

                    if(!act && $checkPrdList.length ===0){
                        showSweetAlertError(error_msg.buynow_ckeck);
                        return;
                    }else if(act === 'single_credit'){
                       $checkPrdList = $that.parents('ul').find('input[type="checkbox"]');
                    } 

                    let data_credit = [];
                    $.each($checkPrdList, function(){
                        data_credit.push({
                            'cartId' : $(this).parents('ul').find('li .spiner').data('cartid'),
                            'quantity' : $(this).parents('ul').find('li .spiner input').val(),
                        });
                    });
                    swal({
                        title : error_msg.pay_cerdit,
                        type : 'warning',
                        confirmButtonText:lang_ok,
                        cancelButtonText:lang_cancel,
                        showCloseButton : true,
                        showConfirmButton : true,
                        showCancelButton: true,
                    }).then(res=>{
                        //cart action after buy now
                        callAjaxRequest(payProduct, 'post', {'data': JSON.stringify(data_credit),'type':'all_credit'}, function(response){
                            if(response && response.status === 'success') {
                                swal(lang_success, response.msg, "success")
                                .then(res=>{
                                    window.location.href = response.url || ""
                                });
                            }
                            else {
                                if(response.type=='price'){
                                    $('#cart_'+response.cart_id).css("background-color","yellow");
                                    $('#cart_'+response.cart_id+' li.price_li').append('<br><a href="javascript:;" class="update_cart_price text-primary">'+error_msg.update_price+'</a>')
                                }else{
                                    showSweetAlertError(response.msg);
                                }
                                
                            }
                        });
                    }, rej=>{
                        console.log;
                    });
                    break;
            };            
        };
    })();

    $('body').on('click','.update_cart_price',function(e){

        callAjaxRequest(updateCartPrice, 'post', {}, function(response){
            location.reload();
        });
    });

    $('body').on('click', ".sel-pay-method ul li", function(){
        if (jQuery(this).find('input[type="radio"]').is(':checked')) {
            jQuery('.sel-pay-method ul li').removeClass('active');          
            jQuery(this).toggleClass('active');         
        }
        
    });

    jQuery('body').on('click', '#shipTab li', function (e) {
        $(this).closest('li').find('input[type="radio"]').prop('checked','checked');    
    });

    jQuery('body').on('click', '#btn_checkout', function(e){
        //check payment method selection 
        if($('input[name=payment_method]:checked').length && !$('input[name=payment_method]:checked').parents('li').hasClass('active')){
            $('input[name=payment_method]:checked').prop('checked', false);
        }
        var error_str = '';
        if(checkout_type !='buy-now'){

            var ship_method = $('input[name=ship_method]:checked').val();
            //console.log(ship_method);
            if(typeof ship_method == 'undefined'){
                $('#e_ship_method').html(error_msg.select_shipping);
                error_str += '<p class="error">'+error_msg.select_shipping+'</p>';
            }else{
                if(ship_method == '1' || ship_method == '2' || ship_method == '3'){

                    $('#e_ship_method').html('');
                    if(ship_method == '3'){
                        var shipping_address = $('select[name=ship_address]').val();
                        var billing_address = $('select[name=bill_address]').val();
                        
                        if(shipping_address == '' || typeof shipping_address == 'undefined'){
                            $('#e_ship_address').html(error_msg.select_shipping_address);
                            error_str += '<p class="error">'+error_msg.select_shipping_address+'</p>';
                        }else{
                            $('#e_ship_address').html('');
                        }

                        if(billing_address == '' || typeof billing_address == 'undefined'){
                            $('#e_bill_address').html(error_msg.select_billing_address);
                            error_str += '<p class="error">'+error_msg.select_billing_address+'</p>';
                        }else{
                            $('#e_bill_address').html('');
                        }
                    }else{
                        var phone_no = $('#phone_no').val();
                        if(phone_no == '' || typeof phone_no == 'undefined'){
                            $('#e_phone_no').html(error_msg.enter_phone_no);
                            error_str += '<p class="error">'+error_msg.enter_phone_no+'</p>';
                        }else{
                            $('#e_phone_no').html('');
                        }
                    }
                    
                }else{
                    $('#e_ship_method').html(error_msg.select_shipping_error);
                    error_str += '<p class="error">'+error_msg.select_shipping_error+'</p>';
                }
            }
        }
        
        var pickup_time = $('select[name=pickup_time]').val();
        
        if(pickup_time == '' || typeof pickup_time == 'undefined'){
            $('#e_pickup_time').html(error_msg.select_pickup_time);
            error_str += '<p class="error">'+error_msg.select_pickup_time+'</p>';
        }else{
            $('#e_pickup_time').html('');
        }
        var check_pay_method = $('#check_pay_method').val();
        if(check_pay_method == 1){
            var payment_method = $('input[name=payment_method]:checked').val();
            //console.log(payment_method);
            if(payment_method == '' || typeof payment_method == 'undefined'){
                
                $('#e_payment_method').html(error_msg.select_payment);
                error_str += '<p class="error">'+error_msg.select_payment+'</p>';
            }else{
                $('#e_payment_method').html('');
            }
        }
        
        if(error_str != ''){
            showSweetAlertError(error_str);     
            return false;
        }else{
            $("#btn_checkout").prop("disabled", true);
            var formAction = $('#checkout_form').attr('action');
            var form = $('#checkout_form').serialize();
            callAjaxRequest(formAction,'post',form,function(response){
                if(response.status == "success"){
                    window.location.href=response.url;
                }
                else if(response.status == "fail"){
                    if(response.validation == true){
                        var error = '';
                        $.each(response.msg, function(key,val){
                            error +='<p class="error">'+val+'</p>'
                            $('#e_'+key).html(val);
                        });
                        $("#btn_checkout").prop("disabled", false);
                        showSweetAlertError(error);
                    }else if(response.type=='price'){
                        $('#cart_'+response.cart_id).css("background-color","yellow");
                        $('#cart_'+response.cart_id+' li.price_li').append('<br><a href="javascript:;" class="update_cart_price text-primary">'+error_msg.update_price+'</a>')
                        $("#btn_checkout").prop("disabled",false);
                        showSweetAlertError(response.msg);
                    }else if(response.type=='pickup_time'){
                        $("#btn_checkout").prop("disabled", false);
                        swal({
                            type: 'error',
                            text: response.msg,
                            confirmButtonText: lang_ok,
                        }).then(function () {
                            location.reload();
                        });
                    }else{
                        $("#btn_checkout").prop("disabled", false);
                        showSweetAlertError(response.msg);
                    }
                }else{
                    $("#btn_checkout").prop("disabled", false);
                    showSweetAlertError(response.msg);
                }
            });
        }
    });

    // when user add shipping/billing address
    $('body').on('click','.add_address',function() {

        var address_type = $(this).prev('select').attr('name');
        //alert(address_type);return;

        var ajax_url = address_form_url;
        var data = {call_type:'ajax_data', 'address_type':address_type};

        callAjaxRequest(ajax_url, 'get', data, function(result) {

            $('#popupdiv').html(result);           
        });
    });

    $('body').on('click','#pick_up_at_center,#pick_up_at_the_store',function(event) {
        getDeliveryFee();
    });
    // when user change shipping address
    $('body').on('change','#dd_shipping',function(event) {
        var shipId = $(this).val();
        if(shipId){
            getDeliveryFee(shipId);
        }else{
            $('#shipping_address').html('');
        }
    });

    // when user change billing address
    $('body').on('change','#dd_billing',function(event) {
        var billId = $(this).val();
        if(billId){
            callAjaxRequest(change_bill_address,"post",{billId:billId},function(response) {
                
                if(response.status=='success'){
                    $('#billing_address').html(response.billVal);       
                }else{
                    $('#billing_address').html('');
                }
            });
        }else{
            //$('#shipping_address').html('');
        }
    });

    jQuery('body').on('click', '#delivery_at_the_address', function (e) {
        var shipId = $("#dd_shipping").val();
        if(shipId){
            getDeliveryFee(shipId);
        }else{
            $('#shipping_address').html('');
        }
    });


})(jQuery);

function SubmitCartAddressForm() {

    var ajax_url = save_address_url;
    var data = $("#addess_frm").serialize();

    callAjaxRequest(ajax_url, 'POST', data, function(response) {
        response = JSON.parse(response);
        if(response.status == 'success'){
            $('#dd_shipping').append(response.shipdd);
            $('#dd_billing').append(response.billdd);
            if(response.shipVal)
                $('#shipping_address').html(response.shipVal);
            if(response.billVal)
                $('#billing_address').html(response.billVal);

            $('#add-address').modal('hide');

            var shipId = $('#dd_shipping').val();
            
            if(shipId){
                getDeliveryFee(shipId);
            }else{
                $('#shipping_address').html('');
            }
        }
        else if(response.status == 'validate_error'){
            $('.error-msg').text('');
            $.each(response.message, function(key,val){
                $('#error_'+key).text(val);
            });
        }          
    });  
}

$(document).on('change','#dd_logistic',function(e){
    var val = $(this).val();
    alert(val);
    var data = {val:val,tot_delivery_time:tot_delivery_time};
    callAjaxRequest(pickup_time_url,"get",data,function(result) {
        console.log(result);
    });
});

$(document).ready(function(){
    //shipping type click 
    $('.ship-method-list').click(function(e){
        makeOptionTempkae($(this).attr('href'));
    });
    // on load get shipping type 
    $('.ship-method-list').each(function(){
        if($(this).hasClass('active'))
            makeOptionTempkae($(this).attr('href'));
    });
});



function makeOptionTempkae(flag){
    let optHtml = '';
    (getData()).map(o=>{
        optHtml+='<option value='+o.key+'>\
                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">'+o.val+'</font></font>\
            </option>'
    });
    $('#pickup_time :not(:first-child)').remove();
    $('#pickup_time').append(optHtml);
    function getData(){
        if(flag == '#select-address') return delivery_time_arr['buyer_address'];
        else if(flag == '#shop_address') return delivery_time_arr['shop_address'];
        else if(flag == '#pick_up_center')return delivery_time_arr['pickup_center'];
    };
};

function getDeliveryFee(shipId){
    if(shipId===undefined){
        var data = {shipId:''};
        $('#user_phone_no_div').show();
    }else{
        var data = {shipId:shipId};
        $('#user_phone_no_div').hide();
    }

    callAjaxRequest(change_ship_address,"post",data,function(result) {
        var response = jQuery.parseJSON(result); 
        if(response.status=='success'){
            //alert(response.shipVal);
            console.log(response);
            if(response.shipping_fee==='false'){
                $('#delvery_fee_div').html('');
            }else{
                var shipping_fee = response.shipping_fee;
                shipping_fee = parseFloat(shipping_fee.replace(',', ''));
                if(shipping_fee > 0){
                    var delivery_fee_html = "<div class=\"row border-bottom\"><span class=\"col-6\">"+error_msg.shipping_fee+"</span><span class=\"col-6\"><span id=\"tot_ship_amount\">"+response.shipping_fee+"</span> "+error_msg.currency+"</span></div>"; 
                }else{
                    var discount_fee = response.discount_fee;
                    discount_fee = parseFloat(discount_fee.replace(',', ''));
                    if(discount_fee > 0){
                        var delivery_fee_html = "<div class=\"row border-bottom\"><span class=\"col-6\">"+error_msg.shipping_fee+"</span><span class=\"col-6\"><span id=\"tot_ship_amount\">"+response.discount_fee+"</span> "+error_msg.currency+"</span></div><div class=\"row border-bottom\"><span class=\"col-6\">"+error_msg.discount_shipping_fee+"</span><span class=\"col-6\"><span id=\"tot_ship_amount\"> - "+response.discount_fee+"</span> "+error_msg.currency+"</span></div>";
                    }else{
                        $('#delvery_fee_div').html('');
                    }
                    //var delivery_fee_html = "<div class=\"row border-bottom\"><span class=\"col-6\">"+error_msg.shipping_fee+"</span><span class=\"col-6\"><span id=\"tot_ship_amount\">"+response.discount_fee+"</span> "+error_msg.currency+"</span></div><div class=\"row border-bottom\"><span class=\"col-6\">"+error_msg.discount_shipping_fee+"</span><span class=\"col-6\"><span id=\"tot_ship_amount\"> - "+response.discount_fee+"</span> "+error_msg.currency+"</span></div>";
                    
                }
                $('#delvery_fee_div').html(delivery_fee_html);
            }
            if(response.totAmt > 0){
                $('#payment_method_div').show();
                $('#check_pay_method').val(1);
            }else{
                $('#payment_method_div').hide();
                $('#check_pay_method').val(0);
            }
            $('#e_ship_address').html('');
            $('#shipping_address').html(response.shipVal);
            $('#tot_order_amount').html(response.total_amount);          
        }
    });
}

/*$(document).on('change','input[name="ship_method"]',function(e){
    var val = $(this).val();
    console.
    if(val !='3'){
        $('#user_phone_no_div').hide();
    }else{
        $('#user_phone_no_div').show();
    }
});*/
