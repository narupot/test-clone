@extends('layouts/admin/default')

@section('title')
    @lang('admin_notification.manage_line_transmission_method')
@stop

@section('header_styles')

<!--page level css -->
<link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
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
		@elseif(Session::has('errorMsg'))
        <div class="alert alert-danger alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
        </div>  
        @endif 
        
            <div class="header-title">
                <h1 class="title">@lang('admin_notification.manage_line_transmission_method') </h1>
                {{--<div class="float-right">
                    <button type="button" name="edit_server" class="btn btn-dark d-none" id="edit_server">@lang('admin_common.edit')</button>
                    <button type="button" name="cancel_edit" class="btn btn-danger d-none" id="cancel_edit">@lang('admin_common.cancel')</button>
                    <button type="submit" id="save_server" name="submit_type" value="submit" class="btn btn-primary">@lang('admin_common.save')</button>
                </div>--}}
            </div>       
            <div class="content-wrap column-modal clearfix">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config')!!}
                    </ul>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <ul class="nav nav-tabs chtabs">
                            <li class="nav-item"><a class="nav-link"  href="{{action('Admin\Notification\MailTemplateController@manageEmailTransmission')}}">@lang('admin_notification.email')</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{action('Admin\Notification\MailTemplateController@manageSMSTransmission')}}">@lang('admin_notification.sms_notification')</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{action('Admin\Notification\MailTemplateController@manageOTPTransmission')}}">@lang('admin_notification.sms_otp')</a></li>
                            <li class="nav-item"><a class="nav-link show active" href="{{action('Admin\Notification\MailTemplateController@manageLineTransmission')}}">@lang('admin_notification.line_notification')</a></li>
                        </ul>
                        <div class="tab-content">
							<!--<h2 class="title">@lang('admin_notification.manage_line_channel') </h2>-->
							<div class="float-right">
								<a class="link-primarys btn btn-primary add_templete" href="javascript:void(0)" data-attr="{{ action('Admin\Notification\MailTemplateController@addLineChannel') }}" data-target="#add-modal"> 
									@lang('admin_common.create')
								</a>
							</div>
							<table class="table table-bordered" id="table">
								<thead>
									<tr class="filters">
										<th>@lang('admin_common.sno')</th>
										<th>@lang('admin_notification.token')</th>
										<th>@lang('admin_notification.name')</th>
										<th>@lang('admin_notification.remark')</th>
										<th>@lang('admin_notification.message')</th>
										<th>@lang('admin_notification.created_at')</th>
										<th>@lang('admin_common.updated_at')</th>
										<th>@lang('admin_common.status')</th>
										<th>@lang('admin_common.action')</th>
									</tr>
								</thead>
								<tbody>
								@if(!empty($default_line_server))
								@foreach ($default_line_server as $key => $line)
								
									<tr>
										<td>{{ ++$key }}</td>
										<td>{{ $line->token ?? ''}}</td>
										<td>{{ $line->name ?? '' }}</td>
										<td>{{ $line->remark ?? '' }}</td>
										<td>{{ $line->message ?? '' }}</td>
										<td>{{ getDateFormat($line->created_at, '1') }}</td>                                    
										<td>{{ getDateFormat($line->updated_at, '1') }}</td>
										<td>
											@if(!empty($line->status=="1"))
												@lang('admin_common.active')
											@else
												@lang('admin_common.inactive')
											@endif
										</td>
										<td>
											<a class="link-primarys btn btn-primary view_templete" href="javascript:void(0)" data-attr="{{ action('Admin\Notification\MailTemplateController@testLineChannel', $line->id) }}" data-target="#preview-modal" data-token="{{$line->token}}" data-message="{{$line->message}}">
												@lang('admin_notification.test_line_channel')
											</a>
											<a class="link-primarys btn btn-dark edit_templete" href="javascript:void(0)" data-attr="{{ action('Admin\Notification\MailTemplateController@editLineChannel', $line->id) }}" data-target="#edit-modal">
												@lang('admin_common.edit')
											</a>
											<a class="link-primarys btn btn-danger" href="{{ action('Admin\Notification\MailTemplateController@deleteLineTransmission', $line->id) }}" onclick="return confirm('Are you sure to delete this line.');">
											   @lang('admin_common.delete')
											</a> 	
										</td>
									</tr>
								@endforeach
								@endif
      							</tbody>
							</table>
						
						{{--<h2 class="title">@lang('admin_notification.add_line_channel') </h2>		
						<form id="cmsForm" action="{{ action('Admin\Notification\MailTemplateController@storeLineChannel') }}" method="post" class="form-horizontal form-bordered">		
                            {{ csrf_field() }}
							<div id="server_email" class="desc tab-pane fade show active">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <label for="form-text-input">@lang('admin_notification.token')<i class="strick">*</i></label>
                                            <input type="text" name="line_token" id="line_token" value="" class="form-control" placeholder="Enter Token">
                                            <p class="error error-msg"></p>
                                        </div>
                                    </div>
									<div class="col-sm-5">
                                        <div class="form-group">
                                            <label for="form-text-input">@lang('admin_notification.name')<i class="strick">*</i></label>
                                            <input type="text" name="name" id="name" value="" class="form-control" placeholder="Enter Name">
                                            <p class="error error-msg"></p>
                                        </div>
                                    </div>
									<div class="col-sm-5">
                                        <div class="form-group">
                                            <label for="form-text-input">@lang('admin_notification.message')<i class="strick">*</i></label>
                                            <input type="text" name="message" id="message" value="" class="form-control" placeholder="Enter Message">
                                            <p class="error error-msg"></p>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <label for="form-text-input">@lang('admin_notification.remark')<i class="strick">*</i></label>
                                            <textarea name="remark" id="remark" class="form-control" placeholder="Enter Remark"></textarea>
                                            <p class="error error-msg"></p>
                                        </div>
                                    </div>
									<div class="col-sm-2">
										<div class="form-group">
											<label>&nbsp;</label>
											<button type="submit" name="add_line" class="btn btn-primary">@lang('admin_common.add')</button>
										</div>
									</div>
									<div class="dynamic_email_trans_container">
										<div class="form-group">
											<label for="form-text-input"></label>
											<button type="button" name="test_line_server" id="test_line_server" value="test_line_server" class="btn btn-secondary">@lang('admin_notification.test_line_connection')</button>
										</div>
									</div>
                                </div>

                                <div class="form-row">
                                    <div class="col-sm-6">
                                        <div class="dynamic_email_trans_container">
                                            <div class="form-group">
                                                <label for="form-text-input">@lang('admin_notification.connection_result')</label>
                                                <span id="connection_response"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                            
                            </div>  
						</form>--}}
						
                    </div>
                </div>
            </div>
        </div>
    </div>
	
	<div id="preview-modal" class="modal fade" role="dialog"> 
        <div class="modal-dialog modal-lg">
           <div class="modal-content">
               <span class="loader loader-medium text-center"></span>
            </div>
        </div>
    </div>
	
	<div id="edit-modal" class="modal fade" role="dialog"> 
        <div class="modal-dialog modal-lg">
           <div class="modal-content">
               <span class="loader loader-medium text-center"></span>
            </div>
        </div>
    </div>
	
	<div id="add-modal" class="modal fade" role="dialog"> 
        <div class="modal-dialog modal-lg">
           <div class="modal-content">
              <span class="loader loader-medium text-center"></span>
            </div> 
        </div>
    </div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
<script src="{{ Config('constants.page_js_url') }}validatenotification.js" type="text/javascript"></script>
<script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
<script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
<!-- end of page level js --> 

@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
</script>
<script src="{{asset('js/normal_froala_editor_model.js')}}"></script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>
<script type="text/javascript" >
    
    /*$(document).ready( function (){
		
        $("#test_line_server").on('click', function(){
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
                url : "{{action('Admin\Notification\MailTemplateController@testLineServerConnection')}}",
                method : "post",
                dataType : "json",
                data : {'_token':csrftoken,'form_data':f_data},
                beforeSend : function(){
                    $("#showHideLoader").removeClass('d-none');
                },
                success : function (response){
                   
                   if(response.status=="400"){
                       $("#connection_response").html('['+response.date+'] '+response.message).addClass('error').removeClass('succMsg');
                   }else{
                      $("#connection_response").html(response.message+'  ['+response.date+']').addClass('succMsg').removeClass('error');
                      //$('.form-control').attr('readonly',true);
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
                //timeout:default_timeout // set timeout of 30 second
            });
        });
    });*/

</script>
<script type="text/javascript">
$(document).ready(function() {
	$('#table').dataTable();
});

$(document).ready(function() {
	$('.view_templete').on('click', function(e){
	  e.preventDefault();
	  $('#preview-modal').modal('show').find('.modal-content').load($(this).attr('data-attr'));
	}); 

    //modal closed 
    $('#preview-modal').on('hide.bs.modal', function(){
        $('#preview-modal .modal-content').empty();
    });
	
	$('.edit_templete').on('click', function(e){
	  e.preventDefault();
	  $('#edit-modal').modal('show').find('.modal-content').load($(this).attr('data-attr'));
	});
	
	$('.add_templete').on('click', function(e){
	  e.preventDefault();
	  $('#add-modal').modal('show').find('.modal-content').load($(this).attr('data-attr'));
	});	
	
    $("input[name$='type']").click(function() {
        var test = $(this).val();
        $("div.desc").hide();
        $("#server_" + test).show();
    });	
});
</script>

@stop
