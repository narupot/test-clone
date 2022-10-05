<div class="modal-header">
    <h3 class="modal-title">@lang('admin_common.edit_line_channel')</h3>
    <span class="fas fa-times close" data-dismiss="modal"></span> 
</div>
<form id="cmsForm" action="{{ action('Admin\Notification\MailTemplateController@updateLineChannel', $line_channel->id) }}" method="post">
{{ csrf_field() }}
{{ method_field('post') }}
<input type="hidden" name="line_id" value="{{$line_channel->id}}">
 <div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-body">
					<div id="rootwizard">
						<div class="tab-content">
							<div id="server_email" class="desc tab-pane fade show active">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <label for="form-text-input">@lang('admin_notification.token')<i class="strick">*</i></label>
                                            <input type="text" name="token" id="token" value="{{$line_channel->token ?? ''}}" class="form-control" placeholder="Enter Token">
                                            <p class="error error-msg"></p>
                                        </div>
                                    </div>
									<div class="col-sm-5">
                                        <div class="form-group">
                                            <label for="form-text-input">@lang('admin_notification.name')<i class="strick">*</i></label>
                                            <input type="text" name="name" id="name" value="{{$line_channel->name ?? ''}}" class="form-control" placeholder="Enter Name">
                                            <p class="error error-msg"></p>
                                        </div>
                                    </div>
									<div class="col-sm-5">
                                        <div class="form-group">
                                            <label for="form-text-input">@lang('admin_notification.message')<i class="strick">*</i></label>
                                            <input type="text" name="message" id="message" value="{{$line_channel->message ?? ''}}" class="form-control" placeholder="Enter Message">
                                            <p class="error error-msg"></p>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <label for="form-text-input">@lang('admin_notification.remark')<i class="strick">*</i></label>
                                            <textarea name="remark" id="remark" class="form-control" placeholder="Enter Remark">{{$line_channel->remark ?? ''}}</textarea>
                                            <p class="error error-msg"></p>
                                        </div>
                                    </div>
									<div class="dynamic_email_trans_container">
										<div class="form-group" style="margin-left:15px;">
											<button type="submit" name="update" value="update" class="btn btn-primary">@lang('admin_common.update')</button>
										</div>
									</div>
                                </div>                         
                            </div> 
							
						</div>
					</div>                                   
                </div>
            </div>
        </div>
    </div>
    <!--row end-->
</div>
</form>