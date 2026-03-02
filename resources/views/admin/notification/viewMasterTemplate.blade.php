<div class="modal-header">
    <h3 class="modal-title">@lang('admin.master_templates')</h3>
    <span class="fas fa-times close" data-dismiss="modal"></span> 
</div>
<div class="modal-body">
    <div id="rootwizard">
        <div class="tab-content">
            <div id="tab1">
                <h2 class="hidden">&nbsp;</h2>                                              
                <div class="row">
                    <label for="first_name" class="col control-label form-group">@lang('admin.name'): </label>
                    <div class="col-sm-10 form-group">
                       {{ $templete_list->name }}
                    </div>
                </div>
                <div class="row margin-top-10">
                    <label class="col control-label">@lang('admin.template'): </label>
                    <div class="col-sm-10">
                        {!! $templete_list->template !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
       