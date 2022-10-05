@extends('layouts/admin/default')

@section('title')
		@lang('admin_notification.email_event_management')
@stop

@section('header_styles')

		<!--page level css -->

		<link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
		<!-- end of page level css -->
		
@stop

@section('content')
		<div class="content">
				<div class="header-title">
						<h1 class="title">@lang('admin_notification.email_event_management')</h1>
				</div>
							 
				<!-- Main content -->         
					 
				<div class="content-wrap ">
						<div class="breadcrumb">
							<ul class="bredcrumb-menu">
								{!!getBreadcrumbAdmin('config','mail','list')!!}
							</ul>
						</div>
						@if(Session::has('succMsg'))
						<div class="alert alert-success alert-dismissable margin5">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
								<strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
						</div>
						@endif  
						<table class="table table-bordered mail-type-table" id="table">
								<thead>
										<tr class="filters">
												<th>@lang('admin_common.slno')</th>
												<th style="display:none">@lang('admin_common.id')</th>
												<th>@lang('admin_notification.event_name')</th>
												<th>@lang('cms.description')</th>
												<th>@lang('admin_notification.notification_type')</th>
												<th>@lang('admin_common.action')</th>
										</tr>
								</thead>
								<tbody>

								@foreach ($templete_type_list as $key => $templete_type)
								
										<tr>
												<td>{{ ++$key }}</td>
												<td style="display:none">{{ $templete_type->id }}</td>

												<td class="primary-color">{{ $templete_type->mail_type }}</td>
												<td>
													@if(isset($templete_type->GetNotificationEventDetails->mail_desc))
															{!! $templete_type->GetNotificationEventDetails->mail_desc !!}
													@endif
													<a class="link-primarys btn btn-dark" href="{{ action('Admin\Notification\MailTemplateController@editevent',[$templete_type->id])}}" >@lang('admin_common.edit') </a>
												</td>
												<td>
													
										 		<a class="link-primarys btn btn-primary" href="#"  data-toggle="modal" data-target="#templatetype_{{$templete_type->id}}">
													 @lang('admin_notification.add_type')
												</a>
													

														
										<div id="templatetype_{{$templete_type->id}}" class="modal fade notification" role="dialog">
											<div class="modal-dialog modal-lg">

												<!-- Modal content-->
												<div class="modal-content">
													<div class="modal-header">
														<h2 class="modal-title">@lang('admin_notification.notification_type')</h2> 
														<span class="fas fa-times" data-dismiss="modal"></span>
																															 
													</div>
													<div class="modal-body">
														
														<form id="formsaveTemplateType_{{$templete_type->id}}">

													 
															<div class="form-group">
																@php( $temptype = !empty($templete_type->noti_type)? unserialize($templete_type->noti_type) : [])
																@foreach($templateType as $key=>$value)

																		<label class="check-wrap">
																			<input type="checkbox" name="template_type[]" value="{{$key}}"  @if(in_array($key,$temptype))checked="checked" @endif >
																			<span class="chk-label">{{$value}}</span>
																		</label>
															
																	@endforeach
															</div> 
															<div class="saveTemplateType">
																 <a class="btn btn-primary" rel="{{$templete_type->id}}">@lang('admin_common.save')</a>
															</div>   
											 
													 </form>
													</div>
												</div>
											</div>
										</div>
									</td>

									<td class="text-nowrap">
								@if($permission_arr['edit'] === true)
							
								@foreach($templateType as $key=>$value) 

									@if(in_array($key,$temptype))  
										<a class="btn btn-secondary" href="{{ action('Admin\Notification\MailTemplateController@editTemplateType', [$templete_type->id, $key]) }}">{{$value}}</a>
									@endif 

								@endforeach

								@endif                                    
										<!--a class="btn btn-secondary" href="{{ action('Admin\Notification\MailTemplateController@show', $templete_type->id) }}">
											 Detail
										</a-->                                  
									</td>
								</tr>										
								 @endforeach 
								 
							</tbody>
						</table>
				</div>
		</div>
@stop

@section('footer_scripts')

		<!-- begining of page level js -->

		<script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
		<script>
		$(document).ready(function() {
				$('#table').dataTable();
		});
		</script>
		<script>
		var action = '{{ action('Admin\Notification\MailTemplateController@addtemplatetype')}}';
		$(document).ready(function() {
				$('#table').dataTable();

				$(document).on('click', '.saveTemplateType a', function(e){
								e.preventDefault();
								var id = $(this).attr('rel');
								var formdata = jQuery('form#formsaveTemplateType_'+id).serialize();
								$.ajax({
										type: 'POST',
										async: false,
										url: action,
										data: '_token=' + window.Laravel.csrfToken + '&noti_event_id=' + id + '&' + formdata,
										
										success: function (data) {
												if(data == 1){

														
													setTimeout(function () {
																window.location.reload(); // = window.location;
													 }, 2000);

				 
														
												}else{


													 


												}
										}

								});
								 


				})

		});
		</script>
		<!-- end of page level js -->
		
@stop
