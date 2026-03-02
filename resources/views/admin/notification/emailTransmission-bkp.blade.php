@extends('layouts/admin/default')

@section('title')
    @lang('admin_notification.manage_mail_transmission_method')
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
        <form id="cmsForm" action="{{ action('Admin\Notification\MailTemplateController@updateEmailTransMethod') }}" method="post" class="form-horizontal form-bordered">
            <div class="header-title">
                <h1 class="title">@lang('admin_notification.manage_mail_transmission_method') </h1>
                <div class="float-right">
                    <button type="button" name="edit_server" class="btn btn-dark d-none" id="edit_server">@lang('admin_common.edit')</button>
                    <button type="button" name="cancel_edit" class="btn btn-danger d-none" id="cancel_edit">@lang('admin_common.cancel')</button>
                    <button type="submit" id="save_server" name="submit_type" value="submit" class="btn-save d-none">@lang('admin_common.save')</button>
                </div>
            </div>       
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config')!!}
                    </ul>
                </div>
                
                {{ csrf_field() }}
                
                <div class="form-group striped-col">
                    <label class="col-md-2 control-label" for="form-file-input">@lang('admin_notification.email_system') <i class="strick">*</i></label>
                    <div class="col-md-6">
                    <select id="driver" name="driver" class="form-control email-system" size="1">
                        <option value="">@lang('admin_common.select')</option>
                        @foreach($transMethods->driver as $key => $email_server)
                        <option value="{{$email_server->key}}" @if($default_mail_server->driver==$email_server->key) selected="selected"  @endif >{{strtoupper($email_server->name)}}</option>
                        @endforeach
                    </select>
                    <p class="error error-msg"></p>  
                                                        
                    </div>    
                </div> 
                <div class="form-group striped-col">
                    <label class="col-md-2 control-label" for="form-file-input">@lang('admin_notification.email_provider') <i class="strick">*</i></label>
                    <div class="col-md-6">
                    <select id="provider" name="provider" class="form-control email-system">
                        <option  value="@if($default_mail_server->provider!='') {{$default_mail_server->provider}} @endif" >@if($default_mail_server->provider!='') {{ucfirst($default_mail_server->provider)}} @else {{'Select'}} @endif</option>
                    </select>
                    <p class="error error-msg"></p>  
                                                        
                    </div>    
                </div>
               
                <div class="form-group">
                    <label class="col-md-2 control-label" for="form-text-input">@lang('admin_notification.host_name') <i class="strick">*</i></label>
                    <div class="col-md-6">
                        <input type="text" name="host" id="host" value="@if($default_mail_server->host!='') {{$default_mail_server->host}} @endif" class="form-control" placeholder="Host Name" readonly="readonly">
                        <p class="error error-msg"></p>                                         
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="col-md-2 control-label" for="form-text-input">@lang('admin_notification.encription') <i class="strick">*</i></label>
                    <div class="col-md-6">
                        <select name="encription" class="form-control email-system" id="encription">
                            <option  value="@if($default_mail_server->encription!='') {{$default_mail_server->encription}} @endif" >@if($default_mail_server->encription!='') {{ucfirst($default_mail_server->encription)}} @else {{'Select'}} @endif</option>
                        </select>
                        <p class="error error-msg"></p>                                         
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="col-md-2 control-label" for="form-text-input">@lang('admin_notification.port_number') <i class="strick">*</i></label>
                    <div class="col-md-6">
                        <input type="text" name="port" id="port" value="@if($default_mail_server->port!='') {{$default_mail_server->port}} @endif" class="form-control" placeholder="Enter Port Number" readonly="readonly">
                        <p class="error error-msg"></p>                                         
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="col-md-2 control-label" for="form-text-input">@lang('admin_notification.sender') <i class="strick">*</i></label>
                    <div class="col-md-6">
                        <input type="email" name="email_from" id="email_from" value="@if($default_mail_server->email_from!='') {{$default_mail_server->email_from}} @endif" class="form-control" placeholder="Enter Email">
                        <p class="error error-msg"></p>                                        
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label" for="form-text-input">@lang('admin_notification.username') <i class="strick">*</i></label>
                    <div class="col-md-6">
                        <input type="text" name="username" id="username" value="@if($default_mail_server->username!='') {{$default_mail_server->username}} @endif" class="form-control" placeholder="Enter Username">
                        <p class="error error-msg"></p>                                        
                    </div>
                </div>
                
                
                <div class="form-group">
                    <label class="col-md-2 control-label" for="form-text-input">@lang('admin_notification.password') <i class="strick">*</i></label>
                    <div class="col-md-6">
                        <input type="password" name="password" id="password" value="@if($default_mail_server->password!='') {{$default_mail_server->password}} @endif" class="form-control" placeholder="Enter Password">
                        <p class="error error-msg"></p>                                        
                    </div>
                </div>
                 
                <div class="dynamic_email_trans_container">
                    <div class="form-group">
                        <label class="col-md-2 control-label" for="form-text-input"></label>
                        <div class="col-md-6">
                            <button type="button" name="test_email_server" id="test_email_server" value="test_email_server" class="btn btn-secondary">@lang('admin_notification.test_email_connection')</button>
                            
                        </div>
                    </div>
                </div>
                <div class="dynamic_email_trans_container">
                    <div class="form-group">
                        <label class="col-md-2 control-label" for="form-text-input">@lang('admin_notification.connection_result')</label>
                        <div class="col-md-6">
                            <span id="connection_response"></span>
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
<script src="{{asset('js/normal_froala_editor_model.js')}}"></script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>
<script type="text/javascript" >
    
    $(document).ready( function (){

        var setting_data = {!! json_encode($transMethods) !!};
        var default_timeout = {!! $default_timeout !!};
        var default_value = $(".email-system").val();
        var default_attribute = $(".email-system").attr('id');
        var default_parent_val = $("#driver").val();
        var provider = $("#provider").val();
        
        setServerEmailData(default_value,default_attribute,default_parent_val,provider);

        function setServerEmailData(value,attribute,parent_val,provider){
            var default_provider = "{{$default_mail_server->provider}}";
            $.ajax({
                url : "{{action('Admin\Notification\MailTemplateController@getSelectdDriverData')}}",
                method : "post",
                dataType : "json",
                data : {'_token':csrftoken,'value':value,'parent_val':parent_val,'attribute':attribute,'provider':provider,'setting_data':setting_data},
                before_send : function(){
                    $("#showHideLoader").removeClass('d-none');
                },
                success : function (response){
                    if(response.success=='success'){
                        if(attribute=='driver'){
                            var html_content = "";
                            $.each(response.driverData, function (key, val) {
                                var selected = (key===default_provider)?'selected="selected"':'';
                                html_content+= "<option value='"+key+"' "+selected+">"+val+"</option>";
                            });
                            $("#provider").html(html_content);
                        }else{
                            //alert(attribute);
                            if(attribute!='encription'){
                                $("#host").val(response.driverData.host);
                                if(response.driverData.host==''){
                                    $("#host").prop("readonly", false);
                                }
                                
                                $("#port").val(response.driverData.port);
                                if(response.driverData.port==''){
                                    $("#port").prop("readonly", false);
                                }

                                var encription_content = "";
                                $.each(response.driverData.encription, function (key, val) {
                                    encription_content+= "<option value='"+val.name+"'>"+val.name+"</option>";
                                });

                                $("#encription").html(encription_content);
                                $("#email_from").val(response.driverData.sender);
                                $("#username").val(response.driverData.username);
                                $("#password").val(response.driverData.password); 
                            }else{
                                $("#port").val(response.driverData.port);
                                if(response.driverData.port==''){
                                    $("#port").prop("readonly", false);
                                }
                            }
                            
                        }

                        $("#showHideLoader").addClass('d-none');
                    }
                }
            });
        }

        $(".email-system").on('change', function(){
            var value = $(this).val();
            var attribute = $(this).attr('id');
            var parent_val = $("#driver").val();
            var provider = $("#provider").val();
            setServerEmailData(value,attribute,parent_val,provider);
        });

        $("#test_email_server").on('click', function(){
            
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
                url : "{{action('Admin\Notification\MailTemplateController@testEmailServerConnection')}}",
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
                timeout:default_timeout // set timeout of 30 second
            });
        });

        $("#edit_server").on('click', function(){
             $('.form-control').attr('readonly',false); 
             $('#edit_server').addClass('d-none'); 
             $('#save_server').removeClass('d-none'); 
             $('#cancel_edit').removeClass('d-none'); 
             $("#connection_response").html('').removeClass('error');
             
        });

        $("#cancel_edit").on('click', function(){
             $('.form-control').attr('readonly',true); 
             $('#edit_server').removeClass('d-none'); 
             $('#save_server').removeClass('d-none'); 
             $('#cancel_edit').addClass('d-none'); 
             
             $("#connection_response").html('').removeClass('error');
             

        });
    });

</script>        
@stop
