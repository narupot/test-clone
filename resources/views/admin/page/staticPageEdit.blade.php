@extends('layouts/admin/default')

@section('title')
    @lang('cms.edit_page')
@stop

@section('header_styles')

<!--page level css -->

<!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        <form id="cmsForm" action="javascript:void(0);" method="post" class="form-horizontal form-bordered" novalidate="novalidate">
            <div class="header-title">
                @if(Session::has('succMsg'))    
                    <script type="text/javascript">               
                        _toastrMessage('success', "{{ Session::get('succMsg') }}");    
                    </script>                             
                @endif  
                <h1 class="title">@lang('cms.edit_page') : @if(isset($page_dtls->staticPageDesc->page_title)) {{$page_dtls->staticPageDesc->page_title}} @else {{'N/A'}} @endif </h1> 
              
                <div class="float-right">                
                    <a href="{{ action('Admin\Page\StaticPageController@index') }}" class="btn btn-back">@lang('common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn static-page-save btn-secondary" data-action="submit_continue">@lang('common.save_and_continue')</button>

                    <button type="submit" class="btn btn-save btn-success">@lang('common.save')</button>
                    
                    <a class="btn btn-primary d-none pr-4" id="revision" href="{{ action('Admin\Page\StaticPageController@pagerevision',$page_dtls->id) }}">@lang('common.revision')<span class="num revision-block">{{ $revision }}</span></a>
                    
                </div>
                                
            </div>
            <div class="content-wrap clearfix">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('cms')!!}
                    </ul>
                </div>
                <div class="content-left">
                    <div class="tablist">
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a class="nav-link show active" data-toggle="tab" data-target="#page-details">General</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#seo-details">SEO</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#blogSocilashare">@lang('page.page_socialshare')</a></li>
                        </ul>
                    </div>
                </div>

                <div class="content-right container">
                    <div class="tab-content">
                        <div id="page-details" class="tab-pane fade show active">
                        <h2 class="title-prod">General</h2>
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                            <div class="form-group row">
                                <div class="col-md-5">
                                    <label for="form-text-input">@lang('cms.url_key') <i class="strick">*</i></label>
                                        <input type="text" name="cms_url" value="{{ $page_dtls->url }}">
                                        <p class="error" id="url"></p>
                                </div>
                            </div> 
                            <div class="form-group row">
                                <div class="col-md-5">
                                    <label for="form-text-input">@lang('common.status') <i class="strick">*</i></label> 
                                    <select class="select" name="status">
                                        <option value="1" @if($page_dtls->status == '1') selected @endif>@lang('common.enable')</option>
                                        <option value="0" @if($page_dtls->status == '0') selected @endif>@lang('common.disable')</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'page_title', 'label'=>Lang::get('cms.title').'<p class="error" id="page_ttl"></p>'.' <i class="strick">*</i>', 'errorkey'=>'page_ttl'], ['field'=>'textarea', 'name'=>'page_desc', 'label'=>Lang::get('cms.description').' <i class="strick">*</i>', 'errorkey'=>'page_description']], '3', 'static_page_id', $page_dtls->id, $tblStaticPageDesc, $errors) !!}
                                    <p class="error" id="page_description"></p>
                                </div>
                            </div>
                        </div>
                        <div id="seo-details" class="tab-pane">
                        <h2 class="title-prod">SEO</h2>
                            <div class="form-group row">
                                <div class="col-md-8">
                                    <label for="form-text-input">@lang('common.meta_title') <i class="strick">*</i></label>
                                    {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'meta_title', 'label'=>Lang::get('cms.title'), 'errorkey'=>'meta_title'], ['field'=>'text', 'name'=>'meta_keyword', 'label'=>Lang::get('cms.keyword'), 'errorkey'=>'meta_keyword'], ['field'=>'textarea', 'name'=>'meta_desc', 'label'=>Lang::get('cms.description'), 'errorkey'=>'meta_desc', 'editor_required'=>'N']], '4', 'static_page_id', $page_dtls->id, $tblStaticPageDesc, $errors) !!}
                                </div>
                            </div>
                            <div class="form-group thumb-image-upload file-wrapper">
                                <label>@lang('blog.meta_image')</label>
                                <img class="upload-img" src="{{getPageSocialshareImageUrl($page_dtls->metaimage)}}" width="360" height="230">                                           
                                <input type="file" name="metaimage" accept=".png, .jpg, .jpeg" class="file-upload">
                                @if($errors->has('metaimage'))
                                    <p class="error error-msg">{{ $errors->first('metaimage') }}</p>
                                @endif
                            </div>
                        </div>

                        <div id="blogSocilashare" class="tab-pane">
                            <h2 class="title-prod">@lang('page.page_socialshare')</h2>
                            <div class="blog-share">
                                <ul class="nav nav-tabs listing-nav-tabs ">
                                    <li class="nav-item"><a class="active nav-link" data-toggle="tab" href="#seo-facebook"><i class="icon-facebook"></i></a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#seo-twitter"><i class="fab fa-twitter"></i></a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#seo-instagram"><i class="fab fa-instagram"></i></a></li>
                                </ul>
                                <div class="tab-content listing-tab">
                                    <div id="seo-facebook" class="tab-pane fade show active">
                                        <div class="form-group">
                                            {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'fbtitle', 'label'=>'Facebook Title', 'errorkey'=>'fbtitle',],['field'=>'textarea', 'name'=>'fbdesc', 'label'=>'Facebook Description', 'errorkey'=>'fbdesc','editor_required'=>'N']], '3','static_page_id',$page_dtls->id,$tblStaticPageDesc, $errors) !!}                                        
                                        </div>                                    
                                        <div class="form-group thumb-image-upload file-wrapper">
                                            <label>@lang('page.page_fb_image')</label>
                                            <img class="upload-img" src="{{getPageSocialshareImageUrl($page_dtls->fbimage)}}" width="360" height="230">                                           
                                            <input type="file" name="fbimage" accept=".png, .jpg, .jpeg" class="file-upload">
                                            @if($errors->has('fbimage'))
                                                <p class="error error-msg">{{ $errors->first('fbimage') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div id="seo-twitter" class="tab-pane fade">
                                        <div class="form-group">
                                            {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'twtitle', 'label'=>'Twitter Title', 'errorkey'=>'twtitle'],['field'=>'textarea', 'name'=>'twdesc', 'label'=>'Twitter Description', 'errorkey'=>'twdesc','editor_required'=>'N']], '4','static_page_id',$page_dtls->id,$tblStaticPageDesc, $errors) !!}                                        
                                        </div>                                    
                                        <div class="form-group thumb-image-upload file-wrapper">
                                            <label>@lang('page.page_tw_image')</label>
                                            <img class="upload-img" src="{{getPageSocialshareImageUrl($page_dtls->twimage)}}" width="360" height="230">
                                            <input type="file" name="twimage" accept=".png, .jpg, .jpeg" class="file-upload">
                                            @if($errors->has('twimage'))
                                                <p class="error error-msg">{{ $errors->first('twimage') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div id="seo-instagram" class="tab-pane fade">
                                        <div class="form-group">
                                            {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'institle', 'label'=>'Instagram Title', 'errorkey'=>'institle'],['field'=>'textarea', 'name'=>'insdesc', 'label'=>'Instagram Description', 'errorkey'=>'insdesc','editor_required'=>'N']], '5', 'static_page_id',$page_dtls->id,$tblStaticPageDesc, $errors) !!}                                         
                                        </div>
                                        <div class="form-group thumb-image-upload file-wrapper">
                                            <label>@lang('page.page_ins_image')</label>
                                            <img class="upload-img" src="{{getPageSocialshareImageUrl($page_dtls->insimage)}}" width="360" height="230">
                                            <input type="file" name="insimage" accept=".png, .jpg, .jpeg" class="file-upload">
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
            rules['cms_url'] = 'required';            
        var messages = {};
            messages['page_title['+lang_id+']'] = "@lang('cms.title_is_required')";
            messages['page_desc['+lang_id+']'] = "@lang('cms.description_is_required')";
            messages['cms_url'] = "@lang('cms.url_is_required')";       

        $("#cmsForm").validate({
            rules: rules,
            messages: messages,
            submitHandler : function(from, evt){               
                var formData = new FormData($('#cmsForm')[0]);
                $.ajax({
                    type: "POST",
                    url : "{{ action('Admin\Page\StaticPageController@update', $page_dtls->id) }}",
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
                        if(response.status=='update'){

                            revision_count = response.count;
                            revisionhandler();
                            toastr.options.positionClass = 'toast-top-right';
                            _toastrMessage('success', "{{ Lang::get('common.records_updated_successfully') }}");
                            $('#revision').removeClass("d-none");
                        }                   
                        if(response && response.status === 'success')
                            {
                                toastr.options.positionClass = 'toast-top-right';
                                _toastrMessage('success', "{{ Lang::get('common.records_updated_successfully') }}");
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

<script type="text/javascript">
    var revision_count = "{{ $revision }}";
    function revisionhandler(){
        if (revision_count>1) {
            $('#revision').removeClass("d-none");
            $('.revision-block').text(revision_count);

        }
    }
    
    $(document).ready(function(){
        revisionhandler();
    });
</script>        
@stop
