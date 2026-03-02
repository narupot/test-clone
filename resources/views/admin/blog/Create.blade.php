@extends('layouts/admin/default')

@section('title')
    @lang('blog.create_blog')
@stop

@section('header_styles')
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}tags/bootstrap-tagsinput.css"/>
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}tags/app.css"/>
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}flatpickr.min.css"/>
    <!--page level css -->
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
@stop

@section('content')
    <div class="content">
        @if(Session::has('succMsg'))
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script> 
        @endif 

        <form id="blogForm" action="javascript:void(0);" method="post" enctype="multipart/form-data" class="form-horizontal form-bordered" novalidate="novalidate">    
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('blog.create_blog')</h1>
                <div class="float-right btn-groups">
                    <a class="btn btn-back" href="{{ action('Admin\Blog\BlogController@index') }}">&lt;@lang('common.back')</a>                    
                    <button type="submit" name="submit_type" value="submit_continue" class="btn btn-secondary">@lang('common.save_and_continue')</button>
                    <button type="submit" name="submit_type" value="submit" class="btn btn-save btn-success">@lang('common.save')</button>
                </div>            
            </div>

            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('blog','blog')!!}
                    </ul>
                </div>
                <div class="content-left">
                    <div class="tablist">
                        <ul class="nav nab-tabs">
                            <li class="nav-item"><a class="nav-link show active" data-toggle="tab" data-target="#blogDetail">@lang('blog.blog_details')</a></li>
                            <li class="nav-item left-margin"><a class="nav-link" data-toggle="tab" data-target="#blogRelated">@lang('blog.blog_related')</a></li>
                            <li class="nav-item left-margin"><a class="nav-link" data-toggle="tab" data-target="#blogSlider">@lang('blog.blog_slider')</a></li>
                            <li class="nav-item left-margin"><a class="nav-link" data-toggle="tab" data-target="#blogSeo">@lang('blog.blog_seo')</a></li>
                            <li class="nav-item left-margin"><a class="nav-link" data-toggle="tab" data-target="#blogSocilashare">@lang('blog.blog_socialshare')</a></li>
                        </ul>
                    </div>
                </div>
                <div class="content-right grey-bg container">                                      
                    <div class="tab-content">
                        <div id="blogDetail" class="tab-pane active">
                            <h2 class="title-prod">@lang('blog.blog_details')</h2>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label>@lang('common.thumb_image')</label>
                                    <div class="thumb-image-upload file-wrapper">   
                                        <input type="file" accept=".png, .jpg, .jpeg" class="file-upload" name="uploadfile">
                                        <img class="upload-img" src="{{ Config::get('constants.image_url') }}file-upload.jpg" width="360" height="250">
                                        @if($errors->has('uploadfile'))
                                            <p class="error error-msg">{{ $errors->first('uploadfile') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-5">
                                <label>@lang('cms.url_key') <i class="strick">*</i></label> 
                                    <input type="text" name="url" value="{{ old('url') }}">
                                    <p class="error error-msg" id="url"></p>         
                                    <!-- @if($errors->has('url'))
                                        <p class="error error-msg">{{ $errors->first('url') }}</p>
                                    @endif -->
                                </div>
                            </div> 
                            <div class="form-group row">
                                <div class="col-md-5">
                                    <label>@lang('common.status') <i class="strick">*</i></label> 
                                    <select class="select w-100" name="status">
                                        <option value="">@lang('blog.default_option')</option>
                                        <option value="1">@lang('common.enable')</option>
                                        <option value="0">@lang('common.disable')</option>
                                    </select>   
                                    
                                        <p class="error" id="status"></p>
                                                                    
                                </div>
                            </div>                          
                            <div class="form-group row">
                                <div class="col-md-8">
                                    {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'blog_title', 'label'=>'Title', 'errorkey'=>'title'],['field'=>'textarea', 'name'=>'blog_short_desc','label'=>'Short Description', 'errorkey'=>'short_description'],['field'=>'textarea', 'name'=>'blog_desc','cssClass'=>'froala-editor-apply', 'froala'=>'froala', 'label'=>'Description', 'errorkey'=>'description']], '1', $errors) !!}
                                    <p class="error error-msg" id="title"></p> 
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-5">
                                <label>@lang('cms.features') <i class="strick">*</i></label> 
                                    <select class="select w-100" name="features">
                                        <option value="">@lang('blog.default_option')</option>
                                        <option value="0">@lang('common.no')</option>
                                        <option value="1">@lang('common.yes')</option>
                                    </select>
                                    
                                        <p class="error" id="features"></p>
                                </div>
                            </div>  
                            <div class="form-group row">
                                <div class="col-md-5">
                                <label>@lang('common.comment') <i class="strick">*</i></label> 
                                
                                    <select class="select w-100" name="comment">
                                        <option value="">@lang('blog.default_option')</option>
                                        <option value="2">@lang('blog.use_global_setting')</option>
                                        <option value="1">@lang('common.enable')</option>
                                        <option value="0">@lang('common.disable')</option>
                                    </select>
                                    <p class="error" id="comment"></p>
                                </div>
                            </div>  
                            <div class="form-group row">
                                <div class="col-md-5">
                                <label>@lang('cms.publish') <i class="strick">*</i></label> 
                                
                                    <select class="select w-100" name="publish">
                                        <option value="">@lang('blog.default_option')</option>
                                        <option value="1">@lang('common.enable')</option>
                                        <option value="0">@lang('common.disable')</option>
                                    </select>
                                    <p class="error" id="publish"></p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-5">
                                    <label>@lang('blog.publish_date') <i class="strick">*</i></label> 
                                    <input type="text" id="datepickers" class="date-select-new" name="publish_date" value="{{ old('publish_date') }}" >
                                    <p class="error" id="publish_date"></p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-5">
                                    <label>@lang('blog.tags')</label>
                                    <input type="text" name="tags" value="" id="tags" data-role="tagsinput">
                                    <!-- <p class="error" id="tags"></p> -->
                                </div>
                            </div>
                            <h2>@lang('blog.blog_category')</h2>
                                <div class="form-group row">
                                    <div class="col-sm-6">
                                    @if(count($categories) > 0) 
                                    <ul class="tree tree-menu">
                                        <label id="blog_cat_id[]" class="error" for="blog_cat_id[]" style="display: none;">Please select category</label>
                                    @foreach($categories as $key=>$mainCategory)
                                        <li>
                                            <a href="javascript:void(0);">
                                            @if(count($mainCategory->category) > 0)
                                                <i class="menuIcon glyphicon glyphicon-plus"></i>
                                            @endif
                                            <input type="checkbox" class="cat-checkbox valid " name="blog_cat_id[]" value="{{$mainCategory->id}}">
                                            <i><img src="assets/images/folder.svg" alt=""></i> {{$mainCategory->getCatDesc->name}}</a>
                                            @if(count($mainCategory->category) > 0) 
                                                <ul>
                                                @foreach($mainCategory->category as $subcategory)
                                                    <li>
                                                        <a href="javascript:void(0);">
                                                        @if(count($subcategory->category) > 0)
                                                            <i class="menuIcon glyphicon glyphicon-plus"></i>
                                                        @endif
                                                        <input type="checkbox" class="cat-checkbox" name="blog_cat_id[]" value="{{$subcategory->id}}">
                                                        <i><img src="assets/images/folder.svg" alt=""></i> {{$subcategory->getCatDesc->name}}</a> 
                                                        @if(count($subcategory->category) > 0)
                                                            <ul>
                                                            @foreach($subcategory->category as $subsubcategory)
                                                                <li>
                                                                    <a href="javascript:void(0);">
                                                                    @if(count($subsubcategory->category) > 0)
                                                                        <i class="menuIcon glyphicon glyphicon-plus"></i>
                                                                    @endif
                                                                    <input type="checkbox" class="cat-checkbox" name="blog_cat_id[]" value="{{$subsubcategory->id}}">
                                                                    <i><img src="assets/images/subfolder.svg" alt=""></i> {{$subsubcategory->getCatDesc->name}}</a>
                                                                    @if(count($subsubcategory->category) > 0) 
                                                                <ul>
                                                                @foreach($subsubcategory->category as $finalcategory)
                                                                    <li>
                                                                        <a href="javascript:void(0);">
                                                                        <input type="checkbox" class="cat-checkbox"  name="blog_cat_id[]" value="{{$finalcategory->id}}">
                                                                        <i><img src="assets/images/subfolder.svg" alt=""></i> {{$finalcategory->getCatDesc->name}}</a> 
                                                                    </li>
                                                                @endforeach
                                                                        </ul>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                    </ul>
                                     <!-- <p class="error">bvvc</p> -->
                                @endif                                         
                                </div>
                            </div>
                        </div>
                        <div id="blogRelated" class="tab-pane">
                        <h2 class="title-prod">@lang('blog.blog_related')</h2>
                            <table class="table white-bg table-bordered">
                                <thead>
                                    <tr class="filters">
                                        <th>@lang('common.sno')</th>
                                        <th>@lang('common.select')</th>
                                        <th>@lang('cms.title')</th>
                                        <th>@lang('cms.url_key')</th>
                                        <th>@lang('cms.publish')</th>
                                        <th>@lang('common.created_at')</th>
                                        <th>@lang('common.last_updated')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($blog_dtls as $key => $blog_dtl)
                                
                                    <tr>
                                        <td>{{ ++$key }}</td>
                                        <td><input name="related_blog[]" value="{{$blog_dtl['id']}}" type="checkbox"></td>
                                        <td>{{ $blog_dtl['title'] }}</td>
                                        <td>{{ $blog_dtl['url'] }}</td>
                                        <td>{{ $blog_dtl['publish'] }}</td>
                                        <td>{{ $blog_dtl['created_at'] }}</td>
                                        <td>{{ $blog_dtl['updated_at'] }}</td>          
                                    </tr>
                                 @endforeach  
                                 </tbody>
                            </table>
                        </div>
                        <div id="blogSlider" class="tab-pane">
                            <h2 class="title-prod">@lang('blog.blog_slider')</h2>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label>@lang('blog.blog_slider_image')</label>
                                    <div class="thumb-image-upload file-wrapper custom-height">
                                        <input type="file" accept=".png, .jpg, .jpeg" name="sliderimage[]" id="upload_multiple_image" multiple>
                                        <button type="button" id="sliderimagebtn">Add Images</button>
                                        <div id="image_preview"></div>
                                        @if ($errors->has('sliderimage'))
                                            <p class="error">{{ $errors->first('sliderimage') }}</p>
                                        @endif 
                                    </div> 
                                </div>               
                            </div>
                        </div>

                        <div id="blogSeo" class="tab-pane">
                            <h2 class="title-prod">@lang('blog.blog_seo')</h2>
                            <div class="form-group row no-star">
                                <div class="col-sm-8">
                                    {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'meta_title', 'label'=>'Meta Title', 'errorkey'=>'meta_title'],['field'=>'text', 'name'=>'meta_keyword', 'label'=>'Meta Keyword', 'errorkey'=>'meta_keyword'],['field'=>'textarea', 'name'=>'meta_desc', 'label'=>'Meta Description', 'errorkey'=>'meta_desc']], '2', $errors) !!}
                                </div> 
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label>@lang('blog.meta_image')</label>
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
                            <h2 class="title-prod">@lang('blog.blog_socialshare')</h2>
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

@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
   var lang_id = "{{ Session::get('admin_default_lang') }}";
</script>      
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>
<script src="{{asset('js/tags/typeahead.bundle.min.js')}}"></script>
<script src="{{asset('js/tags/bootstrap-tagsinput.js')}}"></script>  
<script>    
    (function($){
        var tag_titles = new Bloodhound({
          datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
          queryTokenizer: Bloodhound.tokenizers.whitespace,
          prefetch: {
            url: "{{action('Admin\Blog\BlogController@getAllTags')}}",
            filter: function(list) {                
              return $.map(list, function(tag_title) {
                return { name: tag_title }; });
            }
          }
        });

        tag_titles.initialize();

        $('#tags').tagsinput({          
          cancelConfirmKeysOnEmpty: false,
          typeaheadjs: {
            name: 'tag_titles',
            displayKey: 'name',
            valueKey: 'name',
            source: tag_titles.ttAdapter()
          }
        });
    })(jQuery);
</script>

<!-- begining of page level js -->
<script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
<script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
<script src="{{ Config('constants.admin_js_url') }}flatpickr.min.js"></script>
<script src="{{ Config('constants.js_url') }}common.js"></script>
<script>
    $(document).ready(function() {
        var table =  $('table.table').DataTable();

        // Date time Pickers
        $("#datepickers").flatpickr({
            altFormat: 'F j, Y H:i:S',
            dateFormat: 'Y-m-d H:i:S',
            enableTime: true,
            enableSeconds: true,       
            showOtherMonths: true
        });


    });
</script>

<script src="{{ Config('constants.js_url') }}jquery.validate.min.js" type="text/javascript"></script> 
<script type="text/javascript">
    (function($){
        var rules= {};            
            rules['page_title['+lang_id+']'] = 'required';
            rules['page_desc['+lang_id+']'] = 'required';
            rules['status'] = 'required';
            rules['features'] = 'required';
            rules['comment'] = 'required';
            rules['publish'] = 'required';
            rules['publish_date'] = 'required';
            rules['blog_cat_id[]'] = 'required';

        var messages = {};
            messages['page_title['+lang_id+']'] = "@lang('blog.title_is_required')";
            messages['page_desc['+lang_id+']'] = "@lang('blog.description_is_required')";
            messages['status'] = "@lang('blog.please_select_status')";
            messages['features'] = "@lang('blog.please_select_features')";
            messages['comment'] = "@lang('blog.please_select_comment')";
            messages['publish'] = "@lang('blog.please_select_publish')";
            messages['publish_date'] = "@lang('blog.please_select_publish_date')";
            messages['blog_cat_id[]'] = "@lang('blog.please_select_blog_cat_id')";       
            
        $("#blogForm").validate({
            rules: rules,
            messages: messages,
            submitHandler : function(from, evt){               
                var formData = new FormData($('#blogForm')[0]);
                $.ajax({
                    type: "POST",
                    url : "{{ action('Admin\Blog\BlogController@store')}}",
                    enctype: 'multipart/form-data',
                    processData: false,  // Important!
                    contentType: false,
                    cache: false,
                    data : formData,
                    success : function(response){
                        $('p[class="error"]').html('');
                        if(response.status=='fail'){
                        
                            $.each(response.message, function(key,val){
                              $('p[id='+key+']').text(val);
                            });
                            return false;
                        }                   
                        if(response && response.status === 'success')
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
<!-- end of page level js -->
@stop
