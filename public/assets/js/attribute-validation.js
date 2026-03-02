jQuery(document).ready(function (e) {


if(type == 'variant'){
   jQuery('.manage-value a').show();
   jQuery('.manage-value-next').show();
   jQuery('#attribute_type_value_cont').show();
}


jQuery("select[name='front_input']").change(function (e) {
      var selected = jQuery(this).val();
      if(selected == 'multiselect' || selected == 'select'){
            jQuery('#attribute_type_value_cont').show();
            jQuery('#display_style_cont').show();
            var attribute_type = jQuery("#attribute_type").val();
            if(selected == 'select' && attribute_type == '1'){
                    jQuery('#use_in_varient').show();
            }else{
               jQuery('#use_in_varient').prop('checked', false);
               jQuery('#use_in_varient').hide();

            }
            jQuery('.manage-value .nav-link').show();
            jQuery('.manage-value-next').show();

          }else{
             jQuery('#attribute_type_value_cont').hide(); 
             jQuery('#display_style_cont').hide();
             jQuery('#use_in_varient').prop('checked', false);
             jQuery('#use_in_varient').hide();
             jQuery('.manage-value .nav-link').hide();
             jQuery('.manage-value-next').hide();
              
               
         }


   });


    jQuery(document).on('click', '.select_color_or_image[type="radio"]',function(e){
        
       //alert('hi');
        var currCap = jQuery(this);
        var value = currCap.val();
        if(value == '2'){

          currCap.parent().siblings().children('input[type="file"]').prop('disabled', false);
          currCap.parent().parent().parent().siblings().children().find('input[type="text"]').prop('disabled', 'disabled');

        }else{

           currCap.parent().parent().children().children('input[type="text"]').prop('disabled', false);
           currCap.parent().parent().parent().parent().siblings().find('input[type="file"]').prop('disabled', 'disabled');

        }

        //alert(value);


    })

    jQuery('.manage-value-next').click(function(e){
      jQuery('.tablist ul li').removeClass('active');
      jQuery('.manage-value').addClass('active');
    });
    jQuery('#attribute_type_value').change(function(){
       var val = jQuery(this).val();
       if(val == 'text_color_image'){
         jQuery('.color_picker').show();
       }else{
         jQuery('.color_picker').hide();

       }
    })


        /*jQuery("select[name='attribute_type']").change(function (e) {
              var selected = jQuery(this).val();
              var front_input = jQuery("select[name='front_input'] option:selected").val();

              if(selected == '1' && front_input == 'select'){
                  jQuery('#use_in_varient').show();
              }else{

                  jQuery('#use_in_varient').prop('checked', false);
                  jQuery('#use_in_varient').hide();
                  

              }


        });*/



    jQuery('div.form-group').addClass('form-row');
    jQuery('div.form-row').removeClass('form-group');
    var counter = 1;
    
     /****************/
       jQuery(".actionsClone a.add-clone").click(function (e) {
            e.preventDefault();
            var clone = jQuery(".original").clone(false);
            clone.removeClass('original');
            clone.addClass('cloneData');
            clone.find('img.switherimage').remove();

            var htddenval = clone.find('input#attr_val_id').attr('value');
            /*if(htddenval === undefined){
               //htddenval = '';
            }*/

            var stemp = Math.floor(Date.now())+'_'+Math.floor((Math.random() * 100) + 1);
            clone.find('input[type="hidden"]').attr('value', '');
            clone.find('input[type="radio"]').attr('checked', false);
            //alert(val);
            //clone.find('input[type="radio"]').val(++counter);
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
            clone.find('ul.nav-tabs li:first a').tab('show');
            //clone.find('ul.nav-tabs li:first').tab('show');
            clone.find('div.tab-content div').removeClass('active');
            /**/
            var appendcontentclass = clone.find('div.tab-content .tab-pane');
            appendcontentclass.each(function () {
              jQuery(this).attr('id', href+'_'+i);
              i++;
            });
            clone.find('div.tab-content div:first').removeClass('fade');
            clone.find('div.tab-content div:first').addClass('in active');
            clone.find('div.tab-content div:first').tab('show');

            //color_picker
            clone.find('.color_code').remove();
            clone.find('.mt-5').remove();
            var html = '';
            if(attr_id > 0){
              html += '<div class="form-row color_code"><label>Color Code <i class="red">*</i>'+
              '<div class="input-inline"><label class="radio-wrap"><input class="select_color_or_image" name="select_color_or_image['+stemp+']" type="radio" checked="checked" value="1">'+
              '<span class="radio-label"></span></label><div class="color_picker_2"><input class="form-control" name="color_code['+stemp+']" value="#05abbc" type="text">'+
              '<span class="input-group-addon coloraddon"><i style="background-color: #05abbc;"></i></span></div></div></label>'+
              '</div><div class="mt-5"><label>Color Image<i class="red">*</i></label>'+
              '<div class="input-inline"><label class="radio-wrap"><input class="select_color_or_image" name="select_color_or_image['+stemp+']" type="radio" value="2">'+
              '<span class="radio-label"></span></label><div class="file-wrapper"><span class="add-files"><img src="images/browse-btn3.png" width="38" height="38"></span>'+
              '<input class="form-control" name="color_file['+stemp+']" type="file" disabled="true"></div></div</div>';
            }else{
              html += '<div class="form-row color_code"><label>Color Code <i class="red">*</i>'+
              '<div class="input-inline"><label class="radio-wrap"><input class="select_color_or_image" name="select_color_or_image['+counter+']" type="radio" checked="checked" value="1">'+
              '<span class="radio-label"></span></label><div class="color_picker_2"><input class="form-control" name="color_code['+counter+']" value="#05abbc" type="text">'+
              '<span class="input-group-addon coloraddon"><i style="background-color: #05abbc;"></i></span></div></div></label>'+
              '</div><div class="mt-5"><label>Color Image<i class="red">*</i></label>'+
              '<div class="input-inline"><label class="radio-wrap"><input class="select_color_or_image" name="select_color_or_image['+counter+']" type="radio" value="2">'+
              '<span class="radio-label"></span></label><div class="file-wrapper"><span class="add-files"><img src="images/browse-btn3.png" width="38" height="38"></span>'+
              '<input class="form-control" name="color_file['+counter+']" type="file" disabled="true"></div></div</div>';
             
            }   

            clone.find('.color_picker').append(html);
            clone.find('.color_code .color_picker_2').colorpicker();
            var allvalue = clone.find('input.form-control');
            //allvalue.colorpicker('destroy');
            allvalue.each(function () {
               if(attr_id > 0){
                  jQuery(this).attr('name', jQuery(this).attr('name').replace(htddenval, stemp));
                }else{
                  //jQuery(this).attr('name', jQuery(this).attr('name').replace(htddenval, stemp));
                  //jQuery(this).attr('name', jQuery(this).attr('name').replace('[]', '['+stemp+']'));
                } 
               
                jQuery(this).removeAttr('readonly');


              //jQuery(this).attr('name', jQuery(this).attr('name').replace(htddenval, stemp));
               // jQuery(this).attr('name', jQuery(this).attr('name').replace('['+htddenval+']', '['+stemp+']'));
            });
            clone.find('.position input.form-control').val(0);
            clone.find('.actionsClone').html('<a href="#" class="minus-clone"><i class="icon-close"></i></a>');
            jQuery(".original-group .rows:last").after(clone);
            counter++;
        })
       

    /**************************/
    jQuery(document).on('click', ".cloneData a.minus-clone", function (e) {
        e.preventDefault();
        jQuery(this).parent().parent('.cloneData').remove();
    })

    /*jQuery('.color_code').each(function() {
         jQuery(this).colorpicker();
    });*/


  //jQuery('.color_code input[type="text"]').colorpicker();
  jQuery('.color_code .color_picker_2').colorpicker();

  
 // jQuery('.color_code .input-group-addon').colorpicker();

  var readURL = function(input) {
     //alert(input);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
             //alert(e.target.result);
            jQuery(input).siblings('.add-files').children('img').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
  }

jQuery('body').on('change','.file-wrapper input[type="file"]',function(){

      //alert('hi');
      var file = this.files[0];
      var ext = file.name.split('.').pop().toLowerCase();
      if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
      jQuery(this).val('');
      jQuery(this).siblings('.add-files').children('img').attr('src', '');
        alert('invalid extension!');
        return false;
      }
      var KBsize = (file.size/1024).toFixed(2);
      name = file.name;
      size = file.size;
      type = file.type;
      readURL(this);
     
})

})

