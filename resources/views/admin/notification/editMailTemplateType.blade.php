@extends('layouts/admin/default')

@section('title')
    @lang('admin_notification.edit_mail_template_type')
@stop

@section('header_styles') 
<link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}select.css" /> 
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @elseif(Session::has('errorMsg'))
        <div class="alert alert-danger alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
        </div>
        @endif     


        <div class="header-title">
            <h1 class="title">@lang('admin_notification.edit_notification_details') :  @if(isset($templete_type_dtl->mail_type)) {{$templete_type_dtl->mail_type}} @else {{'N/A'}} @endif </h1>
        </div>
        <div class="content-wrap column-modal clearfix">
            <div id="tab3" class="tab-pane">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config','mail')!!}
                    </ul>
                </div>
                <div class="containers">
                    <ul class="nav nav-tabs chtabs" role="tablist">
                         <li><a class="active nav-link" href="{{ action('Admin\Notification\MailTemplateController@editTemplateType', [$templete_type_dtl->id, $template_type]) }}">{{ ucfirst($notificationName) }} @lang('admin_notification.event')</a></li>
                         <li><a class="nav-link" href="{{ action('Admin\Notification\MailTemplateController@showdetails', [$templete_type_dtl->id, $template_type]) }}">@lang('admin_notification.add_message')</a></li>
                    </ul>
                    <div class="tab-content">
                        <form id="mail_temp_type_form" action="{{ action('Admin\Notification\MailTemplateController@updateTemplateType') }}" method="post" class="form-horizontal form-bordered" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <input type="hidden" name="noti_event_id" value="{{ $templete_type_dtl->id }}">

                           <input type="hidden" name="noti_type_id" value="{{ $template_type }}">

                           <div class="row">
                               <div class="col-sm-5">
    								@if( $template_type == '6')
    								@php
    									$tokendata = explode(',',$templete_type_dtl->token);
    									//dd($line_token,$templete_type_dtl);
    								@endphp
    								<div class="form-group">         
                                        <label>@lang('admin_notification.token') <i class="strick">*</i></label>                                   
                                        <select id="form-select" name="token[]" class="form-control multi-select" data-placeholder="token" multiple="3">
    									{{--<option value="">--@lang('admin_notification.select_token')--</option>--}}
    										@if(!empty($line_token))  
    											@foreach($line_token as $token)
    												<option value="{{ $token->id }}" @if(!empty($tokendata) && in_array($token->id,$tokendata)) selected @endif>{{ trim($token->name ?? '') }}</option>
    											@endforeach 
    										@endif
                                        </select>  
                                        @if($errors->has('token'))
                                          <p class="error error-msg">{{ $errors->first('token') }}</p>
                                        @endif                                     
                                           
                                    </div>
    								@endif
    								@if( $template_type != '6')
                                    <div class="form-group">
                                        <label>@lang('admin_notification.subject')</label>
                                        <input type="text" name="mail_type" value="{{ $templete_type_dtl->mail_type }}" class="form-control" placeholder="mail subject" readonly>
                                    </div>
    								@endif
                                    <div class="form-group" @if( $template_type != '3') style="display:none" @endif>
                                        <label>@lang('admin_notification.event_icon')</label>
                                         
                                        <div class="">
                                           {!! Form::file('icon', ['class'=>'form-control']) !!}
                                           @if ($errors->has('icon'))
                                              <p id="banner_image-error" class="error error-msg">{{ $errors->first('icon') }}</p>
                                           @endif
                                           <img src="{{ Config::get('constants.social_icon_url').$templete_type_dtl->icon }}" width="16" height="16"> 
                                        </div>
                                    </div>
                                  
                                   <!--  Hide in all the case accept email-->
                                    <div class="form-group" @if( $template_type != '1') style="display:none" @endif >
                                        <label>@lang('admin_notification.sender') <i class="strick">*</i></label>                                   
                                        <select id="form-select" name="sender" class="form-control">
                                            <option value="">--@lang('admin_notification.select_sender')--</option>
                                            @foreach($sender_arr as $sender)
                                                <option value="{{ $sender->id }}" @if($templete_type_dtl->sender == trim($sender->id)) selected @endif>{{ trim($sender->sender_name) }} ({{ trim($sender->sender_email) }})</option>
                                            @endforeach 
                                        </select>
                                        @if($errors->has('sender'))
                                          <p class="error error-msg">{{ $errors->first('sender') }}</p>
                                        @endif                                     
                                           
                                    </div>                                
                                    <div class="form-group" @if( $template_type != '1' && $template_type != '2') style="display:none" @endif>
                                        <label>@lang('admin_notification.to') <i class="strick">*</i></label> 
                                        
                                        <select name="recievers[]" class="form-control multi-select" data-placeholder="recievers" multiple="3">
                                            @if($template_type == '2')
                                                
                                                <option value="buyer" @if($templete_type_dtl->to_buyer == '1') selected = "selected"  @endif>@lang('admin_notification.buyer_contact_phone')</option>
                                                <option value="buyer_phone_login" @if($templete_type_dtl->buyer_phone_login == '1') selected = "selected"  @endif>@lang('admin_notification.buyer_phone_login')</option>

                                                <option value="buyer_shipping_phone" @if($templete_type_dtl->buyer_shipping_phone == '1') selected = "selected" @endif>@lang('admin_notification.buyer_shipping_phone')</option>
                                                
                                            @else
                                                <option value="buyer" @if($templete_type_dtl->to_buyer == '1') selected = "selected" @endif>@lang('admin_notification.buyer')</option>
                                            @endif
                                            <option value="seller" @if($templete_type_dtl->to_seller == '1') selected = "selected" @endif>@lang('admin_notification.seller')</option>
                                            @foreach($roles as $value)   
                                                <option value="{{ $value->id }}" @if(strpos($templete_type_dtl->to_admin, '-'.$value->id.'-') !== false) selected @endif>{{ $value->name }}</option> 
                                            @endforeach
                                        </select>                                        

                                        @if($errors->has('recievers'))
                                          <p class="error error-msg">{{ $errors->first('recievers') }}</p>
                                        @endif                                         
                                        
                                    </div>
                                    <div class="form-group" @if( $template_type != '1') style="display:none" @endif>
                                        <label>CC</label>                                    
                                        <!-- <input name="cc" value="{{ $templete_type_dtl->cc }}" class="form-control" placeholder="" type="text"> -->

                                        <select name="cc[]" class="form-control multi-select" data-placeholder="cc" multiple>
                                            @foreach($roles as $value) {    
                                                <option value="{{ $value->id }}" @if(strpos($templete_type_dtl->cc, '-'.$value->id.'-') !== false) selected @endif>{{ $value->name }}</option> 
                                            @endforeach
                                        </select> 
                                    </div>
                                    <div class="form-group" @if( $template_type != '1') style="display:none" @endif>
                                        <label>BCC </label>                                     
                                        <!-- <input name="bcc" value="{{ $templete_type_dtl->bcc }}" class="form-control" placeholder="" type="text"> -->

                                        <select name="bcc[]" class="form-control multi-select" data-placeholder="bcc" multiple>
                                            @foreach($roles as $value) {    
                                                <option value="{{ $value->id }}" @if(strpos($templete_type_dtl->bcc, '-'.$value->id.'-') !== false) selected @endif>{{ $value->name }}</option> 
                                            @endforeach
                                        </select>                                         
                                        
                                    </div>
                                    <div class="form-group" @if( $template_type != '1') style="display:none" @endif>
                                        <label>@lang('admin_notification.mail_type')</label>
                                        
                                        <select id="form-select" name="type" class="form-control">
                                            <option value="1" @if($templete_type_dtl->type == '1') selected @endif>@lang('admin_notification.html')</option> 
                                            <option value="2" @if($templete_type_dtl->type == '2') selected @endif>@lang('admin_notification.plane')</option>
                                        </select>                                    
                                       
                                    </div>                         
                                         

                                    <!--CustomHelpers::textWithEditLanuage('textarea','mail_desc', $tableNoticeEventDesc, $templete_type_dtl->id, 'noti_event_id')-->

                                    <div class="form-group btns-group">
                                        <a class="btn btn-back" href="{{ action('Admin\Notification\MailTemplateController@index') }}">&lt;@lang('admin_common.back')</a>
                                        <button type="submit" class="btn btn-primary">@lang('admin_common.submit')</button>
                                        
                                    </div>
                                    
                                    @php($exampleImage = '')
                                    @if( $template_type == '3')  <!--WEB-->
                                      @php($exampleImage = 'images/web.jpg')    
                                    @elseif($template_type == '4') <!--PUSH-->
                                       @php($exampleImage = 'images/push_image.jpg')    
                                    @elseif($template_type == '5') <!--TOASTR-->
                                       @php($exampleImage = 'images/toastr.jpg')  

                                    @endif
                                    @if(!empty($exampleImage))
                                     <div class="form-group">
                                        <label>@lang('admin_notification.example_image')</label>
                                        <img src="{{ Config('constants.public_url').$exampleImage}}" > 

                                   </div>
                                   @endif


                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
      
@stop

@section('footer_scripts')
 
    <!-- start page level js --> 
    <script src="{{ Config('constants.admin_js_url') }}chosen.jquery.min.js"></script> 

    <script src="{{ asset('assets/js/pages/validateMailTemplate.js') }}" type="text/javascript"></script>
    <!-- end of page level js -->  

    <script>
        $('.multi-select').chosen();
    </script> 
    
@stop
