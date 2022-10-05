jQuery(document).ready(function($){

    $('body').on('click','#add_user_address',function(){

        var ajax_url = create_address_url;
        var data = {call_type:'ajax_data'};

        callAjaxRequest(ajax_url, 'get', data, function(result){

            $('#popupdiv').html(result);           
        });
    });

    $('body').on('change','.address_dd',function(){    
        dropDownHandler($(this).val(), $(this).attr('name'), $(this).attr('address_seq'));
    });

    function updateCompAddr(){
        
        var txt_address = $('input[name="address"]').val()?$('input[name="address"]').val()+' ':'';
            var input_val = '';
            $.each( txt_addr, function( key, value ){
                input_val = $('input[name="'+key+'"]').val();
                if(input_val){
                    txt_address += input_val+' ';
                }
            })
            $('#company_detail').slideDown();
            if(txt_address){
                
                $('#company_address').val(txt_address);
            }
    }

    $('body').on('change','#tax_invoice',function(){    
        var isChecked = $(this).is(':checked');
        if(isChecked === true) {
            updateCompAddr();
            $('#same_as_address').prop('checked',true);
        }
        else {
            $('#company_detail').slideUp();
        }
    });

    $('body').on('click','#company_address',function(e){
        if($.trim($(this).val())== ''){
            updateCompAddr();
        }
    })

    $('body').on('change','#same_as_address',function(){    
        var isChecked = $(this).is(':checked');
        if(isChecked === true) {
            //$('#company_address').text($('#address').val());
            updateCompAddr();
        }
        else {
            $('#company_address').text('');
            $('#company_address').val('');
        }
    });         

    //Make element draggable
    $("#sortable .drag").draggable({
        helper: 'clone',
        handle : '.ui-draggable-handle',
        cursor: 'move',
        revert: true,
        revertDuration: 0,
    });

    //Make element sortable
    $("#sortable").sortable({

        items: "li:not(:first)",
        revert: true,
        //containment: "parent",
        cursor: 'move',
        update: function (event, ui) {

            var order = $(this).sortable('toArray');
            var ajax_url = sort_address_url;
            var data = {sequence:order};

            callAjaxRequest(ajax_url, 'post', data, function(response){
                if(response == 'success'){
                    swal(lang_json.success,lang_json.order_updated_successfully,'success')
                    .then(function () {
                        //setTimeout(function(){ location.reload(); }, 2000);
                    });                     
                } 
            });           
        }
    }).disableSelection();
});

function SubmitAddressForm() {
    
    var ajax_url = store_address_url;
    var data = $("#addess_frm").serialize();

    callAjaxRequest(ajax_url, 'POST', data, function(response){
        response = JSON.parse(response);
        if(response.status == 'success'){
           swal(lang_json.success, response.message, 'success')
           .then(function(){ location.reload(); }); 
        }
        else if(response.status == 'validate_error'){
            $('.error-msg').text('');
            $.each(response.message, function(key,val){
                $('#error_'+key).text(val);
            });
        }          
    });  
}

function deleteAddress(id){
    swal({
        //title: 'Are you sure?',
        text: lang_json.are_you_sure_to_delete_this_record,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: lang_cancel,
        confirmButtonText: lang_json.yes_delete_it

    }).then(function () {

        var ajax_url = delete_url; 
        var data = {action:'delete', id:id};   

        callAjaxRequest(ajax_url, 'post', data, function(response){
            if(response == 'success'){
                swal(lang_json.deleted, lang_json.records_deleted_successfully, 'success')
                .then(function() {location.reload();});                     
            } 
        });
    },function(){
        return false;
    });
}

function setDefault(type, id) {
    if(type == '1'){
        var lang_confirm = lang_json.are_you_sure_shipping;
    }else{
        var lang_confirm = lang_json.are_you_sure_to_billing;
    }
    swal({
        //title: 'Are you sure?',
        text: lang_confirm,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: lang_json.ok,
        cancelButtonText: lang_cancel,

    }).then(function () {

        var ajax_url = set_default_address_url;
        var data = {address_type:type, address_id:id};
      
        callAjaxRequest(ajax_url, 'post', data, function(response){
            if(response == 'success'){
                swal(lang_json.success+'!', lang_json.records_updated_successfully, 'success')
                .then(function () {location.reload();});                     
            } 
        });
    },function(){
        return false;
    });             
}

function dropDownHandler(address_id, address_type, address_seq){  
    var ajax_url = address_dd_url; 
    var data = {address_id:address_id, address_type:address_type};

    callAjaxRequest(ajax_url, 'post', data, function(result){
        var response = jQuery.parseJSON(result);

        if(response.status == 'success'){
            //alert(response.opt_str);
            address_seq = parseInt(address_seq)+1;
 
            $('#address_dd_'+address_seq).html(response.opt_str);                                             
        }

        if(address_type == 'province_state') {
            $('#zip_code').val('');
        }else if(address_type == 'city_district') {
            $('#zip_code').val(response.zip_code);
        }

        cleanChildSelectBox(address_seq);        
    });
};

function cleanChildSelectBox(address_seq){
    var loop_ord = parseInt(address_seq)+1;
    for(i=loop_ord; i<=2; i++) { 
        $('#address_dd_'+i).html('');
    };
}
 