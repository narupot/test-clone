jQuery("a.add-clone").click(function (e) {
    e.preventDefault();
    var stemp = Math.floor(Date.now())+'_'+Math.floor((Math.random() * 100) + 1);
    var clone = '<div class="row cloneData">'+
                    '<div class="col-md-1 form-group"><input type="checkbox" name="checkboxes['+stemp+']" value="" class="form-control checkboxes" placeholder=""></div>'+
                    '<div class="col-md-6" form-group><input type="text" name="sources['+stemp+']" value="" class="form-control sources" placeholder="'+langMsg.source_key+'"></div>'+
                    '<div class="col-md-4" form-group>'+
                        '<button type="button" class="btn singleUpdate">'+langMsg.update+'</button> '+
                        '<a type="button" class="btn btn-delete singleDelete" rev="">'+langMsg.delete+'</a>'+
                    '</div>'+
                    '<div class="col-md-11 alert alert-success updatemessage" style="display: none;"></div>'+
                '</div>'; 
          
    jQuery(".original-group .original:first").before(clone);
});

jQuery(document).on('click', 'button.singleUpdate', function(e){
    e.preventDefault();
    var thiscap = jQuery(this);
    var path = thiscap.parent('div').siblings();
    var sources = $.trim(path.children('input[name^="sources"]').val());
    var sourcesname = path.children('input[name^="sources"]').attr('name');
    var comments = path.children('input[name^="comments"]').val();
    var module_id = jQuery('input#module_id').val();
    //alert('sources: '+sources+', sourcesname: '+sourcesname);

    jQuery('.updatemessage').hide();
    if(sources == '') {
        thiscap.parent().siblings('.updatemessage').removeClass('alert-success').addClass('alert-danger');
        thiscap.parent().siblings('.updatemessage').text(langMsg.please_enter_source_key).show();
        return false;
    }

    jQuery.ajax({

        url: key_update_url,
        type: 'POST',
        data: '_token=' + window.Laravel.csrfToken + '&'+sourcesname+'=' + sources + '&comments=' + comments + '&module_id='+ module_id,
        success: function (result) {

            var response = JSON.parse(result);
            if(response.status == 'success'){
                thiscap.siblings('.singleDelete').attr('rev', response.id);
                thiscap.parent().siblings().children('input.form-control.checkboxes').attr('name', 'checkboxes['+response.id+']');
                thiscap.parent().siblings('.updatemessage').removeClass('alert-danger').addClass('alert-success');
            }
            else {
                thiscap.parent().siblings('.updatemessage').removeClass('alert-success').addClass('alert-danger');
            }
            thiscap.parent().siblings('.updatemessage').text(response.response).show();    
        }
    });
});

jQuery("#allcheckbox").click(function (e) {
    var current = jQuery(this);
    if(current.is(':checked')){
        jQuery('input[type="checkbox"]').prop('checked', 'checked');
    }else{
        jQuery('input[type="checkbox"]').prop('checked', false);
    }
});

jQuery(document).on('click', 'a.singleDelete', function(e){
    e.preventDefault();
    var thiscap = jQuery(this);
    var id = thiscap.attr('rev');

    if(id == '' || id === 'undefined'){
       thiscap.parent().parent('.cloneData').remove();
       return false;
    }

    jQuery.ajax({
        url: key_delete_url,
        type: 'POST',
        data: '_token=' + window.Laravel.csrfToken + '&id=' + id,
        success: function (response) {
            if(response == 'sucess'){
                thiscap.parent().parent('').remove();
            }
        }
    });
});