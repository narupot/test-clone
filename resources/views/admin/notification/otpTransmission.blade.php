@extends('layouts/admin/default')

@section('title')
    @lang('admin_notification.manage_otp_transmission_method')
@stop

@section('header_styles')

<!--page level css -->

<!-- end of page level css -->
    
@stop

@section('content')
    <div class="loader-wrapper d-none" id="showHideLoader">
        <span class="loader">
            <img src="{{ Config::get('constants.loader_url')}}ajax-loader.gif" alt="Loader"> 
        </span>
     </div>
    <div class="content">
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif 
        <form id="cmsForm" action="{{ action('Admin\Notification\MailTemplateController@updateOTPTransMethod') }}" method="post" class="form-horizontal form-bordered">
            <div class="header-title">
                <h1 class="title">@lang('admin_notification.manage_otp_transmission_method') </h1>
                <div class="float-right">
                    <button type="button" name="edit_server" class="btn btn-dark d-none" id="edit_server">@lang('admin_common.edit')</button>
                    <button type="button" name="cancel_edit" class="btn btn-danger d-none" id="cancel_edit">@lang('admin_common.cancel')</button>
                    <button type="submit" id="save_server" name="submit_type" value="submit" class="btn btn-primary">@lang('admin_common.save')</button>
                </div>
            </div>       
            <div class="content-wrap column-modal clearfix">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config')!!}
                    </ul>
                </div>
                <!--div class="radio-group mb-5">
                    <label class="radio-wrap">
                        <input name="type" type="radio" value="email" checked="checked">
                        <span class="radio-label">Email</span>
                    </label>
                    <label class="radio-wrap">
                        <input name="type" type="radio" value="sms">
                        <span class="radio-label">SMS</span>
                    </label>
                </div-->

                {{ csrf_field() }}
                <div class="row">
                    <div class="col-sm-7">
                        <ul class="nav nav-tabs chtabs">
                            <li class="nav-item"><a class="nav-link"  href="{{action('Admin\Notification\MailTemplateController@manageEmailTransmission')}}">@lang('admin_notification.email')</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{action('Admin\Notification\MailTemplateController@manageSMSTransmission')}}">@lang('admin_notification.sms_notification')</a></li>
                            <li class="nav-item"><a class="nav-link show active" href="{{action('Admin\Notification\MailTemplateController@manageOTPTransmission')}}">@lang('admin_notification.sms_otp')</a></li>
                            <li class="nav-item"><a class="nav-link show active" href="{{action('Admin\Notification\MailTemplateController@manageLineTransmission')}}">@lang('admin_notification.line_notification')</a></li>
                        </ul>
                        <div class="tab-content">
                            <div id="server_email" class="desc tab-pane fade show active">
                                <div class="form-row">
                                    <div class="col-sm-6">
                                        <div class="form-group striped-col">
                                            <label for="form-file-input">@lang('admin_notification.otp_provider') <i class="strick">*</i></label>
                                            <select id="provider" name="provider" class="form-control email-system">
                                                @foreach($default_sms_server as $server_data)
                                                    <option  value="{{$server_data->id}}" @if($server_data->id == $default_mail_server->id) selected="selected" @endif >{{ucfirst($server_data->provider)}}</option>
                                                @endforeach
                                            </select>
                                            <p class="error error-msg"></p>  
                                        </div>
                                        <div class="form-group">
                                            <label for="form-text-input">@lang('admin_notification.api_url') <i class="strick">*</i></label>
                                            <input type="text" name="api_url" id="api_url" value="@if($default_mail_server->api_url!=''){{$default_mail_server->api_url}}@endif" class="form-control" placeholder="API URL" readonly="readonly">
                                            <p class="error error-msg"></p>                                         
                                        </div>
                                        <div class="form-group">
                                            <label for="form-text-input">@lang('admin_notification.sms_key') <i class="strick">*</i></label>
                                            <input type="text" name="username" id="username" value="@if($default_mail_server->username!=''){{$default_mail_server->username}}@endif" class="form-control" placeholder="Enter Username">
                                            <p class="error error-msg"></p>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="form-text-input">@lang('admin_notification.secret_key') <i class="strick">*</i></label>
                                            <input type="password" name="password" id="password" value="@if($default_mail_server->password!=''){{$default_mail_server->password}}@endif" class="form-control" placeholder="Enter Password">
                                            <p class="error error-msg"></p>
                                        </div>
                                        <div class="form-group">
                                            <label for="form-text-input">@lang('admin_notification.msisdn') <i class="strick">*</i></label>
                                            <input type="text" name="msisdn" id="msisdn" value="@if($default_mail_server->msisdn!=''){{$default_mail_server->msisdn}}@endif" class="form-control" placeholder="Enter Msisdn">
                                            <p class="error error-msg"></p>
                                        </div>
                                        <div class="form-group">
                                            <label for="form-text-input">@lang('admin_notification.sender') <i class="strick">*</i></label>
                                            <input type="text" name="sender" id="sender" value="@if($default_mail_server->sender!=''){{$default_mail_server->sender}}@endif" class="form-control" placeholder="Enter Sender">
                                            <p class="error error-msg"></p>
                                        </div>
                                        <div class="dynamic_email_trans_container">
                                            <div class="form-group">
                                                <label for="form-text-input"></label>
                                                <button type="button" name="test_sms_server" id="test_sms_server" value="test_sms_server" class="btn btn-secondary">@lang('admin_notification.test_otp_connection')</button>
                                            </div>
                                        </div>
                                        <div class="dynamic_email_trans_container">
                                            <div class="form-group">
                                                <label for="form-text-input">@lang('admin_notification.connection_result')</label>
                                                <span id="connection_response"></span>
                                            </div>
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

    <div class="modal fade" id="otpModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">@lang('admin_notification.verify_otp')</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
                <div class="form-group">
                    <input type="text" name="otpMatch" id="otpMatch">
                    <input type="hidden" name="otpMatchToken" id="otpMatchToken">
                    <p class="error error-msg"></p>
                </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('admin_notification.close')</button>
            <button type="button" class="btn btn-primary" id="verify_otp" >@lang('admin_notification.verify_otp')</button>
            <div class="dynamic_email_trans_container">
                <div class="form-group">
                    <span id="connection_response_1"></span>
                </div>
            </div>
            
          </div>
        </div>
      </div>
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
<script type="text/javascript" >
    
    $(document).ready( function (){
        function setServerSMSData(provider){
            var default_provider = "{{$default_mail_server->provider}}";
            $.ajax({
                url : "{{action('Admin\Notification\MailTemplateController@getSmsData')}}",
                method : "post",
                dataType : "json",
                data : {'_token':csrftoken,'provider':provider},
                before_send : function(){
                    $("#showHideLoader").removeClass('d-none');
                },
                success : function (response){
                    if(response.status=='success'){
                       
                        $("#username").val(response.driverData.username);
                        $("#password").val(response.driverData.password); 
                        $("#api_url").val(response.driverData.api_url); 
                        $("#msisdn").val(response.driverData.msisdn);
                        $("#sender").val(response.driverData.sender);
                        $("#showHideLoader").addClass('d-none');
                    }
                }
            });
        }

        $("#provider").on('change', function(){
            var provider = $("#provider").val();
            setServerSMSData(provider);
        });

        
        $("#test_sms_server").on('click', function(){
            var f_data = {};
            var error = false;
            var formData = $('#cmsForm').find("select, textarea, input, checkbox").each( function(){
                
                f_data[$(this).attr('name')] = $(this).val();
                var elem_val = $(this).val();
                if(elem_val===''){
                    $(this).css('border-color','red');
                    $(this).siblings(".error").html('This field is empty.');
                    error = true;
                }
                
            });

            if(error){
                return false;
            }
            
            $.ajax({
                url : "{{action('Admin\Notification\MailTemplateController@testOTPServerConnection')}}",
                method : "post",
                dataType : "json",
                data : {'_token':csrftoken,'form_data':f_data},
                beforeSend : function(){
                    $("#showHideLoader").removeClass('d-none');
                },
                success : function (response){
                   
                    if(response.error===true){
                        $("#connection_response").html('['+response.date+'] '+response.message).addClass('error').removeClass('succMsg');
                    }else{

                        $("#connection_response").html(response.message+'    ['+response.date+']').addClass('succMsg').removeClass('error');
                        $('.form-control').attr('readonly',true);
                        $('#edit_server').removeClass('d-none'); 
                        $('#save_server').removeClass('d-none'); 
                         
                        
                        /*$('#otpModal').on('shown.bs.modal', function () {
                            
                        })*/
                        /*$(document).on('show.bs.modal','#otpModal', function () {
                              
                        });  */

                        $('#otpMatchToken').val(response.message);
                        $('#otpModal').modal('show');

                          
                          
                    }
                   $("#showHideLoader").addClass('d-none');

                },
                error : function(jqXHR, textStatus){
                    if(textStatus === 'timeout')
                    {     
                        $("#connection_response").html('Request Timeout').addClass('error').removeClass('succMsg');
                        $("#showHideLoader").addClass('d-none');
                    }
                },
                //timeout:default_timeout // set timeout of 30 second
            });
        });
        
        $(document).on('click', "#verify_otp",function(){
        
            var otp = $('#otpMatch').val();
            var token = $('#otpMatchToken').val();
            var error = false;
            if(otp == ''){
                $('#otpMatch').css('border-color','red');
                $('#otpMatch').siblings(".error").html('This field is empty.');
                error = true;
            }
            if(error){
                return false;
            }

            $.ajax({
                url : "{{action('Admin\Notification\MailTemplateController@verifyOtp')}}",
                method : "post",
                dataType : "json",
                data : {'_token':csrftoken,'otp':otp, 'token':token},
                before_send : function(){
                    $("#showHideLoader").removeClass('d-none');
                },
                success : function (response){
                    if(response.status=='success'){
                        $("#connection_response_1").html(response.msg).addClass('succMsg').removeClass('error');
                        $("#showHideLoader").addClass('d-none');
                    }else{
                        $("#connection_response_1").html(response.msg).addClass('error').removeClass('succMsg');
                        $("#showHideLoader").addClass('d-none');
                    }
                }
            });


        });

    });

</script>
<script type="text/javascript">
    $(document).ready(function() {
    $("input[name$='type']").click(function() {
        var test = $(this).val();

        $("div.desc").hide();
        $("#server_" + test).show();
    });
});
</script>

@stop
