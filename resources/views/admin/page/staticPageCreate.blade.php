@extends('layouts/admin/default')

@section('title')
    @lang('cms.create_page')
@stop

@section('header_styles')

<!--page level css -->

<!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @endif 
        <form id="cmsForm" action="javascript:void(0)" method="post" class="form-horizontal form-bordered" novalidate="novalidate">
            <div class="header-title">
                <h1 class="title">@lang('cms.create_page')</h1>
                <div class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Page\StaticPageController@index') }}"><span><</span>@lang('common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn btn-secondary">@lang('common.save_and_continue')</button>                    
                    <button type="submit" name="submit_type" value="submit" class="btn btn-save btn-success">@lang('common.save')</button>
                </div>
            </div>       
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('cms')!!}
                    </ul>
                </div>
                <div class="content-left">
                    <div class="tablist">
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a class="nav-link show active" data-toggle="tab" data-target="#page-details" class="active">General</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#seo-details">SEO</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#blogSocilashare">@lang('cms.page_socialshare')</a></li>
                        </ul>
                    </div>
                </div>
                <div class="content-right container">
                    <div class="tab-content">
                        <div id="page-details" class="tab-pane fade show active">
                            <h2 class="title-prod">General</h2>
                            {{ csrf_field() }}
                            <div class="form-group row">
                                <div class="col-md-5">
                                <label for="form-text-input">@lang('cms.url_key') <i class="strick">*</i></label>
                                    <input type="text" name="cms_url" value="">
                                    <p class="error" id="url"></p>
                                    
                                </div>
                            </div> 
                            <div class="form-group row">
                                <div class="col-md-5">
                                    <label for="form-text-input">@lang('common.status') <i class="strick">*</i></label>
                                    <select class="select" name="status">
                                        <option value="1">@lang('common.enable')</option>
                                        <option value="0">@lang('common.disable')</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-5">
                                    <label for="form-text-input">@lang('common.header_footer') <i class="strick">*</i></label>
                                    <select class="select" name="header_footer">
                                        <option value="0">@lang('common.not_visible')</option>
                                        <option value="2">@lang('common.visible_only_desktop')</option>
                                        <option value="3">@lang('common.visible_only_mobile_app')</option>
                                        <option value="1">@lang('common.visible_both_desktop_and_mobile_app')</option>
                                    
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-8">
                                    {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'page_title', 'label'=>'Title <i class="strick">*</i>', 'errorkey'=>'page_ttl'], ['field'=>'textarea', 'name'=>'page_desc', 'label'=>'Description <i class="strick">*</i>', 'errorkey'=>'page_description', 'cssClass'=>'froala-editor-apply']], '3', $errors) !!}
                                    <p class="error" id="page_description"></p>
                                </div>
                            </div>        
                        </div>
                        <div id="seo-details" class="tab-pane">
                            <h2 class="title-prod">SEO</h2>
                            <div class="form-group row">
                                <div class="col-md-8">
                                <label for="form-text-input">@lang('common.meta_title') <i class="strick">*</i></label>
                                    {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'meta_title', 'label'=>'Title', 'errorkey'=>'meta_title'], ['field'=>'text', 'name'=>'meta_keyword', 'label'=>'Keyword', 'errorkey'=>'meta_keyword'], ['field'=>'textarea', 'name'=>'meta_desc', 'label'=>'Description', 'errorkey'=>'meta_desc']], '4', $errors) !!}
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label>@lang('cms.meta_image')</label>
                                    <div class="thumb-image-upload file-wrapper">
                                        <input type="file" accept=".png, .jpg, .jpeg" class="file-upload" name="metaimage"> 
                                        <img class="upload-img" src="{{ Config::get('constants.image_url') }}file-upload.jpg" width="360" height="230">
                                        @if($errors->has('metaimage'))
                                            <p class="error error-msg">{{ $errors->first('metaimage') }}</p>
                                        @endif
                                    </div>                                    
                                </div>
                            </div>
                        </div>
                         <div id="blogSocilashare" class="tab-pane">
                            <h2 class="title-prod">@lang('cms.page_socialshare')</h2>
                            <div class="blog-share">
                                <ul class="nav nav-tabs listing-nav-tabs">
                                    <li class="nav-item"><a class="nav-link show active" data-toggle="tab" href="#seo-facebook"><i class="icon-facebook"></i></a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#seo-twitter"><i class="fab fa-twitter"></i></a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#seo-instagram"><i class="fab fa-instagram"></i></a></li>
                                </ul>
                                <div class="tab-content listing-tab">
                                    <div id="seo-facebook" class="tab-pane fade show active">
                                        <div class="form-group row no-star">
                                            <div class="col-sm-8">
                                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'fbtitle', 'label'=>'Facebook Title', 'errorkey'=>'fbtitle'],['field'=>'textarea', 'name'=>'fbdesc', 'label'=>'Facebook Description', 'errorkey'=>'fbdesc']], '3', $errors) !!}
                                            </div>                                     
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-6">
                                                <label>@lang('blog.blog_fb_image')</label>
                                                <div class="thumb-image-upload file-wrapper">
                                                    <input type="file" accept=".png, .jpg, .jpeg" class="file-upload" name="fbimage"> 
                                                    <img class="upload-img" src="{{ Config::get('constants.image_url') }}file-upload.jpg" width="360" height="230">
                                                    @if($errors->has('fbimage'))
                                                        <p class="error error-msg">{{ $errors->first('fbimage') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>                               
                                    </div>
                                    <div id="seo-twitter" class="tab-pane fade in">
                                        <div class="form-group row no-star">
                                           <div class="col-sm-8">
                                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'twtitle', 'label'=>'Twitter Title', 'errorkey'=>'twtitle'],['field'=>'textarea', 'name'=>'twdesc', 'label'=>'Twitter Description', 'errorkey'=>'twdesc']], '4', $errors) !!}                                        
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-6">                                   
                                                <div class="thumb-image-upload file-wrapper">
                                                    <label>@lang('blog.blog_tw_image')</label>
                                                    <input type="file" accept=".png, .jpg, .jpeg" name="twimage" class="file-upload">  
                                                    <img class="upload-img" src="{{ Config::get('constants.image_url') }}file-upload.jpg" width="360" height="230"> 
                                                    @if($errors->has('twimage'))
                                                        <p class="error error-msg">{{ $errors->first('twimage') }}</p>
                                                    @endif                                     
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="seo-instagram" class="tab-pane fade in">
                                        <div class="form-group row no-star">
                                            <div class="col-sm-8">
                                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'institle', 'label'=>'Instagram Title', 'errorkey'=>'institle'],['field'=>'textarea', 'name'=>'insdesc', 'label'=>'Instagram Description', 'errorkey'=>'insdesc']], '5', $errors) !!}                                         
                                            </div>
                                        </div>
                                        <div class="form-group thumb-image-upload file-wrapper">
                                            <label>@lang('blog.blog_ins_image')</label>
                                            <input type="file" name="insimage" accept=".png, .jpg, .jpeg" class="file-upload"> 
                                            <img class="upload-img" src="{{ Config::get('constants.image_url') }}file-upload.jpg" width="360" height="230"> 
                                            @if($errors->has('insimage'))
                                                <p class="error error-msg">{{ $errors->first('insimage') }}</p>
                                            @endif                                       
                                        </div>
                                    </div>
                                </div>
                            </div>                          
                        </div>
                    </div>                                                  
                </div>
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
<script src="{{ Config('constants.page_js_url') }}validateCMS.js" type="text/javascript"></script>
<!-- end of page level js --> 

@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
   var lang_id = "{{Session::get('admin_default_lang')}}";

</script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>
<script src="{{ Config('constants.js_url') }}jquery.validate.min.js" type="text/javascript"></script>
<script type="text/javascript">
    (function($){
        var rules= {};            
            rules['page_title['+lang_id+']'] = 'required';
            rules['page_desc['+lang_id+']'] = 'required';
            //rules['cms_url'] = 'required';            
        var messages = {};
            messages['page_title['+lang_id+']'] = "@lang('cms.title_is_required')";
            messages['page_desc['+lang_id+']'] = "@lang('cms.description_is_required')";
           // messages['cms_url'] = "@lang('cms.url_is_required')";       

        $("#cmsForm").validate({
            rules: rules,
            messages: messages,
            submitHandler : function(from, evt){               
                var formData = new FormData($('#cmsForm')[0]);
                $.ajax({
                    type: "POST",
                    url : "{{ action('Admin\Page\StaticPageController@store') }}",
                    enctype: 'multipart/form-data',
                    processData: false,  // Important!
                    contentType: false,
                    cache: false,
                    data : formData,
                    success : function(response){
                        //console.log(result);return false;
                        $('p[class="error"]').html('');
                        if(response.status=='fail'){
                        
                            $.each(response.message, function(key,val){
                              $('p[id='+key+']').text(val);
                            });
                            return false;
                        }                   
                        if(response && response.status === 'success') //
                            {
                                toastr.options.positionClass = 'toast-top-right';
                            _toastrMessage('success', "{{ Lang::get('common.records_added_successfully') }}");
                            setTimeout(function() {
                                window.location.href = response.url;
                            }, 1000);

                            }
                    },
                });
            },
        });
    })(jQuery);
</script>       
@stop
