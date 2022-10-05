@extends('layouts/admin/default')

@section('title')
    @lang('cms.create_block')
@stop

@section('header_styles')
<!--page level css -->
<!-- end of page level css --> 
@stop

@section('content')
    <div class="content">
        @if(Session::has('succMsg'))
            <div class="alert alert-success alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong>@lang('common.success'):</strong> {{ Session::get('succMsg') }}
            </div>
        @endif 
        <form id="cmsForm" action="javascript:void(0)" method="post" class="form-horizontal form-bordered" novalidate="novalidate">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('cms.create_block')</h1>
                <div class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Block\StaticBlockController@index') }}"><span><</span>@lang('common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn static-block-save btn-secondary" data-action="submit_continue">@lang('common.save_and_continue')</button>
                    
                    <!-- <button type="button" name="submit_type" value="preview" class="btn static-block-save" style="background: #38c1ff;" data-action="preview">@lang('admin_common.priview')</button> -->                    
                    <button type="submit" name="submit_type" value="submit" class="btn btn-save static-block-save btn-success" data-action="submit">@lang('common.save')</button>
                </div>
            </div>       
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('block')!!}
                    </ul>
                </div>
                <div class="form-group row">
                    <div class="col-md-5">
                        <label>@lang('common.status') <i class="strick">*</i></label>
                        <select class="select" name="status">
                            <option value="1">@lang('common.active')</option>
                            <option value="0">@lang('common.inactive')</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-5">                        
                        <label for="form-text-input">&nbsp;</label>                   
                        {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'page_title', 'label'=>'Title <i class="strick">*</i>', 'errorkey'=>'page_ttl']], '1', $errors) !!}
                        <p class="error" id="page_ttl"></p>
                        <p class="error" id="url"></p>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-5">
                        
                        {!! CustomHelpers::fieldstabWithLanuage([['field'=>'textarea', 'name'=>'page_desc', 'label'=>'Description <i class="strick">*</i>','errorkey'=>'page_description','cssClass'=>'froala-editor-apply']], '2', $errors) !!}
                        <p class="error" id="page_description"></p>
                    </div>

                </div>                                                          
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
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
        var messages = {};
            messages['page_title['+lang_id+']'] = "@lang('common.title_is_required')";
            messages['page_desc['+lang_id+']'] = "@lang('common.description_is_required')";       
        $("#cmsForm").validate({
            rules: rules,
            messages: messages,
            submitHandler : function(from, evt){               
                var formData = new FormData($('#cmsForm')[0]);
                $.ajax({
                    type: "POST",
                    url : "{{ action('Admin\Block\StaticBlockController@store') }}",
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
@stop
