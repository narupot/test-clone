@extends('layouts/admin/default')

@section('title')
    @lang('cms.edit_block')
@stop

@section('header_styles')

<!--page level css -->

<!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        <form id="cmsForm" action="javascript:void(0);" method="post" class="form-horizontal form-bordered" novalidate="novalidate">
            {{ csrf_field() }}
            {{ method_field('PUT') }}        
            <div class="header-title">
            @if(Session::has('succMsg'))
                <div class="alert alert-success alert-dismissable margin5">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <strong>@lang('common.success'):</strong> {{ Session::get('succMsg') }}
                </div>
            @endif 
                <h1 class="title">@lang('cms.edit_block') : {{$page_dtls->staticBlockDesc->page_title}}</h1> 
                <div class="float-right">                
                    <a class="btn btn-back" href="{{ action('Admin\Block\StaticBlockController@index') }}">@lang('common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn static-block-save btn-secondary" data-action="submit_continue">@lang('common.save_and_continue')</button>
                    <button type="submit" class="btn btn-save btn-success">@lang('common.save')</button>
                    
                    <a class="btn btn-primary d-none pr-4" href="{{ action('Admin\Block\StaticBlockController@blockrevision',$page_dtls->id) }}" id="revision">@lang('common.revision')<span class="num revision-block" >{{ $revision }}</span>
                    </a>
                    

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
                        <label class="control-label" for="form-text-input">@lang('common.status') <i class="strick">*</i></label>                     
                        <select class="select" name="status">
                            <option value="1" @if($page_dtls->status == '1') selected @endif>@lang('common.active')</option>
                            <option value="0" @if($page_dtls->status == '0') selected @endif>@lang('common.inactive')</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-5">
                        <label class="control-label" for="form-text-input">&nbsp;</label>
                    
                        {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'page_title', 'label'=>Lang::get('common.title').' <i class="strick">*</i>', 'errorkey'=>'page_ttl']], '1', 'static_block_id', $page_dtls->id, $tblStaticBlockDesc, $errors) !!}
                        <p class="error" id="page_ttl"></p>
                        <p class="error" id="url"></p>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-5">
                    <label class="control-label" for="form-text-input">&nbsp;</label>
                    
                        {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'textarea', 'name'=>'page_desc', 'label'=>Lang::get('common.description').' <i class="strick">*</i>', 'errorkey'=>'page_description']], '2', 'static_block_id', $page_dtls->id, $tblStaticBlockDesc, $errors) !!}
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
   var revision_count = "{{ $revision }}";  
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
                    url : "{{ action('Admin\Block\StaticBlockController@update', $page_dtls->id) }}",
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
                        if(response.status=='update'){
                            revision_count = response.count;
                        	revisionhandler();
                            toastr.options.positionClass = 'toast-top-right';
                            _toastrMessage('success', "{{ Lang::get('common.records_updated_successfully') }}");
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
