@extends('layouts/admin/default')

@section('title')
    @lang('admin_common.edit_mail_template_type')
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
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif 
        <form id="cmsForm" action="{{ action('Admin\Notification\MailTemplateController@updateeditevent', [$templete_type_dtl->id]) }}" method="post" class="form-horizontal form-bordered">
            <div class="header-title">
                <h1 class="title">@lang('admin_notification.edit_mail_template') : @if(isset($templete_type_dtl->mail_type)) {{$templete_type_dtl->mail_type}} @else {{'N/A'}} @endif</h1>
                <div class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Notification\MailTemplateController@index') }}">&lt;@lang('admin_common.back')</a>
                    <button type="submit" name="submit_type" value="submit" class="btn btn-primary">@lang('admin_common.save')</button>                    
                </div>
            </div>       
            <div class="content-wrap">
                
                {{ csrf_field() }}
                <input type="hidden" name="noti_event_id" value="{{ $templete_type_dtl->id }}">
               
                <div class="row mt-3">
                    <div class="col-sm-6 form-group">
                        <label for="form-text-input" style="text-align: left;">@lang('admin_common.subject')</label>
                        <input type="text" name="mail_type" value="{{ $templete_type_dtl->mail_type }}" class="form-control" placeholder="mail subject" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'textarea', 'name'=>'mail_desc', 'label'=>' <i class="strick">*</i>', 'errorkey'=>'texteditor']], '1', 'noti_event_id', $templete_type_dtl->id, $tableNoticeEventDesc, $errors) !!}
                    </div>
                </div>
                
            
                                                       
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
<script src="{{ Config('constants.page_js_url') }}validatenotification.js" type="text/javascript"></script>
<!-- end of page level js --> 

@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
</script>
<script src="{{asset('js/normal_froala_editor_model.js')}}"></script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>        
@stop
