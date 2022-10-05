@extends('layouts/admin/default')

@section('title')
    @lang('admin_notification.edit_result')
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
        <form id="cmsForm" action="{{ action('Admin\Notification\MailTemplateController@updateSender')}}" method="post">
            <div class="header-title">
                <h1 class="title">@lang('admin_notification.edit_result') : @if(isset($result->sender_name)) {{$result->sender_name}} @else {{'N/A'}} @endif</h1>
                <div class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Notification\MailTemplateController@senderlist') }}">&lt;@lang('admin_common.back')</a>
                    <button type="submit" name="submit_type" value="submit" class="btn btn-primary">@lang('admin_common.save')</button>                    
                </div>
            </div>       
            <div class="content-wrap">
                
                {{ csrf_field() }}

                <input type="hidden" name="sender_id" value="{{ $result->id }}">

                <div class="row">
                    <div class="col-sm-6">
                        
                        <div class="form-group">                            
                            <label for="form-file-input">@lang('admin_notification.sender_name') <i class="strick">*</i></label>
                            <input type="text" name="sender_name" value="{{ $result->sender_name }}" class="form-control" placeholder="Sender Name">

                            @if($errors->has('sender_name'))
                              <p class="error error-msg">{{ $errors->first('sender_name') }}</p>
                            @endif                                    
                              
                        </div>                                
                        <div class="form-group">                            
                            <label for="form-file-input">@lang('admin_notification.sender_email') <i class="strick">*</i></label>
                            <input type="text" name="sender_email" value="{{ $result->sender_email }}" class="form-control" placeholder="Sender Email">

                            @if($errors->has('sender_email'))
                              <p class="error error-msg">{{ $errors->first('sender_email') }}</p>
                            @endif                                    
                              
                        </div>
                        <div class="form-group row">
                            <div class="col-md-5">
                                <label for="form-text-input">@lang('admin_common.status') <i class="strick">*</i></label> 
                                <select class="select" name="status">
                                    <option value="1" @if($result->status == '1') selected @endif>@lang('admin_common.enable')</option>
                                    <option value="0" @if($result->status == '0') selected @endif>@lang('admin_common.disable')</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-5">
                                <label class="check-wrap mt-2">
                                    <input @if($result->is_default == '1') checked="checked" @endif name="is_default" class="checkbox" type="checkbox">
                                    <span class="chk-label mt-2">{{ Lang::get('admin_common.default') }}</span>
                                </label>    
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
<script src="{{ Config('constants.page_js_url') }}validatenotification.js" type="text/javascript"></script>
<!-- end of page level js --> 

@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
</script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>        
@stop
