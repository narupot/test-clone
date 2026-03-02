 (function($){
   "user strict";
   let eventType = 'ontouchend' in document ? 'touchend' : 'click';
   $("#telephone").intlTelInput({
      allowDropdown: false,
      autoHideDialCode: false,
     // initialCountry: $('.wareCountry').children('option:first').attr('data-attr').slice(0, 2),
      nationalMode: true,
      placeholderNumberType: "MOBILE",
      separateDialCode: false,
      utilsScript: "//cdnjs.cloudflare.com/ajax/libs/intl-tel-input/12.0.3/js/utils.js"
   });
   $(document).on(eventType,'#online_store',function(){
      if($(this).is(":checked")===true){
        $('#store_status').children('option:first').prop('selected',true);
        $('#store_status').children('option:not(:first)').hide();
      }else{
         if($('#store_status').children('option').length === 1){
           $('#store_status').children('option:first').after('<option value="0">No</option>');
         }
        $('#store_status').children('option:not(:first)').show();
      }
   }).on(eventType,'.wareCountry',function(argument) {
      if($(this).val()==''){
        $('#inputstate').val('').hide();
        $('#selectstate').show().empty().append('<option value="">Select State</option>');
        return;
      } 
      $("#telephone").intlTelInput("setCountry",$(this).find(':selected').attr('data-attr').slice(0, 2));
      $.ajax({
       url: getProvince,
       type: 'GET',
       dataType: 'json',
       data: {country_id: $(this).val()},
       beforeSend : function(){
          console.log('loding......');
       },
      }).done(function(data) {
          if($.isArray(data) && data.length>0){
              let optHtml ='<option value="">Select State</option>';
              $.each(data,function(index, el) {
                  optHtml+='<option value="'+el.province_state_id+'">'+el.province_state_name+'</option>';
              });
              $('#selectstate').show().empty().append(optHtml);
              $('#inputstate').val('').hide();
          }else{
              $('#selectstate').hide();
              $('#inputstate').show();
          }
      }).fail(function() {
          //$('#selectstate').hide();
          //$('#inputstate').show();
       console.log("error");
      }).always(function() {
       console.log("complete");
      });
   });
   $(document).on('submit','form', function() {
      return true;
   });
})(jQuery);