var counter = jQuery('input[type="radio"]').last().val();
jQuery(".actionsClone a.add-clone").click(function (e) {
        e.preventDefault();
        var clone = jQuery(".original").clone(false);
        clone.removeClass('original');
        clone.addClass('cloneData');
        var htddenval = clone.find('input#attr_val_id').attr('value');

        var stemp = Math.floor(Date.now())+'_'+Math.floor((Math.random() * 100) + 1);
        clone.find('input[type="hidden"]').attr('value', '');
        clone.find('input[type="radio"]').attr('checked', false);
        //alert(val);
        clone.find('input[type="radio"]').val(++counter);
        clone.find('input.form-control').attr('value', '');
        var appendclass = clone.find('ul.nav-tabs li');
        var classtab = 'tablang_'+stemp;
        var href = 'lang'+stemp;
        var i=1;
        /*change tab value*/
        appendclass.each(function () {
            jQuery(this).attr('class', classtab+'_'+i+'_');
            jQuery(this).children('a').attr('href','#'+href+'_'+i);
            i++;
        });

        var i=1;
        clone.find('ul.nav-tabs li:first').tab('show');

        clone.find('div.tab-content div').removeClass('active show');
        /**/
        var appendcontentclass = clone.find('div.tab-content .tab-pane');
        appendcontentclass.each(function () {
            jQuery(this).attr('id', href+'_'+i);
            i++;
        });
        clone.find('ul.nav-tabs li:first a').addClass('active show');
        clone.find('div.tab-content div:first').addClass('active show');
        clone.find('div.tab-content div:first').tab('show');

        var allvalue = clone.find('input.form-control');

        allvalue.each(function () {
            jQuery(this).attr('name', jQuery(this).attr('name').replace(htddenval, stemp)); 
        });

        clone.find('.position input.form-control').val(0);
        clone.find('.actionsClone').html('<a href="#" class="minus-clone"><i class="icon-close"></i></a>');
        jQuery(".original-group .rows:last").after(clone);
})

jQuery(document).on('click', ".cloneData a.minus-clone", function (e) {
    e.preventDefault();
    jQuery(this).parent().parent('.cloneData').remove();
})


function checkValidateType(val){
    if(val == 'text'){
        jQuery('#validation_type_div').show();
    }else{
        jQuery('#validation_type_div').hide();
    }

    if(val == 'droupdown' || val=='checkbox' || val=='radio-button'){
        jQuery('#manage-val').show();
        jQuery('#attr-manage').show();
    }else{
        jQuery('#manage-val').hide();
        jQuery('#attr-manage').hide();
    }
}

jQuery(document).ready(function(e){
    var val = jQuery('select[name=input_type]').val();
    checkValidateType(val);
})

jQuery('select[name=input_type]').change(function(e){
    var val = jQuery(this).val();
    checkValidateType(val);
})