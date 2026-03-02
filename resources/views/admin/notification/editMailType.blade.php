@extends('layouts/admin/default')

@section('title')
    @lang('admin.edit_mail_template')
@stop

@section('header_styles')

    <!--page level css -->
    <link href="{{ asset('assets/vendors/jasny-bootstrap/css/jasny-bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendors/iCheck/css/all.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/pages/form_layouts.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/vendors/bootstrapvalidator/css/bootstrapValidator.min.css') }}" type="text/css" rel="stylesheet">
    <!-- end of page level css -->
    
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
        @if(Session::has('errorMsg'))
        <div class="alert alert-danger alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin.error'):</strong> {{ Session::get('errorMsg') }}
        </div>
        @endif   
        <div class="header-title clearfix">
            <h1 class="title">
                @lang('admin.edit_mail_template') : @if(isset($mail_template->mail_subject)) {{$mail_template->mail_subject}} @else {{'N/A'}} @endif
            </h1>
        </div>     
        <div id="tab3" class="tab-pane content-wrap">
 
            <div class="containers">
              
                <div class="border">
                    <form id="MailTempForm" action="{{ action('MailTemplateController@update', $mail_template->id) }}" method="post" enctype="multipart/form-data" class="form-horizontal form-bordered">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <div class="form-group striped-col">
                            <label class="col-md-2 control-label" for="form-file-input">@lang('admin.language') <i class="strick">*</i></label>
                            <div class="col-md-6">
                            <select id="form-select" name="lang_id" class="form-control" size="1">
                                <option value="">--@lang('admin.select_language')--</option>
                                
                                @foreach($lang_lists as $language)
                                
                                    <option value="{{ $language->id }}" {{ ($language->id==$mail_template->lang_id)?'selected':'' }}>{{ $language->languageName }}</option> 
                                    
                                @endforeach
                                
                            </select>
                            </div>    
                        </div>                                
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="form-text-input">@lang('admin.subject') <i class="strick">*</i></label>
                            <div class="col-md-6">
                                <input type="text" name="mail_subject" value="{{ $mail_template->mail_subject }}" class="form-control" placeholder="mail subject">
                            </div>
                        </div>
                        <div class="form-group striped-col">
                            <label class="col-md-2 control-label" for="form-text-input">@lang('admin.from') <i class="strick">*</i></label> 
                            <div class="col-md-6">
                                <input id="form-email" name="email_id" value="{{ $mail_template->email_id }}" class="form-control" placeholder="Email" type="email">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="form-text-input">@lang('admin.message') <i class="strick">*</i></label> 
                            <div class="col-md-8">
                                <textarea id="form-textarea-input" name="mail_containt" rows="8" class="form-control resize_vertical" placeholder="Description..">{{ $mail_template->mail_containt }}</textarea>
                            </div>
                        </div>                                 
                        <div class="form-group striped-col form-actions">
                            <div class="col-md-8 col-md-offset-2">
                                <button type="submit" class="btn btn-secondary">@lang('admin.submit')</button>
                                <a class="btn btn-secondary" href="{{ action('MailTemplateController@show', $mail_template->mail_type_id) }}">&lt;@lang('admin.back')</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
               
        </div>
    </div>
      
@stop

@section('footer_scripts')
 
    <!-- begining of page level js -->
    <script src="{{ asset('assets/vendors/jasny-bootstrap/js/jasny-bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendors/iCheck/js/icheck.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form_layouts.js') }}"></script>
    <script src="{{ asset('assets/vendors/bootstrapvalidator/js/bootstrapValidator.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/pages/validateMailTemplate.js') }}" type="text/javascript"></script>
    <!-- end of page level js -->   
    
@stop
