@extends('layouts/admin/default')

@section('title')
    @lang('admin_notification.add_mail_template')
@stop

@section('header_styles')
    
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
        @if(Session::has('errorMsg'))
        <div class="alert alert-danger alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
        </div>
        @endif   
        <div class="header-title clearfix">
            <h1 class="title">@lang('admin_notification.add_mail_template')</h1>
        </div>     
        <div id="tab3" class="tab-pane content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','mastertemplate')!!}
                </ul>
            </div>
   
            <div class="content-right">
                <form id="master_temp_form" action="{{ action('Admin\Notification\MailTemplateController@masterTemplateSubmit') }}" method="post" class="form-horizontal form-bordered">
                    {{ csrf_field() }}

                    <div class="row">
                        <div class="col-sm-7">
                        
                            <div class="form-group striped-col">
                                <label>@lang('admin_notification.language') <i class="strick">*</i></label>
                           
                                <select id="form-select" name="lang_id" class="form-control" size="1">
                                    <option value="">--@lang('admin_notification.select_language')--</option>
                                    
                                    @foreach($languages as $language)
                                        <option value="{{ $language->id }}" @if(old('lang_id') == $language->id)) selected @endif>{{ $language->languageName }}</option>
                                    @endforeach
                                    
                                </select>
                                @if($errors->has('lang_id'))
                                  <p class="error error-msg">{{ $errors->first('lang_id') }}</p>
                                @endif                                    
                                  
                            </div>                                
                            <div class="form-group">
                                <label>@lang('admin_notification.template_name') <i class="strick">*</i></label>
                              
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Name">
                                @if($errors->has('name'))
                                  <p class="error error-msg">{{ $errors->first('name') }}</p>
                                @endif                                        
                               
                            </div>
                            <div class="form-group">
                                <label>@lang('admin_notification.template') <i class="strick">*</i></label> 
                               
                                <textarea id="form-textarea-input" name="template" rows="16" class="form-control resize_vertical froala-editor-apply" placeholder="Template..">{{ old('template') }}</textarea>
                                @if($errors->has('template'))
                                  <p class="error error-msg">{{ $errors->first('template') }}</p>
                                @endif                                        
                            
                                <div class="mt-10"><b>@lang('admin_notification.variable'):</b> [CONTENT] </div>
                            </div>                                 
                            <div class="form-group btns-group">
                              
                                <a class="btn btn-secondary mr-2" href="{{ action('Admin\Notification\MailTemplateController@masterTempateList') }}">&lt;@lang('admin_common.back')</a>
                                <button type="submit" class="btn btn-primary">@lang('admin_common.submit')</button>                                    
                              
                            </div>
                        </div>
                    </div>
                </form>
            </div>
   
        </div>
    </div>
      
@stop

@section('footer_scripts')
 
    <!-- begining of page level js -->
    <script src="{{ Config::get('constants.page_js_url').'validateMasterTemplate.js' }}" type="text/javascript"></script>
    <!-- end of page level js -->   

      @include('includes.froalaeditor_dependencies') 

     <script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
</script>
<script src="{{asset('js/normal_froala_editor_model.js')}}"></script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>
    
@stop
