<div class="modal-header">
    <h3 class="modal-title">@lang('admin_common.view_test_line_channel_connection')</h3>
    <span class="fas fa-times close" data-dismiss="modal"></span> 
</div>
 <div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-body">
					<div id="rootwizard">
						<div class="tab-content">
							<div id="tab1">
								<h2 class="hidden">&nbsp;</h2> 
								<div class="row">
									<label for="first_name" class="col control-label">@lang('admin_common.date'): </label>
									<div class="col-sm-10">
									   {{ $date ?? ''}}
									</div>
								</div>
								<div class="row">
									<label for="last_name" class="col control-label">@lang('admin_common.status'): </label>
									<div class="col-sm-10">
										{{ $status ?? ''}}
									</div>
								</div>
								<div class="row margin-top-10">
									<label for="email" class="col control-label">@lang('admin_common.message'): </label>
									<div class="col-sm-10">
										{{ $message ?? ''}}
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