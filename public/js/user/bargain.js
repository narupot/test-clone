$('body').on('click','.addBargin',function(e){
    e.preventDefault();
    var ajax_url = $("#FormBargain").attr('action');
    var data = new FormData($("#FormBargain")[0]);
    callAjaxFormRequest(ajax_url, 'post', data, function(response){
        response = JSON.parse(response);
        if(response.status == 'success'){
            swal({
                type: response.status, 
                title: lang_json.success, 
                text: response.message,
                confirmButtonText : lang_json.ok,
                allowOutsideClick: false
            })
           .then(function(){
                if(response.url == '#'){
                    window.location.href = window.location.href;
                }else{
                    window.location.href = response.url;
                }
            }); 
        }else if(response.status == 'validate_error'){
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
                confirmButtonText: lang_json.ok,
            }); 
        }else if(response.status == 'fail'){
            swal({
                type: 'error',
                text: response.msg,
                confirmButtonColor: '#d33',
                confirmButtonText: lang_json.ok,
            });

        }


    });     

});


$(document).ready(function() {

    $('.increase').on('click',function(){
        var $qty=$('.qtyvalue');       
        var currentVal = parseInt($qty.val());
        if (!isNaN(currentVal)) {
            var cqty = currentVal + 1;
            $qty.val(cqty);
          
            //orignal_unit_price
            var bargainUnit = $('input[name="unit_price"]').val().replace(/,/g,'');
            var totalbaramount = bargainUnit*cqty;
            $('.bargainTotal').text(formatNumberforView(totalbaramount.toString()) + ' ' + currency);
            
            var totalUnitamount = orignal_unit_price*cqty;
            $('.originalTotalPrice').text(formatNumberforView(totalUnitamount.toString())+ ' ' + currency);
        }
    });

    $('.decrease').on('click',function(){
        var $qty=$('.qtyvalue');
        var currentVal = parseInt($qty.val());
        if (!isNaN(currentVal) && currentVal > 0) {
            var cqty = currentVal - 1;
            $qty.val(cqty);
            
            var bargainUnit = $('input[name="unit_price"]').val().replace(/,/g,'');
            var totalbaramount = bargainUnit*cqty;
            $('.bargainTotal').text(formatNumberforView(totalbaramount.toString()) + ' ' + currency);
            
            var totalUnitamount = orignal_unit_price*cqty;
            $('.originalTotalPrice').text(formatNumberforView(totalUnitamount.toString())+ ' ' + currency);

        }
    });


    $('input[name="qty"]').keyup(function(){      
        var cqty = $(this).val();
        var bargainUnit = $('input[name="unit_price"]').val().replace(/,/g,'');
        var totalbaramount = bargainUnit*cqty;
        $('.bargainTotal').text(formatNumberforView(totalbaramount.toString()) + ' ' + currency);
        
        var totalUnitamount = orignal_unit_price*cqty;
        $('.originalTotalPrice').text(formatNumberforView(totalUnitamount.toString())+ ' ' + currency);
    
    });

    $('input[name="unit_price"]').keyup(function(){  
       var cur = $(this).val().replace(/,/g,'');
       var qty = $('input[name="qty"]').val();
       var base_unit_price = cur/weight_per_unit;
       $('input[name="base_unit_price"]').val(formatNumberforView(base_unit_price.toString()));
       var totalbaramount = cur*qty;
       totalbaramount = totalbaramount.toString();
       $('.bargainTotal').text(formatNumberforView(totalbaramount) + ' ' + currency);

    });

    $('input[name="base_unit_price"]').keyup(function(){  
       var cur = $(this).val().replace(/,/g,'');
       var qty = $('input[name="qty"]').val();
       var unit_price = cur*weight_per_unit;
       
       $('input[name="unit_price"]').val(formatNumberforView(unit_price.toString()));
       var totalbaramount = unit_price*qty;
       $('.bargainTotal').text(formatNumberforView(totalbaramount.toString()) + ' ' +currency);
    });

 });




