jQuery(document).ready(function($){

    $('body').on('click','#add_user_address',function(){
  
        $('#icon_plus').hide();
        $('#icon_loader').show();

        var ajax_url = create_address_url;
        var data = {call_type:'ajax_data'};

        callAjax(ajax_url, 'get', data, function(result){

            $('#popupdiv').html(result);
            $('#icon_plus').show();
            $('#icon_loader').hide();            
        });
    });

    $('body').on('change','#country',function(){

        cleanCountryRelatedData();

        var country_id = $(this).val();  
        var ajax_url = country_dtl_url;
        var data = {country_id:country_id};
        //alert(country_id+'===='+ajax_url);
        callAjax(ajax_url, 'post', data, function(result){

            var response = jQuery.parseJSON(result);
            //alert(response.status+'=='+response.isd_code+'=='+response.province_state+'=='+response.city_district+'=='+response.sub_district); 
            if(response.status == 'success'){  
                $('.isd_code').val('+'+response.isd_code);
                $('#province_state_level').text(response.province_state);
                $('#city_district_level').text(response.city_district);
                $('#sub_district_level').text(response.sub_district); 
                if(response.country_code == 'TH') {
                    $('#sub_district_div').removeClass('hide');
                }
                else {
                    $('#sub_district_div').addClass('hide');
                }
            } 
        });
    });

    $('body').on('keyup','.autofill',function(){

        var country_isd_code = $('#country').find('option:selected').attr('isd_code');
        if(country_isd_code == '66') {
            var country_id = $('#country').val();
            var address_type  = $(this).attr('name');  
            var ajax_url = address_fill_url;

            $(".autofill").autocomplete({

                source: address_fill_url+'?country_id='+country_id+'&address_type='+address_type,                                             // data should be in json format
                minLength: 2,
                classes: {
                    "ui-autocomplete": "address-autosearch"
                },
                select: function (event, ui) {
                    event.preventDefault(); 

                    //var str = ui.item.label;
                    var str = ui.item.value;
                    var str_arr = str.split('==>');
                    $('#province_state').val(str_arr['0']);
                    $('#city_district').val(str_arr['1']);
                    $('#sub_district').val(str_arr['2']);
                    $('#zip_code').val(str_arr['3']);
                }                
            }); 
        }
    });    

   //Make element draggable
    $("#sortable .drag").draggable({
      helper: 'clone',
      handle : '.address-icon-hamburger .fa-bars',
      //containment: "window",
      //connectToSortable: '#sortable',
      cursor: 'move',
      revert: true,
      revertDuration: 0,
    });

    //Make element sortable
    $("#sortable").sortable({
      items: "li:not(:first)",
      revert: true,
      //handle :'.glyphicon-menu-hamburger',
      containment: "parent",
      cursor: 'move',
      update: function (event, ui) {
        var order = $(this).sortable('toArray');

        var ajax_url = sort_address_url;
        var data = {sequence:order};

        callAjax(ajax_url, 'post', data, function(response){
            if(response == 'success'){
                swal(
                    langMsg.success+'!',
                    langMsg.status_updated_successfully,
                    'success'
                ).then(function () {
                    //setTimeout(function(){ location.reload(); }, 2000);
                });                     
            } 
        });           
      }
    }).disableSelection();

    //Make element droppable 
    $(".address_drag").droppable({
      accept: '#sortable .drag',
      drop: function( event, ui ) {

        // clone item to retain in original "list"
        var $item = ui.draggable.clone();
        if (!$item.is('.has-drop')) {
          $(this).html($item.addClass('has-drop bggreen'));
          //code to set default
          var ajax_url = set_default_address_url;
          var address_id = $item.attr('data-attr');
          var address_type = $(this).parent('div').attr('data-attr')
          var data = {address_id:address_id, address_type:address_type};
          //alert(address_id+'==='+address_type);
          callAjax(ajax_url, 'post', data, function(response){
            if(response == 'success'){
                swal(
                    langMsg.success+'!',
                    langMsg.status_updated_successfully,
                    'success'
                ).then(function () {
                    location.reload();
                });                     
            } 
          });             
        }
      }
    });
});

function deleteShippingAddress(address_id,id){
  swal({
    //title: 'Are you sure?',
    text: langMsg.are_you_sure_to_delete_this_record,
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: langMsg.yes_delete_it
  }).then(function () {

    var ajax_url = delete_url; 
    var data = {action:'delete', address_id:address_id,id:id};   
    callAjax(ajax_url, 'post', data, function(response){
        if(response == 'success'){
            swal(
                langMsg.deleted,
                langMsg.record_deleted_successfully,
                'success'
            ).then(function () {
                location.reload();
            });                     
        } 
    });
  });
};





function deleteAddress(id){
  swal({
   
    text: langMsg.are_you_sure_to_delete_this_record,
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: langMsg.yes_delete_it
  }).then(function () {

    var ajax_url = delete_url; 
    var data = {action:'delete', address_id:id};   
    callAjax(ajax_url, 'post', data, function(response){
        
        if(response.status == 'success'){
            swal(
                response.status,
                response.message,
                response.status
            ).then(function () {
                location.reload();
            });                     
        } 
    });
  });
};

function SubmitBillingAddressForm(ajax_url) {
    
    var form_data = $("#addess_frm").serialize();
    $.ajax({
        dataType:'json',
        url:ajax_url,
        method:'POST',
        data:form_data,
        success:function(response) {
            
            if(response.status == 'success'){
                   swal(
                        langMsg.success,
                        response.message,
                        'success'
                    ).then(function () {
                        location.reload();
                    }); 
            }
            else if(response.status == 'validate_error'){
                $('.error-msg').text('');
                $.each(response.message, function(key,val){
                    
                    $('#error_'+key).text(val);
                });
            }
        }
    });    
}

//Save Customer details
function SaveCustomera(ajax_url,email){
    var form_data = {'email':email,'_token':csrftoken};
    window.swal({
              //title: "In progress...",
              //text: "Please wait sending mail",
              imageUrl: siteurll+"loader/ajax-loader.gif",
              showConfirmButton: false,
              allowOutsideClick: false
            });
     $.ajax({
        dataType:'json',
        url:ajax_url,
        method:'POST',
        data:form_data,
        success:function(response) {
             $('#load').hide(); 
            if(response=='1'){
                   swal(
                        langMsg.success,
                        response.message,
                        'success'
                    ).then(function () {
                        if(redirect_url!=''){
                            location.href=redirect_url;
                        }else{
                            location.reload();
                        }
                    }); 

            }
            else if(response.status == 'validate_error'){
                $('.error-msg').text('');
                $.each(response.message, function(key,val){
                    
                    $('#error_'+key).text(val);
                });
            }
        }
    });
}



//Save Customer details
function SaveCustomer(ajax_url,redirect_url){
    var form_data = $("#customerForm").serialize();
    $.ajax({
        dataType:'json',
        url:ajax_url,
        method:'POST',
        data:form_data,
        success:function(response) {
            if(response.status == 'success'){
                   swal(
                        langMsg.success,
                        response.message,
                        'success'
                    ).then(function () {
                        if(redirect_url!=''){
                            location.href=redirect_url;
                        }else{
                            location.reload();
                        }
                    }); 

            }
            else if(response.status == 'validate_error'){
                $('.error-msg').text('');
                $.each(response.message, function(key,val){
                    
                    $('#error_'+key).text(val);
                });
            }
        }
    });  

}




function cleanCountryRelatedData() {
    $('#province_state').val('');
    $('#city_district').val('');
    $('#sub_district').val('');    
} 