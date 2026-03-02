
    (function($){
        var rules= {};            
            rules['page_title['+lang_id+']'] = 'required';
            rules['page_desc['+lang_id+']'] = 'required';
            //rules['cms_url'] = 'required';           
        var messages = {};
            messages['page_title['+lang_id+']'] = "@lang('cms.title_is_required')";
            messages['page_desc['+lang_id+']'] = "@lang('cms.description_is_required')";
            //messages['cms_url'] = "@lang('cms.url_is_required')";
        var submiturl = "{{ action('Admin\Page\StaticPageController@store') }}";       

        $("#cmsForm").validate({
            rules: rules,
            messages: messages,
            submitHandler : function(from, evt){               
                var formData = new FormData($('#cmsForm')[0]);
                //callAjaxSavedata(submiturl,formData,function(response)
                callAjaxSavedata(submiturl,formData);

                
            },
        });
    })(jQuery);